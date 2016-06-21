<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 消息接口
 */
class Notify extends CI_Controller {

    /**
     * 消息写入
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
        $this->form_validation->set_rules('user', '消息接收者', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '消息接收者[ '.$this->input->post('user').' ]不符合规则'
            )
        );
        $this->form_validation->set_rules('log_id', '关联的日志ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '关联的日志ID[ '.$this->input->post('log_id').' ]不符合规则'
            )
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //写入数据
        $this->load->model('Model_notify', 'notify', TRUE);
        $Post_data = array(
            'user' => $this->input->post('user'),
            'log_id' => $this->input->post('log_id'),
            'add_time' => time(),
        );
        $id = $this->notify->add($Post_data);
        if ($id) {
            exit(json_encode(array('status' => true, 'content' => $id)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入错误');
            exit(json_encode(array('status' => false, 'error' => '写入错误')));
        }
    }

    /**
     * 获取未读取的消息记录
     *
     * 根据用户uid获取消息记录
     */
    public function get_rows()
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

        //uid格式验证
        $uid = $this->input->get('uid');
        if (!($uid != 0 && ctype_digit($uid))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':用户UID格式不正确');
            exit(json_encode(array('status' => false, 'error' => '用户UID格式不正确')));
        }

        //状态值格式验证
        $is_read = $this->input->get('is_read');
        $where = array('user' => $uid);
        if ($is_read) {
            if (!in_array($is_read, array(0, 1))) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':状态值格式不正确');
                exit(json_encode(array('status' => false, 'error' => '状态值格式不正确')));
            }
            $where = array('user' => $uid, 'is_read' => $is_read);
        }

        $this->load->model('Model_notify', 'notify', TRUE);
        $rows = $this->notify->get_rows(array('id', 'is_read', 'user', 'log_id', 'add_time'), $where, array('id' => 'desc'), 10);
        if ($rows['total']) {
            foreach ($rows['data'] as $key => $value) {
                $ids[] = $value['log_id'];
            }
            $this->load->model('Model_logs', 'logs', TRUE);
            $Log_rows = $this->logs->get_rows_by_ids($ids, array('id', 'sender', 'target_type', 'target', 'content', 'add_time'));
            if ($Log_rows) {
                foreach ($Log_rows as $key => $value) {
                    $Log_rows_tmp[$value['id']] = $value;
                }
            }
            foreach ($rows['data'] as $key => $value) {
                $rows['data'][$key]['log'] = $Log_rows_tmp[$value['log_id']];
            }
            exit(json_encode(array('status' => true, 'content' => $rows)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':数据不存在');
            exit(json_encode(array('status' => false, 'error' => '数据不存在')));
        }
    }
}
