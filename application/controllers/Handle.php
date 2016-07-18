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
        $this->form_validation->set_rules('sender', '发送者', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '发送者ID[ '.$this->input->post('sender').' ]不符合规则',
            )
        );
        $this->form_validation->set_rules('action', '动作', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('target_type', '目标类型', 'trim|required|is_natural_no_zero|max_length[1]',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '目标类型[ '.$this->input->post('target_type').' ]不符合规则',
                'max_length' => '目标类型[ '.$this->input->post('target_type').' ]太长了'
            )
        );
        $this->form_validation->set_rules('target', '目标ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '目标ID[ '.$this->input->post('target').' ]不符合规则',
            )
        );
        $this->form_validation->set_rules('type', '日志类型', 'trim|required|is_natural_no_zero|max_length[1]',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '日志类型[ '.$this->input->post('type').' ]不符合规则',
                'max_length' => '日志类型[ '.$this->input->post('type').' ]太长了'
            )
        );
        $this->form_validation->set_rules('subject', '标题', 'trim');
        $this->form_validation->set_rules('content', '快照', 'trim');
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
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
            'subject' => $this->input->post('subject'),
            'content' => $this->input->post('content'),
            'add_time' => time(),
        );
        $id = $this->logs->add($Post_data);
        if ($id) {
            exit(json_encode(array('status' => true, 'content' => $id)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入错误');
            exit(json_encode(array('status' => false, 'error' => '写入错误')));
        }
    }

    /**
     * 输出操作记录
     *
     * 根据任务id
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

        //id格式验证
        $id = $this->input->get('id');
        $type = $this->input->get('type');
        if ($id && $type) {
            if (!ctype_digit($id)) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':操作目标id格式不正确');
                exit(json_encode(array('status' => false, 'error' => '操作目标id格式不正确')));
            }

            if (!in_array($type, array('project', 'plan', 'issue', 'test', 'bug'))) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':类型格式不正确');
                exit(json_encode(array('status' => false, 'error' => '类型格式不正确')));
            }
            $type_arr = array('project' => '1', 'plan' => '2', 'issue' => '3', 'test' => '4', 'bug' => '5');
            $where = array('target' => $id, 'target_type' => $type_arr[$type]);
        }

        //id格式验证
        $uid = $this->input->get('uid');
        if ($uid) {
            if (!ctype_digit($uid)) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':用户id格式不正确');
                exit(json_encode(array('status' => false, 'error' => '用户id格式不正确')));
            } else {
                $where = array('sender' => $uid);
            }
        }

        $limit = $this->input->get('limit') ? $this->input->get('limit') : '20';
        $offset = $this->input->get('offset') ? $this->input->get('offset') : '0';

        $this->load->model('Model_logs', 'logs', TRUE);
        $rows = $this->logs->get_rows(array('id', 'sender', 'action', 'target_type', 'target', 'subject', 'content', 'add_time'), $where, array('id' => 'desc'), $limit, $offset);
        if ($rows['total']) {
            exit(json_encode(array('status' => true, 'content' => $rows)));
        } else {
            log_message('debug', $this->router->fetch_class().'/'.$this->router->fetch_method().':数据不存在，任务id是[ '.$id.' ]');
            exit(json_encode(array('status' => false, 'error' => '数据不存在')));
        }
    }
}