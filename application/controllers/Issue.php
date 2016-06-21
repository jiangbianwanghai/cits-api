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
        if ($_GET) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':本接口只接受POST传值');
            exit(json_encode(array('status' => false, 'error' => '本接口只接受POST传值')));
        }

        //POST传值不能为空
        if (empty($_POST)) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':请填写POST数据');
            exit(json_encode(array('status' => false, 'error' => '请填写POST数据')));
        }
        
        //验证输入
        $this->load->library('form_validation');
        $this->form_validation->set_rules('project_id', '所属项目团队ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '所属项目团队ID[ '.$this->input->post('project_id').' ]不符合规则',
            )
        );
        $this->form_validation->set_rules('plan_id', '所属计划ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '所属计划ID[ '.$this->input->post('plan_id').' ]不符合规则',
            )
        );
        $this->form_validation->set_rules('type', '类型', 'trim|required|is_natural_no_zero|max_length[1]',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '类型[ '.$this->input->post('type').' ]不符合规则',
                'max_length' => '类型[ '.$this->input->post('type').' ]太长了'
            )
        );
        $this->form_validation->set_rules('level', '优先级', 'trim|required|is_natural_no_zero|max_length[1]',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '优先级[ '.$this->input->post('level').' ]不符合规则',
                'max_length' => '优先级[ '.$this->input->post('level').' ]太长了'
            )
        );
        $this->form_validation->set_rules('issue_name', '任务标题', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('issue_summary', '任务详情', 'trim');
        $this->form_validation->set_rules('url', '相关链接', 'trim');
        $this->form_validation->set_rules('add_user', '创建人ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '类型[ '.$this->input->post('add_user').' ]不符合规则'
            )
        );
        $this->form_validation->set_rules('accept_user', '受理人ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '类型[ '.$this->input->post('accept_user').' ]不符合规则'
            )
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        if ($this->_check_issue_name($this->input->post('plan_id'), $this->input->post('issue_name'))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':任务名[ '.$this->input->post('issue_name').' ], 计划id[ '.$this->input->post('plan_id').' ]验证重复');
            exit(json_encode(array('status' => false, 'error' => '此任务已经存在')));
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
            'url' => $this->input->post('url'),
            'add_time' => time(),
        );
        $id = $this->issue->add($Post_data);
        if ($id) {
            exit(json_encode(array('status' => true, 'content' => $id)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入错误');
            exit(json_encode(array('status' => false, 'error' => '写入错误')));
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

    /**
     * 验证任务名称是否重复
     *
     * 同一个计划下的任务名称不能重复
     */
    private function _check_issue_name($planid = 0 , $issue_name = '')
    {
        if ($planid && $issue_name) {
            $this->load->model('Model_issue', 'issue', TRUE);
            $rows = $this->issue->get_rows(array('id'), array('plan_id' => $planid, 'issue_name' => $issue_name));
            if ($rows['total']) {
                return true;
            } else {
                return false;
            }
        }
    }
}