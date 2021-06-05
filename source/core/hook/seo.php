<?php


namespace  ng169\hook;
use ng169\Y;
use ng169\TPL;

checktop();

function seo()
{

   $c=D_MEDTHOD;	$a=D_FUNC;
    $fixa = $a;
    if ($a == 'index' || $a == '') {
        $fixa = 'run';
    }
    $id = $c . "_" . $fixa;

    $info = T('seo')->set_field(array('metatitle','metadesc','metakeyword'))->get_one();
    if (is_array($info)) {

        $info['page_title'] = get_p($info['metatitle']);
        $info['page_description'] = get_p($info['metadesc']);
        $info['page_keyword'] = get_p($info['metakeyword']);


        

    }
    
    $var_array = $info;
    TPL::assign($var_array);
}
function get_p($msg)
{
    if ($msg != null) {


        $msg = preg_replace_callback('/【[^【】]*】/', 'get_tpl_val', $msg);

    }
    return $msg;
}
function get_tpl_val($msg)
{

    if (is_array($msg) && sizeof($msg) == 1) {
        $msg = $msg[0];
        $msg = trim($msg, '【');
        $msg = trim($msg, '】');
        $msg = TPL::getValue($msg);
    }

    return $msg;

}

















?>
