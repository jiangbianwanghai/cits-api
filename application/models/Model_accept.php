<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Model_accept extends MY_Model {
    public $dbgroup = 'default';
    public $table   = 'accept';
    public $dbprefix = 'choc_';
    public $primary = 'id';

    /**
     * 任务受理量统计
     */
    public function report($stime = 0, $etime = 0, $uid = 0)
    {
        $where = "WHERE `accept_time` > '".$stime."' AND `accept_time` <= '".$etime."'";
        if ($uid) {
            $where .= " AND `accept_user` = '".$uid."'";
        }

        $customDB = $this->load->database($this->dbgroup, TRUE);
        $sql = "SELECT FROM_UNIXTIME(`accept_time`, '%Y-%m-%d') AS `perday`, COUNT(DISTINCT `issue_id`) AS `total` FROM `".$this->dbprefix.$this->table."` ".$where." GROUP BY FROM_UNIXTIME(`accept_time`, '%Y-%m-%d')";
        $query = $customDB->query($sql);
        return $query->result_array();
    }
}