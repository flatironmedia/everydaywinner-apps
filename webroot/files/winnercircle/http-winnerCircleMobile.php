<?php 
class WinnerCircleJsonReader 
{
    var $json=null;
    function __construct($jsonPath)
    {
        $this->json=json_decode(file_get_contents($jsonPath));
    }
    public function printHtmlWinnners()
    {
        $list="";
        
        foreach ($this->json->edw_winners as $winner) 
        {
            $image=(empty($winner->image))?"images/placeholder.jpg":$winner->image;    
            $name=(strlen($winner->name)>13)?substr($winner->name,0,12).".":$winner->name;
            $list.= '
            <li>
                <img src="'.$image.'" width="128" height="128" alt="'.$winner->name.'" />
                <div class="pop-img">
                    <p class="pop-date">'.$winner->date_won.'</p>
                    <p class="pop-winner">'.$name.'</p>
                    <p class="pop-location">'.$winner->city.'</p>
                </div>
            </li>
            ';
        }
        return $list;
    }
}

$winnerCircle= new WinnerCircleJsonReader("http://www.everydaywinner.com/feature/EDW/confirm/fetchWinnerCircle/fullCircle");
echo $winnerCircle->printHtmlWinnners();
?>