<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Biller - Your Billing Solution | Dashboard</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="{{asset('assetlogin/plugins/fontawesome-free/css/all.min.css')}}">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="{{asset('assetlogin/plugins/overlayScrollbars/css/OverlayScrollbars.min.css')}}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{asset('assetlogin/dist/css/adminlte.min.css')}}">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
  <style>
    /* Warna dropdown 'Tampilkan entri' */
    

    /* Dropdown entries background & text color */
    .dataTables_length select {
        background-color: #007bff00; /* Biru */
        color: rgb(0, 0, 0); /* Teks putih */
        border: 1px solid #0056b3;
        border-radius: 5px;
        padding: 5px;
    }

    /* Hover efek dropdown */
    .dataTables_length select:hover {
        background-color: #00000000;
        color: rgb(0, 0, 0);
    }

    /* Agar dropdown tidak full putih saat diklik */
    .dataTables_length select:focus {
        background-color: #323233;
        color: white;
        outline: none;
    }
</style>
</head>