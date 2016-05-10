<?php
class Dindex extends CI_Model{
    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }
    public function getData($table,$where,$n,$off){
        if(is_array($where)){
            foreach ($where as $w=>$k) {
                $this->db->where($w,$k);
            }
        }

        if($n>0 && $off>=0){

            $q =$this->db->get($table,$off,$n);
        }
        else{
            $q =$this->db->get($table);
        }
        return $q->result_array();
    }
    public function getNumData($table,$where){
        if(is_array($where)){
            foreach ($where as $w=>$k) {
                $this->db->where($w,$k);
            }
        }
        $q =$this->db->get($table);
        return $q->num_rows();
    }
    public function getNumDataDetail($table,$where){
        $sql = "select * from $table where 1=1 ";
        if(is_array($where)){
            foreach ($where as $w=>$k) {
                $sql .=" and ".$k['key']." ".$k['compare']." ".$k['value'];
            }
        }
        $q =$this->db->query($sql);
        return $q->num_rows();
    }
    public function getDataDetail($options){
        $default = array(
            'table'=>'',
            'input'=>'*',
            'order'=>'id',
            'where'=>array(),
            'limit'=>'',
            'escape'=>0,
            'group_by'=>''
            );
        if(is_array($options)){
            $options = array_replace($default, $options);
            if(isNull($options['table'])) return;
            $sql = "select ". $options['input']." from ".$options['table']." where 1 = 1 ";
            if(is_array($options['where'])){
                foreach ($options['where'] as $subwhere) {
                    $swhere = $subwhere['value'];
                    if($options['escape']==1){
                        $swhere = $this->db->escape($swhere);
                    }
                    $con = 'and';
                    $sql .= " ".$con." ".$subwhere['key']." ".$subwhere['compare']." ".$swhere;
                }
            }
            if(!isNull($options['group_by'])){
                $sql .=" group by ".$options['group_by'];
            }
            if(!isNull($options['order'])){
                $sql .=" order by ".$options['order'];
            }
            if(!isNull($options['limit'])){
                $sql .=" limit ".$options['limit'];
            }
            $q = $this->db->query($sql);
            return $q->result_array();
        }
    }
    public function getDataOrder($table,$where,$n,$off,$order){
        if(is_array($where)){
            foreach ($where as $w=>$k) {
                $this->db->where($w,$k);
            }
        }

        if($order!=null && strlen($order)>0){
            $this->db->order_by($order);
        }
        if($n>=0 && $off>=0){
            $q =$this->db->get($table,$off,$n);
        }
        else{
            $q =$this->db->get($table);
        }
        return $q->result_array();
    }
    public function getSettings($key){
        $setting = $this->cache->get('website_setting');
        if ( !@$setting )
        {
            $q= $this->db->get('configs');
            $setting = $q->result_array();
            $this->cache->save('website_setting', $setting, $this->config->item('enuy_time_cache_setting'));
        }
        $lang = $this->session->userdata('lang');
        $add = (isNull($lang)?"vi":$lang)."_"; 
        foreach ($setting as  $value) {
            if(strtoupper($value['keyword']) === strtoupper($key)){

                return $value[$add."value"];
            }
        }
        return $key;
    }
    function recursiveTableOrder($input="",$table,$field,$basefield,$fieldValue,$where,$order){
        $sql = "select ".(strlen($input)>0?$input:" * ")." from ".$table." where 1= 1 ";
        if($fieldValue!="-1"){
            $sql .=" and ".$field." = '".$fieldValue."'";
        }
        if(is_array($where)){
            foreach ($where as $w) {
                $sql .=" and ".$w["key"]." = ".$w["value"]."";
            }
        }
        if(!isNull($order)){
            $sql .=" order by ".$order;
        }
        $q = $this->db->query($sql);
        $arr = $q->result_array();
        $r = array();
        foreach ($arr as $item) {
            $obj = new stdClass();
            $obj->item = $item;
            if(@$item[$basefield]){ 
                $obj->childs= $this->recursiveTable($input,$table,$field,$basefield,$item[$basefield],$where);
            }
            else{
                $obj->childs=array();
            }
            array_push($r, $obj);
        }
        return $r;
    }
    function recursiveTable($input="",$table,$field,$basefield,$fieldValue,$where){
        return $this->recursiveTableOrder($input,$table,$field,$basefield,$fieldValue,$where," ord ");
    }
    function getRelateItem($id,$parent, $table,$limit){
        $sql = "select * from $table where 1=1 and act=1 ";
        if(strlen($parent)>0 && is_string($parent)){
            $tmp = explode(',', $parent);
            $aw = array();

            $str = " and (";
            for ($i=0;$i<sizeof($tmp);$i++) {
                $value = $tmp[$i];
                $str .=" parent = ".$value;
                if($i<sizeof($tmp)-1){
                    $str .=" or ";
                }
            }
            $str.=")";
        }
        
        else if(strlen($parent)>0 && is_numeric($parent)){
            $str = " and parent = $parent ";
        }
        else{
            $str ="";
        }
        $sql .= $str;
        $sql .=" and id != ".$id;
        $sql .=" order by id desc limit ".$limit;
        $q = $this->db->query($sql);
        return $q->result_array();
    }
    function updateData($table,$data,$where){
        $this->db->trans_start();
        if(is_array($where)){
            foreach ($where as $w=>$k) {
                $this->db->where($w,$k);
            }
        }
        foreach ($data as $key=>$val) {
            $this->db->set($key, $val, FALSE);
        }
        
        $this->db->update($table);
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();

            return false;
        }
        else
        {
            $this->db->trans_commit();
            return true;
        }
    }
    function updateDataFull($table,$data,$where){
        $this->db->trans_start();
        if(is_array($where)){
            foreach ($where as $w=>$k) {
                $this->db->where($w,$k);
            }
        }
        
        $this->db->update($table,$data);
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();

            return false;
        }
        else
        {
            $this->db->trans_commit();
            return true;
        }
    }
    function insertData($table,$data){
        $this->db->trans_start();
        $this->db->insert($table,$data);
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();

            return false;
        }
        else
        {
            $this->db->trans_commit();
            return true;
        }
    }
    function getBreadcrumb($table,$pid){
        $this->getBreadcrumbFull($table,$pid,"");
    }
    
    function getBreadcrumbFull($table, $pid,$div){

        if(strlen($pid)<=0 || $pid==0) {
            echo "<li class=\"active pull-left\"><a  class=\"box-active\" ";
            if(!isNull($div)){
                echo " onclick=\"loadPageContent('#changeable','".base_url()."');return false;\" "; 
            }
            echo " href='".base_url()."'> <i class=\"fa fa-home\"> </i> </a></li> ";
            return;
        }

        if(is_string($pid)){
            $sub = explode(',', $pid);
            $this->db->where('id',$sub[0]);
        }
        else{
            $this->db->where('id',$pid);
        }
        $q= $this->db->get($table);
        $arr = $q->result_array();
        if(sizeof($arr)>0){
            if(array_key_exists('parent', $arr[0])){
                $this->getBreadcrumb($table,$arr[0]['parent']);
                echo "<li class=\"padding0 pull-left\"><span>/</span></li><li class=\"active pull-left\"><a class=\"box-active\" ";
                if(!isNull($div)){
                    echo " onclick=\"loadPageContent('#changeable','".echor($arr[0],'slug',0)."');return false;\" ";
                }
                echo " href='".echor($arr[0],'slug',0)."'>". echor($arr[0],'name',1)."</a></li>";
            }
        }
        
    }
    public function printCategories($datatable, $dataitem,$key){

        $key = isNull($key)?"table_parent":$key;
        $arrSub=array();
        $parentGet = 0;
        if(array_key_exists($key, $datatable)){
            $tablename=$datatable[$key];
            if($tablename && isset($dataitem['parent'])){
                $parent = is_string($dataitem['parent'])?explode(',', $dataitem['parent'])[0]:$dataitem['parent'];
                $arr = array();
				
                while($parent!=0){
                    $arr = $this->Dindex->getData($tablename,array('id'=>$parent),0,1);
                    if(sizeof($arr)>0){ 
                        $parent = $arr[0]['parent'];
                    }
                    else{
                        $parent =0;
                    }
                    if($parent==0){
                        $parentGet = 0;
                    }  
                }
            }
        }
        $this->getCategories(0,$tablename,$parentGet);

    }
    public function getCategories($count,$tablename, $parentGet){
        $count++;
        $arrSub = $this->getData($tablename,array('parent'=>$parentGet),0,100);
		
        echo "<ul class='cates".$count."'>";
        foreach ($arrSub as $item) {
            echo "<li class='itemcate".$count."'>";
            echo '<a onclick="loadPageContent(\'#changeable\',\''.getExactLink(echor($item,'tag',1)).'\');return false;"';
            echo "href='".echor($item,'tag',1)."'>".echor($item,'name',1)."</a>";
            $this->getCategories($count,$tablename,$item['id']);
            echo "</li>";
        }
        echo "</ul>";
    }
    public function getVisited(){
        $sql = "select (select count(*) from (select ip from visit_online where create_time + 600>UNIX_TIMESTAMP(now()) group by ip) tmpcount) online ,";
        $sql .= " (select count(*) from visit_online) total_visit,";
        $sql .= " (select count(*) from visit_online where month(from_unixtime(create_time)) = MONTH(NOW()) and";
        $sql .= " year(from_unixtime(create_time)) = year(NOW()) and day(from_unixtime(create_time)) = day(NOW())) today,";
        $sql .= " (select count(*) from visit_online where month(from_unixtime(create_time)) = MONTH(NOW())) this_month,";
        $sql .= " (select count(*) from visit_online where week(from_unixtime(create_time)) = week(NOW())) this_week,";
        $sql .= " (select count(*) from visit_online where year(from_unixtime(create_time)) = year(NOW())) this_year";

        $q = $this->db->query($sql);
        return $q->result_array();
    }

    public function deleteData($table,$where){
        

        $this->db->trans_start();
        if(is_array($where)){
            foreach ($where as $key => $value) {
                $this->db->where($key,$value);
            }
        }
        $this->db->delete($table);
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();

            return false;
        }
        else
        {
            $this->db->trans_commit();
            return true;
        }
    }
    public function checkCaptcha($captcha,$expiration){
        $sql = "SELECT COUNT(*) AS count FROM captcha WHERE word = ? AND ip_address = ? AND captcha_time > ?"; 
        $binds = array($captcha, $this->input->ip_address(), $expiration); 
        $query = $this->db->query($sql, $binds); 
        $row = $query->row(); 
        return $row->count >0;
    }
    function getCaptcha(){
        $vals = array('img_path' => './captchas/', 
        'img_url' => base_url().'captchas/', 
        'font_path' => FCPATH.'captchas/MyriadProBold.otf',
        'img_width' => '120',
        'img_height' => '45',
        'word_length'=>'4',
        'expiration' => 7200);
        $cap = create_captcha($vals);
        $data = array('captcha_time' => $cap['time'], 'ip_address' => $this->input->ip_address(), 'word' => $cap['word']);
        
        $b_SaveData = $this->insertData('captcha',$data);

        return $cap['image'];
    }



    public function cachenao($cache,$id){
        if($cache){
            $this->db->cache_on();
        }
        else{
            $this->db->cache_delete_all();
        }
        $this->db->where('id',$id);
        $q =$this->db->get('test');
        if($cache){
            $this->db->cache_off();
        }
        return $q->result_array();
    }
    public function clearCache($param1,$param2){
        $this->db->cache_delete($param1,$param2);
    }
}
?>