<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EverydayWinner</title>
<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700,800" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="<?php echo $this->request->webroot;?>files/ew/css/bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->request->webroot;?>files/ew/css/style.css" />

</head>

<body>
	<!-- WRAPPER STARTS -->
	<div class="wrapper">

        <!-- HEADER STARTS -->
    	<header class="site_header">
        	<div class="container">
            	<div class="logo text-center"><img src="//cdn.everydaywinner.com/views/templates/everydaywinner/desktop/assets/images/logo.png" alt="" class="img-responsive" /></div>
            </div>
        </header>
        <!-- HEADER ENDS -->


		<!-- CONTENT STARTS -->
		<section class="site_section">
			<div class="container">
            	<div class="content_wrapper">
                	<div class="page_header text-center bb_section">
                        <div class="header_title">
                            <h1>Unsubscribe</h1>
                        </div>
                        <div class="header_text">
                        	<?php if(!isset($unsubscribed)) : ?>
                            	<p>To stop receiving emails from EveryDay Winner, please enter your email and click "Unsubscribe".</p>
                        	<?php else: ?>
								<p><?php echo $message; ?></p>
                        	<?php endif; ?>
                        </div>
                    </div>

                    <div class="page_content">
                    	<div class="row">

                            <div class="col-md-3 col-sm-3 col-sm-push-9">
                            	<div class="steps_wrap">
                                	
                                </div>
                            </div>

                        	<div class="col-md-6 col-md-offset-3 col-sm-9 col-sm-pull-3">

                            	<div class="form_wrap">
                            	<?php if(!isset($unsubscribed) || (isset($success) && !$success)) : ?>
									<form id="UnsubscribeForm" class="form-horizontal" method="post">
										<div class="row">
                                            <div class="col-md-10 col-md-offset-1">
                                                <label for="winner-email">Email <span>*</span></label>
												<?php
												echo $this->Form->input('email_winner', array(
													'id' => 'winner-email',
													'class' => 'form-control',
													'label' => false,
													'data-vldtr'=> 'email',
													'value' => ''
												));
												?>
                                            </div>
                                        </div>

                                        <div class="row">
                                        	<div class="col-md-10 col-md-offset-1">
												<?php
												echo $this->Form->input('Unsubscribe', array(
															'id' => 'unsubscribeButton',
															'type'=>'submit',
															'label'=>false,
															'class' => 'btn form-control btn_green',
															'value' => 'unsubscribe'
														));
												?>
                                            </div>
                                        </div>

                                        <div>
                                        	<div class="col-md-10 col-md-offset-1">
                                            	<div class="form_footer"><p>Your information is safe with us.<br>View our <a href="//www.everydaywinner.com/privacy_policy.php"  target="_blank" >Privacy Policy</a></p></div>
                                            </div>
                                        </div>
                                    </form>
								<?php endif; ?>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
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


<script type="text/javascript" src="<?php echo $this->request->webroot;?>files/ew/js/jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="<?php echo $this->request->webroot;?>files/ew/js/css3-mediaqueries.js"></script>
<script type="text/javascript" src="<?php echo $this->request->webroot;?>files/ew/js/modernizr.custom.49493.js"></script>
<!--[if lt IE 9]>
	<script type="text/javascript" src="js/html5shiv.min.js"></script>
	<script type="text/javascript" src="js/respond.min.js"></script>
<![endif]-->
<?php echo $this->Html->script('everyday_winners.js?v=1.1.1'); ?>
<script src="<?php echo $this->request->webroot;?>files/ew/js/jquery.valideater-0.2.2.js"></script>
<script>
	$('form').valideater();
</script>
</body>

</html>
