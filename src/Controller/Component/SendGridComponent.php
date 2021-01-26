<?php
namespace App\Controller\Component;

use Cake\Filesystem\Folder;
use Cake\Filesystem\File;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use SendGrid;
use stdClass;
use DateTime;

class SendGridComponent extends Component {

    private $sendGrid = null;

    public function __construct(ComponentRegistry $collection, $settings = array()){
        $this->sendGrid = new SendGrid(Configure::read('SendGrid.ApiKey')); // Load API KEy from Config File.

        parent::__construct($collection, $settings);
    }

    /**
    * Add a new contact to SendGrid EDW list and subscribe to the two EDW campaigns.
    */
    public function subscribeUser($firstName, $lastName, $email, $source){
        $contact = new stdClass();
        $contact->first_name = $firstName;
        $contact->last_name = $lastName;
        $contact->email = $email;
        $contact->source = $source;

        $userExists = $this->userExists($contact->email);
        $statusCode = 201;

        if(!$userExists){
            $statusCode = $this->addUser($contact);
        }

        $this->removeEDWSuppressionToUser($contact, 'EDW Email Submits');

        return $statusCode;
    }

    /**
    * Unsubscribe an Email address from the EDW Campaigns
    */
    public function unsubscribeUser($email, $groupName=null){

        if(!$this->userExists($email)) {
            return false;
        }

        $recipients = new stdClass();
        $recipients->recipient_emails = array($email);

        $edwSuppressionGroupId = $this->getEDWSuppressionGroupId( (!is_null($groupName)) ? $groupName : 'EDW Email Submits');
        $statusCode = 201;
        if(!is_null($edwSuppressionGroupId)){
            $suppressionResponse = $this->sendGrid->client->asm()->groups()->_($edwSuppressionGroupId)->suppressions()->post($recipients);
            $statusCode = $suppressionResponse->statusCode();
        }

        return $statusCode;
    }

    /**
    * Create Campaign and Send the Campaign to all the Subscribed Users.
    */
    public function createAndSendCampaign($campaignCode, $segmentName='Mailable List', $senderNickname='EDW mail@', $suppressionGroupName='EDW Email Submits'){
        $campaignId = $this->createCampaign($campaignCode, $segmentName, $senderNickname, $suppressionGroupName);

        if(!is_null($campaignId) && intval($campaignId) > 0){
          return $this->sendCampaign($campaignId);
        }

        return null;
    }

    /**
    * Create a New Campaign in SendGrid
    */
    public function createCampaign($campaignCode, $segmentName, $senderNickname, $suppressionGroupName){
        $unsubscribeTag = '| To unsubscribe click: here [Unsubscribe]';
        if($campaignCode == 'EDW_WINNER_CIRCLE')
        {
            $title = "EDW Winner's Circle - Test";
            $subject = "Winner's Circle - Test";
            $plainContent = "Winner's Circle - Test ".$unsubscribeTag;
            $winnerFileName = '\EveryDayWinner-Gold-B.html';

            //Get Yesterday Winner 
            $Winner = TableRegistry::getTableLocator()->get('Winners');

            $lastWinner = $Winner->find('all', array('order'=>'Winner.id desc', 'fields'=>array('first_name', 'last_name', 'city', 'state')))->first();
            $winnerName = $lastWinner['first_name'].' '.substr($lastWinner['last_name'],0,1).'. '.$lastWinner['city'].', '.$lastWinner['state'];
        } 
        elseif ($campaignCode == 'EDW_LAST_CHANCE') 
        {
            $title = "EDW Last Chance - Test";
            $subject = "Everyday Winner - Last Chance - Test";
            $plainContent = "Winner's Last Chance - Test ".$unsubscribeTag;
            $winnerFileName = '\EveryDayWinner-Gold-A.html';
        }

        $segmentId = $this->getSegmentId($segmentName);
        $senderId = $this->getSenderId($senderNickname);
        $suppressionGroupId = $this->getEDWSuppressionGroupId($suppressionGroupName);

        if(intval($segmentId) == 0 || intval($senderId) == 0 || intval($suppressionGroupId) == 0 || !isset($title)) return null;

        $edwFolder = ROOT.'\everydaywinner';
        $edwFile = new File($edwFolder.$winnerFileName);

        $edwFileContent = $edwFile->read();
        $edwFile->close();
        
        $campaignHtml = str_replace('WINNER NAME HERE', (isset($winnerName)) ? $winnerName : '', $edwFileContent); // Replace with Yesterday Winner.

        $newCampaign = new stdClass();
        $newCampaign->title = $title;
        $newCampaign->subject = $subject;
        $newCampaign->html_content = $campaignHtml;
        $newCampaign->plain_text = $plainContent;
        $newCampaign->segment_ids = array($segmentId);
        $newCampaign->sender_id = $senderId;
        $newCampaign->suppression_group_id = $suppressionGroupId;
        $newCampaign->categories = array();
        $newCampaign->list_ids = array();
        $newCampaign->custom_unsubscribe_url = "";

        $campaignResponse = $this->sendGrid->client->campaigns()->post($newCampaign);
        $campaignResult = json_decode($campaignResponse->body());

        if(isset($campaignResult->id)) return $campaignResult->id;

        return null;

    }
    /**
    * Update new Contacts mail_to field from 'N' to 'Y'
    */
    public function updateSendGridConctactList(){
        $segmentId = $this->getSegmentId('Mailable List');

        if(is_null($segmentId)) return null;

        $request = new stdClass();
        $query = new stdClass();
        $today = new DateTime();
        $filterCondition = new stdClass();

        $filterCondition->field = 'created_at';
        $filterCondition->value = $today->format('m/d/Y');
        $filterCondition->operator = 'lt'; // is Before

        $request->conditions = array($filterCondition);
        $query->segment_id = $segmentId;

        $segmentResponse = $this->sendGrid->client->contactdb()->segments()->_($segmentId)->patch($request, $query);
        $segmentResult = json_decode($segmentResponse->body());

        if(isset($segmentResult->id)) return $segmentResult->id;

        return null;
    }

    //Check if the Contact is Already in the SendGrid.
    private function userExists($email){
        $search_param = json_decode('{"email" : "'.$email.'"}');
        $searchResponse = $this->sendGrid->client->contactdb()->recipients()->search()->get(null, $search_param);
        $searchResult = json_decode($searchResponse->body());

        if(count($searchResult->recipients) == 0) return false;

        return true;
    }

    //Add User to SendGrid.
    private function addUser($contact){
        $contactResponse = $this->sendGrid->client->contactdb()->recipients()->post(array($contact));

        return $contactResponse->statusCode();
    }

    //Update User in the SendGrid.
    private function updateUser($contact){
        $contactResponse = $this->sendGrid->client->contactdb()->recipients()->patch(array($contact));

        return $contactResponse->statusCode();
    }

    //Remove Suprression from 'EDW Email Submits' Group.
    private function removeEDWSuppressionToUser($contact, $suppressionGroupName){
        $edwSuppressionGroupId = $this->getEDWSuppressionGroupId($suppressionGroupName, $contact->email);

        if(!is_null($edwSuppressionGroupId)){
            // Remove User Suppression from 'EDW Email Submits' Group.
            $removeSuppressionResponse = $this->sendGrid->client->asm()->groups()->_($edwSuppressionGroupId)->suppressions()->_($contact->email)->delete();
        }

        return true;
    }

    //Get Suppression Group Id by Group Name
    private function getEDWSuppressionGroupId($groupName, $email=null){
        $edwSuppressionGroupId = null;
        
        if(!is_null($email)){
            
            $suppressionResponse = $this->sendGrid->client->asm()->suppressions()->_($email)->get();
            $suppressionResult = json_decode($suppressionResponse->body());
            
            foreach($suppressionResult->suppressions as $suppressionGroup){
                if($suppressionGroup->name == $groupName && $suppressionGroup->suppressed){
                    $edwSuppressionGroupId = $suppressionGroup->id;
                }
            }

        } else {
            $groupResponse = $this->sendGrid->client->asm()->groups()->get();
            $groupResult = json_decode($groupResponse->body());

            foreach($groupResult as $suppressionGroup){
                if($suppressionGroup->name == $groupName){
                    $edwSuppressionGroupId = $suppressionGroup->id;
                }
            }
        }

        return $edwSuppressionGroupId;
    }

    //Get Segment ID by Segment Name
    private function getSegmentId($name){
        $segmentResponse = $this->sendGrid->client->contactdb()->segments()->get();
        $segmentResult = json_decode($segmentResponse->body());
        foreach($segmentResult->segments as $segment){
            if($segment->name == $name){
                return $segment->id;
            }
        }
        return null;
    }

    //Get Sender ID by NickName
    private function getSenderId($nickName){
        $senderResponse = $this->sendGrid->client->senders()->get();
        $senderResult = json_decode($senderResponse->body());

        foreach($senderResult as $sender){
            if($sender->nickname == $nickName){
                return $sender->id;
            }
        }

        return null;
    }

    //Send Campaign to all subscribed users.
    public function sendCampaign($campaignId){
        $campaignResponse = $this->sendGrid->client->campaigns()->_($campaignId)->schedules()->now()->post();
        $campaignResult = json_decode($campaignResponse->body());

        if(isset($campaignResult->status)) return $campaignResult->status;

        return $campaignResponse->statusCode();
    }

}
