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
        $this->form_validation->set_rules('accept_user', '参与者ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '参与者ID[ '.$this->input->post('accept_user').' ]不符合规则',
            )
        );
        $this->form_validation->set_rules('issue_id', '任务ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '任务ID[ '.$this->input->post('issue_id').' ]不符合规则',
            )
        );
        $this->form_validation->set_rules('flow', '参与角色类型', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '参与角色类型[ '.$this->input->post('flow').' ]不符合规则',
            )
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
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
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入错误');
            exit(json_encode(array('status' => false, 'error' => '写入错误')));
        }
    }

    /**
     * 输出参与人员
     */
    public function users_by_plan()
    {
        //验证请求的方式
        if ($_POST) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':本接口只接受GET传值');
            exit(json_encode(array('status' => false, 'error' => '本接口只接受GET传值')));
        }

        //项目id格式验证
        $projectid = $this->input->get('projectid');
        if (!($projectid != 0 && ctype_digit($projectid))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':项目id格式错误');
            exit(json_encode(array('status' => false, 'error' => '项目id格式错误')));
        }

        //计划id格式验证
        $planid = $this->input->get('planid');
        if (!($planid != 0 && ctype_digit($planid))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':计划id格式错误');
            exit(json_encode(array('status' => false, 'error' => '计划id格式错误')));
        }

        //得到计划下的任务信息
        $this->load->model('Model_issue', 'issue', TRUE);
        $rows = $this->issue->get_rows(array('id'), array('project_id' => $projectid, 'plan_id' => $planid, 'status' => 1), array(), 500);
        if ($rows) {
            foreach ($rows['data'] as $key => $value) {
                $ids[] = $value['id'];
            }
            $this->load->model('Model_accept', 'accept', TRUE);
            $rows = $this->accept->get_rows_by_ids($ids, array('id', 'accept_user', 'accept_time', 'issue_id', 'flow'), 'issue_id');
            if ($rows) {
                exit(json_encode(array('status' => true, 'content' => $rows)));
            } else {
                exit(json_encode(array('status' => false, 'error' => '无参与人员')));
            }
        } else {
            exit(json_encode(array('status' => false, 'error' => '此计划下无任务')));
        }
    }

    /**
     * 根据根据任务id输出bug
     */
    public function get_rows_by_issue()
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

        //任务id格式验证
        $id = $this->input->get('id');
        if (!($id != 0 && ctype_digit($id))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':任务ID[ '.$id.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '任务id格式错误')));
        }

        $this->load->model('Model_accept', 'accept', TRUE);
        $rows = $this->accept->get_rows(array('id', 'accept_user', 'accept_time', 'issue_id', 'flow'), array('issue_id' => $id), array('id' => 'asc'), 100);
        if ($rows) {
            exit(json_encode(array('status' => true, 'content' => $rows)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }
    }
}
