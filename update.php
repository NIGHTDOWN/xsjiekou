<?php

header("Content-type: text/html; charset=utf-8"); 
define('ROOT',dirname(__FILE__).'/');
#相对URL路径

define('PATH_URL','/');
$lockfile="lock.php";
$dbfile="/conf/global/db.inc.php";
$sqlfile="update.sql";
$conffile='source/compile/define.inc.php';
require(ROOT.'source/core/tool/static.file.php');

  function cache_del($val=null)
    {
        $dirName = ROOT . './data/cache';
    
        if (file_exists($dirName) && $handle = opendir($dirName)) {
            while (false !== ($item = readdir($handle))) {
               
                $f=$dirName . '/' . $item;
              
                if ($val != null) {
                    if (strpos($item, $val)) {
                        if (file_exists($f) && is_dir($f)) {
                            
                        } else {
                            if (unlink($f)) {
                            /*    return true;*/
                            }
                        }
                    }
                } else {
                  
                    if (true) {
                        if (file_exists($f) && is_dir($f)) {
                            
                        } else {
                         
                            if (unlink($f)) {
                               /* return true;*/
                            }
                        }
                    }
                }
            }
            
            closedir($handle);
        }

    }

if(isset($_GET['act'])){

ini_set("memory_limit", "128M");
require(ROOT.'source/core/tool/static.exesql.php');
require(ROOT.'source/core/db/class.mysql.php');
require(ROOT.'conf/global/db.inc.php');
	Yexesql::load(ROOT.$sqlfile);
	YFile::createFile($lockfile);
	unlink(ROOT.$sqlfile);
    cache_del();
echo('更新完成');
die();
}else{
	if(!(version_compare ( PHP_VERSION ,  '5.2.6' ) >= 0 && version_compare ( PHP_VERSION ,  '5.3.0' ) <= 0)){echo "";}
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>程序升级</title>
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
<form action="/update.php?act=update" method="post">
<table width="300">
  
  <tr>
    <td colspan="2" align="center"><input class="sub" type="submit" value="确认升级"/></td>
   
  </tr>
</table>

</form></div>
</body>
</html>

';
}
?>
