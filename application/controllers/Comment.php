<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 评论接口
 *
 * 基于任务的Bug。
 *
 * @author jiangbianwanghai <webmaster@jiangbianwanghai.com>
 * @since 0.1
 */
class Comment extends CI_Controller {

    /**
     * 评论写入
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
        $this->form_validation->set_rules('id', '关联ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '关联ID[ '.$this->input->post('id').' ]不符合规则'
            )
        );
        $this->form_validation->set_rules('content', '任务ID', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('type', '关联类型', 'trim|required|alpha',
            array('required' => '%s 不能为空',
                'alpha' => '关联类型[ '.$this->input->post('type').' ]不符合规则'
            )
        );
        $this->form_validation->set_rules('add_user', '创建人id', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '创建人id[ '.$this->input->post('add_user').' ]不符合规则',
            )
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        $type = $this->input->post('type');
        if (!in_array($type, array('issue', 'bug'))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':关联类型[ '.$type.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '表类型格式错误')));
        }
        //写入数据
        if ($type == 'bug') {
            $this->load->model('Model_bug_comment', 'bug_comment', TRUE);
            $Post_data = array(
                'bug_id' => $this->input->post('id'),
                'content' => $this->input->post('content'),
                'add_user' => $this->input->post('add_user'),
                'add_time' => time()
            );
            $id = $this->bug_comment->add($Post_data);
        }
        if ($type == 'issue') {
            $this->load->model('Model_issue_comment', 'issue_comment', TRUE);
            $Post_data = array(
                'issue_id' => $this->input->post('id'),
                'content' => $this->input->post('content'),
                'add_user' => $this->input->post('add_user'),
                'add_time' => time()
            );
            $id = $this->issue_comment->add($Post_data);
        }
        if ($id) {
            exit(json_encode(array('status' => true, 'content' => $id)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入错误');
            exit(json_encode(array('status' => false, 'error' => '写入错误')));
        }
    }

    /**
     * 根据根据id评论列表
     */
    public function get_rows_by_id()
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

        $type = $this->input->get('type');
        if (!in_array($type, array('issue', 'bug'))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':表类型[ '.$type.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '表类型格式错误')));
        }

        
        if ($type == 'issue') {
            $this->load->model('Model_issue_comment', 'comment', TRUE);
            $where = array('issue_id' => $id, 'status' => '1');
        }

        if ($type == 'bug') {
            $this->load->model('Model_bug_comment', 'comment', TRUE);
            $where = array('bug_id' => $id, 'status' => '1');
        }
        
        $rows = $this->comment->get_rows(array('id', 'content', 'add_user', 'add_time'), $where, array('id' => 'asc'), 100);
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
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':任务ID[ '.$id.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '任务id格式错误')));
        }

        $type = $this->input->get('type');
        if (!in_array($type, array('issue', 'bug'))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':表类型[ '.$type.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '表类型格式错误')));
        }

        if ($type == 'issue') {
            $this->load->model('Model_issue_comment', 'comment', TRUE);
        }

        if ($type == 'bug') {
            $this->load->model('Model_bug_comment', 'comment', TRUE);
        }

        $row = $this->comment->fetchOne(array(), array('id' => $id));
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

        $type = $this->input->get('type');
        if (!in_array($type, array('issue', 'bug'))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':表类型[ '.$type.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '表类型格式错误')));
        }

        if ($type == 'issue') {
            $this->load->model('Model_issue_comment', 'comment', TRUE);
        }

        if ($type == 'bug') {
            $this->load->model('Model_bug_comment', 'comment', TRUE);
        }

        $bool = $this->comment->update_by_where(array('status' => '-1', 'last_user' => $user, 'last_time' => time()), array('id' => $id));
        if ($bool) {
            exit(json_encode(array('status' => true, 'content' => $bool)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':删除失败');
            exit(json_encode(array('status' => false, 'error' => '删除失败')));
        }
    }
}
