<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Bug接口
 *
 * 基于任务的Bug。
 *
 * @author jiangbianwanghai <webmaster@jiangbianwanghai.com>
 * @since 0.1
 */
class Bug extends CI_Controller {

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
        $this->form_validation->set_rules('level', '优先级', 'trim|required|is_natural_no_zero|max_length[1]',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '优先级[ '.$this->input->post('level').' ]不符合规则',
                'max_length' => '优先级[ '.$this->input->post('level').' ]太长了'
            )
        );
        $this->form_validation->set_rules('issue_id', '任务id', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '任务id[ '.$this->input->post('issue_id').' ]不符合规则'
            )
        );
        $this->form_validation->set_rules('subject', '标题', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('content', '描述', 'trim');
        $this->form_validation->set_rules('add_user', '创建人ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '创建人ID[ '.$this->input->post('add_user').' ]不符合规则'
            )
        );
        $this->form_validation->set_rules('accept_user', '受理人ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '受理人ID[ '.$this->input->post('accept_user').' ]不符合规则'
            )
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        if ($this->_check_subject($this->input->post('plan_id'), $this->input->post('subject'))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':任务名[ '.$this->input->post('subject').' ], 计划id[ '.$this->input->post('plan_id').' ]验证重复');
            exit(json_encode(array('status' => false, 'error' => '此BUG已经存在')));
        }

        //写入数据
        $this->load->model('Model_bug', 'bug', TRUE);
        $Post_data = array(
            'project_id' => $this->input->post('project_id'),
            'plan_id' => $this->input->post('plan_id'),
            'issue_id' => $this->input->post('issue_id'),
            'level' => $this->input->post('level'),
            'subject' => $this->input->post('subject'),
            'content' => $this->input->post('content'),
            'add_user' => $this->input->post('add_user'),
            'accept_user' => $this->input->post('accept_user'),
            'add_time' => time(),
        );
        $id = $this->bug->add($Post_data);
        if ($id) {
            exit(json_encode(array('status' => true, 'content' => $id)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入错误');
            exit(json_encode(array('status' => false, 'error' => '写入错误')));
        }
    }

    /**
     * 根据任务id输出bug
     */
    public function get_rows_by_issue()
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

        $this->load->model('Model_bug', 'bug', TRUE);
        $rows = $this->bug->get_rows(array('id', 'project_id', 'plan_id', 'level', 'issue_id', 'test_id', 'subject', 'add_user', 'add_time', 'accept_user', 'accept_time', 'state', 'status'), array('issue_id' => $id), array('id' => 'desc'), 100);
        if ($rows) {
            exit(json_encode(array('status' => true, 'content' => $rows)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }
    }

    /**
     * 根据bug id输出bug详情
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
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':bug id[ '.$id.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => 'bug id格式错误')));
        }

        $this->load->model('Model_bug', 'bug', TRUE);
        $row = $this->bug->fetchOne(array(), array('id' => $id));
        if ($row) {
            exit(json_encode(array('status' => true, 'content' => $row)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }
    }

    /**
     * 删除
     */
    public function del()
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
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':bug id[ '.$id.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => 'bug id格式错误')));
        }

        //任务id格式验证
        $user = $this->input->get('user');
        if (!($user != 0 && ctype_digit($user))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':用户 id[ '.$id.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '用户 id格式错误')));
        }

        $this->load->model('Model_bug', 'bug', TRUE);
        $bool = $this->bug->update_by_where(array('status' => '-1', 'last_user' => $user, 'last_time' => time()), array('id' => $id));
        if ($bool) {
            exit(json_encode(array('status' => true, 'content' => $bool)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':删除失败');
            exit(json_encode(array('status' => false, 'error' => '删除失败')));
        }
    }

    /**
     * 关闭
     */
    public function close()
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
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':bug id[ '.$id.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => 'bug id格式错误')));
        }

        //任务id格式验证
        $user = $this->input->get('user');
        if (!($user != 0 && ctype_digit($user))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':用户 id[ '.$id.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '用户 id格式错误')));
        }

        $this->load->model('Model_bug', 'bug', TRUE);
        $bool = $this->bug->update_by_where(array('status' => '0', 'last_user' => $user, 'last_time' => time()), array('id' => $id));
        if ($bool) {
            exit(json_encode(array('status' => true, 'content' => $bool)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入错误');
            exit(json_encode(array('status' => false, 'error' => '写入错误')));
        }
    }

    /**
     * 开启
     */
    public function open()
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
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':bug id[ '.$id.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => 'bug id格式错误')));
        }

        //任务id格式验证
        $user = $this->input->get('user');
        if (!($user != 0 && ctype_digit($user))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':用户 id[ '.$id.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '用户 id格式错误')));
        }

        $this->load->model('Model_bug', 'bug', TRUE);
        $bool = $this->bug->update_by_where(array('status' => '1', 'last_user' => $user, 'last_time' => time()), array('id' => $id));
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

        $limit = empty($this->input->get('limit')) ? '20' : $this->input->get('limit');
        $offset = empty($this->input->get('offset')) ? '0' : $this->input->get('offset');

        $this->load->model('Model_bug', 'bug', TRUE);
        $rows = $this->bug->get_rows(array('id', 'level', 'subject', 'add_user', 'add_time', 'accept_user', 'accept_time', 'last_user', 'last_time', 'state', 'status'), $where, array('id' => 'desc'), $limit, $offset);
        if ($rows['total']) {
            exit(json_encode(array('status' => true, 'content' => $rows)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
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

        $limit = empty($this->input->get('limit')) ? '20' : $this->input->get('limit');
        $offset = empty($this->input->get('offset')) ? '0' : $this->input->get('offset');

        $result = array('total' => 0, 'data' => array());
        $this->load->model('Model_star', 'star', TRUE);
        $rows = $this->star->get_rows(array('star_id'), array('add_user' => $uid, 'star_type' => '3'), array('id' => 'desc'), $limit, $offset);
        $result['total'] = $rows['total'];
        if ($rows['data']) {
            foreach ($rows['data'] as $key => $value) {
                $ids[] = $value['star_id'];
            }
        }
        $this->load->model('Model_bug', 'bug', TRUE);
        $result['data'] = $this->bug->get_rows_by_ids($ids, array('id', 'level', 'subject', 'add_user', 'add_time', 'accept_user', 'accept_time', 'last_user', 'last_time', 'state', 'status'));
        if ($rows['total']) {
            exit(json_encode(array('status' => true, 'content' => $result)));
        } else {
            log_message('debug', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }
    }

    /**
     * 验证bug名称是否重复
     *
     * 同一个计划下的bug名称不能重复
     */
    private function _check_subject($planid = 0 , $subject = '')
    {
        if ($planid && $subject) {
            $this->load->model('Model_bug', 'bug', TRUE);
            $rows = $this->bug->get_rows(array('id'), array('plan_id' => $planid, 'subject' => $subject));
            if ($rows['total']) {
                return true;
            } else {
                return false;
            }
        }
    }
}
