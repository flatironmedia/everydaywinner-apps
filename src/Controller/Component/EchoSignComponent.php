<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Routing\Router;
use Cake\ORM\TableRegistry;
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;
use Exception;
use Cake\Log\Log;
use Cake\Core\Configure;

//Try to import the Lib in other way.
require_once(ROOT.DS.'src'.DS.'Lib'.DS.'EchoSign'.DS.'API.php');
require_once(ROOT.DS.'src'.DS.'Lib'.DS.'EchoSign'.DS.'Info'.DS.'FileInfo.php');
require_once(ROOT.DS.'src'.DS.'Lib'.DS.'EchoSign'.DS.'Info'.DS.'MergeFieldInfo.php');
require_once(ROOT.DS.'src'.DS.'Lib'.DS.'EchoSign'.DS.'Info'.DS.'RecipientInfo.php');
require_once(ROOT.DS.'src'.DS.'Lib'.DS.'EchoSign'.DS.'Info'.DS.'SenderInfo.php');
require_once(ROOT.DS.'src'.DS.'Lib'.DS.'EchoSign'.DS.'Info'.DS.'AbstractCreationInfo.php');
require_once(ROOT.DS.'src'.DS.'Lib'.DS.'EchoSign'.DS.'Info'.DS.'DocumentCreationInfo.php');
require_once(ROOT.DS.'src'.DS.'Lib'.DS.'EchoSign'.DS.'Info'.DS.'WidgetCreationInfo.php');
require_once(ROOT.DS.'src'.DS.'Lib'.DS.'EchoSign'.DS.'Options'.DS.'AbstractDocumentOptions.php');
require_once(ROOT.DS.'src'.DS.'Lib'.DS.'EchoSign'.DS.'Options'.DS.'GetDocumentsOptions.php');
require_once(ROOT.DS.'src'.DS.'Lib'.DS.'EchoSign'.DS.'Options'.DS.'GetDocumentUrlsOptions.php');

class EchoSignComponent extends Component {

    public $apiKey = '';

    private $client = null;
    private $api = null;
    private $baseUrl = null;
    private $DefaultConfig = null;

    public $components = ['Site', 'AdobeSignRestApi'];

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->DefaultConfig = TableRegistry::getTableLocator()->get("DefaultConfigs");
        $this->SiteConfig = TableRegistry::getTableLocator()->get("SiteConfigs");
        $this->apiKey = $this->SiteConfig->getConfigBySiteCode('echo_sign_api_key', 'EDW');
        $this->baseUrl = Router::url('/', true);
    }

    public function getBaseUris()
    {
        try {
            return $this->getAPI()->getBaseUris();
        } catch (Exception $e) {
            return array();
        }
    }

    private function getClient(){
        if($this->client == null)
            $this->client = new \SoapClient(\EchoSign\API::getWSDL());
        return $this->client;
    }

    private function getAPI(){
        if($this->api == null)
            $this->api = new \EchoSign\API($this->getClient(), $this->apiKey);
        return $this->api;
    }

    public function sendToWinner($winnerObject, $options = array()){
        $result = array('docKey' => null);
        $recipient = $winnerObject->email;
        $result['docKey'] = $this->sendDocument($winnerObject, array('recipient'=>$recipient, 'sender'=>null, 'message'=>'winnerMessage'));
        return $result;
    }

    public function getDocumentInfo($documentKey){
        try {
            return $this->getAPI()->getDocumentInfo($documentKey);
        } catch (Exception $e) {
            return array();
        }
    }

    private function createPDFDocument($winner){
        $generator = $this->baseUrl . 'winners/getpdf/' . base64_encode($winner['Winner']['id']) . '/pdf/1';
        $file = '';
        $fileFullPath = '';
        $context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
        $fileContents = file_get_contents($generator,false,$context);
        if($fileContents == ""){
            $fileFullPath = ROOT . DS . 'tmpfiles' . DS . $winner['Winner']['token'] . '.pdf';
            $file = \EchoSign\Info\FileInfo::createFromFile($fileFullPath);
        }
        if($file == ''){
            // Show Error notification
        }
        return array('file' => $file, 'path' => $fileFullPath);
    }

    private function createReleaseDocument($winner){
        $fileFullPath = ROOT . DS .'tmpfiles' . DS ."EverydayWinner_Release_Document" . '.pdf';
        $file = \EchoSign\Info\FileInfo::createFromFile($fileFullPath);
        return array('file' => $file, 'path' => $fileFullPath);
    }

    private function getRecipient($params){
        $recipientInfo = null;
        $recipient = isset($params['recipient']) ? $params['recipient'] : array();
        $recipientInfo = new \EchoSign\Info\RecipientInfo();
        $recipientInfo->addRecipient($recipient);
        return $recipientInfo;
    }

    private function getDocumentCreationInfo($file, $params = array()){
        $documentTitle = isset($params['recipient']) ? $params['recipient'] : 'Flat Iron Media - E-Signature';
        $document = new \EchoSign\Info\DocumentCreationInfo($documentTitle, $file);
        return $document;
    }

    private function getWidgetCreationInfo($file, $params = array()){
        $documentTitle = isset($params['recipient']) ? $params['recipient'] : 'Everyday Winner - E-Signature';
        $mergeInfo = isset($params['mergeInfo']) ? $params['mergeInfo']: NULL;
        $document = new \EchoSign\Info\WidgetCreationInfo($documentTitle, $file, $mergeInfo);
        // set after sign callback
        if(Configure::read('DEVELOPMENT_MODE')) {
            $document->setWidgetCompletionInfo('https://west.everydaywinner.com/feature/EDW/app/confirm/confirmed?site=' . $params['siteCode'], true);
        } else {
            $document->setWidgetCompletionInfo('https://www.everydaywinner.com/feature/EDW/app/confirm/confirmed?site=' . $params['siteCode'], true);
        }
        return $document;
    }

    private function getSenderInfo($params, $recipientInfo = null){
        $senderInfo = null;
        return $senderInfo;
    }

    private function getDocumentMessage($params){
        switch ($params['message']) {
        case 'winnerMessage':
            $message = file_get_contents(APP."Template".DS."Email".DS."html".DS."thirdnotification.ctp");
            break;

        default:
            $message = '
                Please review and sign it electronically. If you have any questions, please contact FM representative.
                Thank you for your business.
                Flatiron Media';
            break;
        }
        return $message;
    }

    private function getCallBackRoute($winner){
        $token = base64_encode($winner['Winner']['token']);
        return $this->baseUrl . 'winners/esignaturelistener/' . $token;
    }

    private function getCarbonCopyUsers($params){
        return null;
    }

    public function sendDocument($winner, $options){
        $documentKey = null;

        $pdfDocument = $this->createReleaseDocument($winner);

        if($pdfDocument['file'] == '') return $documentKey;

        $recipientInfo = $this->getRecipient($options);

        if($recipientInfo == null) return $documentKey;

        $document = $this->getDocumentCreationInfo($pdfDocument['file'], $options);
        $document->setRecipients($recipientInfo);

        $senderInfo = $this->getSenderInfo($options, $recipientInfo);

        $message = $this->getDocumentMessage($options);
        $message = $this->formatWinnerMessage($message,$winner);
        $document->setMessage($message);
        $document->setName("EverydayWinner Release Document");

        $sentResult = $this->getAPI()->sendDocument($document, $senderInfo);

        if(isset($sentResult->documentKeys) &&
           isset($sentResult->documentKeys->DocumentKey) &&
           isset($sentResult->documentKeys->DocumentKey->documentKey)){
            $documentKey = $sentResult->documentKeys->DocumentKey->documentKey;
        }
        return $documentKey;
    }

    private function formatWinnerMessage($message, $winner)
    {
        $firstName=ucwords(strtolower($winner->first_name));
        $dateWon=date("l, F j Y",strtotime($winner->standing->date_won));
        $message=str_replace('[firstname]',$firstName, $message );
        $message=str_replace('[datewon]',$dateWon, $message );
        return $message;
    }
    //This if set for when  echosign is going to be used  someday here
    public function getWinnerWidget($options, $siteCode = 'EDW'){
        $documentKey = null;
        $fileFullPath = $this->Site->getSiteConfig('release_document_url', $siteCode);
        $fileFullPath = isset($options['filePath']) ? $options['filePath'] : $fileFullPath;

        if (isset($options["mergeInfo"])) {
            $mergeInfo = new \EchoSign\Info\MergeFieldInfo($options["mergeInfo"]);
            $options["mergeInfo"] = $mergeInfo;
        }
        $document = $this->AdobeSignRestApi->createWidgetInfo($fileFullPath, $options);
        $widgetResult = $document;
        return $widgetResult;
    }

    private function deleteTempDocument($path){
        try{
            unlink($path);
        }catch (Exception $e){}
    }

    public function getMyDocuments(){
        return $this->getAPI()->getMyDocuments()->getMyDocumentsResult->documentListForUser->DocumentListItem;
    }

    public function getDocumentUrl($documentKey)
    {
        $documentURL = '';
        if(trim($documentKey) != ''){
            $option = new \EchoSign\Options\GetDocumentUrlsOptions();
            $response = $this->getAPI()->getDocumentUrls($documentKey, $option);
            if(isset($response->getDocumentUrlsResult->urls->DocumentUrl->url)){
                $documentURL = $response->getDocumentUrlsResult->urls->DocumentUrl->url;
            }
        }
        return $documentURL;
    }

    public function getSigningUrl($documentKey)
    {
        $documentURL = '';
        if(trim($documentKey) != ''){
            $response = $this->getAPI()->getSigningUrl($documentKey);
            if(isset($response->getSigningUrlResult->signingUrls->SigningUrl->esignUrl)){
                $documentURL = $response->getSigningUrlResult->signingUrls->SigningUrl->esignUrl;
            }

        }
        return $documentURL;
    }

    public function sendReminder($documentKey){
        $this->Notification->write('reminder_send');
        $sent = false;
        if(!empty($documentKey)){
            $result = $this->getAPI()->sendReminder($documentKey);
            if(isset($result->sendreminderResult) && isset($result->sendreminderResult->result)){
                $resultMessageKey = (string) $result->sendreminderResult->result;
                $message = $this->reminderResultMessages[$resultMessageKey];
                $this->Notification->write('reminder', array('values'=>array($message)));
                $sent = true;
            }
        }else{
            $this->Notification->write('reminder_key');
        }
        return $sent;
    }
}
