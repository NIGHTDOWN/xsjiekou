<?php
/*
 * template_lite plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     get_region
 * Purpose:  print out a counter value
 * Credit:   Taken from the original Smarty
 *           http://smarty.php.net
 * -------------------------------------------------------------
 */
function tpl_function_get_region($params, &$tpl)
{
    $result = '';
    
    if (!isset($tpl->_vars['regions'])) {
        return $result;
    }
    
    if (array_key_exists($params['name'], $tpl->_vars['regions'])) {
        $result = $tpl->_vars['regions'][$params['name']];
    }

    if (isset($params['assign'])) {
        $varname = str_replace('$', '', $params['assign']);
        $tpl->_vars[$varname] = $result;
        return;
    }
    
    return $result;
}
?>
