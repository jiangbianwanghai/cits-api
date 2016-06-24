<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Model_bug_comment extends MY_Model {
    public $dbgroup = 'default';
    public $table   = 'issue_bug_comment';
    public $primary = 'id';
}