<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 任务接口
 */
class Issue extends CI_Controller {

    /**
     * 任务写入
     */
    public function write()
    {
        //验证请求的方式
        if (empty($_POST)) {
            exit(json_encode(array('status' => false, 'error' => '本接口只接受POST')));
        }
        
        //验证输入
        $this->load->library('form_validation');
        $this->form_validation->set_rules('project_id', '所属项目团队ID', 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('plan_id', '所属计划ID', 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('type', '类型', 'trim|required|is_natural_no_zero|max_length[1]');
        $this->form_validation->set_rules('level', '优先级', 'trim|required|is_natural_no_zero|max_length[1]');
        $this->form_validation->set_rules('issue_name', '任务标题', 'trim|required');
        $this->form_validation->set_rules('issue_summary', '任务详情', 'trim');
        $this->form_validation->set_rules('add_user', '创建人ID', 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('accept_user', '受理人ID', 'trim|required|is_natural_no_zero');
        if ($this->form_validation->run() == FALSE) {
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //写入数据
        $this->load->model('Model_issue', 'issue', TRUE);
        $Post_data = array(
            'project_id' => $this->input->post('project_id'),
            'plan_id' => $this->input->post('plan_id'),
            'type' => $this->input->post('type'),
            'level' => $this->input->post('level'),
            'issue_name' => $this->input->post('issue_name'),
            'issue_summary' => $this->input->post('issue_summary'),
            'add_user' => $this->input->post('add_user'),
            'accept_user' => $this->input->post('accept_user'),
            'add_time' => time(),
        );
        $id = $this->issue->add($Post_data);
        if ($id) {
            exit(json_encode(array('status' => true, 'data' => $id)));
        } else {
            exit(json_encode(array('status' => false, 'error' => '执行错误')));
        }
    }

    /**
     * 根据条件输出列表
     *
     * 计划id
     * 项目id
     * 步长
     * 偏移
     */
    public function rows_by_plan()
    {
        //验证请求的方式
        if ($_POST) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':本接口只接受GET传值');
            exit(json_encode(array('status' => false, 'error' => '本接口只接受GET传值')));
        }

        //项目id格式验证
        $projectid = $this->input->get('projectid');
        if (!($projectid != 0 && ctype_digit($projectid))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':项目id格式错误');
            exit(json_encode(array('status' => false, 'error' => '项目id格式错误')));
        }

        //计划id格式验证
        $planid = $this->input->get('planid');
        if (!($planid != 0 && ctype_digit($planid))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':计划id格式错误');
            exit(json_encode(array('status' => false, 'error' => '计划id格式错误')));
        }

        //偏移量
        $offset = $this->input->get('offset');
        if (!(ctype_digit($offset))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':查询偏移量格式错误');
            exit(json_encode(array('status' => false, 'error' => '查询偏移量格式错误')));
        }

        $this->load->model('Model_issue', 'issue', TRUE);
        $rows = $this->issue->get_rows(array('id', 'type', 'level', 'issue_name', 'add_user', 'add_time', 'accept_user', 'accept_time', 'workflow', 'status'), array('project_id' => $projectid, 'plan_id' => $planid, 'status' => 1), array('id' => 'desc'), 20, $offset);
        if ($rows) {
            exit(json_encode(array('status' => true, 'content' => $rows)));
        } else {
            exit(json_encode(array('status' => false, 'error' => '数据不存在')));
        }
    }
}