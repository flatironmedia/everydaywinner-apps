<?php
	$this->assign('title', 'Agreements');
    $this->assign('box-title', 'Agreements');
    $this->assign('page-id', 'Agreements-tab');
?>
<div class="content_wrapper">
	<div class="page_header text-center bb_section">
        <div class="header_title">
            <h1>SIGN YOUR CONFIRMATION</h1>
        </div>
        <div class="header_text">
            <p>Please read the waiver and press 'Start' to begin.</p>
        </div>
    </div>

    <div class="page_content">
    	<div class="content">
			<?php if($displayAgreements): ?>
				 <object id="widget-container" class="widget-container" data="<?php echo $widget;?>" width="100%" height="1000">
				    <embed id="widget-document" src="<?php echo $widget;?>"</embed>
				    Error: Embedded data could not be displayed.
				</object>
			<?php endif; ?> 
        </div>
    </div>
</div>