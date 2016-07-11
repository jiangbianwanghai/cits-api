<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 环境接口
 *
 * @author jiangbianwanghai <webmaster@jiangbianwanghai.com>
 * @since 0.1
 */
class Env extends CI_Controller {
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

        $limit = $this->input->get('limit') ? '500' : $this->input->get('limit');
        $offset = $this->input->get('offset') ? '0' : $this->input->get('offset');

        $this->load->model('Model_env', 'env', TRUE);
        if ($ids) {
            $rows = $this->env->get_rows_by_ids($ids, array('id', 'repo'));
            if ($rows) {
                exit(json_encode(array('status' => true, 'content' => $rows)));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
                exit(json_encode(array('status' => false, 'error' => '记录不存在')));
            }
        } else {
            $rows = $this->env->get_rows(array('id', 'repo', 'env', 'br', 'rev', 'createtime', 'updatetime'), $where, array('id' => 'desc'), $limit, $offset);
            if ($rows['total']) {
                exit(json_encode(array('status' => true, 'content' => $rows)));
            } else {
                log_message('error', $this->router->fetch_class().'/'.$this->router->fetch_method().':记录不存在');
                exit(json_encode(array('status' => false, 'error' => '记录不存在')));
            }
        }
    }
}
