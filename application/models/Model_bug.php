<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Model_bug extends MY_Model {
    public $dbgroup = 'default';
    public $table   = 'issue_bug';
    public $primary = 'id';
}