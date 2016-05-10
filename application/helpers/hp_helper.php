<?php

/* try catch option */
function tryCatchset(){
    set_error_handler(
        create_function(
            '$severity, $message, $file, $line',
            'throw new ErrorException($message, $severity, $severity, $file, $line);'
        )
    );
}
function echoJSON($code,$message){
    $obj = new stdClass();
    $obj->code= $code;
    $obj->message= $message;
    echo json_encode($obj);
}
function getImageThumb($img){
    if(@$img){
        $pos = strrpos($img, "/");
        $imgthumb = substr($img, 0,$pos+1)."thumbs/".substr($img, $pos+1);
         if(isNull($imgthumb) || !file_exists($imgthumb)) return "theme/admin/images/noimage.png";
        return $imgthumb;
    }
}


function echom($arr,$key,$check=1){
    echo echor($arr,$key,$check);
}
function echor($arr,$key,$check=1){
     if(!is_array($arr)){ echo $arr;return;}
    if(!array_key_exists($key, $arr) && $key!="img_thumb"){echo "";return;}
    if(is_array($arr)){
        if($check==1){
            $CI = & get_instance();
            $k = @$CI->session->userdata('lang')?$CI->session->userdata('lang'):"vi";
            $add = $k=="en"?"en":"";
            if(array_key_exists($key."_".$add, $arr)){
                return $arr[$key."_".$add];
            }
            else{
                if($key=='create_time'||$key=='update_time'){
                    return date('d/m/Y H:i:s',$arr[$key]);
                }
                else if($key=='slug'){
                    return base_url().$arr[$key];
                }
                else if($key=='price'||$key=='price_sale'){
                    if((double)$arr[$key]==0)
                    return lang('LIENHE');
                    else
                    return number_format((double)$arr[$key],0,',','.')." vnđ";
                }
                else if($key=='img_thumb'){
                    
                    $key = 'img';
                    if(isNull($arr[$key]) || !file_exists($arr[$key])) return "theme/admin/images/noimage.png";
                    if($arr[$key]!=null && strpos($arr[$key], 'http')===FALSE && strpos($arr[$key], 'theme/frontend')===FALSE){
                        return getImageThumb($arr[$key]);    
                    }
                    else{
                        return $arr[$key];
                    }
                    
                }
                else if($key=='img'){
                    if(isNull($arr[$key]) || !file_exists($arr[$key])) return "theme/admin/images/noimage.png";
                    return $arr['img'];
                }
                else{
                return $arr[$key];
                }
            }
        }
        else{
            return $arr[$key];
        }
    }
    else{
        return $arr;
    }
}
function isNull($str){
    return  $str==NULL || (is_string($str) && strlen(trim($str))==0);
}
function subString($body,$length){
    $line=$body;
    if (preg_match('/^.{1,'.$length.'}\b/s', $body, $match))
    {
        $line=$match[0];
    }
    return $line;
}

function getExactLink($link){
    if($link!=NULL && strlen($link)>0 &&  strpos($link, 'http')!==FALSE){
        return $link;
    }
    else return base_url().$link;
}
/* send mail */

function sendMail($email,$tieude,$noidung){

    tryCatchset();

    $CI= & get_instance();

    $CI->load->helper('url');

    $dl=$CI->Dindex->get_site_setting();

    if(@$dl[0]['emailsend'] && @$dl[0]['passsend']){

        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $CI->load->library('email');
        $config['protocol']    = 'smtp';
        $config['smtp_host']    = 'ssl://smtp.'.base_url();
        $config['smtp_port']    = '465';
        $config['smtp_timeout'] = '7';
        $config['smtp_user']    = $dl[0]['emailsend'];
        $config['smtp_pass']    = $dl[0]['passsend'];
        $config['charset']    = 'utf-8';


        $config['newline']    = "\r\n";
        $config['mailtype'] = 'html';
        $config['validation'] = TRUE;
        $CI->email->initialize($config);
        $today = date("F j, Y, g:i a");
        $CI->email->from($dl[0]['emailsend'], base_url());
        $CI->email->to($email);

        $CI->email->subject($tieude);
        $CI->email->message($noidung);

        try{
            $CI->email->send();
            return true;
        }
        catch( exception $e ){
            echo "Lỗi Gửi mail bạn chưa cấu hình đầy đủ thông tin để gửi mail ";
            return false;
        }


    }
    else{
        return false;
    }





}


function replaceURL($string){
    $string=strtolower($string);
    $str = str_replace('-', ' ', $string);

    $utf8characters = 'à|a, ả|a, ã|a, á|a, ạ|a, ă|a, ằ|a, ẳ|a, ẵ|a,  ắ|a, ặ|a, â|a, ầ|a, ẩ|a, ẫ|a, ấ|a, ậ|a, đ|d, è|e, ẻ|e, ẽ|e, é|e, ẹ|e,  ê|e, ề|e, ể|e, ễ|e, ế|e, ệ|e, ì|i, ỉ|i, ĩ|i, í|i, ị|i, ò|o, ỏ|o, õ|o,  ó|o, ọ|o, ô|o, ồ|o, ổ|o, ỗ|o, ố|o, ộ|o, ơ|o, ờ|o, ở|o, ỡ|o, ớ|o, ợ|o,  ù|u, ủ|u, ũ|u, ú|u, ụ|u, ư|u, ừ|u, ử|u, ữ|u, ứ|u, ự|u, ỳ|y, ỷ|y, ỹ|y,  ý|y, ỵ|y, À|a, Ả|a, Ã|a, Á|a, Ạ|a, Ă|a, Ằ|a, Ẳ|a, Ẵ|a, Ắ|a, Ặ|a, Â|a,  Ầ|a, Ẩ|a, Ẫ|a, Ấ|a, Ậ|a, Đ|d, È|e, Ẻ|e, Ẽ|e, É|e, Ẹ|e, Ê|e, Ề|e, Ể|e,  Ễ|e, Ế|e, Ệ|e, Ì|i, Ỉ|i, Ĩ|i, Í|i, Ị|i, Ò|o, Ỏ|o, Õ|o, Ó|o, Ọ|o, Ô|o,  Ồ|o, Ổ|o, Ỗ|o, Ố|o, Ộ|o, Ơ|o, Ờ|o, Ở|o, Ỡ|o, Ớ|o, Ợ|o, Ù|u, Ủ|u, Ũ|u,  Ú|u, Ụ|u, Ư|u, Ừ|u, Ử|u, Ữ|u, Ứ|u, Ự|u, Ỳ|y, Ỷ|y, Ỹ|y, Ý|y, Ỵ|y, "|,  &|';

    $replacements = array();
    $items = explode(',', $utf8characters);
    foreach ($items as $item) {
        @list($src, $dst) = explode('|', trim($item));
        $replacements[trim($src)] = trim($dst);
    }
    $str = trim(strtr($str, $replacements));
    $str = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', $str);
    $str = trim($str, '-');

    return $str;
}











function printRecursiveSelect($lv,$arrD,$value){
	$lv++;
	for ($i=0;$i<sizeof($arrD);$i++) {
		$sub = $arrD[$i];
		$item = $sub->item;
		echo '<option '.($value==$item['id']?' selected ':'').' value="'.$item['id'].'">└'.str_repeat("---", $lv).$item['name'].'</option>';
		
		printRecursiveSelect($lv,$sub->childs,$value);
	}
}
function printRecursiveSelectWithTag($lv,$arrD,$value){
    $lv++;
    for ($i=0;$i<sizeof($arrD);$i++) {
        $sub = $arrD[$i];
        $item = $sub->item;
        echo '<option dt-slug="'.$item['slug'].'" '.($value==$item['id']?' selected ':'').' value="'.$item['id'].'">└'.str_repeat("---", $lv).$item['name'].'</option>';
        
        printRecursiveSelectWithTag($lv,$sub->childs,$value);
    }
}
function printRecursiveMultiSelect($lv,$arrD,$value){
    $lv++;
    for ($i=0;$i<sizeof($arrD);$i++) {
        $sub = $arrD[$i];
        $item = $sub->item;
        $checked = (is_array($value) && in_array($item['id'], $value))?' checked ':'';
        echo '<li style="  font-size: 15px;color:#1D1D1D;margin: 2px 0px;margin-left:'.(($lv-1)*20).'px;">'.($lv>1?'└----':'').'<input type="checkbox" '.$checked.' value="'.$item['id'].'"/>'.$item['name'].'</li>';
        
        if(@$sub->childs){
            printRecursiveMultiSelect($lv,$sub->childs,$value);
        }
    }
}
function printMenu($arrD,$arrSetting){
    printMenuC($arrD,$arrSetting,0);
}
function printMenuC($arrD,$arrSetting,$count){
    $count++;
    $arrDef = array(
        'classli'=>'',
        'classa'=>'',
        'classul'=>'',
        'divajax'=>'',
        'divclr'=>1
        );

    $arrDefault = array_replace($arrDef, $arrSetting);
    
    $div = $arrDefault['divajax'];
    for ($i=0;$i<sizeof($arrD);$i++) {
        $sub = $arrD[$i];
        $item = $sub->item;
        $exactLink = getExactLink($item["link"]);
        if($i==0){ echo "<ul class='".$arrDefault['classul']."'>";}
        echo "<li class=' clli".$count." ".echor($item,'clazz',1)." ".$arrDefault['classli']."'><a class=' clli".$count."".$arrDefault['classa']."' href='".$exactLink."' ";
        
        if(strlen($div)>0){
            echo "onclick= \"loadPageContent('$div','$exactLink');return false;\" ";
        }
        echo " href='".$item['link']."'>".echor($item,'name',1)."</a>";
        
        printMenuC($sub->childs,$arrSetting,$count);
        echo "</li>";
        if($i==sizeof($arrD)-1){
            if($arrDefault['divclr']==1) echo "<div class='clr'></div>";
            echo "</ul>";
        }
    }
}
function getImageAnyTime($item,$key){
    return (@$item && !isNull($item[$key]))?$item[$key]:'theme/frontend/img/noimage.png';

}
function fakeEval($phpCode) {
    $tmpfname = tempnam("/tmp", "fakeEval");
    $handle = fopen($tmpfname, "w+");
    fwrite($handle, "<?php\n" . $phpCode);
    fclose($handle);
    include $tmpfname;
    unlink($tmpfname);
    return get_defined_vars();
}

?>