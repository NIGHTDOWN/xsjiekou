<?php

header("Content-type: text/html; charset=utf-8"); 
define('ROOT',dirname(__FILE__).'/');
#相对URL路径

define('PATH_URL','/');

require(ROOT.'source/core/tool/File.php');
require(ROOT.'source/core/tool/Exesql.php');
require(ROOT.'source/core/db/Dbsql.php');

use ng169\tool\File as YFile;
use ng169\tool\Exesql;
function loadsysconf()
{
  $F_compile = ROOT.'source/compile/define.inc.php';
  $F_config  = ROOT.'conf/conf.inc.php';
  
  if (!file_exists($F_compile)) {

    $config = include($F_config);
   
    foreach ($config as $k=>$v) {
      $index = strtoupper($k);
      $S_text .= "define('{$index}','{$v}');\n";
    }
    $S_content = "<?php \n".$S_text."\n?>";
    $O_handle  = fopen($F_compile,'w');
    fwrite($O_handle,$S_content);
    fclose($O_handle);
    header('location:'.PATH_URL);
  }else{
  require_once $F_compile;
  }
}
loadsysconf();
$lockfile="lock.php";
$dbfile="conf/db.inc.php";
$sqlfile="ng169.sql";
$conffile='source/compile/define.inc.php';

if(is_file(ROOT.$lockfile)){
	die('程序已经安装');
}

if(@$_GET['act']=='install'){
	
	$user=$_POST['dbuser'] or die('数据库用户名不能留空');;
	$dbname=$_POST['dbname']or die('数据库名不能留空');;
	$dbpwd=$_POST['dbpwd']or die('数据库密码不能留空');;
	$dbpre=$_POST['dbpre'];
	$dbhost=$_POST['ip'];
	$dbcharset='utf8';
	$in="<?php
	
	\r return array(
	'main'=>
	array('dbhost'=>'$dbhost',
	'dbname'=>'$dbname',
	'dbuser'=>'$user',
	'dbpwd'=>'$dbpwd',
	'dbpre'=>'$dbpre',
	'charset'=>'$dbcharset'
	)

	);

	?>
	";

//	YFile::delFile($dbfile);
//	YFile::delFile($conffile);

	
	$boo=YFile::writeFile($dbfile,$in);
	
	ini_set("memory_limit", "128M");
	
//	require(ROOT.'conf/db.inc.php');
	Exesql::load(ROOT.$sqlfile);
	YFile::createFile($lockfile);
	unlink(ROOT.$sqlfile);
	echo('安装完成');
	die();
}else{
	
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>程序安装</title>
	<style>
	body{
	margin:0px;
	padding:0px;
	width:100%;
	height:100%;
	
	}
	table {   
	padding: 0;
	margin: 0;   
	border-collapse:collapse;
	}

	td,th {
	border: 1px solid #C1DAD7;   
	background: #fff;
	font-size:11px;
	padding: 6px 6px 6px 12px;
	color: #4f6b72;
	}
	td input{
	border: 1px solid #C1DAD7;   
	background: #fff;
	font-size:11px;
	padding: 6px 6px 6px 12px;
	color: #4f6b72;  
	}
	td input.sub {
	background: #F5FAFA;
	color: #797268;
	cursor:pointer;
	border-radius: 10px;
	}
	td input.sub:hover {
	background: #F7FFFF;
	color: #797268;
	
	}
	.row{width:130px;}
	tr{width:260px;}
	input{width:130px;}
	div{width:300px;margin:0 auto;top: 100px;
	position: relative;}
	</style>
	</head>

	<body>
	<div>
	<form action="/install.php?act=install" method="post">
	<table width="300">
	<tr>
	<th class="row">&nbsp;数据库IP:</th>
	<td>&nbsp;<input name="ip" type="text" value="127.0.0.1" /></td>
	</tr>
	<tr>
	<th class="row">&nbsp;数据库名称:</th>
	<td>&nbsp;<input name="dbname" type="text" value="" /></td>
	</tr>
	<tr>
	<th class="row">&nbsp;数据库用户名:</th>
	<td>&nbsp;<input name="dbuser" type="text" value="" /></td>
	</tr>
	<tr>
	<th class="row">&nbsp;数据库密码:</th>
	<td>&nbsp;<input name="dbpwd" type="password" value="" /></td>
	</tr>
	<tr style="display:">
	<th class="row">&nbsp;数据表前缀:</th>
	<td>&nbsp;<input name="dbpre" type="text" value="" /></td>
	</tr>
	<tr style="display:none">
	<th class="row">&nbsp;编码类型:</th>
	<td>&nbsp;<input name="dbcharset" type="text" value="utf8" /></td>
	</tr>
	<tr>
	<td colspan="2" align="center"><input class="sub" type="submit" /></td>
   
	</tr>
	</table>

	</form></div>
	</body>
	</html>

	';
}
?>
