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
        $this->form_validation->set_rules('project_id', '所属项目团队ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '所属项目团队ID[ '.$this->input->post('project_id').' ]不符合规则',
            )
        );
        $this->form_validation->set_rules('plan_id', '所属计划ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '所属计划ID[ '.$this->input->post('plan_id').' ]不符合规则',
            )
        );
        $this->form_validation->set_rules('issue_id', '任务ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '任务ID[ '.$this->input->post('issue_id').' ]不符合规则'
            )
        );
        $this->form_validation->set_rules('repos_id', '代码库id', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '代码库id[ '.$this->input->post('repos_id').' ]不符合规则'
            )
        );
        $this->form_validation->set_rules('br', '分支', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('test_flag', '版本号', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('add_user', '创建人ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '类型[ '.$this->input->post('add_user').' ]不符合规则'
            )
        );
        $this->form_validation->set_rules('test_summary', '提测说明', 'trim');
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        //写入数据
        $this->load->model('Model_test', 'test', TRUE);
        $Post_data = array(
            'project_id' => $this->input->post('project_id'),
            'plan_id' => $this->input->post('plan_id'),
            'issue_id' => $this->input->post('issue_id'),
            'repos_id' => $this->input->post('repos_id'),
            'br' => $this->input->post('br'),
            'test_flag' => $this->input->post('test_flag'),
            'test_summary' => $this->input->post('test_summary'),
            'add_user' => $this->input->post('add_user'),
            'add_time' => time(),
        );
        $id = $this->test->add($Post_data);
        if ($id) {
            exit(json_encode(array('status' => true, 'content' => $id)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入错误');
            exit(json_encode(array('status' => false, 'error' => '写入错误')));
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
        $rows = $this->test->get_rows(array('id', 'project_id', 'plan_id', 'issue_id', 'repos_id', 'br', 'test_flag', 'trunk_flag', 'test_summary', 'state', 'rank', 'tice', 'tice_time', 'add_user', 'add_time', 'status'), array('issue_id' => $id), array('id' => 'desc'), 100);
        if ($rows) {
            exit(json_encode(array('status' => true, 'content' => $rows)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }
    }

    /**
     * 输出列表
     *
     * 跟进给定的条件输出列表
     */
    public function rows()
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

        //如果有uid传值，则输出指定uid下的项目列表
        $where = array();
        $filter = $this->input->get('filter');
        if ($filter) {
            $filter_arr = explode('|', $filter);
            if ($filter_arr) {
                foreach ($filter_arr as $key => $value) {
                    $tmp = explode(',', $value);
                    $where[$tmp[0]] = $tmp[1];
                }
            }
        }

        $limit = empty($this->input->get('limit')) ? '20' : $this->input->get('limit');
        $offset = empty($this->input->get('offset')) ? '0' : $this->input->get('offset');

        $this->load->model('Model_test', 'test', TRUE);
        $rows = $this->test->get_rows(array('id', 'project_id', 'plan_id', 'issue_id', 'repos_id', 'br', 'test_flag', 'trunk_flag', 'test_summary', 'state', 'rank', 'tice', 'tice_time', 'add_user', 'add_time', 'accept_user', 'accept_time', 'status'), $where, array('id' => 'desc'), $limit, $offset);
        if ($rows['total']) {
            foreach ($rows['data'] as $key => $value) {
                $ids[] = $value['issue_id'];
            }
            $this->load->model('Model_issue', 'issue', TRUE);
            $Issue_rows = $this->issue->get_rows_by_ids($ids, array('id', 'issue_name'));
            if ($Issue_rows) {
                foreach ($Issue_rows as $key => $value) {
                    $Issue_rows_tmp[$value['id']] = $value;
                }
            }
            foreach ($rows['data'] as $key => $value) {
                $rows['data'][$key]['issue'] = $Issue_rows_tmp[$value['issue_id']];
            }
            exit(json_encode(array('status' => true, 'content' => $rows)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }
    }

    /**
     * 关注列表
     */
    public function star()
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

        $uid = $this->input->get('uid');
        if (!($uid != 0 && ctype_digit($uid))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':用户ID[ '.$uid.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '用户ID格式错误')));
        }

        $limit = empty($this->input->get('limit')) ? '20' : $this->input->get('limit');
        $offset = empty($this->input->get('offset')) ? '0' : $this->input->get('offset');

        $result = array('total' => 0, 'data' => array());
        $this->load->model('Model_star', 'star', TRUE);
        $rows = $this->star->get_rows(array('star_id'), array('add_user' => $uid, 'star_type' => '2'), array('id' => 'desc'), $limit, $offset);
        $result['total'] = $rows['total'];
        if ($rows['data']) {
            foreach ($rows['data'] as $key => $value) {
                $ids[] = $value['star_id'];
            }
        }
        $this->load->model('Model_test', 'test', TRUE);
        $result['data'] = $this->test->get_rows_by_ids($ids, array('id', 'project_id', 'plan_id', 'issue_id', 'repos_id', 'br', 'test_flag', 'trunk_flag', 'test_summary', 'state', 'rank', 'tice', 'tice_time', 'add_user', 'add_time', 'accept_user', 'accept_time', 'status'));
        if ($rows['total']) {
            foreach ($result['data'] as $key => $value) {
                $ids[] = $value['issue_id'];
            }
            $this->load->model('Model_issue', 'issue', TRUE);
            $Issue_rows = $this->issue->get_rows_by_ids($ids, array('id', 'issue_name'));
            if ($Issue_rows) {
                foreach ($Issue_rows as $key => $value) {
                    $Issue_rows_tmp[$value['id']] = $value;
                }
            }
            foreach ($result['data'] as $key => $value) {
                $result['data'][$key]['issue'] = $Issue_rows_tmp[$value['issue_id']];
            }
            exit(json_encode(array('status' => true, 'content' => $result)));
        } else {
            log_message('debug', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }
    }
}
