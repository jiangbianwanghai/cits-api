<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 计划接口
 *
 * 计划是包含在项目中的，任务是包含在计划中的。
 *
 * @author jiangbianwanghai <webmaster@jiangbianwanghai.com>
 * @since 0.1
 */
class Plan extends CI_Controller {

    /**
     * 计划写入
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
        $this->form_validation->set_rules('project_id', '项目ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '项目ID[ '.$this->input->post('project_id').' ]不符合规则',
            )
        );
        $this->form_validation->set_rules('plan_name', '项目团队全称', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('plan_description', '描述', 'trim');
        $this->form_validation->set_rules('startime', '开始时间', 'trim|required|is_natural_no_zero|exact_length[10]',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '开始时间[ '.$this->input->post('startime').' ]不符合规则',
                'exact_length' => '开始时间[ '.$this->input->post('startime').' ]必须是时间戳'
            )
        );
        $this->form_validation->set_rules('endtime', '结束时间', 'trim|required|is_natural_no_zero|exact_length[10]',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '结束时间[ '.$this->input->post('endtime').' ]不符合规则',
                'exact_length' => '结束时间[ '.$this->input->post('startime').' ]必须是时间戳'
            )
        );
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
        $this->load->model('Model_plan', 'plan', TRUE);
        $Post_data = array(
            'project_id' => $this->input->post('project_id'),
            'plan_name' => $this->input->post('plan_name'),
            'plan_discription' => $this->input->post('plan_description'),
            'startime' => $this->input->post('startime'),
            'endtime' => $this->input->post('endtime'),
            'add_user' => $this->input->post('add_user'),
            'add_time' => time(),
        );
        $id = $this->plan->add($Post_data);
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
        $this->load->model('Model_project', 'project', TRUE);
        $rows = $this->project->get_rows(array('id', 'project_name', 'add_user', 'add_time'), array(), array('id' => 'desc'), 100);
        if ($rows) {
            exit(json_encode(array('status' => true, 'data' => $rows)));
        } else {
            exit(json_encode(array('status' => false, 'data' => '')));
        }
    }

    /**
     * 根据项目团队ID输出计划列表
     */
    public function rows_by_projectid()
    {
        //验证请求的方式
        if ($_POST) {
            exit(json_encode(array('status' => false, 'error' => '本接口只接受GET传值')));
        }

        //项目id格式验证
        $id = $this->input->get('id');
        if (!($id != 0 && ctype_digit($id))) {
            exit(json_encode(array('status' => false, 'error' => '项目id格式错误')));
        }

        $this->load->model('Model_plan', 'plan', TRUE);
        $rows = $this->plan->get_rows(array('id', 'plan_name', 'plan_discription', 'startime', 'endtime', 'add_user', 'add_time', 'state', 'timeline'), array('project_id' => $id, 'status' => 1), array('id' => 'desc'), 100);
        if ($rows) {
            exit(json_encode(array('status' => true, 'content' => $rows)));
        } else {
            exit(json_encode(array('status' => false, 'error' => '数据不存在')));
        }
    }

    /**
     * 根据任务id输出任务详情
     */
    public function profile()
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
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':计划ID[ '.$id.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '计划id格式错误')));
        }

        $this->load->model('Model_plan', 'plan', TRUE);
        $row = $this->plan->fetchOne(array(), array('id' => $id));
        if ($row) {
            exit(json_encode(array('status' => true, 'content' => $row, 'test' => 'ok')));
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

        $ids = array();
        $Id_string = $this->input->get('ids');
        if ($Id_string) {
            $idarr = explode(',', $Id_string);
            foreach ($idarr as $key => $value) {
                $ids[] = $value;
            }
        }

        $limit = $this->input->get('limit') ? $this->input->get('limit') : '20';
        $offset = $this->input->get('offset') ? $this->input->get('offset') : '0';

        $this->load->model('Model_plan', 'plan', TRUE);
        if ($ids) {
            $rows = $this->plan->get_rows_by_ids($ids, array('id', 'plan_name'));
            if ($rows) {
                exit(json_encode(array('status' => true, 'content' => $rows)));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
                exit(json_encode(array('status' => false, 'error' => '记录不存在')));
            }
        } else {
            $rows = $this->plan->get_rows(array('id', 'project_id', 'plan_name', 'plan_discription', 'startime', 'endtime', 'add_user', 'add_time', 'last_user', 'last_time', 'state', 'status', 'timeline'), $where, array('id' => 'desc'), $limit, $offset);
            if ($rows['total']) {
                exit(json_encode(array('status' => true, 'content' => $rows)));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
                exit(json_encode(array('status' => false, 'error' => '记录不存在')));
            }
        }
    }

    /**
     * 提测成功率计算
     */
    public function rate()
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

        //计划id格式验证
        $id = $this->input->get('id');
        if (!($id != 0 && ctype_digit($id))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':计划ID[ '.$id.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '计划id格式错误')));
        }

        //读取计划中的任务
        $this->load->model('Model_issue', 'issue', TRUE);
        $rows = $this->issue->get_rows(array('id'), array('status' => 1, 'workflow' => 7), array(), 100);

        //循环计算每个任务的提测率
        $rateArr = array();
        $this->load->model('Model_test', 'test', TRUE);
        //组合issue ID
        $issueIdArr = array();
        if ($rows['data']) {
            foreach ($rows['data'] as $key => $val) {
                $issueIdArr[] = $val['id'];
            }
        }
        $testRows = $this->test->get_rows_by_ids($issueIdArr, array('issue_id', 'repos_id'));
        $maxTest = 0;
        $testIdArr = array();
        //计算每个任务的提测成功率
        if ($testRows) {
            foreach ($testRows as $key => $value) {
                if (isset($testIdArr[$value['issue_id']][$value['repos_id']])) {
                    $testIdArr[$value['issue_id']][$value['repos_id']] += 1;
                } else {
                    $testIdArr[$value['issue_id']][$value['repos_id']] = 1;
                }
            }
            if ($testIdArr) {
                foreach ($testIdArr as $key => $value) {
                    $rateArr[$key] = 1/max($value);
                }
            }
        }

        //输出整个计划的提测率
        if ($rateArr) {
            $rateTotal = 0;
            foreach ($rateArr as $key => $value) {
                $rateTotal += $value;
            }
            exit(json_encode(array('status' => true, 'content' => sprintf("%.2f", $rateTotal/count($rateArr)))));
        } else {
            exit(json_encode(array('status' => true, 'content' => '无提测数据用于计算')));
        }
    }
}
