<?php

namespace App\Http\Controllers;

use App\Models\OLT;
use RouterOS\Query;
use RouterOS\Client;
use App\Models\Mikrotik;
use App\Helpers\ActivityLogger;

use Illuminate\Http\Request;

class OLTController extends Controller
{
    public function epon()
    {
        $user = auth()->user();
        $userId = $user->id;
    
        // Tentukan query Mikrotik berdasarkan role user
        if ($user->role == 'member') {
            // Jika member, ambil Mikrotik berdasarkan user_id
            $dataSite = Mikrotik::where('user_id', $userId)->get();
        } else {
            // Jika bukan member (misal teknisi), ambil Mikrotik berdasarkan parent_id
            $dataSite = Mikrotik::where('user_id', $user->parent_id)->get();
        }

        // Ambil data OLT berdasarkan mikrotik_id
        $dataOLT = OLT::whereIn('mikrotik_id', $dataSite->pluck('id'))->get();

        return view('ROLE.MEMBER.OLT.EPON.epon', compact('dataSite', 'dataOLT'));
    }
    public function tambah_epon(Request $req)
{
    $namaOLT = $req->input('namaOLT');
    $ipOLT = $req->input('ipOLT');
    $portOLT = $req->input('portOLT');
    $ipMikrotik = $req->input('site');

    $user = auth()->user();
    $userId = $user->id;

    try {
        // Validasi input
        if (empty($ipOLT) || empty($portOLT) || empty($ipMikrotik)) {
            session()->flash('error', "IP OLT, Port OLT, atau Site tidak boleh kosong.");
            return redirect()->back();
        }

        // Tentukan query Mikrotik berdasarkan role user
        if ($user->role == 'member') {
            // Jika member, ambil Mikrotik berdasarkan user_id
            $mikrotik = Mikrotik::where('user_id', $userId)->where('remote_ip', $ipMikrotik)->first();
        } else {
            // Jika bukan member (misal teknisi), ambil Mikrotik berdasarkan parent_id
            $mikrotik = Mikrotik::where('user_id', $user->parent_id)->where('remote_ip', $ipMikrotik)->first();
        }

        if (!$mikrotik) {
            session()->flash('error', "IP VPN tidak ditemukan.");
            return redirect()->back();
        }

        $routerUsername = $mikrotik->username;

        // Konfigurasi koneksi ke MikroTik
        $client = new Client([
            'host' => 'id-1.aqtnetwork.my.id',
            'user' => 'admin',
            'pass' => 'bakpao1922',
        ]);

        // Ambil semua aturan NAT
        $natListQuery = new Query('/ip/firewall/nat/print');
        $natListResponse = $client->query($natListQuery)->read();

        $existingPorts = [];

        // Loop data NAT dan cek dst-port dalam range 56000-57999
        foreach ($natListResponse as $nat) {
            if (isset($nat['dst-port']) && is_numeric($nat['dst-port'])) {
                $port = (int) $nat['dst-port'];
                if ($port >= 56000 && $port <= 57999) {
                    $existingPorts[] = $port;
                }
            }
        }

        // Cari port yang tersedia dalam range 56000-57999
        $newPort = 56000;
        while (in_array($newPort, $existingPorts) && $newPort <= 57999) {
            $newPort++;
        }

        // Jika tidak ada port yang tersedia dalam rentang tersebut
        if ($newPort > 57999) {
            session()->flash('error', "Semua port dalam rentang 56000-57999 telah digunakan.");
            return redirect()->back();
        }

        // Tentukan aturan NAT untuk IP OLT
        $natQueryOLT = new Query('/ip/firewall/nat/add');
        $natQueryOLT->equal('chain', 'dstnat')
            ->equal('protocol', 'tcp')
            ->equal('dst-port', $newPort)
            ->equal('dst-address-list', 'ip-public')
            ->equal('action', 'dst-nat')
            ->equal('to-addresses', $mikrotik->remote_ip)
            ->equal('to-ports', $newPort)
            ->equal('comment', 'BILLER_' . $routerUsername . '_OLT');

        $natResponseOLT = $client->query($natQueryOLT)->read();

        // Cek jika ada kesalahan dalam response NAT
        if (isset($natResponseOLT['!trap'])) {
            session()->flash('error', $natResponseOLT['!trap'][0]['message']);
            return redirect()->back();
        }

        // Menyimpan data ke database dengan mikrotik_id
        OLT::create([
            'mikrotik_id' => $mikrotik->id,
            'namaolt' => $namaOLT,
            'ipolt' => $ipOLT,
            'portolt' => $portOLT,
            'ipvpn' => $mikrotik->remote_ip,
            'portvpn' => $newPort,
        ]);
        ActivityLogger::log('Menambahkan OLT', 'Nama OLT : ' . $namaOLT);

        session()->flash('success', "Konfigurasi OLT Berhasil Ditambahkan!");
        return redirect()->back();
    } catch (ClientException $e) {
        session()->flash('error', "Gagal terhubung ke MikroTik: " . $e->getMessage());
        return redirect()->back();
    } catch (\Exception $e) {
        session()->flash('error', "Terjadi kesalahan: " . $e->getMessage());
        return redirect()->back();
    }
}





}
