<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 订阅接口
 */
class Subscription extends CI_Controller {

    /**
     * 订阅写入
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
        $this->form_validation->set_rules('target', '目标ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '目标ID[ '.$this->input->post('target').' ]不符合规则',
            )
        );
        $this->form_validation->set_rules('target_type', '目标类型', 'trim|required|is_natural_no_zero|max_length[1]',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '目标类型[ '.$this->input->post('target_type').' ]不符合规则',
                'max_length' => '长度只能是一位'
            )
        );
        $this->form_validation->set_rules('user', '用户ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '用户ID[ '.$this->input->post('user').' ]不符合规则',
            )
        );
        if ($this->form_validation->run() == FALSE) {
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //如果已经订阅，则不再订阅
        if ($this->_check_subscription($this->input->post('target'), $this->input->post('target_type'), $this->input->post('user'))) {
            log_message('debug', $this->router->fetch_class().'/'.$this->router->fetch_method().':已订阅');
            exit(json_encode(array('status' => false, 'error' => '已订阅')));
        }

        //写入数据
        $this->load->model('Model_subscription', 'subscription', TRUE);
        $Post_data = array(
            'target' => $this->input->post('target'),
            'target_type' => $this->input->post('target_type'),
            'user' => $this->input->post('user'),
            'add_time' => time(),
        );
        $id = $this->subscription->add($Post_data);
        if ($id) {
            exit(json_encode(array('status' => true, 'content' => $id)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入错误');
            exit(json_encode(array('status' => false, 'error' => '写入错误')));
        }
    }

    /**
     * 输出订阅列表
     */
    public function get_user_by_target()
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

        $target_id = $this->input->get('target_id');
        if (!($target_id != 0 && ctype_digit($target_id))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':目标id[ '.$target_id.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '目标id格式错误')));
        }

        $target_type = $this->input->get('target_type');
        if (!in_array($target_type, array('1', '2', '3', '4', '5'))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':目标类型[ '.$target_id.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '目标类型格式错误')));
        }

        $this->load->model('Model_subscription', 'subscription', TRUE);
        $rows = $this->subscription->get_rows(array('id', 'user', ), array('target' => $target_id, 'target_type' => $target_type), array('id' => 'desc'), 100);
        if ($rows['total']) {
            exit(json_encode(array('status' => true, 'content' => $rows)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }
    }

    /**
     * 验证是否重复订阅
     */
    private function _check_subscription($target = 0 , $target_type = 0, $user = 0)
    {
        if ($target && $target_type && $user) {
            $this->load->model('Model_subscription', 'subscription', TRUE);
            $rows = $this->subscription->get_rows(array('id'), array('target' => $target, 'target_type' => $target_type, 'user' => $user));
            if ($rows['total']) {
                return true;
            } else {
                return false;
            }
        }
    }
}
