<?php
class logos_model extends Model{
    public function getHeaderLogo(){
        return $this->select("select name, height, width, file_name from tblogos where name = 'header'");
    }
    public function getLoginLogo(){
        return $this->select("select name, height, width, file_name from tblogos where name = 'login'");
    }
    public function getReportsLogo(){
        return $this->select("select name, height, width, file_name from tblogos where name = 'reports'");
    }
    public function upload($filename, $height, $width, $where){
        return $this->db->Execute("update tblogos set file_name = '$filename', height = '$height', width = '$width' where name = '$where'");
    }
}
?>
