<?php
namespace App\Controller;

class UnsubscribeController extends AppController {

	public function index(){
		// $this->layout='ewlayout';
		$this->viewBuilder()->setLayout('ewlayout');
		if($this->request->is('post')){
			$email = htmlentities($this->request->data['email_winner']);
			$result = $this->SendGrid->unsubscribeUser($email);

			$message = "Your email address is now unsubscribed.";
			$success = true;
			if($result == false){
				$message = "We could not find your email address in our mailing list.";
				$success = false;
			}

			$this->set(array('success'=> $success,'message'=>$message, 'unsubscribed'=>true));
		}
	}
}
?>