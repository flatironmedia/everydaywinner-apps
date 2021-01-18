<?php
namespace App\Controller;

use Cake\Core\Configure;
use App\Controller\Component\SendGridComponent;


class GridController extends AppController{

	// public function testGrid(){//this is temporary
	// 	$this->autoRender = false;
	// 	$this->sendGrid = new SendGridComponent("test");
	// 	debug($this->sendGrid);
	// 	die();
	// }
	public function subscribeUser(){
		// $this->layout = 'ajax';
		$this->viewBuilder()->setLayout('ajax');
		$this->autoRender = false;

		if($this->request->is('post')){
			$data = $this->request->data;

			$result = $this->SendGrid->subscribeUser(ucwords(strtolower($data['first_name'])), ucwords(strtolower($data['last_name'])), $data['email'], $data['source']);
			echo json_encode(array('result'=>$result));
		}
	}

	public function unsubscribeUser(){
		// $this->layout = 'ajax';
		$this->viewBuilder()->setLayout('ajax');
		$this->autoRender = false;

		if($this->request->is('post')){
			$data = $this->request->data;

			$result = $this->SendGrid->unsubscribeUser($data['email']);
			echo json_encode(array('result'=>$result));
		}

	}

}

?>