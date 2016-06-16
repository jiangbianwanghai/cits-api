<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 系统监控
 *
 * 常用对本系统的状态监控
 */
class System extends CI_Controller {
    
    public function index()
    {
        $data['file'] = array();
        $data['log_file'] = '';
        if (!$this->input->get('log_file')) {
            $data['file'] = array_diff(scandir(APPPATH.'logs'), array('.', '..', 'index.html'));
        }
        $data['log_file'] = $this->input->get('log_file');
        $this->load->view('system_log', $data);
    }

    public function log()
    {
        //读取目录中的日志文件
        if ($this->input->get('log_file')) {
            $content = file_get_contents(APPPATH.'logs/'.$this->input->get('log_file'));
            echo nl2br($content);
        }
    }
}
