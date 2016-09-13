<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Model_webhooks extends MY_Model {
    public $dbgroup = 'default';
    public $table   = 'hooks';
    public $primary = 'id';
}
