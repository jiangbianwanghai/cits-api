<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 任务参与人接口
 *
 * 一项任务可能会涉及到很多人参与协作，核心成员有四个角色，分别是：任务发起人，开发人员，测试人员，上线人员。除了
 * 核心成员外，还有其他参与开发和测试的人员。
 *
 * @author jiangbianwanghai <webmaster@jiangbianwanghai.com>
 * @since 0.1
 */
class Accept extends CI_Controller {

    /**
     * 参与人写入
     *
     * 此方法只接受POST传值，并对传值进行有效性验证.
     *
     * 传值
     *
     * ```
     * accept_user: 参与人ID [必填]
     * issue_id: 任务ID [必填]
     * flow: 参与角色类型 [必填]
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
        $this->form_validation->set_rules('accept_user', '参与者ID', 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('issue_id', '任务ID', 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('flow', '参与角色类型', 'trim|required|is_natural_no_zero');
        if ($this->form_validation->run() == FALSE) {
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //写入数据
        $this->load->model('Model_accept', 'accept', TRUE);
        $Post_data = array(
            'accept_user' => $this->input->post('accept_user'),
            'issue_id' => $this->input->post('issue_id'),
            'flow' => $this->input->post('flow'),
            'accept_time' => time(),
        );
        $id = $this->accept->add($Post_data);
        if ($id) {
            exit(json_encode(array('status' => true, 'data' => $id)));
        } else {
            exit(json_encode(array('status' => false, 'error' => '执行错误')));
        }
    }
}
