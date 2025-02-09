@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: "{!! session('success') !!}",
        confirmButtonText: 'OK'
    });
</script>
@endif

@if (session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: "{{ session('error') }}",
        confirmButtonText: 'Coba Lagi'
    });
</script>
@endif
