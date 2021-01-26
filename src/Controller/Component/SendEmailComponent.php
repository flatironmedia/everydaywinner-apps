<?php
namespace App\Controller\Component;

// use Network\Email;
use Cake\Mailer\Email;
use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;

class SendEmailComponent extends Component {

    public function sendEmail($recipient, $subject, $template, $viewVars=array(), $options = array()){
        $Email = $this->getEmailHelper();
        $ccRecipient = array();
        $emailSender = array('no-reply@flatironmedia.com' => 'Everyday Winner');

        if(sizeof($options) > 0){
            if(array_key_exists('cc_recipient', $options)){
                $ccRecipient = $options['cc_recipient'];
            }
            if(array_key_exists('email_sender', $options)){
                if(sizeof($options['email_sender']) > 0){
                    $emailSender = $options['email_sender'];
                }
            }
        }

        $Email->viewBuilder()->setTemplate($template, 'default');
        $Email->setEmailFormat('html')
            ->setViewVars($viewVars)
            ->setTo($recipient)
            ->setCc($ccRecipient)
            ->setFrom(array('no-reply@flatironmedia.com' => 'Everyday Winner'))
            ->setReplyTo($emailSender)
            ->setSubject($subject)
            ->send();
    }

    private function getEmailHelper(){
        $siteConfigsTable = TableRegistry::getTableLocator()->get('SiteConfig');

        $host = $siteConfigsTable->getConfigBySiteCode('smtp_host', 'EDW');
        $sender = $siteConfigsTable->getConfigBySiteCode('smtp_email', 'EDW');
        $password = $siteConfigsTable->decrypt($siteConfigsTable->getConfigBySiteCode('smtp_password', 'EDW'));
        $port = $siteConfigsTable->getConfigBySiteCode('smtp_port', 'EDW');

        $Email = new Email(array(
            'host' => $host,
            'port' => $port,
            'username' => $sender,
            'password' => $password,
            'transport' => 'Smtp'
        ));

        return $Email;
    }
}
