<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Model_users extends MY_Model {
    public $dbgroup = 'default';
    public $table   = 'users';
    public $primary = 'id';
}