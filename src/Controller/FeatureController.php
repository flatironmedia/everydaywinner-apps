<?php
// App::import('Console/Command', 'AppShell');
// App::import('Console/Command', 'EDWWinnerJsonExportShell');
/**
*Fallback Controller for EDW in case bad rerouting occurs for currently Unknown reasons
*/
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Log\Log;

class FeatureController extends AppController {

    public function index()
    {
        return $this->redirect(array('action'=>'edw'));
    }

    public function edw()
    {
        
        $log="
        =======================".time()."==========================
        Entered Feature Controller
        request to:".$this->request->url."
        ";

        Log::info($log, ['scope'=>['featureLog']]);

        $redirect=str_replace("feature/EDW/", "/", $this->request->url);
        $redirect=str_replace("feature/edw/", "/", $redirect);

        return $this->redirect($redirect);
    }
}
?>