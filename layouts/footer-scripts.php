<!-- Vendor js -->
<script src="assets/js/vendor.min.js"></script>

<script src="assets/vendor/jquery-toast-plugin/jquery.toast.min.js"></script>

<!-- Toastr Demo js -->
<script src="assets/js/pages/demo.toastr.js"></script>

<script>
    (function(c) {
        "use strict";

        function t() {}
        t.prototype.send = function(t, o, i, e, n, a, s, r) {
            t = {
                heading: t,
                text: o,
                position: i,
                loaderBg: e,
                icon: n,
                hideAfter: a || 3000,
                stack: s || 1,
                showHideTransition: r || "fade"
            };
            c.toast().reset("all");
            c.toast(t);
        };
        c.NotificationApp = new t();
        c.NotificationApp.Constructor = t;
    })(window.jQuery);

    $(document).ready(function() {
        <?php
        if (isset($_SESSION['message'])) {
            foreach ($_SESSION['message'] as $message) {
                $type = $message['type'];
                $content = $message['content'];
                $icon = $type; // Assuming the type corresponds to the icon type
                echo "window.jQuery.NotificationApp.send('$type', '$content', 'top-right', 'rgba(0,0,0,0.2)', '$icon');";
            }
            unset($_SESSION['message']);
        }
        ?>
    });
</script>


<!-- Datatables js -->
<script src="assets/vendor/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/vendor/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
<script src="assets/vendor/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/vendor/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js"></script>
<script src="assets/vendor/datatables.net-fixedcolumns-bs5/js/fixedColumns.bootstrap5.min.js"></script>
<script src="assets/vendor/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js"></script>
<script src="assets/vendor/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="assets/vendor/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js"></script>
<script src="assets/vendor/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="assets/vendor/datatables.net-buttons/js/buttons.flash.min.js"></script>
<script src="assets/vendor/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="assets/vendor/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
<script src="assets/vendor/datatables.net-select/js/dataTables.select.min.js"></script>

<!-- Datatable Demo Aapp js -->
<script src="assets/js/pages/demo.datatable-init.js"></script>


<!-- Daterangepicker js -->
<script src="assets/vendor/daterangepicker/moment.min.js"></script>
<script src="assets/vendor/daterangepicker/daterangepicker.js"></script>

<!-- Apex Charts js -->
<script src="assets/vendor/apexcharts/apexcharts.min.js"></script>

<!-- Vector Map js -->
<script src="assets/vendor/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="assets/vendor/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>