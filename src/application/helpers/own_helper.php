<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Roni
 * Date: 3/20/12
 * Time: 4:20 PM
 */
function __t($string,  $domain = false){
    echo _t($string, $domain );
}

function _t($string, $domain = false){
    $CI = & get_instance();
    return $CI->lang->line($string, $domain);
}

function no_empty($el){
    return (!empty($el));
}
function arr_copy_by_key($ref=array(),$key=array()){
    $ret_arr=array();
    foreach($key as $key_name){
     $ret_arr[$key_name]=is_string($ref[$key_name])?htmlspecialchars_decode($ref[$key_name]):$ref[$key_name];
    }
    return $ret_arr;
}

function download_url($oname,$dname=""){
    $data=json_encode(array('path'=>$oname,'name'=>$dname));
    return site_url('/download/attachment/'.rawurlencode(base64_encode($data)));
}

function l_date($format,$date=false){
    $timestamp=$date?$date:time();
    return strftime(dateFormatToStrftime($format),$timestamp);
}

 function dateFormatToStrftime($dateFormat) {
    $caracs = array(
        // Day - no strf eq : S
        'd' => '%d', 'D' => '%a', 'j' => '%e', 'l' => '%A', 'N' => '%u', 'w' => '%w', 'z' => '%j',
        // Week - no date eq : %U, %W
        'W' => '%V',
        // Month - no strf eq : n, t
        'F' => '%B', 'm' => '%m', 'M' => '%b',
        // Year - no strf eq : L; no date eq : %C, %g
        'o' => '%G', 'Y' => '%Y', 'y' => '%y',
        // Time - no strf eq : B, G, u; no date eq : %r, %R, %T, %X
        'a' => '%P', 'A' => '%p', 'g' => '%l', 'h' => '%I', 'H' => '%H', 'i' => '%M', 's' => '%S',
        // Timezone - no strf eq : e, I, P, Z
        'O' => '%z', 'T' => '%Z',
        // Full Date / Time - no strf eq : c, r; no date eq : %c, %D, %F, %x
        'U' => '%s'
    );

    return strtr((string)$dateFormat, $caracs);
}

function populate_multilevel_menu($menus=false,$current_url="",$attr="",$menu_level=0){
    if($menus==false || $menus==""){
        return array('active'=>false,'menus'=>'');
    }
    $str= "<ul $attr>";
    $hasActiveMenu=false;
    foreach($menus as $menu){
        $sub_menu_arr=populate_multilevel_menu($menu['sub'],$current_url,"",($menu_level+1));
        $sub_menu=$sub_menu_arr['menus'];

        if($current_url == $menu['link'] || $sub_menu_arr['active']){
            $active = 'current';
            $hasActiveMenu=true;
        }else{
            $active="";
        }
        if($menu['sub'] && $menu_level==1){ //Have submenu
            $wrap_s=sprintf('<div class="menu-arrow"><img src="%1$s" width="16" height="16"></div><div class="menu">',
                    base_url('assets/images/menu-open-arrow.png'));
            $wrap_e='</div>';
            $sub_menu="$wrap_s $sub_menu $wrap_e";
            $active.=" menu-opener";
        }
            $str.=hasDisabledClass($menu["class"])?
                    sprintf('<li class="%1$s">%2$s'
                        ,"$active $menu[class]"
                        ,$menu['label']
                    ):
                    sprintf('<li class="%1$s"><a title="%2$s" href="%3$s" %4$s>%2$s</a>'
                    ,"$active $menu[class]"
                    ,$menu['label']
                    ,($menu['link_type']!=0?site_url($menu['link']):$menu['link'])
                    ,($menu['link_type']==2?"target='_blank":"")
            );
        //$str.='<li class="'."$active $menu[class]".'"><a title="'.$menu['label'].'" href="'.site_url($menu['link']).'">'.$menu['label'].'</a>';
        $str.= $sub_menu.'</li>';
    }
    $str.= "</ul>";
    return array('active'=>$hasActiveMenu,'menus'=>$str);
}

function hasDisabledClass($class=""){
    if($class=="")
        return false;
    //we have class
    $classes=explode(" ",$class);
    return (in_array("disabled",$classes));
}

function limit_character($str, $max =27, $end_char = '&#8230;')
{
    $encoding=mb_detect_encoding($str);
    $strlen='mb_strlen';
    $substr='mb_strlen';
    if(strtoupper($encoding)=='ASCII'){
        $strlen='strlen';
        $substr='substr';
    }
    if ($strlen($str) < $max)
    {
        return $str;
    }

    $str = preg_replace("/\s+/", ' ', str_replace(array("\r\n", "\r", "\n"), ' ', $str));

    if ($strlen($str) <= $max)
    {
        return $str;
    }

    $out = "";
    foreach (explode(' ', trim($str)) as $val)
    {
        if ($strlen($out.$val) > $max)  //We are going to exceeded limit!
        {
            $out = trim($out);
            //what if a crazy user put a long word to test you out!! do not disapoint him
            if($out=="" && $str!=""){
                return $substr($str,0,$max).$end_char;
            }
            return ($strlen($out) == $strlen($str)) ? $out : $out.$end_char;
        }

        $out .= $val.' ';
    }
    return ($strlen($out) == $strlen($str)) ? $out : $out.$end_char;
}

function data_table_config_state($id="",$prevConfig=false){
    if($prevConfig==false || $id==""){
        return false;
    }

    $str="<div id='{$id}-config' style='display: none;'>";
    $oldconfig = json_decode(base64_decode($prevConfig));
    if(!is_object($oldconfig)){
        return false;
    }
    if (isset($oldconfig->iSortCol_0)) {
        $str.=sprintf('"aaSorting":[[%1$s,"%2$s"]],',$oldconfig->iSortCol_0,$oldconfig->sSortDir_0);
    } else {
        $str.='"aaSorting": [[ 0, "asc" ]],';
    }
    foreach ($oldconfig as $key => $val) {
        if (is_numeric($val)) {
            $str.="\"$key\":$val,\n";
        } else {
            $str.="\"$key\":\"$val\",\n";
        }
    }
	$str.='</div>';
    echo $str;
}

function is_base64_encoded($data)
{
    if (preg_match('%^([A-Za-z0-9+/]{4})*([A-Za-z0-9+/]{4}|[A-Za-z0-9+/]{3}=|[A-Za-z0-9+/]{2}==)$%', $data)) {
        return TRUE;
    } else {
        return FALSE;
    }
};
function nonce_tick(){
    return ceil(time() / ( 12 * 60 * 60 ));
}

function auth_key($str){
   $CI = & get_instance();
   $i = nonce_tick();
   return substr(md5($i . $str . $CI->session->userdata('user_id') . $CI->config->item('encryption_key')), -12, 10);
}

function valid_auth_key($str,$key){
    return ($key==auth_key($str));
}

function date_value($date){
    return $date!="0000-00-00"?$date:"";
}

function getBengali($english){
    $engArray  = array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 0);
    $bangArray = array('১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '০');
   return str_replace($engArray, $bangArray, $english);
}

function getEnglish($bengali){
    $engArray  = array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 0);
    $bangArray = array('১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '০');
    return str_replace($bangArray, $engArray, $bengali);
}