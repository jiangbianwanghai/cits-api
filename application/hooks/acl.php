<?php
class acl{
    private $CI;
    
    public function __construct()
    {
        $this->CI = &get_instance();
    }
    
    public function index()
    {
        $access_token = 0;
        //获取get方式传递的access_token
        if ($this->CI->input->get('access_token')) {
            $access_token = $this->CI->input->get('access_token');
        }
        //获取get方式传递的access_token
        if ($this->CI->input->post('access_token')) {
            $access_token = $this->CI->input->post('access_token');
        }
        if (!$access_token) {
            log_message('error', $this->CI->router->fetch_class().'/'.$this->CI->router->fetch_method().':请提供授权信息');
            exit(json_encode(array('status' => false, 'error' => '请提供授权信息')));
        }
        //读取access_token资源
        $this->CI->config->load('extension', TRUE);
        $system = $this->CI->config->item('system', 'extension');
        //验证授权
        if (!in_array($access_token, $system['access_token'], true)) {
            log_message('error', $this->CI->router->fetch_class().'/'.$this->CI->router->fetch_method().':授权失败，提交的access_token [ '.$access_token.' ]');
            exit(json_encode(array('status' => false, 'error' => '授权失败')));
        }
        log_message('debug', '正在访问的授权用户：'.$access_token);
    }
}
