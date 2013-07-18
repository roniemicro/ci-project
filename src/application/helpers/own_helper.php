<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Roni
 * Date: 3/20/12
 * Time: 4:20 PM
 */
function __t($string, $domain = FALSE)
{
    echo _t($string, $domain);
}

function _t($string, $domain = FALSE)
{
    $CI = & get_instance();
    return $CI->lang->line($string, $domain);
}

function no_empty($el)
{
    return (!empty($el));
}

function arr_copy_by_key($ref = array(), $key = array())
{
    $ret_arr = array();
    foreach ($key as $key_name) {
        $ret_arr[$key_name] = is_string($ref[$key_name]) ? htmlspecialchars_decode($ref[$key_name]) : $ref[$key_name];
    }
    return $ret_arr;
}

function download_url($oname, $dname = "")
{
    $data = json_encode(array('path' => $oname, 'name' => $dname));
    return site_url('/download/attachment/' . rawurlencode(base64_encode($data)));
}

function l_date($format, $date = FALSE)
{
    $timestamp = $date ? $date : time();
    return strftime(dateFormatToStrftime($format), $timestamp);
}

function dateFormatToStrftime($dateFormat)
{
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

function limit_character($str, $max = 27, $end_char = '&#8230;')
{
    $encoding = mb_detect_encoding($str);
    $strlen   = 'mb_strlen';
    $substr   = 'mb_strlen';
    if (strtoupper($encoding) == 'ASCII') {
        $strlen = 'strlen';
        $substr = 'substr';
    }
    if ($strlen($str) < $max) {
        return $str;
    }

    $str = preg_replace("/\s+/", ' ', str_replace(array("\r\n", "\r", "\n"), ' ', $str));

    if ($strlen($str) <= $max) {
        return $str;
    }

    $out = "";
    foreach (explode(' ', trim($str)) as $val) {
        if ($strlen($out . $val) > $max) //We are going to exceeded limit!
        {
            $out = trim($out);
            //what if a crazy user put a long word to test you out!! do not disapoint him
            if ($out == "" && $str != "") {
                return $substr($str, 0, $max) . $end_char;
            }
            return ($strlen($out) == $strlen($str)) ? $out : $out . $end_char;
        }

        $out .= $val . ' ';
    }
    return ($strlen($out) == $strlen($str)) ? $out : $out . $end_char;
}


function nonce_tick()
{
    return ceil(time() / (12 * 60 * 60));
}

function nonce($str)
{
    $CI = & get_instance();
    $i  = nonce_tick();
    return substr(md5($i . $str . $CI->session->userdata('user_id') . $CI->config->item('encryption_key')), -12, 10);
}

function valid_nonce($str, $key)
{
    return ($key == nonce($str));
}

function date_value($date)
{
    return $date != "0000-00-00" ? $date : "";
}
