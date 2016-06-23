<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 提交代码接口
 *
 * 提交的代码关联到任务中。
 *
 * @author jiangbianwanghai <webmaster@jiangbianwanghai.com>
 * @since 0.1
 */
class Commit extends CI_Controller {

    /**
     * 项目团队信息写入
     *
     * 此方法只接受POST传值，并对传值进行有效性验证.
     *
     * 传值
     *
     * ```
     * project_name: 项目团队全称 [必填]
     * project_description: 项目团队描述 [选填]
     * add_user: 创建人 [必填]
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
        $this->form_validation->set_rules('project_name', '项目团队全称', 'trim|required');
        $this->form_validation->set_rules('project_description', '描述', 'trim');
        $this->form_validation->set_rules('add_user', '创建人', 'trim|required|is_natural_no_zero');
        if ($this->form_validation->run() == FALSE) {
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //写入数据
        $this->load->model('Model_project', 'project', TRUE);
        $Post_data = array(
            'project_name' => $this->input->post('project_name'),
            'project_discription' => $this->input->post('project_description'),
            'add_user' => $this->input->post('add_user'),
            'add_time' => time(),
        );
        $id = $this->project->add($Post_data);
        if ($id) {
            exit(json_encode(array('status' => true, 'data' => $id)));
        } else {
            exit(json_encode(array('status' => false, 'error' => '执行错误')));
        }
    }

    /**
     * 根据根据任务id输出提交测试代码
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

        $this->load->model('Model_test', 'test', TRUE);
        $rows = $this->test->get_rows(array('id', 'project_id', 'plan_id', 'issue_id', 'repos_id', 'br', 'test_flag', 'trunk_flag', 'state', 'rank', 'tice', 'tice_time', 'add_user', 'add_time'), array('issue_id' => $id), array('id' => 'desc'), 100);
        if ($rows) {
            exit(json_encode(array('status' => true, 'content' => $rows)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }
    }
}
