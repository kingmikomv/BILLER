<x-dhs.head />

<body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">
        <x-dhs.preload />
        <x-dhs.sumin.nav />
        <x-dhs.sidebar />

        <div class="content-wrapper" style="margin-bottom: 50px">
            <x-dhs.sumin.content-header-sumin />

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header border-transparent">
                                    <h3 class="card-title">Spinner Pemenang</h3>
                                </div>
                                <div class="card-body table-responsive">
                                    <div class="text-center">
                                        <div id="wheel" class="spinner">
                                            @foreach($activeConnections as $connection)
                                                <div class="spinner-item">{{ $connection['name'] }}</div>
                                            @endforeach
                                        </div>
                                        <button id="spinBtn" class="btn btn-primary mt-3">Cari Pemenang</button>
                                        <h2 id="winner" class="mt-4"></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <x-dhs.footer />
    </div>

    <x-dhs.scripts />

    <style>
        .spinner {
            position: relative;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            border: 5px solid #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            background: #444;
        }

        .spinner-item {
            position: absolute;
            width: 100%;
            text-align: center;
            font-size: 16px;
            color: white;
            font-weight: bold;
            transform-origin: center;
        }

        #winner {
            font-size: 24px;
            font-weight: bold;
            color: #ffcc00;
        }
    </style>

    <script>
        document.getElementById("spinBtn").addEventListener("click", function() {
            let spinner = document.getElementById("wheel");
            let items = document.querySelectorAll(".spinner-item");
            let randomIndex = Math.floor(Math.random() * items.length);
            let winnerText = items[randomIndex].textContent;

            spinner.style.transition = "transform 3s ease-out";
            let rotateAngle = 3600 + (randomIndex * (360 / items.length));
            spinner.style.transform = `rotate(${rotateAngle}deg)`;

            setTimeout(() => {
                document.getElementById("winner").textContent = "Pemenang: " + winnerText;
            }, 3000);
        });
    </script>
</body>

</html>
