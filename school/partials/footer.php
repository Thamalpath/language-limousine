<!--start overlay-->
<div class="overlay btn-toggle"></div>
  <!--end overlay-->

    <!--start footer-->
    <footer class="page-footer mt-auto">
        <p class="mb-0">Copyright © 2024. All right reserved.</p>
    </footer>
    <!--end footer-->

    <!--bootstrap js-->
    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <!--plugins-->
    <script src="assets/js/jquery.min.js"></script>
    <!--plugins-->
    <script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
    <script src="assets/plugins/metismenu/metisMenu.min.js"></script>
    <script src="assets/plugins/apexchart/apexcharts.min.js"></script>
    <script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
    <script src="assets/plugins/peity/jquery.peity.min.js"></script>
    <script>
        $(".data-attributes span").peity("donut")
    </script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/dashboard1.js"></script>
    <script>
        new PerfectScrollbar(".user-list")
    </script>

    <script src="assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
	<script src="assets/plugins/datatable/js/dataTables.bootstrap5.min.js"></script>
	<script>
		$(document).ready(function() {
			$('#example').DataTable();
		  } );
	</script>

    <!-- Initialize Notyf.js -->
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script>
        // Initialize Notyf
        const notyf = new Notyf({
            duration: 3000,
            position: {
                x: 'right',
                y: 'top',
            },
        });

        // Submit form on pressing Enter key
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.form.submit();
                }
            });
        });

        <?php
        // Display success or error alerts
        if (isset($_SESSION['success'])) {
            echo "notyf.success('{$_SESSION['success']}');";
            unset($_SESSION['success']);
        }

        if (isset($_SESSION['error'])) {
            echo "notyf.error('{$_SESSION['error']}');";
            unset($_SESSION['error']);
        }
        ?>
    </script>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
	<script>

		$(".time-picker").flatpickr({
				enableTime: true,
				noCalendar: true,
				dateFormat: "H:i",
			});
    </script>

    <script src="assets/plugins/Drag-And-Drop/dist/imageuploadify.min.js"></script>
    <script>
		$(document).ready(function () {
			$('#image-uploadify').imageuploadify();
		})
	</script>

</body>

</html>