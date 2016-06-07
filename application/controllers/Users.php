<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 用户接口
 */
class Users extends CI_Controller {

    /**
     * 用户写入
     */
    public function write()
    {
        //验证请求的方式
        if (empty($_POST)) {
            exit(json_encode(array('status' => false, 'error' => '本接口只接受POST')));
        }

        //验证输入
        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', '邮箱', 'trim|required|valid_email|min_length[5]|max_length[50]');
        $this->form_validation->set_rules('username', '用户名', 'trim|required|alpha_dash|min_length[3]|max_length[30]');
        $this->form_validation->set_rules('password', '密码', 'trim|required|exact_length[32]');
        if ($this->form_validation->run() == FALSE) {
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //写入数据
        $this->load->model('Model_users', 'users', TRUE);
        $Post_data = array(
            'email' => $this->input->post('email'),
            'username' => $this->input->post('username'),
            'password' => $this->input->post('password'),
            'add_time' => time(),
        );
        $id = $this->users->add($Post_data);
        if ($id) {
            exit(json_encode(array('status' => true, 'data' => $id)));
        } else {
            exit(json_encode(array('status' => false, 'error' => '执行错误')));
        }
    }

    /**
     * 用户名唯一性验证
     */
    public function check_username() {

        //验证请求的方式
        if (empty($_GET)) {
            exit(json_encode(array('status' => false, 'error' => '本接口只接受GET')));
        }

        $str = $this->input->get('username');
        if (empty($str)) {
            exit(json_encode(array('status' => false, 'error' => '验证字符不能为空')));
        }

        $this->load->library('form_validation');
        if ($this->form_validation->alpha_dash($str) == FALSE || $this->form_validation->min_length($str, 3) == FALSE || $this->form_validation->max_length($str, 30) == FALSE) {
            exit(json_encode(array('status' => false, 'error' => '用户名格式不正确，拒绝验证')));
        }

        $this->load->model('Model_users', 'users', TRUE);
        $row = $this->users->fetchOne(array('uid', 'username'), array('username' => $str));
        if ($row) {
            exit(json_encode(array('status' => false, 'error' => '用户名已存在')));
        } else {
            exit(json_encode(array('status' => true, 'message' => '用户名不存在，可以注册')));
        }
    }

    /**
     * 邮箱唯一性验证
     */
    public function check_email() {

        //验证请求的方式
        if (empty($_GET)) {
            exit(json_encode(array('status' => false, 'error' => '本接口只接受GET')));
        }

        $str = $this->input->get('email');
        if (empty($str)) {
            exit(json_encode(array('status' => false, 'error' => '邮箱验证字符不能为空')));
        }

        $this->load->library('form_validation');
        if ($this->form_validation->valid_email($str) == FALSE || $this->form_validation->min_length($str, 5) == FALSE || $this->form_validation->max_length($str, 50) == FALSE) {
            exit(json_encode(array('status' => false, 'error' => '邮箱格式不正确，拒绝验证')));
        }

        $this->load->model('Model_users', 'users', TRUE);
        $row = $this->users->fetchOne(array('uid', 'email'), array('email' => $str));
        if ($row) {
            exit(json_encode(array('status' => false, 'error' => '邮箱已存在')));
        } else {
            exit(json_encode(array('status' => true, 'message' => '邮箱不存在，可以注册')));
        }
    }

    /**
     * 登录验证
     */
    public function signin_check() {

        //验证请求的方式
        if (empty($_GET)) {
            exit(json_encode(array('status' => false, 'error' => '本接口只接受GET')));
        }

        $username = $this->input->get('username');
        if (empty($username)) {
            exit(json_encode(array('status' => false, 'error' => '用户名不能为空')));
        }

        $password = $this->input->get('password');
        if (empty($password)) {
            exit(json_encode(array('status' => false, 'error' => '密码不能为空')));
        }

        $this->load->library('form_validation');
        if ($this->form_validation->alpha_dash($username) == FALSE || $this->form_validation->min_length($username, 3) == FALSE || $this->form_validation->max_length($username, 30) == FALSE || $this->form_validation->exact_length($password, 32) == FALSE) {
            exit(json_encode(array('status' => false, 'error' => '用户名OR密码格式不正确，拒绝验证')));
        }

        $this->load->model('Model_users', 'users', TRUE);
        $row = $this->users->fetchOne(array(), array('username' => $username));
        if ($row) {
            if ($row['password'] == $password) {
                exit(json_encode(array('status' => true, 'data' => $row)));
            } else {
                exit(json_encode(array('status' => false, 'error' => '验证不通过')));
            }
        } else {
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }
    }

    /**
     * 添加收藏项目团队
     */
    public function star_project_add()
    {

        //验证请求的方式
        if (empty($_POST)) {
            exit(json_encode(array('status' => false, 'error' => '本接口只接受POST')));
        }

        $id = $this->input->post('id');
        if (!($id != 0 && ctype_digit($id))) {
            exit(json_encode(array('status' => false, 'error' => '项目ID格式错误')));
        }

        $uid = $this->input->post('uid');
        if (!($uid != 0 && ctype_digit($uid))) {
            exit(json_encode(array('status' => false, 'error' => '用户ID格式错误')));
        }

        $this->load->model('Model_users', 'users', TRUE);
        $row = $this->users->fetchOne(array('star_project'), array('uid' => $uid));
        if (!$row) {
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }
        if ($row['star_project']) {
            $star_project = unserialize($row['star_project']);
            if (in_array($id, $star_project)) {
                exit(json_encode(array('status' => false, 'error' => '已经收藏')));
            }
        }
        $star_project[] = $id;
        $star_project = serialize($star_project);
        $flag = $this->users->update_by_where(array('star_project'=>$star_project), array('uid' => $uid));
        if ($flag) {
            exit(json_encode(array('status' => true, 'data' => $star_project)));
        } else {
            exit(json_encode(array('status' => false, 'error' => '操作失败')));
        }
    }

    /**
     * 删除收藏项目团队
     */
    public function star_project_del()
    {

        //验证请求的方式
        if (empty($_POST)) {
            exit(json_encode(array('status' => false, 'error' => '本接口只接受POST')));
        }

        $id = $this->input->post('id');
        if (!($id != 0 && ctype_digit($id))) {
            exit(json_encode(array('status' => false, 'error' => '项目ID格式错误')));
        }

        $uid = $this->input->post('uid');
        if (!($uid != 0 && ctype_digit($uid))) {
            exit(json_encode(array('status' => false, 'error' => '用户ID格式错误')));
        }

        $this->load->model('Model_users', 'users', TRUE);
        $row = $this->users->fetchOne(array('star_project'), array('uid' => $uid));
        if (!$row) {
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }
        if ($row['star_project']) {
            $star_project = unserialize($row['star_project']);
            $key = array_keys($star_project, $id, true);
            unset($star_project[array_search($id, $star_project)]);
            if ($star_project) {
                $star_project = serialize($star_project);
            } else {
                $star_project = '';
            }
            $flag = $this->users->update_by_where(array('star_project'=>$star_project), array('uid' => $uid));
            if ($flag) {
                exit(json_encode(array('status' => true, 'data' => $star_project)));
            } else {
                exit(json_encode(array('status' => false, 'error' => '操作失败')));
            }
        }
    }

    /**
     * 输出单条信息
     */
    public function row()
    {
        //验证请求的方式
        if (empty($_GET)) {
            exit(json_encode(array('status' => false, 'error' => '本接口只接受GET')));
        }

        $uid = $this->input->get('uid');
        if (!($uid != 0 && ctype_digit($uid))) {
            exit(json_encode(array('status' => false, 'error' => '用户ID格式错误')));
        }

        $this->load->model('Model_users', 'users', TRUE);
        $row = $this->users->fetchOne(array(), array('uid' => $uid));
        if (!$row) {
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }

        exit(json_encode(array('status' => true, 'data' => $row)));

    }
}
