<?php

/**
 */

namespace ng169\tool;

require_once    "clibase.php";

im(TOOL."ngSwoole.php");
$sw=new \ng169\lib\ngSwoole();
$sw->start("1199");



