<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 项目团队接口
 *
 * 项目团队是一切协作的基础，先创建项目团队后才可以创建计划和任务
 *
 * @author jiangbianwanghai <webmaster@jiangbianwanghai.com>
 * @since 0.1
 */
class Project extends CI_Controller {

	/**
	 * 项目团队信息写入
	 *
	 * 此方法只接受POST传值，并对传值进行有效性验证.
	 *
	 * 传值
	 *
	 * ```
     * project_name: 项目团队全称 [必填]
     * project_description: 项目团队描述 [选填]
     * add_user: 创建人 [必填]
     * ```
	 *
	 * 错误提示：
	 *
	 * ```
     * {"status":false,"error":"错误信息内容"}
     * ```
     *
	 * 成功提示：
	 *
	 * ```
     * {"status":true,"data":记录ID}
     * ```
     *
	 * @return string 成功返回记录ID，错误返回错误信息。
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

	/**
	 * 输出项目团队缓存信息
	 *
	 * 主要用于常用的项目信息查询，减轻读库的压力
	 */
	public function cache()
	{
		$this->load->model('Model_project', 'project', TRUE);
		$rows = $this->project->get_rows(array('id', 'project_name', 'add_user', 'add_time'));
		if ($rows) {
			exit(json_encode(array('status' => true, 'data' => $rows)));
		} else {
			exit(json_encode(array('status' => false, 'data' => '')));
		}
	}
}
