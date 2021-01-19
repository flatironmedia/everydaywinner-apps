<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
// App::import('Core', 'Controller'); //same as below
// App::import('Component', 'PickWinner'); //tell how to implemente this and where it comes from
use Cake\Controller\Controller;
use Cake\Log\Log;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
// App::uses('Component', 'Controller');
// App::uses('Controller', 'ComponentCollection');

class EDWWinnerJsonExportShell extends Shell
{
    public function initialize()
    {
        parent::initialize();
        // $this->Winners = TableRegistry::get("Winners");
        $this->loadModel('Winners');
    }
    public $uses = array('Winner','Standing','Status', 'Site', 'Prize', 'PrizeSchedule');

    var $statesUS = array('AL' => 'Alabama','AK' => 'Alaska','AZ' => 'Arizona','AR' => 'Arkansas','CA' => 'California',
		'CO' => 'Colorado','CT' => 'Connecticut','DE' => 'Delaware','DC' => 'Washington DC','FL' => 'Florida','GA' => 'Georgia',
		'HI' => 'Hawaii','ID' => 'Idaho','IL' => 'Illinois','IN' => 'Indiana','IA' => 'Iowa','KS' => 'Kansas','KY' => 'Kentucky',
		'LA' => 'Louisiana','ME' => 'Maine','MD' => 'Maryland','MA' => 'Massachusetts','MI' => 'Michigan','MN' => 'Minnesota',
		'MS' => 'Mississippi','MO' => 'Missouri','MT' => 'Montana','NE' => 'Nebraska','NV' => 'Nevada','NH' => 'New Hampshire',
		'NJ' => 'New Jersey','NM' => 'New Mexico','NY' => 'New York','NC' => 'North Carolina','ND' => 'North Dakota','OH' => 'Ohio',
		'OK' => 'Oklahoma','OR' => 'Oregon','PA' => 'Pennsylvania','PR' => 'Puerto Rico','RI' => 'Rhode Island','SC' => 'South Carolina',
		'SD' => 'South Dakota','TN' => 'Tennessee','TX' => 'Texas','UT' => 'Utah','VT' => 'Vermont','VI' => 'Virgin Islands','VA' => 'Virginia',
		'WA' => 'Washington','WV' => 'West Virginia','WI' => 'Wisconsin',  'WY' => 'Wyoming');

    private $siteConfigs = [
        'EDW' => [
            'winnerCirclePath' => "files\\winnercircle\\",
            'photoUploadPath' => "/feature/EDW/app/files/winnerphoto/"
        ],
        'WG' => [
            'winnerCirclePath' => "files\\wg_winnercircle\\",
            'photoUploadPath' => "/feature/EDW/app/files/wg_winnerphoto/"
        ]
    ];

    private $sitesJoin = array(
        array(
            'table' => 'standings',
            'conditions' => array(
                'Standing.winner_id = Winner.id'
            )
        ),
        array(
            'table' => 'sites',
            'alias' => 'Site',
            'conditions' => array(
                'Standing.site_id = Site.id'
            )
        )
    );

    /**
     * Featured Winners
     */
    public function saveRandomWinner($siteCode = 'EDW')//NOTE: this will be called by CRON and winner dasboard
    {
        $filePath = WWW_ROOT . $this->siteConfigs[$siteCode]['winnerCirclePath'] . "featuredwinner.json";
        $numberOfWinners = 1;
        return file_put_contents($filePath, $this->randomWinners($siteCode, $numberOfWinners));
    }

    public function saveWinnerCircle($siteCode = 'EDW')//NOTE: this will be called by CRON and winner dasboard
    {
        $filePath = WWW_ROOT . $this->siteConfigs[$siteCode]['winnerCirclePath'] . "winnercircledata.json";

        if ($siteCode == 'EDW') {
            return  file_put_contents($filePath, $this->winnerCircle());
        } else if ($siteCode == 'WG') {
            return  file_put_contents($filePath, $this->wgWinnerCircle());
        }
    }

    public function saveYesterdayWinner()
    {
        return  file_put_contents(WWW_ROOT."files\\winnercircle\\yesterdaywinner.json",$this->yesterdaywinner());
    }

    public function saveLastSundayWinner()
    {
        $filePath = WWW_ROOT . $this->siteConfigs['WG']['winnerCirclePath'] . "lastsundaywinner.json";
        return file_put_contents($filePath, $this->lastSundayWinner());
    }

    public function saveWinnerFeed()
    {
        return  file_put_contents(WWW_ROOT."files\\winnercircle\\winner-feed.xml",$this->winnerFeed());
    }

    /**
     * Check if the winner-feed.xml file exists
     *
     * @return boolean
     */
    public function checkWinnerFeedExists()
    {
        return file_exists(WWW_ROOT . "files\\winnercircle\\winner-feed.xml");
    }

    /**
     * Check if the winner-feed.xml was created today
     *
     * @return boolean
     */
    public function checkWinnerFeedWasCreatedToday()
    {
        $file = WWW_ROOT . "files\\winnercircle\\winner-feed.xml";
        $modifiedDate = date('Ymd', filemtime($file));
        $today = date('Ymd');

        return $modifiedDate == $today;
    }

    /**
     * Rename the winner-feed.xml file
     *
     * @return boolean
     */
    public function renameWinnerFeed()
    {
        return rename(WWW_ROOT . "files\\winnercircle\\winner-feed.xml", WWW_ROOT . "files\\winnercircle\\winner-feed-old.xml");
    }

    public function randomWinner()
    {
        $result=$this->Winner->find('first',array(
            'conditions'=>array(
                'Standing.status_id IN'=>array(4,5,7),
                'Winner.photo <>'=>"",
                'Winner.blurb <>'=>"",
                'Standing.date_won >='=>date("Y-m-d",strtotime('11/1/2016')),
                'Standing.site_id' => 1
            ),
            'order'=>'rand()'
        ));
        return $this->getformattedWinnerCircleJson($result);
    }

    /**
     * @param string $siteCode        Web site code
     * @param int    $numberOfWinners Number of winners
     *
     * Generate a json with random winners with status (Signed, Fullfilled, CardSent) for a specified site
     */
    public function randomWinners($siteCode = 'EDW', $numberOfWinners = 1)
    {
        if ($numberOfWinners == 1) {
            $finderType = 'first';
            $formattedWinnerCircleMode = 'single';

        } else {
            $finderType = 'all';
            $formattedWinnerCircleMode = 'multiple';
        }

        $contain = array(
            'Standing' => array('Site'),
        );

        if ($siteCode == "WG") {
            $contain[] = "Prize";
        }

        $result = $this->Winner->find($finderType, array(
            'contain' => $contain,
            'joins' => array (
                array (
                    'table' => 'standings',
                    'conditions' => array(
                        'Standing.winner_id = Winner.id'
                    )
                ),
                array (
                    'table' => 'sites',
                    'alias' => 'Site',
                    'conditions' => array(
                        'Standing.site_id = Site.id'
                    )
                )
            ),
            'conditions' => array(
                'Site.code' => $siteCode,
                'Standing.status_id IN' => array(4, 5, 7),
                'Winner.photo <>' => "",
                'Winner.blurb <>' => "",
                'Standing.date_won >=' => date("Y-m-d", strtotime('11/1/2016')),
            ),
            'group' => array(
                'Winner.id'
            ),
            'order' => 'rand()',
            'limit' => $numberOfWinners

        ));

        if ($siteCode == 'EDW' || $numberOfWinners > 1) {
            return $this->getformattedWinnerCircleJson($result, $formattedWinnerCircleMode, $siteCode);
        } elseif ($siteCode == 'WG') {
            return $this->getFormattedWgInvidivualWinnerJson($result);
        }
    }

    /**
     * Generate a json output with the latest 30 EDW winners order by desc
     */
    public function winnerCircle($latest="30")
    {
        Log::write('debug', "The latest =".$latest);
        $siteCode = 'EDW';
        $result = $this->Winner->find('all',array(
            'contain' => array(
                'Standing' => array('Site'),
            ),
            'joins' => $this->sitesJoin,
            'fields' => array(
                'DISTINCT *'
            ),
            'conditions' => array(
                'NOT' => array("Standing.status_id" => array(6, 8)),
                'Winner.photo <>' => "",
                'Standing.date_won >=' => date("Y-m-d",strtotime('11/1/2016')),
                'Site.code' => $siteCode,
            ),
            'order' => 'Standing.date_won desc',
            'limit' => $latest
        ));
        Log::write('debug', "The Query =". $result);
        $testFormatter = $this->getformattedWinnerCircleJson($result, "Multiple", $siteCode);
        Log::write('debug', "The Query  formatted =".$testFormatter);
        return $this->getformattedWinnerCircleJson($result, "Multiple", $siteCode);
    }

    /**
     * Generate a json output with the 5 not repeated WG random winners
     */
    public function wgWinnerCircle()
    {
        $featuredWinners = $this->Winner->find('all', array(
            'contain' => array(
                'Standing' => array('Site'),
                'Prize'
            ),
            'joins' => $this->sitesJoin,
            'conditions' => array(
                'Site.code' => 'WG',
                'Standing.status_id IN' => array(4, 5, 7),
                'Winner.photo <>' => "",
                'Standing.date_won >=' => date("Y-m-d", strtotime('11/1/2016')),
            ),
            'group' => array(
                'Winner.id'
            ),
            'order' => 'rand()',
            'limit' => 5
        ));

        $recentWinners = $this->Winner->find('all',array(
            'contain' => array(
                'Standing' => array('Site'),
                'Prize'
            ),
            'joins' => $this->sitesJoin,
            'group' => array(
                'Winner.id'
            ),
            'conditions' => array(
                "Standing.status_id !=" => 6,
                'Standing.date_won >=' => date("Y-m-d",strtotime('11/1/2016')),
                'Site.code' => 'WG',
            ),
            'order' => 'Standing.date_won desc'
        ));

        return $this->getFormattedWgWinnerCircleJson($featuredWinners, $recentWinners);
    }

     /**
     * Generate a json output with 1 EDW winner with the date_won = yesterday
     */
    public function yesterdaywinner()
    {
        
        // $test = $Winners->find('byToken', ['token' => '588768d8787df1485269208']);
        // debug($test->first());
        debug($this);
        die();
        $result=$this->Winners->find('all',array(
            'conditions'=>array(
                "Standing.status_id !="=>6,
                'Standing.date_won >='=>date("Y-m-d",strtotime('today -1 day')),
                'Standing.site_id' => 1
            ),
            'order'=>array('Winner.id' => 'DESC' )
        ))->first();
        debug($result);
        die();
        if (empty($result))
        {
            $result=$this->yesterdayRandomWinner();//generate a random winner

        }
        $this->yesterdaywinnerforxml = $result;
        file_put_contents(WWW_ROOT."files\\winnercircle\\yesterdaywinner.txt", $this->getformattedYesterdayWinnerJson($result, true));
        return $this->getformattedYesterdayWinnerJson($result);
    }
    
    public function show()
    {
        // debug($this->args[0]);
        // if (empty($this->args[0])) {
        //     // Use error() before CakePHP 3.2
        //     return $this->abort('Please enter a username.');
        // }
        $user = $this->Winners->find('byToken', ['token' => '588768d8787df1485269208'])->first();
        $this->out(print_r($user, true));
    }
    // public function main(){
    //     $this->out();
    // }
    /**
     * Generate a json output with 1 WG winner with the date_won = last sunday date
     */
    public function lastSundayWinner()
    {
        $siteCode = 'WG';
        $lastSundayDate = date("Y-m-d", strtotime("last sunday"));

        $result = $this->Winner->find('first',array(
            'contain' => array(
                'Standing' => array('Site'),
                'Prize'
            ),
            'joins' => $this->sitesJoin,
            'conditions'=>array(
                "Standing.status_id !=" => 6,
                'Standing.date_won LIKE' => $lastSundayDate .'%',
                'Site.code' => $siteCode,
            ),
            'order' => array('Winner.id' => 'DESC')
        ));

        return $this->getFormattedWgInvidivualWinnerJson($result);
    }


    public function winnerFeed()
    {
        $yesterdayWinnerSection = $this->winnerxmlyesterdaysection();
        $unclaimnedWinnerSection = $this->winnerxmlunclamiedprizessection(2);
        debug($yesterdayWinnerSection);
        // debug($unclaimnedWinnerSection);
        die();
        $xml = '
            <feed>
            <mailing>
                '.$yesterdayWinnerSection.'
            <blocks>
                <block name="unclaimed_prizes">
                    '.$unclaimnedWinnerSection.'
                </block>
            </blocks>
            </mailing>
            </feed>
        ';

        return $xml;
    }

    private function winnerxmlyesterdaysection()
    {
        $xml = "
            <variables>
                <variable>
                    <key>yesterday_winner_name</key>
                    <value>".ucwords($this->yesterdaywinnerforxml['Winner']['first_name'])." ".ucwords($this->yesterdaywinnerforxml['Winner']['last_name'][0])."."."</value>
                </variable>
                <variable>
                    <key>yesterday_winner_city</key>
                    <value>".ucwords($this->yesterdaywinnerforxml['Winner']['city']).", ".strtoupper($this->yesterdaywinnerforxml['Winner']['state'])."</value>
                </variable>
            </variables>
        ";
        return $xml;
    }

    private function winnerxmlunclamiedprizessection($numberOfWinners)
    {
        $result = $this->Winners->find('all',array(
            'conditions'=>array(
                'Standing.status_id' => 2,
                'Standing.date_won >' => date('Y-m-d H:i:s', strtotime("11/1/2016" )),
                'Standing.date_won <' => date("Y-m-d", strtotime('today -1 day')),
                'Standing.site_id' => 1
            ),
            'order'=>'rand()',
            'limit'=>$numberOfWinners,
        ));

        usort($result, function ($a,$b)
        {
            return -(strtotime($a['Standing']['date_won']) - strtotime($b['Standing']['date_won']) ); // Order by date DESC
        });

        $xml="";
        foreach ($result as $winner) {
            $xml .= "
                <items>
                    <item>
                    <variables>
                        <variable>
                            <key>date_won</key>
                            <value>".date("m/d/Y",strtotime($winner['Standing']['date_won']))."</value>
                        </variable>
                        <variable>
                            <key>winner_name</key>
                            <value>".ucwords($winner['Winner']['first_name'])." ".ucwords($winner['Winner']['last_name'][0])."."."</value>
                        </variable>
                        <variable>
                            <key>winner_city</key>
                            <value>".ucwords($winner['Winner']['city']).", ".strtoupper($winner['Winner']['state'])."</value>
                        </variable>
                    </variables>
                    </item>
                </items>
            ";
        }
        return $xml;
    }

    /**
    *fetches a random winnner from client url and returns the appropiate winner array used by getformattedYesterdayWinnerJson
    *@return array with winner data
    */
    private function yesterdayRandomWinner()
    {
        $result=(file_get_contents("http://pathfinder.flatironmedia.com/public/api/randomgenerator/"));
        $result=json_decode($this->removeFirstAndLastBracketFromJson($result));

        $state=$result->results[0]->location->state;
        if (strlen($state)>2)//if state is full string, change with state abbreviation
        {
            $state=array_search(ucwords(strtolower($result->results[0]->location->state)), $this->statesUS);
        }
        if (!$state) {
            $state="CA";
        }

        $winner=array(
            'Winner' => array(
                'first_name' => ucwords(strtolower($result->results[0]->name->first)),
                'last_name'=>ucwords(strtolower($result->results[0]->name->last)),
                'city'=>ucwords(strtolower($result->results[0]->location->city)),
                'state'=>$state,
            ),
            'Standing'=>array(
                'date_won'=> date("Y-m-d",strtotime('today -1 day')),
            )
        );
        return $winner;
    }

    private function removeFirstAndLastBracketFromJson($badJsonWithBracket)
    {
        trim($badJsonWithBracket);
        $hasBracketsAtEndAndStart=$badJsonWithBracket[0]=='('&&$badJsonWithBracket[strlen($badJsonWithBracket)-1]==')';
        if ($hasBracketsAtEndAndStart)
        {
            $badJsonWithBracket=str_replace(array('(',')'),'',$badJsonWithBracket);
        }
        return trim($badJsonWithBracket);
    }

    private function getformattedYesterdayWinnerJson($dataArray, $returnPlain = false)
    {
        $title=ucwords(strtolower($dataArray['Winner']['first_name'])).' '.strtoupper($dataArray['Winner']['last_name'][0]).'. from '.ucwords(strtolower($dataArray['Winner']['city'])).', '.$dataArray['Winner']['state'];
        $pubupdate = date("F j, Y",strtotime($dataArray['Standing']['date_won']));
        $returnArray = array(array('title' => $title, 'pubdate' => $pubupdate));
        if ($returnPlain) {
            return $title;
        }
        return json_encode($returnArray,JSON_UNESCAPED_SLASHES);
    }

    private function getformattedWinnerCircleJson($dataArray,$mode="single", $siteCode = 'EDW')
    {
        $returnArray = array("edw_winners"=>array());
        $processArray=$dataArray;
        if ($mode=='single')
        {
            $processArray=array($dataArray);
        }
        foreach ($processArray as $item)
        {
            $genderedNullAvatar="//cdn.everydaywinner.com/feature/edw/images/male.jpg";
            if(in_array($item['Winner']['gender'],array('f','F','Female','female','FEMALE')))
            {
                $genderedNullAvatar="//cdn.everydaywinner.com/feature/edw/images/female.jpg";
            }

  	        $winnerphotothumb=str_replace("-full","-thumb",explode("winnerphoto/",$item['Winner']['photo'])[1]);

            if (Configure::read('DEVELOPMENT_MODE')) {
                $baseUrl = "west.everydaywinner.com";
            } else {
                $baseUrl = "www.everydaywinner.com";
            }

            if (strpos($_SERVER['HTTP_HOST'],"east.")>-1) {
                $baseUrl = $_SERVER['HTTP_HOST'];
            }
            $pushValue=array(
                'date_won' =>date("m/d/Y",strtotime($item['Standing']['date_won'])) ,
                'name' =>  ucwords(strtolower($item['Winner']['first_name']))." ". ucwords(strtolower($item['Winner']['last_name'][0])).".",
                'city' => ucwords(strtolower($item['Winner']['city'])),
                'state' => $item['Winner']['state'],
                'blurb' => ucfirst(trim(preg_replace("/[^ -~]/", "", $item['Winner']['blurb']))),
                'image' => (!empty($item['Winner']['photo']))?"//". $baseUrl . $this->siteConfigs[$siteCode]['photoUploadPath'] . $winnerphotothumb:$genderedNullAvatar,
            );

            if ($mode=="single") {
                $pushValue['headline'] = ucfirst($item['Winner']['headline']);
            }

            array_push($returnArray['edw_winners'],$pushValue);

        }
        return json_encode($returnArray,JSON_UNESCAPED_SLASHES);
    }

    /**
     * Format WG individual winner array to json output
    */
    private function getFormattedWgInvidivualWinnerJson($winner)
    {
        $siteCode = 'WG';
        $returnArray = [
            'wg_winners'=> []
        ];

        $genderedNullAvatar = "//cdn.everydaywinner.com/feature/edw/images/male.jpg";
        if (in_array($winner['Winner']['gender'], array('f','F','Female','female','FEMALE')))
        {
            $genderedNullAvatar = "//cdn.everydaywinner.com/feature/edw/images/female.jpg";
        }

        $winnerphotothumb = str_replace("-full","-thumb", explode("winnerphoto/", $winner['Winner']['photo'])[1]);

        if (Configure::read('DEVELOPMENT_MODE')) {
            $baseUrl = "west.everydaywinner.com";
        } else {
            $baseUrl = "www.everydaywinner.com";
        }

        if (strpos($_SERVER['HTTP_HOST'],"east.") > -1) {
            $baseUrl = $_SERVER['HTTP_HOST'];
        }

        $formattedWinner = array(
            'date_won' => date("m/d/Y",strtotime($winner['Standing']['date_won'])) ,
            'name' => ucwords(strtolower($winner['Winner']['first_name'])) . " " . ucwords(strtolower($winner['Winner']['last_name'][0])) . ".",
            'city' => ucwords(strtolower($winner['Winner']['city'])),
            'state' => $winner['Winner']['state'],
            'prize' => !empty($winner['Prize']['name']) ? $winner['Prize']['name'] : "",
            'value' => !empty($winner['Prize']['value']) ? $winner['Prize']['value'] : "",
            'blurb' => ucfirst(trim(preg_replace("/[^ -~]/", "", $winner['Winner']['blurb']))),
            'image' => (!empty($winner['Winner']['photo'])) ? "//" . $baseUrl . $this->siteConfigs[$siteCode]['photoUploadPath'] . $winnerphotothumb:$genderedNullAvatar,

        );

        $returnArray['wg_winners'][] = $formattedWinner;

        return json_encode($returnArray, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Format WG winners array to a json output with the format {"featuredWinners": <featured_winners>, "recentWinners": <recent_winners>}
    */
    private function getFormattedWgWinnerCircleJson($featuredWinners, $recentWinners)
    {
        $siteCode = 'WG';
        $result = [
            'featuredWinners' => [],
            'recentWinners' => []
        ];

        if (Configure::read('DEVELOPMENT_MODE')) {
            $baseUrl = "west.everydaywinner.com";
        } else {
            $baseUrl = "www.everydaywinner.com";
        }

        if (strpos($_SERVER['HTTP_HOST'],"east.")>-1) {
            $baseUrl = $_SERVER['HTTP_HOST'];
        }

        foreach ($featuredWinners as $item) {
            $genderedNullAvatar = "//cdn.everydaywinner.com/feature/edw/images/male.jpg";
            if (in_array($item['Winner']['gender'], array('f','F','Female','female','FEMALE')))
            {
                $genderedNullAvatar = "//cdn.everydaywinner.com/feature/edw/images/female.jpg";
            }

            $winnerphotothumb = str_replace("-full","-thumb", explode("winnerphoto/",$item['Winner']['photo'])[1]);

            $featureWinnerFormatted = [
                'date_won' => date("m/d/Y",strtotime($item['Standing']['date_won'])) ,
                'name' => ucwords(strtolower($item['Winner']['first_name'])) . " " . ucwords(strtolower($item['Winner']['last_name'][0])) . ".",
                'city' => ucwords(strtolower($item['Winner']['city'])),
                'state' => $item['Winner']['state'],
                'prize' => !empty($item['Prize']['name']) ? $item['Prize']['name'] : "",
                'value' => !empty($item['Prize']['value']) ? $item['Prize']['value'] : "",
                'image' => (!empty($item['Winner']['photo']))?"//". $baseUrl . $this->siteConfigs[$siteCode]['photoUploadPath'] . $winnerphotothumb : $genderedNullAvatar,

            ];

            $result['featuredWinners'][] = $featureWinnerFormatted;
        }

        foreach ($recentWinners as $item) {
            $genderedNullAvatar = "//cdn.everydaywinner.com/feature/edw/images/male.jpg";
            if (in_array($item['Winner']['gender'], array('f', 'F', 'Female','female', 'FEMALE')))
            {
                $genderedNullAvatar = "//cdn.everydaywinner.com/feature/edw/images/female.jpg";
            }
            $recentWinnerFormatted = [
                'date_won' => date("m/d/Y", strtotime($item['Standing']['date_won'])) ,
                'name' => ucwords(strtolower($item['Winner']['first_name'])) . " " . ucwords(strtolower($item['Winner']['last_name'][0])) . ".",
                'city' => ucwords(strtolower($item['Winner']['city'])),
                'state' => $item['Winner']['state'],
                'prize' => 'Braun Gillette Face & Body Hair Removal System',
                'value' => 352,
            ];

            $result['recentWinners'][] = $recentWinnerFormatted;
        }

        return json_encode($result, JSON_UNESCAPED_SLASHES);

    }
}

?>
