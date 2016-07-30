<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 代码库接口
 *
 * 项目团队是一切协作的基础，先创建项目团队后才可以创建计划和任务
 *
 * @author jiangbianwanghai <webmaster@jiangbianwanghai.com>
 * @since 0.1
 */
class Repos extends CI_Controller {

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
        $this->form_validation->set_rules('repos_name', '名称', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('repos_name_other', '别名', 'trim');
        $this->form_validation->set_rules('repos_url', '地址', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('repos_summary', '描述', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('merge', '是否需要合并', 'trim|required|is_natural|max_length[1]',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '是否需要合并[ '.$this->input->post('merge').' ]不符合规则',
                'max_length' => '是否需要合并[ '.$this->input->post('merge').' ]太长了'
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

        if ($this->_check_repeat($this->input->post('repos_name'))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':此代码库已存在');
            exit(json_encode(array('status' => false, 'error' => '此代码库已存在')));
        }

        //写入数据
        $this->load->model('Model_repos', 'repos', TRUE);
        $Post_data = array(
            'repos_name' => $this->input->post('repos_name'),
            'repos_name_other' => $this->input->post('repos_name_other'),
            'repos_url' => $this->input->post('repos_url'),
            'repos_summary' => $this->input->post('repos_summary'),
            'merge' => $this->input->post('merge'),
            'add_user' => $this->input->post('add_user'),
            'add_time' => time(),
            'type' => $this->input->post('type')
        );
        $id = $this->repos->add($Post_data);
        if ($id) {
            exit(json_encode(array('status' => true, 'content' => $id)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入错误');
            exit(json_encode(array('status' => false, 'error' => '写入错误')));
        }
    }

    public function update()
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
        $this->form_validation->set_rules('id', '代码库id', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '代码库id[ '.$this->input->post('id').' ]不符合规则',
            )
        );
        $this->form_validation->set_rules('repos_name', '名称', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('repos_name_other', '别名', 'trim');
        $this->form_validation->set_rules('repos_url', '地址', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('repos_summary', '描述', 'trim|required',
            array('required' => '%s 不能为空')
        );
        $this->form_validation->set_rules('merge', '是否需要合并', 'trim|required|is_natural|max_length[1]',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '是否需要合并[ '.$this->input->post('merge').' ]不符合规则',
                'max_length' => '是否需要合并[ '.$this->input->post('merge').' ]太长了'
            )
        );
        $this->form_validation->set_rules('last_user', '修改人ID', 'trim|required|is_natural_no_zero',
            array(
                'required' => '%s 不能为空',
                'is_natural_no_zero' => '修改人ID[ '.$this->input->post('last_user').' ]不符合规则',
            )
        );
        if ($this->form_validation->run() == FALSE) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':'.validation_errors());
            exit(json_encode(array('status' => false, 'error' => validation_errors())));
        }

        if ($this->_check_repeat($this->input->post('repos_name')) > 1) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':此代码库已存在');
            exit(json_encode(array('status' => false, 'error' => '此代码库已存在')));
        }

        //写入数据
        $this->load->model('Model_repos', 'repos', TRUE);
        $Post_data = array(
            'repos_name' => $this->input->post('repos_name'),
            'repos_name_other' => $this->input->post('repos_name_other'),
            'repos_url' => $this->input->post('repos_url'),
            'repos_summary' => $this->input->post('repos_summary'),
            'merge' => $this->input->post('merge'),
            'last_user' => $this->input->post('last_user'),
            'last_time' => time(),
        );
        $bool = $this->repos->update_by_where($Post_data, array('id' => $this->input->post('id')));
        if ($bool) {
            exit(json_encode(array('status' => true, 'content' => $bool)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':写入错误');
            exit(json_encode(array('status' => false, 'error' => '写入错误')));
        }
    }

    /**
     * 输出代码库缓存信息
     *
     * 主要用于代码库信息查询，减轻读库的压力
     */
    public function cache()
    {
        //验证请求的方式
        if ($_POST) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':本接口只接受GET传值');
            exit(json_encode(array('status' => false, 'error' => '本接口只接受GET传值')));
        }

        $this->load->model('Model_repos', 'repos', TRUE);
        $rows = $this->repos->get_rows(array('id', 'repos_name', 'repos_name_other', 'repos_url', 'merge', 'type'), array('status' => 1), array('id' => 'desc'), 100);
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

        //GET传值不能为空
        if (empty($_GET)) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':请填写GET数据');
            exit(json_encode(array('status' => false, 'error' => '请填写GET数据')));
        }

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

        $limit = $this->input->get('limit') ? $this->input->get('limit') : '20';
        $offset = $this->input->get('offset') ? $this->input->get('offset') : '0';

        $this->load->model('Model_repos', 'repos', TRUE);
        $rows = $this->repos->get_rows(array('id', 'repos_name', 'repos_name_other', 'repos_url', 'repos_summary', 'merge', 'add_user', 'add_time', 'last_user', 'last_time', 'status', 'type'), $where, array('id' => 'desc'), $limit, $offset);
        if ($rows['total']) {
            exit(json_encode(array('status' => true, 'content' => $rows)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }
    }

    /**
     * 删除
     */
    public function del()
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

        $id = $this->input->get('id');
        if (!($id != 0 && ctype_digit($id))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':代码库id[ '.$id.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '代码库id格式错误')));
        }

        $user = $this->input->get('user');
        if (!($user != 0 && ctype_digit($user))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':用户 id[ '.$id.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '用户 id格式错误')));
        }

        $this->load->model('Model_repos', 'repos', TRUE);
        $bool = $this->repos->update_by_where(array('status' => '-1', 'last_user' => $user, 'last_time' => time()), array('id' => $id));
        if ($bool) {
            exit(json_encode(array('status' => true, 'content' => $bool)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':删除失败');
            exit(json_encode(array('status' => false, 'error' => '删除失败')));
        }
    }

    /**
     * 根据bug id输出bug详情
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
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':代码库 id[ '.$id.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '代码库 id格式错误')));
        }

        $this->load->model('Model_repos', 'repos', TRUE);
        $row = $this->repos->fetchOne(array(), array('id' => $id));
        if ($row) {
            exit(json_encode(array('status' => true, 'content' => $row)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }
    }

    /**
     * 验证是否重复
     *
     * 同一个计划下的bug名称不能重复
     */
    private function _check_repeat($string = '')
    {
        if ($string) {
            $this->load->model('Model_repos', 'repos', TRUE);
            $rows = $this->repos->get_rows(array('id'), array('repos_name' => $string, 'status' => '1'));
            if ($rows['total']) {
                return true;
            } else {
                return false;
            }
        }
    }
}
