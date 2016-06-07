<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 授权接口
 *
 * 使用API，需要先获得授权，即拿到发放给客户端的token
 *
 * @author jiangbianwanghai <webmaster@jiangbianwanghai.com>
 * @since 0.1
 */
class Authorize extends CI_Controller {

    /**
     * 返回授权token
     *
     * @return string 返回加密的授权字符串。
     */
    public function get_token()
    {
        return 'token_string';
    }
}