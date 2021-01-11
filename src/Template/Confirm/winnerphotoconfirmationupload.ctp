<?php
    echo $this->Html->css('cropper.min.css');
    $previousUrl= explode("/everydaywinners",$_SERVER['REQUEST_URI']);


    if (isset($imageUrl)) {
        ?>
        <img src="<?php echo $previousUrl[0].'/'.str_replace('\\','/',$imageUrl['newFilePath'])?>" id="imagePreview" alt="There's no image." >
        <?php
    }
?>

<div class="content_wrapper">
	<div class="page_header text-center bb_section">
        <div class="header_title">
            <h1>Welcome to the Winner Circle</h1>
        </div>
        <div class="header_text">
            <p>
                We would love for you to submit a photo of yourself below for our gallery of winners.
                Also, please use the box below to provide a brief statement about your experience with <?php echo $siteName ?>.
            </p>
        </div>
    </div>

    <div class="page_content">
    	<div class="row">

            <div class="col-md-3 col-sm-3 col-sm-push-9">
            	<div class="steps_wrap">
                	<h5>3 EASY STEPS</h5>
                    <ul>
                    	<li>1</li>
                        <li class="active">2</li>
                        <li>3</li>
                    </ul>
                </div>
            </div>

        	<div class="col-md-6 col-md-offset-3 col-sm-9 col-sm-pull-3">

            	<div class="form_wrap">
                    <form id="imageCropping" class="" action="" enctype="multipart/form-data"  method="post">

                        <div class="row">
                            <div class="col-md-10 col-md-offset-1">
                                <br>
                                <label for="happyReason">How do you feel about being selected as our <?php echo $winnerTitle ?>? (No profanity!)</label>
                                <textarea id="happyReason" name="happyReason" class="form-control" rows="3" cols="40"></textarea>
                            </div>
                        </div>

                    	<div class="row">
                        	<div class="col-md-10 col-md-offset-1">
                            	<div class="custom_file_upload">
                                    <p class="form_label">Upload Selfie</p>
                                    <a href="#" id="file_clear" class="file_clear">x</a>
                                    <input type="file" name="fileUpload" id="fileUpload" class="inputfile inputfile-6" />
                                    <label for="fileUpload"><strong>Choose File</strong> <span></span></label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                        	<div class="col-md-10 col-md-offset-1">
                        		<div class="selfie_img"><img id="imagePreview" src="<?php echo $this->request->webroot;?>files/ew/images/no-image.png" alt="" class="img-responsive"></div>
                        	</div>
                        </div>

                        <div class="row editor_btns_row">
                        	<div class="col-xs-3 col-small-gap"><span  class="crop-btn" name="crop" value="1"><img src="//cdn.everydaywinner.com/views/templates/everydaywinner/desktop/assets/images/crop.png" alt="" class="img-responsive"></span></div>
                            <div class="col-xs-3 col-small-gap"><span  class="rotate-btn" name="Rotate"><img src="//cdn.everydaywinner.com/views/templates/everydaywinner/desktop/assets/images/rotate.png" alt="" class="img-responsive"></span></div>
                            <div class="col-xs-3 col-small-gap"><span   class="flip-btn flip-vertical" name="Flip"><img src="//cdn.everydaywinner.com/views/templates/everydaywinner/desktop/assets/images/flip-v.png" alt="" class="img-responsive"></span></div>
                            <div class="col-xs-3 col-small-gap"><span class="flip-btn flip-horizontal" name="Flip"><img src="//cdn.everydaywinner.com/views/templates/everydaywinner/desktop/assets/images/flip-h.png" alt="" class="img-responsive"></span></div>
                        </div>

						<input type="hidden" id="x" name="x" />
					    <input type="hidden" id="y" name="y" />
					    <input type="hidden" id="w" name="w" />
					    <input type="hidden" id="h" name="h" />
					    <input type="hidden" id="rotationValue" name="rotationValue" />
					    <input type="hidden" id="flipHorizontalValue" name="flipHorizontalValue" value="1"/>
					    <input type="hidden" id="flipVerticalValue" name="flipVerticalValue" value="1"/>

                        <input type="hidden" id="croppedImageUrl" name="croppedImageUrl" value="">
						<input type="hidden" id="imageCodeName" name="imageCodeName" value="<?php echo $token; ?>">
                        <input type="hidden" id="site-code" name="siteCode" value="<?php echo $site; ?>">

                        <div class="row">
                        	<div class="col-md-10 col-md-offset-1">
								<button type="submit" class="btn form-control btn_green"  id="imageUploadSubmit">Continue</button>
                            </div>
                        </div>

                        <div>
                        	<div class="col-md-10 col-md-offset-1">
                            	<div class="form_footer"><p>Your information is safe with us.<br>View our <a href="//www.everydaywinenr.com/privacy_policy.php" target="_blank">Privacy Policy</a></p></div>
                            </div>
                        </div>

                    </form>


                </div>

            </div>

        </div>
    </div>
</div>
