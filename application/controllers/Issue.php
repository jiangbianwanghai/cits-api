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
     * 任务更新
     */
    public function update()
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
        $this->form_validation->set_rules('issue_id', '任务ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '任务ID[ '.$this->input->post('issue_id').' ]不符合规则',
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
        $this->form_validation->set_rules('last_user', '修改人ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '类型[ '.$this->input->post('last_user').' ]不符合规则'
            )
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        if ($this->_check_issue_name($this->input->post('plan_id'), $this->input->post('issue_name')) > 1) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':任务名[ '.$this->input->post('issue_name').' ], 计划id[ '.$this->input->post('plan_id').' ]验证重复');
            exit(json_encode(array('status' => false, 'error' => '此任务已经存在')));
        }

        //写入数据
        $this->load->model('Model_issue', 'issue', TRUE);
        $Post_data = array(
            'type' => $this->input->post('type'),
            'level' => $this->input->post('level'),
            'issue_name' => $this->input->post('issue_name'),
            'issue_summary' => $this->input->post('issue_summary'),
            'url' => $this->input->post('url'),
            'last_user' => $this->input->post('last_user'),
            'last_time' => time(),
        );
        $bool = $this->issue->update_by_where($Post_data, array('id' => $this->input->post('issue_id')));
        if ($bool) {
            exit(json_encode(array('status' => true, 'content' => $bool)));
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
        $where = array('project_id' => $projectid, 'plan_id' => $planid, 'status >=' => 0);
        $filter = $this->input->get('filter');
        if ($filter) {
            $filter_arr = explode('|', $filter);
            if ($filter_arr) {
                foreach ($filter_arr as $key => $value) {
                    $tmp = explode(',', $value);
                    $where[$tmp[0]] = $tmp[1];
                }
            }
        }
        $this->load->model('Model_issue', 'issue', TRUE);
        $rows = $this->issue->get_rows(array('id', 'type', 'level', 'issue_name', 'add_user', 'add_time', 'accept_user', 'accept_time', 'workflow', 'status'), $where, array('id' => 'desc'), 20, $offset);
        if ($rows) {
            exit(json_encode(array('status' => true, 'content' => $rows)));
        } else {
            exit(json_encode(array('status' => false, 'error' => '数据不存在')));
        }
    }

    /**
     * 根据任务id输出任务详情
     */
    public function profile()
    {
        //验证请求的方式
        if ($_POST) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':本接口只接受GET传值');
            exit(json_encode(array('status' => false, 'error' => '本接口只接受GET传值')));
        }

        //GET传值不能为空
        if (empty($_GET)) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':请填写GET数据');
            exit(json_encode(array('status' => false, 'error' => '请填写GET数据')));
        }

        //任务id格式验证
        $id = $this->input->get('id');
        if (!($id != 0 && ctype_digit($id))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':任务ID[ '.$id.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '任务id格式错误')));
        }

        $this->load->model('Model_issue', 'issue', TRUE);
        $row = $this->issue->fetchOne(array(), array('id' => $id));
        if ($row) {
            exit(json_encode(array('status' => true, 'content' => $row, 'test' => 'ok')));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }
    }

    /**
     * 更改工作流
     */
    public function change_flow()
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
        $this->form_validation->set_rules('id', '任务ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '任务ID[ '.$this->input->post('id').' ]不符合规则',
            )
        );
        $this->form_validation->set_rules('uid', '用户ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '用户ID[ '.$this->input->post('uid').' ]不符合规则',
            )
        );
        $this->form_validation->set_rules('flow', '工作流', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '工作流[ '.$this->input->post('flow').' ]不符合规则',
            )
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //写入数据
        $this->load->model('Model_issue', 'issue', TRUE);
        $Post_data = array(
            'workflow' => $this->input->post('flow'),
            'accept_user' => $this->input->post('uid'),
            'accept_time' => time(),
            'last_user' => $this->input->post('uid'),
            'last_time' => time(),
        );
        //上线后此任务就关闭了
        if ($this->input->post('flow') == '7') {
            $Post_data['status'] = 0;
        }
        $bool = $this->issue->update_by_where($Post_data, array('id' => $this->input->post('id')));
        if ($bool) {
            exit(json_encode(array('status' => true, 'content' => $bool)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入错误');
            exit(json_encode(array('status' => false, 'error' => '写入错误')));
        }
    }

    /**
     * 输出列表
     *
     * 跟进给定的条件输出列表
     */
    public function rows()
    {
        //验证请求的方式
        if ($_POST) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':本接口只接受GET传值');
            exit(json_encode(array('status' => false, 'error' => '本接口只接受GET传值')));
        }

        //GET传值不能为空
        if (empty($_GET)) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':请填写GET数据');
            exit(json_encode(array('status' => false, 'error' => '请填写GET数据')));
        }

        //如果有uid传值，则输出指定uid下的项目列表
        $where = array();
        $filter = $this->input->get('filter');
        if ($filter) {
            $filter_arr = explode('|', $filter);
            if ($filter_arr) {
                foreach ($filter_arr as $key => $value) {
                    $tmp = explode(',', $value);
                    $where[$tmp[0]] = $tmp[1];
                }
            }
        }

        $ids = array();
        $Id_string = $this->input->get('ids');
        if ($Id_string) {
            $idarr = explode(',', $Id_string);
            foreach ($idarr as $key => $value) {
                $ids[] = $value;
            }
        }

        $limit = $this->input->get('limit') ? $this->input->get('limit') : '20';
        $offset = $this->input->get('offset') ? $this->input->get('offset') : '0';

        $this->load->model('Model_issue', 'issue', TRUE);
        if ($ids) {
            $rows = $this->issue->get_rows_by_ids($ids, array('id', 'issue_name'));
            if ($rows) {
                exit(json_encode(array('status' => true, 'content' => $rows)));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
                exit(json_encode(array('status' => false, 'error' => '记录不存在')));
            }
        } else {
            $rows = $this->issue->get_rows(array('id', 'project_id', 'plan_id', 'issue_name', 'type', 'level', 'url', 'add_user', 'add_time', 'accept_user', 'accept_time', 'last_user', 'last_time', 'workflow', 'status'), $where, array('id' => 'desc'), $limit, $offset);
            if ($rows['total']) {
                exit(json_encode(array('status' => true, 'content' => $rows)));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
                exit(json_encode(array('status' => false, 'error' => '记录不存在')));
            }
        }
    }

    /**
     * 关注列表
     */
    public function star()
    {
        //验证请求的方式
        if ($_POST) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':本接口只接受GET传值');
            exit(json_encode(array('status' => false, 'error' => '本接口只接受GET传值')));
        }

        //GET传值不能为空
        if (empty($_GET)) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':请填写GET数据');
            exit(json_encode(array('status' => false, 'error' => '请填写GET数据')));
        }

        $uid = $this->input->get('uid');
        if (!($uid != 0 && ctype_digit($uid))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':用户ID[ '.$uid.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '用户ID格式错误')));
        }

        $limit = $this->input->get('limit') ? '20' : $this->input->get('limit');
        $offset = $this->input->get('offset') ? '0' : $this->input->get('offset');

        $result = array('total' => 0, 'data' => array());
        $this->load->model('Model_star', 'star', TRUE);
        $rows = $this->star->get_rows(array('star_id'), array('add_user' => $uid, 'star_type' => '1'), array('id' => 'desc'), $limit, $offset);
        $result['total'] = $rows['total'];
        if ($rows['data']) {
            foreach ($rows['data'] as $key => $value) {
                $ids[] = $value['star_id'];
            }
        }
        $this->load->model('Model_issue', 'issue', TRUE);
        $result['data'] = $this->issue->get_rows_by_ids($ids, array('id', 'issue_name', 'type', 'level', 'add_user', 'add_time', 'accept_user', 'accept_time', 'last_user', 'last_time', 'workflow', 'status'));
        if ($rows) {
            exit(json_encode(array('status' => true, 'content' => $result)));
        } else {
            log_message('debug', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
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