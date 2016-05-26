<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 消息接口
 */
class Notify extends CI_Controller {

	/**
	 * 消息写入
	 */
	public function write()
	{
		//验证请求的方式
		if (empty($_POST)) {
			exit(json_encode(array('status' => false, 'error' => '本接口只接受POST')));
		}

		//验证输入
		$this->load->library('form_validation');
		$this->form_validation->set_rules('user', '消息接收者', 'trim|required|is_natural_no_zero');
		$this->form_validation->set_rules('log_id', '关联的日志ID', 'trim|required|is_natural_no_zero');
		if ($this->form_validation->run() == FALSE) {
			exit(json_encode(array('status' => false, 'error' => validation_errors())));
		}

		//写入数据
		$this->load->model('Model_notify', 'notify', TRUE);
		$Post_data = array(
			'user' => $this->input->post('user'),
			'log_id' => $this->input->post('log_id'),
			'add_time' => time(),
		);
		$id = $this->notify->add($Post_data);
		if ($id) {
			exit(json_encode(array('status' => true, 'data' => $id)));
		} else {
			exit(json_encode(array('status' => false, 'error' => '执行错误')));
		}
	}
}
