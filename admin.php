<?php


$path=dirname(__FILE__);

require($path.'/source/core/tool/Url.php');

header("Location: ".\ng169\tool\Url::url(null,null,'login','admin'));

?>
