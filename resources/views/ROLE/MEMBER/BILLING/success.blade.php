<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Pembayaran Berhasil</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #1e3c72, #2a5298);
      color: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      padding: 20px;
    }
    .container {
      background: #fff;
      color: #1e3c72;
      border-radius: 12px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.15);
      max-width: 480px;
      width: 100%;
      padding: 40px 30px;
      text-align: center;
      animation: fadeInUp 0.8s ease forwards;
    }
    .icon-success {
      font-size: 72px;
      color: #28a745;
      margin-bottom: 25px;
      animation: popIn 0.6s ease forwards;
    }
    h1 {
      margin-bottom: 12px;
      font-weight: 600;
      font-size: 2.2rem;
    }
    p {
      font-weight: 400;
      font-size: 1.1rem;
      line-height: 1.6;
      color: #4b4b4b;
    }
    .btn-home {
      margin-top: 30px;
      display: inline-block;
      background-color: #1e3c72;
      color: #fff;
      text-decoration: none;
      padding: 12px 28px;
      font-weight: 600;
      font-size: 1rem;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(30, 60, 114, 0.4);
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }
    .btn-home:hover {
      background-color: #163059;
      box-shadow: 0 6px 20px rgba(30, 60, 114, 0.7);
    }
    @keyframes popIn {
      0% { transform: scale(0.3); opacity: 0; }
      80% { transform: scale(1.1); opacity: 1; }
      100% { transform: scale(1); }
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @media (max-width: 520px) {
      .container { padding: 30px 20px; }
      h1 { font-size: 1.8rem; }
    }
  </style>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

  <div class="container" role="main" aria-labelledby="success-heading" aria-describedby="success-message">
    <div class="icon-success" aria-hidden="true">
      <i class="fas fa-check-circle"></i>
    </div>
    <h1 id="success-heading">Pembayaran Berhasil!</h1>
    <p id="success-message">Terima kasih telah melakukan pembayaran. Transaksi Anda telah kami terima dengan baik.</p>
    <a id="btnHome" href="/" class="btn-home" role="button" aria-label="Kembali ke halaman utama">Lihat Detail Pembayaran</a>
  </div>

  <script>
    // Ambil order_id dari query string
    const params = new URLSearchParams(window.location.search);
    const orderId = params.get('order_id');

    // Jika order_id ditemukan, arahkan tombol ke halaman invoice
    if (orderId) {
      document.getElementById('btnHome').href = `/api/invoice/${orderId}`;
    }
  </script>

</body>
</html>
