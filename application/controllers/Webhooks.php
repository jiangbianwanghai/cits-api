<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 接收Hooks推送过来的消息
 *
 * @author jiangbianwanghai <webmaster@jiangbianwanghai.com>
 * @since 0.1
 */
class Webhooks extends CI_Controller {

    /**
     * 写入
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
        $this->form_validation->set_rules('origin', '来源', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('content', '消息详情', 'trim|required',
            array('required' => '%s 不能为空')
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //写入数据
        $this->load->model('Model_webhooks', 'webhooks', TRUE);
        $Post_data = array(
            'origin' => $this->input->post('origin'),
            'content' => $this->input->post('content'),
            'add_time' => time(),
        );
        $id = $this->webhooks->add($Post_data);
        if ($id) {
            exit(json_encode(array('status' => true, 'content' => $id)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入错误');
            exit(json_encode(array('status' => false, 'error' => '写入错误')));
        }
    }
}
