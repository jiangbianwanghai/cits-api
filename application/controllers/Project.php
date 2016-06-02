<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 项目团队接口
 */
class Project extends CI_Controller {

	/**
	 * 项目团队信息写入
	 */
	public function write()
	{
		//验证请求的方式
		if (empty($_POST)) {
			exit(json_encode(array('status' => false, 'error' => '本接口只接受POST')));
		}

		//验证输入
		$this->load->library('form_validation');
		$this->form_validation->set_rules('project_name', '项目团队全称', 'trim|required');
		$this->form_validation->set_rules('project_description', '描述', 'trim');
		$this->form_validation->set_rules('add_user', '创建人', 'trim|required|is_natural_no_zero');
		if ($this->form_validation->run() == FALSE) {
			exit(json_encode(array('status' => false, 'error' => validation_errors())));
		}

		//写入数据
		$this->load->model('Model_project', 'project', TRUE);
		$Post_data = array(
			'project_name' => $this->input->post('project_name'),
			'project_discription' => $this->input->post('project_description'),
			'add_user' => $this->input->post('add_user'),
			'add_time' => time(),
		);
		$id = $this->project->add($Post_data);
		if ($id) {
			exit(json_encode(array('status' => true, 'data' => $id)));
		} else {
			exit(json_encode(array('status' => false, 'error' => '执行错误')));
		}
	}
}
