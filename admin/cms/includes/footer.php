<?php
declare(strict_types=1);
?>
        <!-- footer start-->
        <footer class="footer">
          <div class="container-fluid">
            <div class="row">
              <div class="col-md-12 footer-copyright text-center">
                <p class="mb-0">Copyright <?php echo date('Y'); ?> © <?php echo e($config['app_name']); ?>  </p>
              </div>
            </div>
          </div>
        </footer>
      </div>
    </div>
    <!-- latest jquery-->
    <script src="../assets/js/jquery.min.js"></script>
    <!-- Bootstrap js-->
    <script src="../assets/js/bootstrap/bootstrap.bundle.min.js"></script>
    <!-- feather icon js-->
    <script src="../assets/js/icons/feather-icon/feather.min.js"></script>
    <script src="../assets/js/icons/feather-icon/feather-icon.js"></script>
    <!-- scrollbar js-->
    <script src="../assets/js/scrollbar/simplebar.js"></script>
    <script src="../assets/js/scrollbar/custom.js"></script>
    <!-- Config js-->
    <script src="../assets/js/config.js"></script>
    <!-- Plugins js start-->
    <script src="../assets/js/sidebar-menu.js"></script>
    <script src="../assets/js/slick/slick.min.js"></script>
    <script src="../assets/js/slick/slick.js"></script>
    <script src="../assets/js/header-slick.js"></script>
    <?php echo $extra_js ?? ''; ?>
    <!-- Plugins js Ends-->
    <!-- Theme js-->
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/theme-customizer/customizer.js"></script>
    <script>
      $(document).ready(function() {
        // Auto-generate slug from title
        $('#title').on('input', function() {
          if ($('#slug').data('auto') !== false) {
            let slug = $(this).val().toLowerCase()
              .replace(/[^\w\s-]/g, '')
              .replace(/[\s_-]+/g, '-')
              .replace(/^-+|-+$/g, '');
            $('#slug').val(slug);
          }
        });
        $('#slug').on('input', function() {
          $(this).data('auto', false);
        });
      });
    </script>
  </body>
</html>
