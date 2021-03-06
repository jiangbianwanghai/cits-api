<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 用户接口
 */
class User extends CI_Controller {

    /**
     * 用户信息写入
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
        $this->form_validation->set_rules('email', '邮箱', 'trim|required|valid_email|min_length[5]|max_length[50]',
            array(
                'required' => '%s 不能为空',
                'valid_email' => '邮箱[ '.$this->input->post('email').' ]不符合规则',
                'min_length' => '邮箱[ '.$this->input->post('email').' ]长度不够',
                'max_length' => '邮箱[ '.$this->input->post('email').' ]太长了'
            )
        );
        $this->form_validation->set_rules('username', '用户名', 'trim|required|alpha_dash|min_length[3]|max_length[30]',
            array(
                'required' => '%s 不能为空',
                'alpha_dash' => '用户名[ '.$this->input->post('username').' ]不符合规则',
                'min_length' => '用户名[ '.$this->input->post('username').' ]长度不够',
                'max_length' => '用户名[ '.$this->input->post('username').' ]太长了'
            )
        );
        $this->form_validation->set_rules('password', '密码', 'trim|required|min_length[6]|max_length[16]',
            array(
                'required' => '%s 不能为空',
                'exact_length' => '你提供的密码[ '.$this->input->post('password').' ] 必须是 32位 的MD5加密字符串'
            )
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //写入数据
        $this->load->model('Model_users', 'users', TRUE);
        $salt = substr(uniqid(rand()), -6);
        $password = md5(md5($this->input->post('password')).$salt);
        $Post_data = array(
            'email' => $this->input->post('email'),
            'username' => $this->input->post('username'),
            'password' => $password,
            'salt' => $salt,
            'add_time' => time(),
        );
        $id = $this->users->add($Post_data);
        if ($id) {
            exit(json_encode(array('status' => true, 'content' => $id)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入错误');
            exit(json_encode(array('status' => false, 'error' => '写入错误')));
        }
    }

    /**
     * 用户名唯一性验证
     */
    public function check_username() {

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

        $str = $this->input->get('username');
        if (empty($str)) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':用户名不能为空');
            exit(json_encode(array('status' => false, 'error' => '用户名不能为空')));
        }

        $this->load->library('form_validation');
        if ($this->form_validation->alpha_dash($str) == FALSE || $this->form_validation->min_length($str, 3) == FALSE || $this->form_validation->max_length($str, 30) == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':用户名[ '.$str.' ]格式不正确，拒绝验证');
            exit(json_encode(array('status' => false, 'error' => '用户名格式不正确，拒绝验证')));
        }

        $this->load->model('Model_users', 'users', TRUE);
        $row = $this->users->fetchOne(array('uid', 'username'), array('username' => $str));
        if ($row) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':用户名[ '.$str.' ]已存在');
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
        if ($_POST) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':本接口只接受GET传值');
            exit(json_encode(array('status' => false, 'error' => '本接口只接受GET传值')));
        }

        //GET传值不能为空
        if (empty($_GET)) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':请填写GET数据');
            exit(json_encode(array('status' => false, 'error' => '请填写GET数据')));
        }

        $str = $this->input->get('email');
        if (empty($str)) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':邮箱不能为空');
            exit(json_encode(array('status' => false, 'error' => '邮箱不能为空')));
        }

        $this->load->library('form_validation');
        if ($this->form_validation->valid_email($str) == FALSE || $this->form_validation->min_length($str, 5) == FALSE || $this->form_validation->max_length($str, 50) == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':邮箱[ '.$str.' ]格式不正确，拒绝验证');
            exit(json_encode(array('status' => false, 'error' => '邮箱格式不正确，拒绝验证')));
        }

        $this->load->model('Model_users', 'users', TRUE);
        $row = $this->users->fetchOne(array('uid', 'email'), array('email' => $str));
        if ($row) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':邮箱[ '.$str.' ]已存在');
            exit(json_encode(array('status' => false, 'error' => '邮箱已存在')));
        } else {
            exit(json_encode(array('status' => true, 'message' => '邮箱不存在，可以注册')));
        }
    }

    /**
     * 登录验证
     *
     * 验证登录，传递过来的PASSWORD是经过加密的
     */
    public function signin_check() {

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

        $account = $this->input->get('account');
        if (empty($account)) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':用户名不能为空');
            exit(json_encode(array('status' => false, 'error' => '用户名不能为空')));
        }

        $password = $this->input->get('password');
        if (empty($password)) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':密码不能为空');
            exit(json_encode(array('status' => false, 'error' => '密码不能为空')));
        }

        $this->load->library('form_validation');
        if ($this->form_validation->min_length($password, 6) == FALSE || $this->form_validation->max_length($password, 16) == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':密码[ '.$password.' ]格式不正确，拒绝验证');
            exit(json_encode(array('status' => false, 'error' => '密码格式不正确，拒绝验证')));
        }
        if ($this->form_validation->valid_email($account) == FALSE) {
            $where = array('username' => $account);
        } else {
            $where = array('email' => $account);
        }

        $this->load->model('Model_users', 'users', TRUE);
        $row = $this->users->fetchOne(array(), $where);
        if ($row) {
            $password = md5(md5($password).$row['salt']);
            if ($row['password'] == $password) {
                exit(json_encode(array('status' => true, 'content' => $row)));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':验证不通过');
                exit(json_encode(array('status' => false, 'error' => '验证不通过')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':账户不存在');
            exit(json_encode(array('status' => false, 'error' => '账户不存在')));
        }
    }

    /**
     * 添加关注项目团队
     *
     * 添加关注成功后将关注的项目团队返回
     */
    public function star_project_add()
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

        $id = $this->input->post('id');
        if (!($id != 0 && ctype_digit($id))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':项目ID[ '.$id.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '项目ID格式错误')));
        }

        $uid = $this->input->post('uid');
        if (!($uid != 0 && ctype_digit($uid))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':用户ID[ '.$uid.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '用户ID格式错误')));
        }

        $this->load->model('Model_users', 'users', TRUE);
        $row = $this->users->fetchOne(array('star_project'), array('uid' => $uid));
        if (!$row) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':用户ID[ '.$uid.' ]记录不存在');
            exit(json_encode(array('status' => false, 'error' => '此用户不存在')));
        }
        if ($row['star_project']) {
            $star_project = unserialize($row['star_project']);
            if (in_array($id, $star_project)) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':已经收藏');
                exit(json_encode(array('status' => false, 'error' => '已经收藏')));
            }
        }
        $star_project[] = $id;
        $star_project = serialize($star_project);
        $flag = $this->users->update_by_where(array('star_project'=>$star_project), array('uid' => $uid));
        if ($flag) {
            exit(json_encode(array('status' => true, 'content' => $star_project)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':添加关注失败');
            exit(json_encode(array('status' => false, 'error' => '添加关注失败')));
        }
    }

    /**
     * 删除关注项目团队
     *
     * 删除成功后将关注的项目团队输出
     */
    public function star_project_del()
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

        $id = $this->input->post('id');
        if (!($id != 0 && ctype_digit($id))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':项目ID[ '.$id.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '项目ID格式错误')));
        }

        $uid = $this->input->post('uid');
        if (!($uid != 0 && ctype_digit($uid))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':用户ID[ '.$uid.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '用户ID格式错误')));
        }

        $this->load->model('Model_users', 'users', TRUE);
        $row = $this->users->fetchOne(array('star_project'), array('uid' => $uid));
        if (!$row) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':用户ID[ '.$uid.' ]记录不存在');
            exit(json_encode(array('status' => false, 'error' => '此用户不存在')));
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
                exit(json_encode(array('status' => true, 'content' => $star_project)));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':删除关注失败');
                exit(json_encode(array('status' => false, 'error' => '删除关注失败')));
            }
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':只有关注后才可以删除操作');
            exit(json_encode(array('status' => false, 'error' => '只有关注后才可以删除操作')));
        }
    }

    /**
     * 输出单条信息
     *
     * 主要用于需要获取单条信息的需求
     */
    public function row()
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

        $this->load->model('Model_users', 'users', TRUE);
        $row = $this->users->fetchOne(array(), array('uid' => $uid));
        if (!$row) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }

        exit(json_encode(array('status' => true, 'content' => $row)));

    }

    /**
     * 输出单条信息根据邮箱
     *
     * 主要用于需要获取单条信息的需求
     */
    public function get_row_by_email()
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

        $str = $this->input->get('email');
        if (empty($str)) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':邮箱不能为空');
            exit(json_encode(array('status' => false, 'error' => '邮箱不能为空')));
        }

        $this->load->library('form_validation');
        if ($this->form_validation->valid_email($str) == FALSE || $this->form_validation->min_length($str, 5) == FALSE || $this->form_validation->max_length($str, 50) == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':邮箱[ '.$str.' ]格式不正确，拒绝验证');
            exit(json_encode(array('status' => false, 'error' => '邮箱格式不正确，拒绝验证')));
        }

        $this->load->model('Model_users', 'users', TRUE);
        $row = $this->users->fetchOne(array(), array('email' => $str));
        if (!$row) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }

        exit(json_encode(array('status' => true, 'content' => $row)));

    }

    /**
     * 输出用户缓存信息
     *
     * 主要用于常用的用户信息查询，减轻读库的压力
     */
    public function cache()
    {
        //验证请求的方式
        if ($_POST) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':本接口只接受GET传值');
            exit(json_encode(array('status' => false, 'error' => '本接口只接受GET传值')));
        }

        $this->load->model('Model_users', 'users', TRUE);
        $rows = $this->users->get_rows(array('uid', 'username', 'realname', 'email', 'add_time', 'last_login_time', 'role'), array(), array('uid' => 'desc'), 500);
        if ($rows['total']) {
            exit(json_encode(array('status' => true, 'content' => $rows)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }
    }

    /**
     * 修改密码
     */
    public function change_password()
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
        $this->form_validation->set_rules('email', '邮箱', 'trim|required|valid_email|min_length[5]|max_length[50]',
            array(
                'required' => '%s 不能为空',
                'valid_email' => '邮箱[ '.$this->input->post('email').' ]不符合规则',
                'min_length' => '邮箱[ '.$this->input->post('email').' ]长度不够',
                'max_length' => '邮箱[ '.$this->input->post('email').' ]太长了'
            )
        );
        $this->form_validation->set_rules('password', '密码', 'trim|required|min_length[6]|max_length[16]',
            array(
                'required' => '%s 不能为空',
                'alpha_dash' => '密码[ '.$this->input->post('password').' ]不符合规则',
                'min_length' => '密码[ '.$this->input->post('password').' ]长度不够',
                'max_length' => '密码[ '.$this->input->post('password').' ]太长了'
            )
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        $this->load->model('Model_users', 'users', TRUE);
        $salt = substr(uniqid(rand()), -6);
        $password = md5(md5($this->input->post('password')).$salt);
        $flag = $this->users->update_by_where(array('password' => $password, 'salt' => $salt), array('email' => $this->input->post('email')));
        if ($flag) {
            exit(json_encode(array('status' => true, 'content' => '修改成功')));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':修改失败');
            exit(json_encode(array('status' => false, 'error' => '修改失败')));
        }
    }

    /**
     * 更改重置密码状态
     */
    public function reset()
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

        $forgot = $this->input->get('forgot');
        if (!(in_array($forgot, array('0', '1')))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':标记[ '.$forgot.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '标记格式错误')));
        }

        $str = $this->input->get('email');
        if (empty($str)) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':邮箱不能为空');
            exit(json_encode(array('status' => false, 'error' => '邮箱不能为空')));
        }

        $this->load->library('form_validation');
        if ($this->form_validation->valid_email($str) == FALSE || $this->form_validation->min_length($str, 5) == FALSE || $this->form_validation->max_length($str, 50) == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':邮箱[ '.$str.' ]格式不正确，拒绝验证');
            exit(json_encode(array('status' => false, 'error' => '邮箱格式不正确，拒绝验证')));
        }
        if ($forgot == 1) {
            $set = array('forgot' => '1', 'reset_email_time' => time());
        }
        if ($forgot == 0) {
            $set = array('forgot' => '0', 'reset_email_time' => '0');
        }

        $this->load->model('Model_users', 'users', TRUE);
        $flag = $this->users->update_by_where($set, array('email' => $str));
        if ($flag) {
            exit(json_encode(array('status' => true, 'content' => '操作成功')));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':操作失败');
            exit(json_encode(array('status' => false, 'error' => '操作失败')));
        }
    }
}
