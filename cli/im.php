<?php

/**
 */

namespace ng169\tool;

require_once    "clibase.php";
use ng169\lib\ngSwoole;
im(TOOL."ngSwoole.php");
$sw=new ngSwoole();
$sw->start("1199");



