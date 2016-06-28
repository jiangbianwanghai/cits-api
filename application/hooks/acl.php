<?php
class acl{
    private $CI;
    
    public function __construct()
    {
        $this->CI = &get_instance();
    }
    
    public function index()
    {
        if (!isset($_SERVER['HTTP_TOKEN'])) {
            log_message('error', $this->CI->router->fetch_class().'/'.$this->CI->router->fetch_method().':请提供授权信息');
            exit(json_encode(array('status' => false, 'error' => '请提供授权信息')));
        }
        //读取access_token资源
        $this->CI->config->load('extension', TRUE);
        $system = $this->CI->config->item('system', 'extension');
        //验证授权
        if (!in_array($_SERVER['HTTP_TOKEN'], $system['access_token'], true)) {
            log_message('error', $this->CI->router->fetch_class().'/'.$this->CI->router->fetch_method().':授权失败，提交的access_token [ '.$_SERVER['HTTP_TOKEN'].' ]');
            exit(json_encode(array('status' => false, 'error' => '授权失败')));
        }
        log_message('debug', 'auth_user:'.$_SERVER['HTTP_TOKEN']);
    }
}
