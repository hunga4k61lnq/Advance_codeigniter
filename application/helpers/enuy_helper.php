<?php 
function ENUY_HEADER(){
	$CI = &get_instance();
	return $CI->Dindex->getSettings('ENUY_HEADER');
}
function ENUY_FOOTER(){
	$CI = &get_instance();
	return $CI->Dindex->getSettings('ENUY_FOOTER');
}
function ENUY_TITLE($dataitem){
	$ret=  "<base href='".base_url()."'/>";
	$CI = &get_instance();
	$tmp = $CI->Dindex->getSettings('TITLE_SEO');
	$titleSEO = (@$dataitem && array_key_exists('s_title', $dataitem) && !isNull($dataitem['s_title']))?$dataitem['s_title']: ((@$dataitem && array_key_exists('name', $dataitem) && !isNull($dataitem['name']))?$dataitem['name']:$tmp) ;
	$tmp = $CI->Dindex->getSettings('DES_SEO');
	$desSEO = (@$dataitem && array_key_exists('s_des', $dataitem) && !isNull($dataitem['s_des']))?$dataitem['s_des']:(isNull($tmp)?$dataitem['name']:$tmp);
	$tmp = $CI->Dindex->getSettings('KEY_SEO');
	$keySEO = (@$dataitem && array_key_exists('s_key', $dataitem) && !isNull($dataitem['s_key']))?$dataitem['s_key']:(isNull($tmp)?$dataitem['name']:$tmp);


	$ret .= '<title>'.addslashes($titleSEO).'</title>';
    $ret .= '<meta name="description" content="'.addslashes($desSEO).'">';
    $ret .= '<meta name="keywords" content="'.addslashes($keySEO).'">';
    $tmp = $CI->Dindex->getSettings('SITE_NAME');
	$ret .= '<meta property="og:site_name" content="'.(isNull($tmp)?$titleSEO:$tmp).'">';
	$ret .= '<meta property="og:url" content="'.current_url().'">';
	$ret .= '<meta property="og:type" content="article">';
	$ret .= '<meta property="og:title" content="'.addslashes($titleSEO).'">';

	if(base_url()==current_url()){
		$img = $CI->Dindex->getSettings('FBSHARE');
		$img = (isNull($img) || $img =='FBSHARE')?$CI->Dindex->getSettings('LOGO'):$img;
		$pos = strpos($img , 'http');
		if($pos === FALSE) $img = base_url().$img;
	}
	else{
		$img = (@$dataitem && @$dataitem['img'])?$dataitem['img']:"";
		if(isNull($img)){
			$tmp = (@$dataitem && @$dataitem['content'])?$dataitem['content']:"";
			$img = getImageFromContent($tmp,$CI->Dindex->getSettings('FBSHARE'));
			$img = (isNull($img) || $img =='FBSHARE')?$CI->Dindex->getSettings('LOGO'):$img;
		}
		$pos = strpos($img , 'http');
		if($pos === FALSE) $img = base_url().$img;
	}
	$ret .= '<meta property="og:image" content="'.$img.'">';
	$ret .= '<meta property="og:locale" content="vi_vn">';
	$wmt = $CI->Dindex->getSettings('WMT');
	if(!isNull($wmt)){
		$ret .='<meta name="google-site-verification" content="'.$CI->Dindex->getSettings('WMT').'" />';
	}
	$fbappid = $CI->Dindex->getSettings('FBAPPID');
	if('FBAPPID'!=$fbappid){
		$ret .= '<meta property="fb:app_id" content="'.$fbappid.'">';
	}
	
	$ret .= '<link rel="shortcut icon" href="'.$CI->Dindex->getSettings('FAVICON').'">';
	return $ret;
}


function getImageFromContent($html,$def){
    preg_match_all('/<img [^>]*src=["|\']([^"|\']+)/i', $html, $matches);
    $val = $def;
	foreach ($matches[1] as $key=>$value) {
	    $val = $value;
	    break;
	}
	$pos = strpos($val , 'http');
	if($pos === FALSE) $val = base_url().$val;
	return $val;
}
function concatenateFiles($files)
{
    $buffer = '';

    foreach($files as $file) {
        $buffer .= file_get_contents($file);
    }

    return $buffer;
}

function loadCss($files){
	if(ENVIRONMENT=='production'){
		if(!file_exists('theme/frontend/styles.min.css')){
			$CI= &get_instance();
			$CI->load->library('minify'); 
	    	$CI->minify->css($files);
	    	$CI->minify->deploy_css(TRUE);
		}
		echo "<style>";
		echo concatenateFiles(array('theme/frontend/styles.min.css'));
		echo "</style>";
	}
	else{
		foreach ($files as $key => $value) {
			echo '<link rel="stylesheet" type="text/css" href="theme/frontend/'.$value.'">';
		}
		
	}
}
function loadJs($files){
	if(ENVIRONMENT=='production'){
		if(!file_exists('theme/frontend/scripts.min.js')){
			$CI= &get_instance();
			$CI->load->library('minify'); 
			$CI->minify->js($files); 
			$CI->minify->deploy_js(); 
		}
		echo '<script type="text/javascript" defer src="theme/frontend/scripts.min.js"></script>';
	}
	else{
		foreach ($files as $key => $value) {
			echo '<script type="text/javascript" defer src="theme/frontend/"'.$value.'></script>';
		}
	}
}




 ?>