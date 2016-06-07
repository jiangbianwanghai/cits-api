<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 日志接口
 *
 * 提供操作日志的写入和查询功能
 *
 * @author jiangbianwanghai <webmaster@jiangbianwanghai.com>
 * @since 0.1
 */
class Handle extends CI_Controller {

    /**
     * 日志写入
     *
     * 此方法只接受POST传值，并对传值进行有效性验证.
     *
     * 传值
     *
     * ```
     * sender: 发送者 [必填]
     * action: 动作 [必填]
     * target_type: 目标类型 [必填]
     * target: 目标ID [必填]
     * type: 日志类型 [必填]
     * ```
     *
     * 错误提示：
     *
     * ```
     * {"status":false,"error":"错误信息内容"}
     * ```
     *
     * 成功提示：
     *
     * ```
     * {"status":true,"data":记录ID}
     * ```
     *
     * @return string 成功返回记录ID，错误返回错误信息。
     */
    public function write()
    {
        //验证请求的方式
        if (empty($_POST)) {
            exit(json_encode(array('status' => false, 'error' => '本接口只接受POST')));
        }
        
        //验证输入
        $this->load->library('form_validation');
        $this->form_validation->set_rules('sender', '发送者', 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('action', '动作', 'trim|required');
        $this->form_validation->set_rules('target_type', '目标类型', 'trim|required|is_natural_no_zero|max_length[1]');
        $this->form_validation->set_rules('target', '目标ID', 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('type', '日志类型', 'trim|required|is_natural_no_zero|max_length[1]');
        if ($this->form_validation->run() == FALSE) {
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //写入数据
        $this->load->model('Model_logs', 'logs', TRUE);
        $Post_data = array(
            'sender' => $this->input->post('sender'),
            'action' => $this->input->post('action'),
            'target_type' => $this->input->post('target_type'),
            'target' => $this->input->post('target'),
            'type' => $this->input->post('type'),
            'add_time' => time(),
        );
        $id = $this->logs->add($Post_data);
        if ($id) {
            exit(json_encode(array('status' => true, 'data' => $id)));
        } else {
            exit(json_encode(array('status' => false, 'error' => '执行错误')));
        }
    }
}