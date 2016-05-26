<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Authorize extends CI_Controller {

	public function get_token()
	{
		$token = array('name' => $this->security->get_csrf_token_name(), 'cookie' => $this->security->get_csrf_hash());
		echo json_encode($token);
	}
}