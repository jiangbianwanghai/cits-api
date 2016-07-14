<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 报表接口
 *
 * 一项任务可能会涉及到很多人参与协作，核心成员有四个角色，分别是：任务发起人，开发人员，测试人员，上线人员。除了
 * 核心成员外，还有其他参与开发和测试的人员。
 *
 * @author jiangbianwanghai <webmaster@jiangbianwanghai.com>
 * @since 0.1
 */
class Report extends CI_Controller {

    /**
     * 输出每天的任务受理量
     *
     * 如果没有传递参数，则输出的是每天的任务受理量
     * 如果传递的参数是uid，则输出指定用户的任务受理量
     * 如果传递的有时间范围则，输出指定时间范围的受理量。
     */
    public function accept()
    {
        //验证请求的方式
        if ($_POST) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':本接口只接受GET传值');
            exit(json_encode(array('status' => false, 'error' => '本接口只接受GET传值')));
        }

        $etime = $this->input->get('etime') ? $this->input->get('etime') : time();
        $stime = $this->input->get('stime') ? $this->input->get('stime') : strtotime("last year");
        $uid = $this->input->get('uid') ? $this->input->get('uid') : '0';

        $this->load->model('Model_accept', 'accept', TRUE);
        $report = $this->accept->report($stime, $etime, $uid);
        if ($report) {
            exit(json_encode(array('status' => true, 'content' => $report)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':统计数据不存在');
            exit(json_encode(array('status' => false, 'error' => '统计数据不存在')));
        }
    }

    public function bug()
    {
        //验证请求的方式
        if ($_POST) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':本接口只接受GET传值');
            exit(json_encode(array('status' => false, 'error' => '本接口只接受GET传值')));
        }

        $etime = $this->input->get('etime') ? $this->input->get('etime') : time();
        $stime = $this->input->get('stime') ? $this->input->get('stime') : strtotime("last year");
        $uid = $this->input->get('uid') ? $this->input->get('uid') : '0';

        $this->load->model('Model_bug', 'bug', TRUE);
        $report = $this->bug->report($stime, $etime, $uid);
        if ($report) {
            exit(json_encode(array('status' => true, 'content' => $report)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':统计数据不存在');
            exit(json_encode(array('status' => false, 'error' => '统计数据不存在')));
        }
    }
}
