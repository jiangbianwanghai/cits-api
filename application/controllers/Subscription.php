<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 订阅接口
 */
class Subscription extends CI_Controller {

	/**
	 * 订阅写入
	 */
	public function write()
	{
		//验证请求的方式
		if (empty($_POST)) {
			exit(json_encode(array('status' => false, 'error' => '本接口只接受POST')));
		}

		//验证输入
		$this->load->library('form_validation');
		$this->form_validation->set_rules('target', '目标ID', 'trim|required|is_natural_no_zero');
		$this->form_validation->set_rules('target_type', '目标类型', 'trim|required|is_natural_no_zero|max_length[1]');
		$this->form_validation->set_rules('user', '用户ID', 'trim|required|is_natural_no_zero');
		if ($this->form_validation->run() == FALSE) {
			exit(json_encode(array('status' => false, 'error' => validation_errors())));
		}

		//写入数据
		$this->load->model('Model_subscription', 'subscription', TRUE);
		$Post_data = array(
			'target' => $this->input->post('target'),
			'target_type' => $this->input->post('target_type'),
			'user' => $this->input->post('user'),
			'add_time' => time(),
		);
		$id = $this->subscription->add($Post_data);
		if ($id) {
			exit(json_encode(array('status' => true, 'data' => $id)));
		} else {
			exit(json_encode(array('status' => false, 'error' => '执行错误')));
		}
	}
}
