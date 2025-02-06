@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Koneksi Berhasil',
        text: "{!! session('success') !!}",
        confirmButtonText: 'OK'
    });
</script>
@endif

@if (session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Koneksi Gagal',
        text: "{{ session('error') }}",
        confirmButtonText: 'Coba Lagi'
    });
</script>
@endif
