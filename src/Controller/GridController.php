<?php
namespace App\Controller;

class GridController extends AppController{

	public function subscribeUser(){
		// $this->layout = 'ajax';
		$this->autoRender = false;
		$this->viewBuilder()->setLayout('ajax');

		if($this->request->is('post')){
			$data = $this->request->data;

			$result = $this->SendGrid->subscribeUser(ucwords(strtolower($data['first_name'])), ucwords(strtolower($data['last_name'])), $data['email'], $data['source']);
			echo json_encode(array('result'=>$result));
		}
	}

	public function unsubscribeUser(){
		// $this->layout = 'ajax';
		$this->autoRender = false;
		$this->viewBuilder()->setLayout('ajax');

		if($this->request->is('post')){
			$data = $this->request->data;

			$result = $this->SendGrid->unsubscribeUser($data['email']);
			echo json_encode(array('result'=>$result));
		}

	}

}

?>