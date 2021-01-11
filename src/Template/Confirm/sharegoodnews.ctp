
<?php
$temp=explode("/feature/everydaywinner",$baseUrl);
$baseUrl=$temp[0];


?>
<div class="content_wrapper">

    <div class="page_header text-center bb_section">
        <div class="header_title">
            <h1>YOU'RE FINISHED!</h1>
        </div>
        <div class="header_text">
            <p>Once again, congratulations. We have received all of your information.</p>
			<p>You should receive your <?php echo $sitePrize ?> in 2 to 4 weeks via snail mail.</p>
        </div>
    </div>

    <div class="page_content text-center bb_section">
    	<div class="content ">
        	<h3>CONTACT US</h3>
			<p>If you have any questions, we can be reached via email at <a href="mailto:fulfillment@everydaywinner.com">fulfillment@everydaywinner.com</a> or by phone at (646) 580-6118.</p>
        </div>
    </div>

    <div class="share_wrap text-center">
    	<h3>SHARE THE GOOD NEWS.</h3>
        <h4>Help your friends and family win $500.</h4>
        <?php if ($siteCode == 'EDW'): ?>\
            <div class="social_icons">
                <div class="addthis_inline_share_toolbox"
                    data-title="I Just Won $500!"
                    data-url="//www.everydaywinner.com"
                    data-media="//cdn.everydaywinner.com/images/edw_social_300x300.jpg"
                    data-description="Now it's your turn. Click here to sign up for a chance to win! You will be automatically entered in Everyday Winner's exclusive daily $500 sweepstakes!">
                </div>
            </div>
        <?php else: ?>
            <div class="social_icons">
                <div class="addthis_inline_share_toolbox"
                    data-title="I Just Won"
                    data-url="//www.everydaywinner.com"
                    data-media="//cdn.everydaywinner.com/images/edw_social_300x300.jpg"
                    data-description="Now it's your turn. Click here to sign up for a chance to win! You will be automatically entered in Everyday Winner Giveaways exclusive weekly sweepstakes!">
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-57f5531dff09e351"></script>
