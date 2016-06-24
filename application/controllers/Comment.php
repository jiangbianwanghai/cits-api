<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 评论接口
 *
 * 基于任务的Bug。
 *
 * @author jiangbianwanghai <webmaster@jiangbianwanghai.com>
 * @since 0.1
 */
class Comment extends CI_Controller {

    /**
     * 根据根据id评论列表
     */
    public function get_rows_by_id()
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

        $table = $this->input->get('table');
        if (!in_array($table, array('issue', 'bug'))) {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':表类型[ '.$table.' ]格式错误');
            exit(json_encode(array('status' => false, 'error' => '表类型格式错误')));
        }

        
        if ($table == 'issue') {
            $this->load->model('Model_issue_comment', 'comment', TRUE);
            $where = array('issue_id' => $id);
        }

        if ($table == 'bug') {
            $this->load->model('Model_bug_comment', 'comment', TRUE);
            $where = array('bug_id' => $id);
        }
        
        $rows = $this->comment->get_rows(array('id', 'content', 'add_user', 'add_time'), $where, array('id' => 'desc'), 100);
        if ($rows) {
            exit(json_encode(array('status' => true, 'content' => $rows)));
        } else {
            log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
            exit(json_encode(array('status' => false, 'error' => '记录不存在')));
        }
    }
}
