@if(auth()->user()->role == 'member')
<div class="row">
    @php
        $infoBoxes = [
            ['icon' => 'fas fa-users', 'color' => 'info', 'label' => 'Pelanggan', 'value' => $totalPelanggan],
            ['icon' => 'fas fa-thumbs-up', 'color' => 'success', 'label' => 'Online', 'value' => $totalOnline],
            ['icon' => 'fas fa-thumbs-down', 'color' => 'danger', 'label' => 'Offline', 'value' => $totalOffline],
            ['icon' => 'fas fa-dizzy', 'color' => 'warning', 'label' => 'Isolir', 'value' => $totalIsolir],
            ['icon' => 'fas fa-network-wired', 'color' => 'secondary', 'label' => 'Router', 'value' => $totalRouter],
            ['icon' => 'fas fa-server', 'color' => 'secondary', 'label' => 'Radius', 'value' => '-'],
            ['icon' => 'fas fa-microchip', 'color' => 'success', 'label' => 'OLT', 'value' => $totalOlt],
            ['icon' => 'fas fa-id-card', 'color' => 'danger', 'label' => 'Voucher', 'value' => '2,000'],
        ];
    @endphp

    @foreach($infoBoxes as $box)
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
            <div class="info-box h-100">
                <span class="info-box-icon bg-{{ $box['color'] }} elevation-1">
                    <i class="{{ $box['icon'] }}"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ $box['label'] }}</span>
                    <span class="info-box-number">{{ $box['value'] }}</span>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
