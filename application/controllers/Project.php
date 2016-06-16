<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 项目团队接口
 *
 * 项目团队是一切协作的基础，先创建项目团队后才可以创建计划和任务
 *
 * @author jiangbianwanghai <webmaster@jiangbianwanghai.com>
 * @since 0.1
 */
class Project extends CI_Controller {

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
        $this->form_validation->set_rules('project_name', '项目团队全称', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('project_description', '描述', 'trim');
        $this->form_validation->set_rules('add_user', '创建人', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '创建人ID[ '.$this->input->post('add_user').' ]不符合规则',
            )
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
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
            exit(json_encode(array('status' => true, 'content' => $id)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入错误');
            exit(json_encode(array('status' => false, 'error' => '写入错误')));
        }
    }

    /**
     * 输出项目团队缓存信息
     *
     * 主要用于常用的项目信息查询，减轻读库的压力
     */
    public function cache()
    {
        //验证请求的方式
        if ($_POST) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':本接口只接受GET传值');
            exit(json_encode(array('status' => false, 'error' => '本接口只接受GET传值')));
        }

        $this->load->model('Model_project', 'project', TRUE);
        $rows = $this->project->get_rows(array('id', 'project_name', 'add_user', 'add_time'), array(), array('id' => 'desc'), 100);
        if ($rows['total']) {
            exit(json_encode(array('status' => true, 'content' => $rows)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }
    }

    /**
     * 输出项目团队列表信息
     *
     * 默认显示所有项目团队信息，传值UID则显示指定用户下的项目列表
     */
    public function rows()
    {
        //验证请求的方式
        if ($_POST) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':本接口只接受GET传值');
            exit(json_encode(array('status' => false, 'error' => '本接口只接受GET传值')));
        }

        //如果有uid传值，则输出指定uid下的项目列表
        $where = array();
        $uid = $this->input->get('uid');
        if ($uid) {
            if (!($uid != 0 && ctype_digit($uid))) {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':用户ID[ '.$uid.' ]格式错误');
                exit(json_encode(array('status' => false, 'error' => '用户ID格式错误')));
            }
            $where = array('add_user' => $uid);
        }

        $this->load->model('Model_project', 'project', TRUE);
        $rows = $this->project->get_rows(array('id', 'project_name', 'project_discription', 'add_user', 'add_time', 'last_user', 'last_time'), $where, array('id' => 'desc'), 100);
        if ($rows['total']) {
            exit(json_encode(array('status' => true, 'content' => $rows)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }
    }
}
