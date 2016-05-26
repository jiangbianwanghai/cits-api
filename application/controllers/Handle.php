<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 操作日志
 */
class Handle extends CI_Controller {

	/**
	 * 日志写入
	 */
	public function write()
	{
		//验证输入
		$this->load->library('form_validation');
		$this->form_validation->set_rules('sender', '发送者', 'trim|required|is_natural_no_zero');
		$this->form_validation->set_rules('action', '动作', 'trim|required');
		$this->form_validation->set_rules('target_type', '目标类型', 'trim|required|is_natural_no_zero');
		$this->form_validation->set_rules('target', '目标ID', 'trim|required|is_natural_no_zero');
		$this->form_validation->set_rules('type', '日志类型', 'trim|required|is_natural_no_zero');
		if ($this->form_validation->run() == FALSE) {
			exit(json_encode(array('status' => false, 'error' => validation_errors())));
		}

		//写入数据
		$this->load->model('Model_logs', 'logs', TRUE);
		$Post_data = array(
			'sender' => $this->input->post('sender'),
			'action' => $this->input->post('action'),
			'target_type' => $this->input->post('target_type'),
			'target' => $this->input->post('target'),
			'type' => $this->input->post('type'),
			'add_time' => time(),
		);
		$id = $this->logs->add($Post_data);
		if ($id) {
			exit(json_encode(array('status' => true, 'data' => $id)));
		} else {
			exit(json_encode(array('status' => false, 'error' => '执行错误')));
		}
	}
}