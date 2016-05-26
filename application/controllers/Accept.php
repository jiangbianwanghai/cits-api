<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 参与人接口
 */
class Accept extends CI_Controller {

	/**
	 * 参与人写入
	 */
	public function write()
	{
		//验证请求的方式
		if (empty($_POST)) {
			exit(json_encode(array('status' => false, 'error' => '本接口只接受POST')));
		}

		//验证输入
		$this->load->library('form_validation');
		$this->form_validation->set_rules('accept_user', '参与者ID', 'trim|required|is_natural_no_zero');
		$this->form_validation->set_rules('issue_id', '任务ID', 'trim|required|is_natural_no_zero');
		$this->form_validation->set_rules('flow', '参与角色类型', 'trim|required|is_natural_no_zero');
		if ($this->form_validation->run() == FALSE) {
			exit(json_encode(array('status' => false, 'error' => validation_errors())));
		}

		//写入数据
		$this->load->model('Model_accept', 'accept', TRUE);
		$Post_data = array(
			'accept_user' => $this->input->post('accept_user'),
			'issue_id' => $this->input->post('issue_id'),
			'flow' => $this->input->post('flow'),
			'accept_time' => time(),
		);
		$id = $this->accept->add($Post_data);
		if ($id) {
			exit(json_encode(array('status' => true, 'data' => $id)));
		} else {
			exit(json_encode(array('status' => false, 'error' => '执行错误')));
		}
	}
}
