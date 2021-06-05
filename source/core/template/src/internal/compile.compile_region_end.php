<?php

/**
 * Template Lite
 *
 * Type:	 compile
 * Name:	 region_start
 */
 
function compile_region_end($arguments, &$object)
{
	//print(get_class($object) . '(');
	//print($arguments . ')' . "\n");
	
	$_args = $object->_parse_arguments($arguments);
	
	$name = array_pop($object->_region_stack);
	
	$_result = "<?php \$this->_vars['regions'][{$name}] = ob_get_clean(); ?>\n";
	
	return $_result;
}

?>
