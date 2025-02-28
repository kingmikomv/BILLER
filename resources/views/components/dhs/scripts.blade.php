<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<script src="{{asset('assetlogin/plugins/jquery/jquery.min.js')}}"></script>
<!-- Bootstrap -->
<script src="{{asset('assetlogin/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<!-- overlayScrollbars -->
<script src="{{asset('assetlogin/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js')}}"></script>
<!-- AdminLTE App -->
<script src="{{asset('assetlogin/dist/js/adminlte.js')}}"></script>

<!-- PAGE PLUGINS -->
<!-- jQuery Mapael -->
<script src="{{asset('assetlogin/plugins/jquery-mousewheel/jquery.mousewheel.js')}}"></script>
<script src="{{asset('assetlogin/plugins/raphael/raphael.min.js')}}"></script>
<script src="{{asset('assetlogin/plugins/jquery-mapael/jquery.mapael.min.js')}}"></script>
<script src="{{asset('assetlogin/plugins/jquery-mapael/maps/usa_states.min.js')}}"></script>
<!-- ChartJS -->
<script src="{{asset('assetlogin/plugins/chart.js/Chart.min.js')}}"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script>
    $(document).ready(function () {
        $('.nav-item.has-treeview > a').on('click', function () {
            // Tutup semua submenu yang terbuka selain yang diklik
            $('.nav-item.has-treeview').not($(this).parent()).removeClass('menu-open');
            $('.nav-item.has-treeview').not($(this).parent()).children('.nav-treeview').slideUp();
            
            // Toggle submenu dari menu yang diklik
            $(this).parent().toggleClass('menu-open');
            $(this).siblings('.nav-treeview').slideToggle();
        });
    });
    </script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
    let activeMenus = document.querySelectorAll(".nav-item.menu-open");
    activeMenus.forEach(function (menu) {
        menu.classList.remove("menu-open");
    });

    let activeLinks = document.querySelectorAll(".nav-link.active");
    activeLinks.forEach(function (link) {
        let parent = link.closest(".nav-item");
        if (parent) {
            parent.classList.add("menu-open");
        }
    });
});
</script>    