<!-- <?php echo $this->Session->flash(); ?> -->
<!-- header -->
<!DOCTYPE html>
<html>
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EverydayWinner</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700,800" rel="stylesheet">
    <?php 
        // debug($this->request->webroot.'files/ew/css/bootstrap.min.css');
        // die();
    ?>
    <link rel="stylesheet" type="text/css" href="<?php echo $this->request->webroot;?>files/ew/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $this->request->webroot;?>files/ew/css/style.css?v=1.0.0" />
    </head>
    <!-- end of header -->
    <body>
        <!-- WRAPPER STARTS -->
        <div class="wrapper">
            <!-- HEADER STARTS -->
            <?php
                $requestSiteCode = !empty($requestSiteCode) ? $requestSiteCode : 'EDW';
                switch ($requestSiteCode) {
                    case 'EDW': {
                        echo $this->element('header', ['logo' => '//cdn.everydaywinner.com/feature/edw/images/ew_logo.png']);
                        break;
                    }
                    case 'WG': {
                        echo $this->element('header', ['logo' => '//cdn.everydaywinner.com/feature/edw/images/wg_logo.png']);
                        break;
                    }
                    default: {
                        echo $this->element('header', ['logo' => '//cdn.everydaywinner.com/feature/edw/images/ew_logo.png']);
                        break;
                    }
                }
            ?>
            <!-- HEADER ENDS -->

            <!-- CONTENT STARTS -->
            <section class="site_section">
                <div class="container">
                    <?php echo $this->fetch('content'); ?>
                </div>
            </section>
            <!-- CONTENT ENDS -->


            <!-- FOOTER STARTS -->
            <footer class="site_footer">
                <div class="container text-center">
                    <p>Copyright &copy; 2008-2016. All rights reserved. EVERYDAYWINNER.com is a trademark of Flatiron Media, LLC</p>
                    <ul>
                        <li><a href="//www.everydaywinner.com/privacy_policy.php" target="_blank">Privacy Policy</a></li>
                        <li><a href="">Terms Of Service</a></li>
                    </ul>
                </div>
            </footer>
            <!-- FOOTER ENDS -->
        </div>
            <!-- WRAPPER ENDS -->

        <script type="text/javascript" src="<?php echo $this->request->webroot;?>files/ew/js/canvas-to-blob.min.js"></script><!--Multi browser support for toBlob function-->
        <script type="text/javascript" src="<?php echo $this->request->webroot;?>files/ew/js/jquery-1.11.3.min.js"></script>
        <script type="text/javascript" src="<?php echo $this->request->webroot;?>files/ew/js/css3-mediaqueries.js"></script>
        <script type="text/javascript" src="<?php echo $this->request->webroot;?>files/ew/js/modernizr.custom.49493.js"></script>
        <script type="text/javascript" src="<?php echo $this->request->webroot;?>files/ew/js/custom-file-input.js"></script>
        <script src="<?php echo $this->request->webroot;?>files/ew/js/jquery.valideater-0.2.2.js"></script>

        <?php echo $this->Html->script('cropper.min.js?v=1.0.0'); ?>
        <?php echo $this->Html->script('everyday_winners.js?v=1.1.1'); ?>
        <!--[if lt IE 9]>
        <script type="text/javascript" src="js/html5shiv.min.js"></script>
        <script type="text/javascript" src="js/respond.min.js"></script>
        <![endif]-->


        <script>
        $('form').valideater();
        </script>
    </body>

</html>
<!-- Modal -->
<div class="modal fade" id="sooper-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-inverse" data-dismiss="modal">Close</button>
                <button type="button" class="btn continue">Save Changes</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php
    echo $this->Html->script('bootstrap.min');
?>
