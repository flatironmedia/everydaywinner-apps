<?php
// $_SERVER['HTTP_HOST']
    // $hostLink="http://east.everydaywinner.com";
    $rootlink="www.everydaywinner.com";
    /* $hostLink=$_SERVER['HTTP_HOST'];
    $rootlink="west.everydaywinner.com";
    if (strpos($hostLink,'east.')!==false)
    {
        $rootlink="east.everydaywinner.com";
    } */
	$whichProtocol = ((isset($_SERVER['HTTPS'])) && ($_SERVER['HTTPS'] == "on") ? 'https://' : 'http://');
	$rootlink = $whichProtocol . $rootlink;
    $winnersData = json_decode(file_get_contents($rootlink."/feature/EDW/app/confirm/fetchWinnerCircle/fullCircle"));
    $winnersCircle = $winnersData->edw_winners;

?>
<?php foreach ($winnersCircle as $winner): ?>
    <li>
        <?php
            $winner->image = ($winner->image != "") ? $winner->image : "images/placeholder.jpg";
        ?>
        <img src="<?php echo $winner->image; ?>" width="128" height="128" alt="<?php echo $winner->name; ?>" />
        <div class="pop-img">
            <p class="pop-date"><?php echo $winner->date_won; ?></p>
            <p class="pop-winner"><?php echo $winner->name; ?></p>
            <p class="pop-location"><?php echo $winner->city.", ".$winner->state; ?></p>
        </div>
    </li>
<?php endforeach; ?>
