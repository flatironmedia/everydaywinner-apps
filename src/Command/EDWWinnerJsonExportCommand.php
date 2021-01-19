<?php

namespace App\Command;

use Cake\Core\Configure;
use Cake\Controller\Controller;
use Cake\Log\Log;
use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

class EDWWinnerJsonExportCommand extends Command
{

    /**
     * "initialize()" method for some reason is not working on command class types.
     * TODO Juan: Investigate the reasons why after task done and seek a solution to fix it.
     */
    public function initialize()
    {
        $this->loadModel("winners");
    }

    public $uses = ['Winner','Standing','Status', 'Site', 'Prize', 'PrizeSchedule'];

    var $statesUS = ['AL' => 'Alabama','AK' => 'Alaska','AZ' => 'Arizona','AR' => 'Arkansas','CA' => 'California',
		'CO' => 'Colorado','CT' => 'Connecticut','DE' => 'Delaware','DC' => 'Washington DC','FL' => 'Florida','GA' => 'Georgia',
		'HI' => 'Hawaii','ID' => 'Idaho','IL' => 'Illinois','IN' => 'Indiana','IA' => 'Iowa','KS' => 'Kansas','KY' => 'Kentucky',
		'LA' => 'Louisiana','ME' => 'Maine','MD' => 'Maryland','MA' => 'Massachusetts','MI' => 'Michigan','MN' => 'Minnesota',
		'MS' => 'Mississippi','MO' => 'Missouri','MT' => 'Montana','NE' => 'Nebraska','NV' => 'Nevada','NH' => 'New Hampshire',
		'NJ' => 'New Jersey','NM' => 'New Mexico','NY' => 'New York','NC' => 'North Carolina','ND' => 'North Dakota','OH' => 'Ohio',
		'OK' => 'Oklahoma','OR' => 'Oregon','PA' => 'Pennsylvania','PR' => 'Puerto Rico','RI' => 'Rhode Island','SC' => 'South Carolina',
		'SD' => 'South Dakota','TN' => 'Tennessee','TX' => 'Texas','UT' => 'Utah','VT' => 'Vermont','VI' => 'Virgin Islands','VA' => 'Virginia',
		'WA' => 'Washington','WV' => 'West Virginia','WI' => 'Wisconsin',  'WY' => 'Wyoming'];

    private $siteConfigs = [
        'EDW' => [
            'winnerCirclePath' => "files\\winnercircle\\",
            'photoUploadPath' => "/feature/EDW/files/winnerphoto/"
        ],
        'WG' => [
            'winnerCirclePath' => "files\\wg_winnercircle\\",
            'photoUploadPath' => "/feature/EDW/files/wg_winnerphoto/"
        ]
    ];

    private $sitesJoin = [
        [
            'table' => 'standings',
            'alias' => 'Standing',
            'conditions' => [
                'Standing.winner_id = Winners.id'
            ]
        ],
        [
            'table' => 'sites',
            'alias' => 'Site',
            'conditions' => [
                'Standing.site_id = Site.id'
            ]
        ]
    ];

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
        $Winners = TableRegistry::getTableLocator()->get("winners");
        $result = $Winners->find()->contain(['Standings'])->where([
                'Standings.status_id IN'=>[4,5,7],
                'Winners.photo <>'=>"",
                'Winners.blurb <>'=>"",
                'Standings.date_won >='=>date("Y-m-d",strtotime('11/1/2016')),
                'Standings.site_id' => 1
        ])->order(['rand()'])->first();

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
        $Winners = TableRegistry::getTableLocator()->get("winners");
        if ($numberOfWinners == 1) {
            $formattedWinnerCircleMode = 'single';

        } else {
            $formattedWinnerCircleMode = 'multiple';
        }

        $contain = [
            'Standings' => ['Sites'],
        ];

        if ($siteCode == "WG") {
            $contain[] = "Prizes";
        }

        $result = $Winners->find()->contain($contain)->join(
            [
                [
                    'table' => 'standings',
                    'alias' => 'Standing',
                    'conditions' => [
                        'Standing.winner_id = Winners.id'
                    ]
                ],
                [
                    'table' => 'sites',
                    'alias' => 'Site',
                    'conditions' => [
                        'Standing.site_id = Site.id'
                    ]
                ]
            ]
        )->where(
            [
            'Sites.code' => $siteCode,
            'Standings.status_id IN' => [4, 5, 7],
            'Winners.photo <>' => "",
            'Winners.blurb <>' => "",
            'Standings.date_won >=' => date("Y-m-d", strtotime('11/1/2016')),
            ]
        )->group(['Winners.id'])->order(['rand()'])->limit($numberOfWinners);
        if($numberOfWinners == 1) {
            $result = $result->first();
        } else {
            $result = $result->all();
        }

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
        $Winners = TableRegistry::getTableLocator()->get("winners");
        Log::write('debug', "The latest =".$latest);
        $siteCode = 'EDW';

        $result = $Winners->find()->select()->distinct()
            ->contain(['Standings'=>['Sites']])
            ->join($this->sitesJoin)
            ->where(function (QueryExpression $exp){
                $orConditions = $exp->or(["Standing.status_id" => 6])
                    ->eq("Standing.status_id", 8);
                return $exp
                    ->not($orConditions);
            })->where([
                'Winners.photo <>' => "",
                'Standings.date_won >=' => date("Y-m-d",strtotime('11/1/2016')),
                'Sites.code' => $siteCode,
            ])
            ->order(['Standings.date_won' => 'desc'])->all();
        $queryToArray = [];
        foreach($result as $item){
            $queryToArray[] = $item;
        }

        Log::write('debug', "The Query =". implode(',',$queryToArray));
        $testFormatter = $this->getformattedWinnerCircleJson($result, "Multiple", $siteCode);
        Log::write('debug', "The Query formatted =".$testFormatter);
        return $this->getformattedWinnerCircleJson($result, "Multiple", $siteCode);
    }

    /**
     * Generate a json output with the 5 not repeated WG random winners
     */
    public function wgWinnerCircle()
    {
        $Winners = TableRegistry::getTableLocator()->get("winners");

        $featuredWinners = $Winners->find()
        ->contain(['Standings' => ['Sites'],'Prizes'])
        ->join($this->sitesJoin)
        ->where([
            'Sites.code' => 'WG',
            'Standings.status_id IN' => [4, 5, 7],
            'Winners.photo <>' => "",
            'Standings.date_won >=' => date("Y-m-d", strtotime('11/1/2016')),
        ])->group(['Winners.id'])
        ->order(['rand()'])
        ->limit(5)->all();

        $recentWinners = $Winners->find()
        ->contain(['Standings' => ['Sites'],'Prizes'])
        ->join($this->sitesJoin)
        ->group(['Winners.id'])
        ->where([
            "Standings.status_id !=" => 6,
            'Standings.date_won >=' => date("Y-m-d",strtotime('11/1/2016')),
            'Sites.code' => 'WG',
        ])
        ->order(['Standings.date_won' => 'desc'])
        ->all();

        return $this->getFormattedWgWinnerCircleJson($featuredWinners, $recentWinners);
    }

     /**
     * Generate a json output with 1 EDW winner with the date_won = yesterday
     */
    public function yesterdaywinner()
    {
        $Winners = TableRegistry::getTableLocator()->get("winners");

        $result = $Winners->find()->contain(['standings'])->where([
            'Standings.status_id !='=>6,
            'Standings.date_won <='=>date("Y-m-d",strtotime('today -1 day')),
            'Standings.site_id' => 1
        ])->first();

        if (empty($result))
        {
            $result=$this->yesterdayRandomWinner();//generate a random winner
        }
        $this->yesterdaywinnerforxml = $result;

        file_put_contents(WWW_ROOT."files\\winnercircle\\yesterdaywinner.txt", $this->getformattedYesterdayWinnerJson($result, true));
        return $this->getformattedYesterdayWinnerJson($result);
    }

    /**
     * Generate a json output with 1 WG winner with the date_won = last sunday date
     */
    public function lastSundayWinner()
    {
        $Winners = TableRegistry::getTableLocator()->get("winners");

        $siteCode = 'WG';
        $lastSundayDate = date("Y-m-d", strtotime("last sunday"));

        $result = $Winners->find()->contain([
            'Standings' => ['Sites'],
            'Prizes'
        ])
        ->join($this->sitesJoin)
        ->where([
            "Standings.status_id !=" => 6,
            'Standings.date_won LIKE' => $lastSundayDate .'%',
            'Sites.code' => $siteCode,
        ])
        ->order(['Winners.id' => 'desc'])->first();
        
        return $this->getFormattedWgInvidivualWinnerJson($result);
    }


    public function winnerFeed()
    {
        $yesterdayWinnerSection = $this->winnerxmlyesterdaysection();
        $unclaimnedWinnerSection = $this->winnerxmlunclamiedprizessection(2);

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
                    <value>".ucwords($this->yesterdaywinnerforxml['first_name'])." ".ucwords($this->yesterdaywinnerforxml['last_name'])."."."</value>
                </variable>
                <variable>
                    <key>yesterday_winner_city</key>
                    <value>".ucwords($this->yesterdaywinnerforxml['city']).", ".strtoupper($this->yesterdaywinnerforxml['state'])."</value>
                </variable>
            </variables>
        ";
        return $xml;
    }

    private function winnerxmlunclamiedprizessection($numberOfWinners)
    {
        $Winners = TableRegistry::getTableLocator()->get("winners");
        $result = $Winners->find()->contain(['standings'])->where([
            'Standings.status_id' => 2,
            'Standings.date_won >' => date('Y-m-d H:i:s', strtotime("11/1/2016" )),
            'Standings.date_won <' => date("Y-m-d", strtotime('today -1 day')),
            'Standings.site_id' => 1
        ])->order(['rand()'])->limit($numberOfWinners)->all();

        usort($result, function ($a,$b)
        {
            return -(strtotime($a['Standings']['date_won']) - strtotime($b['Standings']['date_won']) ); // Order by date DESC
        });

        $xml="";
        foreach ($result as $winner) {
            $xml .= "
                <items>
                    <item>
                    <variables>
                        <variable>
                            <key>date_won</key>
                            <value>".date("m/d/Y",strtotime($winner['Standings']['date_won']))."</value>
                        </variable>
                        <variable>
                            <key>winner_name</key>
                            <value>".ucwords($winner['first_name'])." ".ucwords($winner['last_name'])."."."</value>
                        </variable>
                        <variable>
                            <key>winner_city</key>
                            <value>".ucwords($winner['city']).", ".strtoupper($winner['state'])."</value>
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

        $winner = [
            'Winners' => [
                'first_name' => ucwords(strtolower($result->results[0]->name->first)),
                'last_name'=>ucwords(strtolower($result->results[0]->name->last)),
                'city'=>ucwords(strtolower($result->results[0]->location->city)),
                'state'=>$state,
            ],
            'Standings'=>[
                'date_won'=> date("Y-m-d",strtotime('today -1 day')),
            ]
        ];
        return $winner;
    }

    private function removeFirstAndLastBracketFromJson($badJsonWithBracket)
    {
        trim($badJsonWithBracket);
        $hasBracketsAtEndAndStart=$badJsonWithBracket[0]=='('&&$badJsonWithBracket[strlen($badJsonWithBracket)-1]==')';
        if ($hasBracketsAtEndAndStart)
        {
            $badJsonWithBracket=str_replace(['(',')'],'',$badJsonWithBracket);
        }
        return trim($badJsonWithBracket);
    }

    private function getformattedYesterdayWinnerJson($dataArray, $returnPlain = false)
    {
        $title=ucwords(strtolower($dataArray['first_name'])).' '.strtoupper($dataArray['last_name']).'. from '.ucwords(strtolower($dataArray['city'])).', '.$dataArray['state'];
        $pubupdate = date("F j, Y",strtotime($dataArray['Standings']['date_won']));
        $returnArray = [['title' => $title, 'pubdate' => $pubupdate]];
        if ($returnPlain) {
            return $title;
        }
        return json_encode($returnArray,JSON_UNESCAPED_SLASHES);
    }

    private function getformattedWinnerCircleJson($dataArray,$mode="single", $siteCode = 'EDW')
    {
        $returnArray = ["edw_winners"=>[]];
        $processArray=$dataArray;
        if ($mode=='single')
        {
            $processArray=[$dataArray];
        }

        foreach ($processArray as $item)
        {
            $genderedNullAvatar="//cdn.everydaywinner.com/feature/edw/images/male.jpg";
            if(in_array($item['gender'],['f','F','Female','female','FEMALE']))
            {
                $genderedNullAvatar="//cdn.everydaywinner.com/feature/edw/images/female.jpg";
            }

  	        $winnerphotothumb=str_replace("-full","-thumb",explode("winnerphoto/",$item['photo'])[1]);

            if (Configure::read('DEVELOPMENT_MODE')) {
                $baseUrl = "west.everydaywinner.com";
            } else {
                $baseUrl = "www.everydaywinner.com";
            }

            if (strpos($_SERVER['HTTP_HOST'],"east.")>-1) {
                $baseUrl = $_SERVER['HTTP_HOST'];
            }
            $pushValue = [
                'date_won' => date("m/d/Y",strtotime($item['standing']['date_won'])) ,
                'name' => ucwords(strtolower($item['first_name']))." ". ucwords(strtolower($item['last_name'])).".",
                'city' => ucwords(strtolower($item['city'])),
                'state' => $item['state'],
                'blurb' => ucfirst(trim(preg_replace("/[^ -~]/", "", $item['blurb']))),
                'image' => (!empty($item['photo']))?"//". $baseUrl . $this->siteConfigs[$siteCode]['photoUploadPath'] . $winnerphotothumb:$genderedNullAvatar,
            ];

            if ($mode=="single") {
                $pushValue['headline'] = ucfirst($item['headline']);
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
        if (in_array($winner['gender'], ['f','F','Female','female','FEMALE']))
        {
            $genderedNullAvatar = "//cdn.everydaywinner.com/feature/edw/images/female.jpg";
        }

        $winnerphotothumb = ($winner['photo'] !== null)? str_replace("-full","-thumb", explode("winnerphoto/", $winner['photo'])[1]): null;

        if (Configure::read('DEVELOPMENT_MODE')) {
            $baseUrl = "west.everydaywinner.com";
        } else {
            $baseUrl = "www.everydaywinner.com";
        }

        if (strpos($_SERVER['HTTP_HOST'],"east.") > -1) {
            $baseUrl = $_SERVER['HTTP_HOST'];
        }

        $formattedWinner = [
            'date_won' => date("m/d/Y",strtotime($winner['standing']['date_won'])) ,
            'name' => ucwords(strtolower($winner['first_name'])) . " " . ucwords(strtolower($winner['last_name'])) . ".",
            'city' => ucwords(strtolower($winner['city'])),
            'state' => $winner['state'],
            'prize' => !empty($winner['Prize']['name']) ? $winner['Prize']['name'] : "",
            'value' => !empty($winner['Prize']['value']) ? $winner['Prize']['value'] : "",
            'blurb' => ucfirst(trim(preg_replace("/[^ -~]/", "", $winner['blurb']))),
            'image' => (!empty($winner['photo'])) ? "//" . $baseUrl . $this->siteConfigs[$siteCode]['photoUploadPath'] . $winnerphotothumb:$genderedNullAvatar,

        ];

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
            if (in_array($item['gender'], ['f','F','Female','female','FEMALE']))
            {
                $genderedNullAvatar = "//cdn.everydaywinner.com/feature/edw/images/female.jpg";
            }

            $winnerphotothumb = str_replace("-full","-thumb", explode("winnerphoto/",$item['photo'])[1]);

            $featureWinnerFormatted = [
                'date_won' => date("m/d/Y",strtotime($item['standing']['date_won'])) ,
                'name' => ucwords(strtolower($item['first_name'])) . " " . ucwords(strtolower($item['last_name'])) . ".",
                'city' => ucwords(strtolower($item['city'])),
                'state' => $item['state'],
                'prize' => !empty($item['Prize']['name']) ? $item['Prize']['name'] : "",
                'value' => !empty($item['Prize']['value']) ? $item['Prize']['value'] : "",
                'image' => (!empty($item['photo']))?"//". $baseUrl . $this->siteConfigs[$siteCode]['photoUploadPath'] . $winnerphotothumb : $genderedNullAvatar,

            ];

            $result['featuredWinners'][] = $featureWinnerFormatted;
        }

        foreach ($recentWinners as $item) {
            $genderedNullAvatar = "//cdn.everydaywinner.com/feature/edw/images/male.jpg";
            if (in_array($item['gender'], ['f', 'F', 'Female','female', 'FEMALE']))
            {
                $genderedNullAvatar = "//cdn.everydaywinner.com/feature/edw/images/female.jpg";
            }
            $recentWinnerFormatted = [
                'date_won' => date("m/d/Y", strtotime($item['standing']['date_won'])) ,
                'name' => ucwords(strtolower($item['first_name'])) . " " . ucwords(strtolower($item['last_name'])) . ".",
                'city' => ucwords(strtolower($item['city'])),
                'state' => $item['state'],
                'prize' => 'Braun Gillette Face & Body Hair Removal System',
                'value' => 352,
            ];

            $result['recentWinners'][] = $recentWinnerFormatted;
        }

        return json_encode($result, JSON_UNESCAPED_SLASHES);

    }
}

?>
