<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 关注接口
 */
class Star extends CI_Controller {

    /**
     * 添加关注
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
        $this->form_validation->set_rules('add_user', '关注人', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '关注人[ '.$this->input->post('add_user').' ]不符合规则'
            )
        );
        $this->form_validation->set_rules('star_id', '关注id', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '关注id[ '.$this->input->post('star_id').' ]不符合规则'
            )
        );
        $this->form_validation->set_rules('star_type', '关注类型', 'trim|required|is_natural_no_zero|max_length[1]',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '关注类型[ '.$this->input->post('star_type').' ]不符合规则',
                'max_length' => '类型[ '.$this->input->post('star_type').' ]太长了'
            )
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //写入数据
        $this->load->model('Model_star', 'star', TRUE);
        $Post_data = array(
            'add_user' => $this->input->post('add_user'),
            'star_id' => $this->input->post('star_id'),
            'star_type' => $this->input->post('star_type'),
            'add_time' => time(),
        );
        $id = $this->star->add($Post_data);
        if ($id) {
            exit(json_encode(array('status' => true, 'content' => $id)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入错误');
            exit(json_encode(array('status' => false, 'error' => '写入错误')));
        }
    }

    /**
     * 取消关注
     */
    public function del()
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
        $this->form_validation->set_rules('add_user', '关注人', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '关注人[ '.$this->input->post('add_user').' ]不符合规则'
            )
        );
        $this->form_validation->set_rules('star_id', '关注id', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '关注id[ '.$this->input->post('star_id').' ]不符合规则'
            )
        );
        $this->form_validation->set_rules('star_type', '关注类型', 'trim|required|is_natural_no_zero|max_length[1]',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '关注类型[ '.$this->input->post('star_type').' ]不符合规则',
                'max_length' => '类型[ '.$this->input->post('star_type').' ]太长了'
            )
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        $this->load->model('Model_star', 'star', TRUE);
        $bool = $this->star->del(array('add_user' => $this->input->post('add_user'), 'star_id' => $this->input->post('star_id'), 'star_type' => $this->input->post('star_type')));
        if ($bool) {
            exit(json_encode(array('status' => true, 'content' => $bool)));
        } else {
            log_message('debug', $this->router->fetch_class().'/'.$this->router->fetch_method().':取消关注失败');
            exit(json_encode(array('status' => false, 'error' => '取消关注失败')));
        }
    }

    /**
     * 根据类型输出记录
     */
    public function get_rows_by_type()
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

        $type = $this->input->get('type');
        if (!($type != 0 && ctype_digit($type))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':类型[ '.$type.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '类型格式错误')));
        }

        $this->load->model('Model_star', 'star', TRUE);
        $rows = $this->star->get_rows(array('add_user', 'star_id'), array('add_user' => $uid, 'star_type' => $type), array(), 100);
        if ($rows) {
            exit(json_encode(array('status' => true, 'content' => $rows)));
        } else {
            log_message('debug', $this->router->fetch_class().'/'.$this->router->fetch_method().':数据不存在');
            exit(json_encode(array('status' => false, 'error' => '数据不存在')));
        }
    }
}
