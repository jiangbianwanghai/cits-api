<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Model_issue_comment extends MY_Model {
    public $dbgroup = 'default';
    public $table   = 'issue_comment';
    public $primary = 'id';
}