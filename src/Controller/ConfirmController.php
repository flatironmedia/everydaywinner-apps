<?php

namespace App\Controller;

use App\Shell\ConsoleShell;
use App\Shell\EDWWinnerJsonExportShell as ShellEDWWinnerJsonExportShell;
use Cake\Controller\Controller;
use Cake\Event\Event;
// use Cake\Datasource\ConnectionManager;
use Cake\Routing\Router;
// use DateTime;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\Log\Log;

class ConfirmController extends AppController {
    
	public function beforeFilter(Event $event) {
        parent::beforeFilter($event);
        $this->baseUrl=Router::url('/',true);
    }
    public $uses = array('Winner','Standing','DefaultConfig','Notes', 'Site', 'SiteConfig', 'PrizeSchedule');
    public $components = array('EDWThings', 'Sites'); //"EchoSign",

	public function index(){
		return $this->redirect(array('action'=>'linkexpired'));
    }
    public function testItemClass(){
        // die("haha shit...");
        $response = [
            'status' => "success",
            "data" => []
        ];
        debug($this->response->type('json'));
        debug($this->response->body(json_encode($response)));
        die();
        $winnerCircleShell = new ShellEDWWinnerJsonExportShell();
        $winnerCircleShell->checkWinnerFeedExists();
        debug($winnerCircleShell);
        die();

    }

    public function congratulations($token=null){
        // $this->layout='ewlayout';
        $this->viewBuilder()->getLayout('ewlayout');
        $requestSiteCode = !empty($this->request->query('site')) ? $this->request->query('site') : 'EDW';

        if ($token == null) {
            return $this->redirect(array('action' => 'linkexpired'));
        }
        $token = base64_decode($token);

        $winner = $this->Winner->findByToken($token);
        if (!empty($winner)) {
            $site = $this->Site->find('first', array(
            'conditions' => array('id' => $winner['Standing']['site_id'])
            ));
            $siteCode = $site['Site']['code'];
        }

        if($this->request->is('get')){
            if(!isset($winner['Winner'])) return $this->redirect(array('action'=>'linkexpired'));

            $this->Winner->id = $winner['Winner']['id'];
            $this->Standing->id = $winner['Standing']['id'];

            $dateWon = date("l, F j, Y",strtotime($winner['Standing']['date_won']));

            if($winner['Standing']['status_id'] == 2){ // Notified Status

                if($this->Standing->save(array('status_id'=>3)))// Clicked Status
                {
                    $this->Notes->noteLog("Winner clicked on notification email",$this->Winner->id);
                }
            }

            $this->set(array('winnerData'=> $winner['Winner'],'dateWon'=>$dateWon,'expire'=>$winner['Standing']['expire'],'statesUS'=>$this->EDWThings->getarrayValue('US_States')));
            $this->set('savedWinnerAddress',$this->Session->read('Winner.Address'));
            $this->set('savedWinnerCity',$this->Session->read('Winner.City'));
            $this->set('savedWinnerState',$this->Session->read('Winner.State'));
            $this->set('savedWinnerZip',$this->Session->read('Winner.Zip'));
            $this->set('savedWinnerphone',$this->Session->read('Winner.phone'));
            $this->set('winnerTitle', $this->Sites->getSiteConfig('winnerTitle', $siteCode));
            $this->set('sitePrize', $this->Sites->getSiteConfig('prize', $siteCode));
            $this->set('siteCode', $siteCode);
            $this->set('requestSiteCode', $requestSiteCode);
        }

        if($this->request->is('post')){
            $this->request->data['id'] = base64_decode($this->request->data['id']);

            $mergeInfo = ["mergeInfo"  => [
                "address" => $this->request->data["address_given"],
                "fullname" => $this->request->data["name_winner"],
                "email" => $this->request->data["email_winner"],
                "city" => $this->request->data["city_given"],
                "state" => $this->request->data["state_given"],
                "zip" => $this->request->data["zip_given"],
                "phone" => $this->request->data["phone_given"],
            ]];

            $encodedSignUrl = explode("/", base64_encode($this->EchoSign->getWinnerWidget($mergeInfo, $siteCode)->urlWidgetCreationResult->url));

            unset($this->request->data['email']);
            unset($this->request->data['first_name']);
            unset($this->request->data['last_name']);

            $this->Session->write('Winner.Address', $this->request->data['address_given']);
            $this->Session->write('Winner.City', $this->request->data['city_given']);
            $this->Session->write('Winner.State', $this->request->data['state_given']);
            $this->Session->write('Winner.Zip', $this->request->data['zip_given']);
            $this->Session->write('Winner.phone', $this->request->data['phone_given']);


            $this->Winner->id = $this->request->data['id'];
            $this->request->data['phone_given']=preg_replace('/\D/', '',$this->request->data['phone_given']);
            $this->request->data['city_given']=trim($this->request->data['city_given']);
            $this->Winner->save($this->request->data);
            $this->Winner->Standing->save($winner);

            return $this->redirect(
                array(
                    'action' => 'agreements',
                    base64_encode($winner['Winner']['token']),
                    "widgeturl" => $encodedSignUrl,
                    "?" => array (
                        'site' => $siteCode
                    )
                )
            );
        }
    }

	public function linkexpired()
	{
        $this->viewBuilder()->setLayout('ewlayout');
		// $this->layout='ewlayout';
	}

	public function winnerData(){
        // $this->layout = "ajax";
        $this->viewBuilder()->setLayout("ajax");
		$this->autoRender = false;

		if(!$this->request->is('post')) return $this->redirect(array('action'=>'linkexpired'));

		$newWinnerData = $this->request->data['winner'];
		$winnerId = intval(base64_decode($newWinnerData['winnerId']));

		$winner = $this->Winner->findById($winnerId);
		$isValid = false;

		$phoneNumber = preg_replace('/[-()+ ]/', "", $winner['Winner']['phone']);
		if($phoneNumber != ""){
			$phoneNumber = ($phoneNumber[0] == 1) ? substr($phoneNumber, 1) : $phoneNumber;
		}
		$stateIsValid=($newWinnerData['state'] == $winner['Winner']['state']);
		$cityIsValid=strtolower(trim($newWinnerData['city'])) ==strtolower(trim($winner['Winner']['city']));
		$phoneIsValid=(!empty($winner['Winner']['phone']))?($newWinnerData['phone'] == $phoneNumber):true;
		$zipIsValid=$newWinnerData['zip'] == $winner['Winner']['zip'];

		if($stateIsValid
			// && $newWinnerData['address'] == $winner['Winner']['address']
			&& $cityIsValid
			&& $phoneIsValid
			&& $zipIsValid){
			$isValid = true;
		}
		$this->Winner->id = $winner['Winner']['id'];
		$this->Winner->save(array('address_flag'=>$isValid)); //This is used to signify the address is unconfirmed

		echo json_encode(array('isValid'=>$isValid, 'phone'=>$phoneNumber));

	}

	public function agreements($token=null){
        $requestSiteCode = !empty($this->request->query('site')) ? $this->request->query('site') : 'EDW';
        // $this->layout='ewlayout';
        $this->viewBuilder()->setLayout('ewlayout');
        $encodedEsignWidgetUrl = empty($this->request->params['named']["widgeturl"])
            ? explode("/", base64_encode($this->EchoSign->getWinnerWidget([], $requestSiteCode)->urlWidgetCreationResult->url))[0]
            : implode("/", $this->request->params['named']["widgeturl"]);

		if($encodedEsignWidgetUrl == null){return $this->redirect(array('action'=>'linkexpired'));}
        if($token == null){return $this->redirect(array('action'=>'linkexpired'));}

       	$token = base64_decode($token);
       	$widgetUrl = base64_decode($encodedEsignWidgetUrl);
        if($this->request->is('get')){
        	$winner = $this->Winner->findByToken($token);
            $site = $this->Site->find('first', array(
                'conditions' => array('id' => $winner['Standing']['site_id'])
            ));
            $siteCode = $site['Site']['code'];
        	$displayAgreements = false;
        	if($winner['Standing']['status_id'] == 3){
        		$displayAgreements = true;
                $widget = $this->SiteConfig->getConfigBySiteCode('echo_sign_widget_url', $siteCode);
        		$this->Session->write('Winner.id', base64_encode($winner['Winner']['id']));
        		$this->set('displayAgreements', $displayAgreements);
				$this->set('widget', $widgetUrl);
                $this->set('requestSiteCode', $requestSiteCode);

        	} else {
        		return $this->redirect(array('action'=>'linkexpired'));
        	}
        }
	}


    public function sentdocumentlistener()
    {
        // $this->layout = 'ajax';
        $this->autoRender = false;
        $this->viewBuilder()->setLayout('ajax');

        $winnerId = base64_decode($this->request->query['winnerId']);
        $winner = $this->Winner->findById($winnerId);

        switch ($this->request->query['eventType']) {
            case 'EMAIL_VIEWED':
                $winner['Standing']['status_id'] = 3; //Status Clicked
                break;
            case 'ESIGNED':
                $docKey = $this->request->query['documentKey'];
                $winner['Winner']['doc_key'] = $docKey;
                $winner['Standing']['status_id'] = 4; //Status Signed
                $winner['Standing']['expire'] = 1;
                break;
            
            default:
                return;
        }
        $this->Winner->save($winner);
        $this->Winner->Standing->save($winner);
    }
    public function confirmed(){
        // $this->layout = "ajax";
        $this->autoRender = false;
        $this->viewBuilder()->setLayout("ajax");

        if($this->request->is('get')){
    		$docKey = $this->request->query['documentKey'];
    		$winnerId = base64_decode($this->Session->read('Winner.id'));

    		if(empty($docKey) || empty($winnerId)){
    			return $this->redirect(array('action'=>'linkexpired'));
    		}

    		$winner = $this->Winner->findById($winnerId);
            $site = $this->Site->find('first', array(
                'conditions' => array('id' => $winner['Standing']['site_id'])
            ));
            $siteCode = $site['Site']['code'];

    		if(!isset($winner['Winner']))
			{
				return $this->redirect(array('action'=>'linkexpired'));
			}

    		$this->Winner->id = $winnerId;

    		//Save EchoSign DocKey Here.
    		if($winner['Standing']['status_id'] == 3){

    			$winner['Winner']['doc_key'] = $docKey;
    			$winner['Standing']['status_id'] = 4; //Status Signed
				$winner['Standing']['expire'] = 1;
				$viewVars = array(
		            'name'=>ucwords(strtolower($winner['Winner']['first_name']))
		        );

                $this->SendEmail->sendEmail($winner['Winner']['email'],
                    $this->SiteConfig->getConfigBySiteCode('thankyou_email_subject', $siteCode),
                    $siteCode . DS . 'thankyou',
                    $viewVars
                );
    			$this->Winner->save($winner);
    			$this->Winner->Standing->save($winner);
				$this->Notes->noteLog("Winner signed release",$this->Winner->id);
    		}

    		return $this->redirect(array(
                'action' => 'winnerphotoconfirmationupload',
                '?' => array('site' => $siteCode),
                base64_encode($winner['Winner']['token']),

            ));

    	}

    }

    public function shareGoodNews($token=null){
        $requestSiteCode = !empty($this->request->query('site')) ? $this->request->query('site') : 'EDW';
        // $this->layout='ewlayout';
        $this->viewBuilder()->setLayout('ewlayout');
    	if($token == null){ return $this->redirect(array('action'=>'linkexpired')); }

		if($this->request->is('get')){
	    	$token = base64_decode($token);

	    	$winner = $this->Winner->findByToken($token);
            $site = $this->Site->find('first', array(
                'conditions' => array('id' => $winner['Standing']['site_id'])
            ));
            $siteCode = $site['Site']['code'];

	    	if(!isset($winner['Winner'])){ return $this->redirect(array('action'=>'linkexpired')); }

			$currentUrl = $this->baseUrl.'everydaywinners/sharegoodnews/'.base64_encode($winner['Winner']['token']);

			if($winner['Standing']['status_id'] == 4 || $winner['Standing']['status_id'] == 6){
				$this->set(array('winner'=>$winner, 'baseUrl'=>$this->baseUrl, 'currentUrl'=>$currentUrl));
                $this->set('sitePrize', $this->Sites->getSiteConfig('prize', $siteCode));
                $this->set('siteCode', $siteCode);
                $this->set('requestSiteCode', $requestSiteCode);
			} else {
				return $this->redirect(array('action'=>'linkexpired'));
			}
    	}

    }


	public function winnerphotoconfirmationupload($token=null)
	{
        // $this->layout = 'ewlayout';
        $this->viewBuilder()->setLayout('ewlayout');
        $requestSiteCode = !empty($this->request->query('site')) ? $this->request->query('site') : 'EDW';

        if($token == null){return $this->redirect(array('action'=>'linkexpired'));}
        $decodedToken = base64_decode($token);
        $winner = $this->Winner->findByToken($decodedToken);
        if (!empty($winner)) {
            $site = $this->Site->find('first', array(
                'conditions' => array('id' => $winner['Standing']['site_id'])
            ));
            $siteCode = $site['Site']['code'];
        }

		if ($this->request->is('post'))
        {
			if(isset($this->request->data['croppedImageUrl'])) $winner['Winner']['photo']=$this->request->data['croppedImageUrl'];
			if(isset($this->request->data['happyReason'])) $winner['Winner']['blurb']=$this->request->data['happyReason'];

			$this->Winner->id = $winner['Winner']['id'];
			$this->Winner->save($winner['Winner']);

			return $this->redirect(
                array(
                    'action'=>'sharegoodnews',
                    base64_encode($winner['Winner']['token']),
                    '?' => array('requestSiteCode' => $siteCode)
                )
            );
		}

		$this->set(array('token'=>$token));
        $this->set('site', $siteCode);
        $this->set('winnerTitle', $this->Sites->getSiteConfig('winnerTitle', $siteCode));
        $this->set('siteName', $this->Sites->getSiteConfig('name', $siteCode));
        $this->set('requestSiteCode', $requestSiteCode);
	}

	public function photouploading($token=null,$scale="full")
	{
		$this->autoRender = false;
		if ($this->request->is('post'))
		{
			$token = base64_decode($token);
			$timeOfUpload=(isset($this->request->data['uploadTime']))?$this->request->data['uploadTime']:time();
			$fileIndex = "croppedImage";
			if($scale != "full"){
				$fileIndex = "thumbImage";
			}
			if (!empty($token)) {
                $winner=$this->Winner->findByToken($token);
                $site = $this->Site->find('first', array(
                'conditions' => array('id' => $winner['Standing']['site_id'])
                ));
                $siteCode = $site['Site']['code'];
                $fileName=date("Ymd",strtotime($winner['Standing']['date_won']))."-".$winner['Winner']['visitor_id']."-".$timeOfUpload."-".$scale.".jpg";
            }

			$imageUrl=$this->webroot . $this->Sites->getSiteConfig('photo_upload_path', $siteCode) . $fileName;

            $imagedestination = WWW_ROOT . $this->Sites->getSiteConfig('photo_upload_path', $siteCode) . $fileName;;
			//saves posted file to server
			if (isset($_FILES[$fileIndex])) {
				move_uploaded_file(
					$_FILES[$fileIndex]['tmp_name'],
					$imagedestination
				);
			}
			else {
				echo json_encode(array('result'=>'NOTHING NULL','fileIndex'=>$fileIndex));
			}

		}
		echo $imageUrl;
	}

	public function saveremote()
    {
        $this->autoRender = false;
        $fileName=$this->request->data['fileName'];
        $imageData=base64_decode($this->request->data['fileData']);
        $siteCode = !empty($this->request->data['siteCode']) ? $this->request->data['siteCode'] : 'EDW';

        $imagedestination=WWW_ROOT . $this->Sites->getSiteConfig('photo_upload_path', $siteCode) . $fileName;

        file_put_contents(
            $imagedestination,
            $imageData
        );
    }

    /**
     * Rest Endpoint to rename the winner-feed.xml file
     */
    public function renameWinnerFeed()
    {
        $this->autoRender = false;
        $response = [
            'status' => 'ok',
            'data' => []
        ];

        $winnerCircleShell = new ShellEDWWinnerJsonExportShell();
        if ($winnerCircleShell->checkWinnerFeedExists()) {
            if ($winnerCircleShell->checkWinnerFeedWasCreatedToday()) {
                $response['data']['result'] = false;
            } else {
                $winnerCircleShell->renameWinnerFeed();
                $response['data']['result'] = true;
            }
        } else {
            $response['data']['result'] = true;
        }

        $this->response->type('json');
        $this->response->body(json_encode($response));
        return $this->response;
    }

    public function generateWinnerCircle($siteCode = 'EDW')
    {
        Log::write('debug', "Site code =".$siteCode);
        $this->autoRender = false;
        try {
            $winneCirclerShell = new ShellEDWWinnerJsonExportShell();

            if ($siteCode == 'EDW') {
                $pastWinner = $winneCirclerShell->saveYesterdayWinner($siteCode);
                $randomWinnerSuccess = $winneCirclerShell->saveWinnerFeed();
            } else {
                $pastWinner = $winneCirclerShell->saveLastSundayWinner($siteCode);
            }

            $randomWinnerSuccess = $winneCirclerShell->saveRandomWinner($siteCode);
            $winnerCircleSuccess = $winneCirclerShell->saveWinnerCircle($siteCode);
            Log::write('debug','WinnerCircle ='.$winnerCircleSuccess);
            if ($randomWinnerSuccess && $winnerCircleSuccess && $pastWinner)
            {
                echo "true";
            }
            else
            {
            	echo "false";
            }
        } catch (\Exception $e) {
            $this->winnerFeedErrorNotification($e->getMessage());
            $logMessage = $e->getMessage();
            $logMessage = $logMessage . "\n" . 'Request URL: ' . $this->request->here;
            Log::write('debug', $logMessage);
            Log::write('error', $logMessage);
        }
    }

    public function fetchWinnerCircle($fetch='',$shouldDownload='')
    {
        $this->autoRender=false;
        // $fetch=base64_decode($fetch);
        switch ($fetch) {
            case 'featuredWinner':
                $filename="featuredwinner.json";
                break;
            case 'fullCircle':
                $filename="winnercircledata.json";
                break;
			case 'yesterdaywinner':
				$filename="yesterdaywinner.json";
                break;
			case 'winnerFeed':
				$filename="winner-feed.xml";
                break;
            default:
                return "invalid option";
                break;
        }
		if ($shouldDownload == 'save') {
			header('Content-disposition: attachment; filename='.$filename);
			header('Content-type: application/json');
		}
        return file_get_contents(WWW_ROOT."files\\winnercircle\\".$filename);
    }

    public function fetchWgWinnerCircle($fetch='', $shouldDownload='')
    {
        $this->autoRender = false;

        switch ($fetch) {
            case 'featuredWinner':
                $filename = "featuredwinner.json";
                break;
            case 'fullCircle':
                $filename = "winnercircledata.json";
                break;
            case 'lastsundaywinner':
                $filename = "lastsundaywinner.json";
                break;
            default:
                return "invalid option";
                break;
        }
        if ($shouldDownload == 'save') {
            header('Content-disposition: attachment; filename='.$filename);
            header('Content-type: application/json');
        }
        return file_get_contents(WWW_ROOT . "files\\wg_winnercircle\\" . $filename);
    }

    public function currentPrize($site = 'WG')
    {
        $this->autoRender = false;
        $response = [
            'status' => "success",
            "data" => []
        ];

        switch ($site) {
            case 'WG': {
                $todayDate = new \DateTime();
                $weekNumber = $todayDate->format("W");
                $data = $this->PrizeSchedule->find("first", [
                    'contain' => ['Prize'],
                    'conditions' => [
                        'week_number' => $weekNumber
                    ]
                ]);

                $response['data']['current_prize'] = $data['Prize']["name"];
                break;
            }
            default: {
                $response['status'] = "error";
                $response['message'] = "Invalid site code";
                break;
            }
        }

        $this->response->type('json');
        $this->response->body(json_encode($response));
        return $this->response;
    }

    /**
     * Sends a notification email for winner-feed.xml proccess failures
     * @param $string body of the error email
     */
    private function winnerFeedErrorNotification($error = '')
    {
        $options = [
            'subject' => 'Failed to select a winner for ' . date('m/d/Y'),
            'template' => 'winner_feed_error_notification',
            'viewVars' => [
                'error' => $error
            ]
        ];

        if (Configure::read('DEVELOPMENT_MODE')) {
            $options['recipient'][] = ['jpurena@intellisys.com.do'];
        } else {
            $options['recipient'][] = ['support@flatironmedia.com', 'editorial@flatironmedia.com'];
        }

        $this->SendEmail->sendEmail(
            $options['recipient'],
            $options['subject'],
            $options['template'],
            $options['viewVars']
        );
    }
}
?>
