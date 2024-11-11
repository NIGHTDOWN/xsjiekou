<?php

/**
 */

namespace ng169\tool;

require_once    "clibase.php";
d(TOOL."ngSwoole.php");
im(TOOL."ngSwoole.php");
$sw=new \ng169\lib\ngSwoole();
$sw->start("1199");



