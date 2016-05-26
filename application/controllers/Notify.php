<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notify extends CI_Controller {

	public function log()
	{
		print_r($_POST);
		$this->load->model('Model_logs', 'logs', TRUE);
	}
}
