<?php

/**
 * Template Lite
 *
 * Type:	 compile
 * Name:	 region_start
 */
 
function compile_region_start($arguments, &$object)
{
	//print(get_class($object) . '(');
	//print($arguments . ')' . "\n");
	
	$_args = $object->_parse_arguments($arguments);
	
	array_push($object->_region_stack, $_args['name']);
	
	$_result .= "<?php ob_start(); ?>\n";
	
	return $_result;	
}

?>
