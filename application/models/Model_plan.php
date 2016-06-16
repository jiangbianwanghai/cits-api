<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 计划数据库模型
 *
 * @package application
 * @subpackage  models
 * @author jiangbianwanghai <webmaster@jiangbianwanghai.com>
 * @since 0.1
 */
class Model_plan extends MY_Model {

	/**
     * @var string dbgroup 数据库
     */
    public $dbgroup = 'default';

    /**
     * @var string table 数据表
     */
    public $table   = 'plan';

    /**
     * @var string primary 主键
     */
    public $primary = 'id';
}