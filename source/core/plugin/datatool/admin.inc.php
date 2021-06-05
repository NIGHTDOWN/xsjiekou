<?php




if(!defined('IN_OEPHP')) {
	exit('Access Denied');
}

if(!function_exists('file_put_contents')){
	function file_put_contents($file, $string, $append = ''){
		$mode = $append == '' ? 'wb' : 'ab';
		$fp = @fopen($file, $mode) or exit("Can not open file $file !");
		flock($fp, LOCK_EX);
		$stringlen = @fwrite($fp, $string);
		flock($fp, LOCK_UN);
		@fclose($fp);
		return $stringlen;
	}
}
$startrow = 0;


function datatool_plugin_setting() {
	
	$data = array();
	
	$dbsize = 0;
	
	$dbnums = 0;
	$i = 1;
	$rs = X::$obj->query("SHOW TABLE STATUS LIKE '".DB_PREFIX."%'");
	while ($dbList = X::$obj->fetch_assoc($rs)) {
		$dbres = X::$obj->get_row('CHECK TABLE ' .$dbList['Name']);
		$dbsize += $dbList['Data_length'];
		$data[] = array(
			'i' => $i,
			'table' => $dbList['Name'],
			'type' => $dbList['Engine'],
			'dbnum' => $dbList['Rows'],
			'dbsize' => YHandle::formatSize($dbList['Data_length']),
			'dbchip' => YHandle::formatSize($dbList['Data_free']),
			'status' => $dbres['Msg_text'],
			'charset' => $dbList['Collation']
		);
		$i = $i+1;
		$dbnums++;
	}
	$dbsize = YHandle::formatSize($dbsize);
	$maxsize = @ini_get('upload_max_filesize')*1024;
	unset($rs);
	require_once(ROOT.'./source/plugin/datatool/tpl/export.tpl.php');
}

XHook::addAction('datatool_plugin_setting_event', 'datatool_plugin_setting');


function datatool_plugin_import() {
    $bakpath = ROOT.'./source/plugin/datatool/data/';
    if (false == YValid::isDir($bakpath)) {
        error('对不起，读取SQL备份文件名错误！请检查 source/plugin/datatool/data/ 目录是否存储', '', 1);
    }
    $sqlfiles = glob($bakpath.'*.sql');
    if (is_array($sqlfiles)) {
        $prepre = '';
        $data = $info = array();
        
        
        foreach ($sqlfiles as $id=>$sqlfile) {
            
            preg_match("/([a-z0-9_]+_[0-9]{8}_[0-9a-z]{4}_)([0-9]+)\.sql/i",basename($sqlfile), $num);
            
            
            $info['filename'] = basename($sqlfile);
            $info['filesize'] = YHandle::formatSize(filesize($sqlfile));
            $info['maketime'] = date('Y-m-d H:i:s', filemtime($sqlfile));
            $info['pre'] = $num[1];
            $info['number'] = $num[2];
            if(!$id) $prebgcolor = '#ecf2f7';
            if($info['pre'] == $prepre){
				 $info['bgcolor'] = $prebgcolor;
			 }else{
			     $info['bgcolor'] = $prebgcolor == '#ecf2f7' ? '#ffffff' : '#ecf2f7';
			 }
             $prebgcolor = $info['bgcolor'];
             $prepre = $info['pre'];
             $data[] = $info;
        }
    }
    require_once(ROOT.'./source/plugin/datatool/tpl/import.tpl.php');  
}

XHook::addAction('datatool_plugin_import_event', 'datatool_plugin_import');


function datatool_plugin_export() {
    global $startrow;
    
    
    $fileid = YRequest::getArgs('fileid');
    if (false === YValid::isNumber($fileid)){
        $fileid = 1;
    }
    
    $tables = YRequest::getArray('tables');
    
    
	if($fileid==1 && $tables) {
		if(!isset($tables) || !is_array($tables)) {
		  error('请选择要备份的数据表！', '', 1);
		}
	    $random = mt_rand(1000, 9999);
        
	    datatool_cache_write('bakup_tables.php', $tables);
	}
    
    
	else{
        $random = YRequest::getArgs('random');
	    if(!$tables = datatool_cache_read('bakup_tables.php')){
	       error('请选择要备份的数据表！', '', 1);
	    }
	}
    
    
    $tableid = YRequest::getInt('tableid');
    if ($tableid>0) {
        $tableid = $tableid-1;
    }
    $startfrom = YRequest::getInt('startfrom');
    $sizelimit = YRequest::getInt('sizelimit');
	$sqldump = '';
	$tablenumber = count($tables);
    
	for($i = $tableid; $i < $tablenumber && strlen($sqldump) < $sizelimit * 1000; $i++){
		$sqldump .= datatool_sql_dumptable($tables[$i], $sizelimit, $startfrom, strlen($sqldump));
		$startfrom = 0;
	}
    
	if(trim($sqldump)){
		$sqldump = str_ireplace(DB_PREFIX, '{dbpre}', $sqldump);
		$tableid = $i;
		$filename = 'oecms_'.date('Ymd').'_'.$random.'_'.$fileid.'.sql';
		$fileid++;
		$bakfile = './source/plugin/datatool/data/'.$filename;
        
        
        Y::loadTool('file');
        $result = XFile::writeFile($bakfile, $sqldump);
        if (false === $result) {
            error('对不起，数据无法备份到服务器！请检查 source/plugin/datatool/data 目录是否有可写权限。', '', 1);
        }
        else {
            echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".OECMS_CHARSET."\" />";
            $jumpurl = __ADMIN_FILE__."?c=plugin&plugin_id=datatool&a=save&do=export".
                    "&sizelimit=".$sizelimit."&tableid=".$tableid."&fileid=".$fileid."".
                    "&startfrom=".$startrow."&random=".$random."";            
            echo "<meta http-equiv='refresh' content='1; url=$jumpurl'>";
            echo "<div align='center' style='font-size:13px;line-height:25px;'><br /><br/ >备份文件：".$filename."备份成功，请耐心等待完全备份完毕。<br /><a href='$jumpurl'>如果您的浏览器没有自动跳转，请点击这里</a></div>";
        }
	}
	else{
	   
	   datatool_cache_delete('bakup_tables.php');
       error('数据库备份完毕', __ADMIN_FILE__.'?c=plugin&plugin_id=datatool&a=setting', 0);
	}
}



function datatool_sql_dumptable($table, $sizelimit, $startfrom = 0, $currsize = 0){
    global $startrow;
	if(!isset($tabledump)) $tabledump = '';
	$offset = 100;
	if(!$startfrom){
		$tabledump = "DROP TABLE IF EXISTS `$table`;\n";
		$createtable = X::$obj->query("SHOW CREATE TABLE $table");
		$create = X::$obj->fetch_row($createtable);
		$tabledump .= $create[1].";\n\n";
	}
    
	$tabledumped = 0;
	$numrows = $offset;
	while($currsize + strlen($tabledump) < $sizelimit * 1000 && $numrows == $offset){
		$tabledumped = 1;
		$rows = X::$obj->query("SELECT * FROM $table LIMIT $startfrom, $offset");
		$numfields = X::$obj->num_fields($rows);
		$numrows = X::$obj->num_rows($rows);
		while ($row = X::$obj->fetch_row($rows)){
			$comma = "";
			$tabledump .= "INSERT INTO `$table` VALUES(";
			for($i = 0; $i < $numfields; $i++){
				$tabledump .= $comma."'".mysql_escape_string($row[$i])."'";
				$comma = ",";
			}
			$tabledump .= ");\n";
		}
		$startfrom += $offset;
	}
	$startrow = $startfrom;
	$tabledump .= "\n";
	return $tabledump;
}



function datatool_plugin_restore() {
    
    $pre = YRequest::getArgs('pre');
    if (empty($pre)) {
        error('对不起，请选择要还原的SQL文件。', '', 1);
    }
    
    
    $filename = YRequest::getArgs('filename');
    
    
    if(!empty($filename) && datatool_file_ext($filename)=='sql'){
    	$filepath = ROOT.'./source/plugin/datatool/data/'.$filename;
    	if(!file_exists($filepath)) {
    	   error("对不起， [source/plugin/datatool/data/$filename] 文件不存在。", '', 1);    	   
    	}
    	$sql = file_get_contents($filepath);
    	datatool_sql_execute($sql);
        error("[$filename] 中的数据已成功导入到数据库！");
    }
    
    
    else{
        
        
        $fileid = YRequest::getArgs('fileid');
        if (false === YValid::isNumber($fileid)) {
            $fileid = 1;
        }
    	$filename = $pre.$fileid.'.sql';
    	$filepath = ROOT.'./source/plugin/datatool/data/'.$filename;
    	if(file_exists($filepath)){
    		$sql = file_get_contents($filepath);
    		datatool_sql_execute($sql);
    		$fileid++;
      
            echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".OECMS_CHARSET."\" />";
            $jumpurl = __ADMIN_FILE__."?c=plugin&plugin_id=datatool&a=save&do=restore".
                    "&pre=".$pre."&fileid=".$fileid."";          
            echo "<meta http-equiv='refresh' content='1; url=$jumpurl'>";
            echo "<div align='center' style='font-size:13px;line-height:25px;'><br /><br/ >数据文件：".$filename."导入成功，请耐心等待其他分卷导入。<br /><a href='$jumpurl'>如果您的浏览器没有自动跳转，请点击这里</a></div>";
        }
        else{
            error('数据库恢复成功！', __ADMIN_FILE__.'?c=plugin&plugin_id=datatool&a=setting', 0);
    	}
    }
}


function datatool_sql_execute($sql){
    $sqls = datatool_sql_split($sql);
    if(is_array($sqls)){
        foreach($sqls as $sql){
            if(trim($sql) != '') {
				$sql = str_ireplace('{dbpre}', DB_PREFIX, $sql);
                X::$obj->query($sql);
            }
        }
    }
	else{
		$sqls = str_ireplace('{dbpre}', DB_PREFIX, $sqls);
		X::$obj->query($sqls);
    }
    return true;
}


function datatool_sql_split($sql){
	if(X::$obj->version() > '4.1' && DB_CHARSET){
		$sql = preg_replace("/TYPE=(InnoDB|MyISAM)( DEFAULT CHARSET=[^; ]+)?/", "TYPE=\\1 DEFAULT CHARSET=".DB_CHARSET ,$sql);
	}
	$sql = str_replace("\r", "\n", $sql);
	$ret = array();
	$num = 0;
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach($queriesarray as $query){
		$ret[$num] = '';
		$queries = explode("\n", trim($query));
		$queries = array_filter($queries);
		foreach($queries as $query){
			$str1 = substr($query, 0, 1);
			if($str1 != '#' && $str1 != '-') $ret[$num] .= $query;
		}
		$num++;
	}
	return($ret);
}


function datatool_plugin_down() {
    $filename = YRequest::getArgs('filename');
    if (empty($filename)) {
        error('对不起，请选择要下载的备份文件。', '', 1);
    }
    datatool_file_down($filename);
}


function datatool_plugin_del() {
    $filename = YRequest::getArgs('filename');
    if (empty($filename)) {
        error('对不起，请选择要删除的备份文件。', '', 1);
    }
    $filepath = './source/plugin/datatool/data/'.$filename;
    if (false === file_exists(ROOT.$filepath)) {
        error('对不起，备份文件不存在。', '', 1);
    }
    $filetype = datatool_file_ext($filename);
    if ($filetype != 'sql') {
        error('对不起，备份文件格式不正确，不能执行删除！', '', 1);
    }
    Y::loadTool('file');
    if (true === XFile::delFile($filepath)) {
        error('删除成功', __ADMIN_FILE__.'?c=plugin&plugin_id=datatool&a=setting&do=import', 0);
    }else {
        error('删除失败', '', 1);
    }
}


function datatool_cache_read($file, $mode = 'i'){
	$cachefile = ROOT.'./source/plugin/datatool/data/'.$file;
	if(!file_exists($cachefile)) return array();
	return $mode == 'i' ? include $cachefile : file_get_contents($cachefile);
}

function datatool_cache_write($file, $string, $type = 'array'){
	if(is_array($string)){
		$type = strtolower($type);
		if($type == 'array'){
			$string = "<?php\n return ".var_export($string,TRUE).";\n?>";
		}
        elseif ($type == 'constant'){
			$data='';
			foreach($string as $key => $value) $data .= "define('".strtoupper($key)."','".addslashes($value)."');\n";
			$string = "<?php\n".$data."\n?>";
		}
	}
    file_put_contents(ROOT.'./source/plugin/datatool/data/'.$file, $string);
}

function datatool_cache_delete($file){
	return @unlink(ROOT.'./source/plugin/datatool/data/'.$file);
}


function datatool_file_ext($filename){
	return trim(substr(strrchr($filename, '.'), 1));
}


function datatool_file_down($file){
    $filepath = ROOT.'./source/plugin/datatool/data/'.$file;
    if (false === file_exists($filepath)) {
        error('对不起，备份文件不存在！', '', 1);
    }
    $filetype = datatool_file_ext($file);
    $filesize = filesize($filepath);
    if ($filetype != 'sql') {
        error('对不起，备份文件格式不正确，不能下载！', '', 1);
    }
	header('Cache-control: max-age=31536000');
	header('Expires: '.gmdate('D, d M Y H:i:s', time() + 31536000).' GMT');
	header('Content-Encoding: none');
	header('Content-Length: '.$filesize);
	header('Content-Disposition: attachment; filename='.$file);
	header('Content-Type: '.$filetype);
	readfile($filepath);
	exit;
}
?>
