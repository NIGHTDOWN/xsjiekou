<?php


namespace ng169\tool;
use ng169\lib\Option;
use ng169\tool\File;
checktop();

class Init
{
  private static $action_name = array('list'    =>'列表','add'     =>'添加','edit'    =>'编辑','save'    =>'保存','show'    =>'详情','run'     =>'列表','add_view'=>'添加','cgpwd'   =>'修改密码','del'     =>'删除');

	/**
	* 初始化admin模块权限配置
	* @param undefined $mod_dir
	* 
	* @return
	*/
  public static function mod($mod_dir,$filename)
  {

    $mod_dir = CONTROL.$mod_dir;
	if(file_exists($filename)){
		$is=include($filename);
	}
    if (is_dir($mod_dir)) {
      if ($dh = opendir($mod_dir)) {
        while (($file = readdir($dh)) !== false) {
          $file_absolute = $mod_dir.'/'.$file;
          if (file_exists($file_absolute)&&!is_dir($file_absolute) && pathinfo($file_absolute,PATHINFO_EXTENSION) == 'php') {
          $index = pathinfo($file_absolute,PATHINFO_FILENAME);
          	
          	if(isset($is[$index])){
			$mod[$index]=$is[$index];
			}else{
				 
            $mod[$index]['alias'] = '';
            $mod[$index]['action'] = self::action($file_absolute);
			}
           
          }
        }
       
        closedir($dh);
        $str="<?php return ".self::printr($mod)."?>";
        File::writeFile($filename,$str);
        return $mod;
      }
    }
  }
  
  public static function printr($var)
{
    ob_start();
    print_r($var);
    $output = ob_get_clean();
 
 
    //键值加引号
    preg_match_all('/\=\> (?!(Array)).*/', $output, $match);
 
    $pattern = $replacement = [];
    $n = 0;
    preg_match_all('/\[.*\]/', $output, $match);
 
    foreach ($match[0] as $val) {
        for ($i=0;isset($val[$i]);$i++) {
 
            switch ($val[$i]) {
                case '[':
                    $pattern[$n] = '[';
                    $replacement[$n] = '\'';
                    break;
                case ']':
                    $pattern[$n] .= ']';
                    $replacement[$n] .= '\'';
                    break;
                default:
                    $pattern[$n] .= $val[$i];
                    $replacement[$n] .= $val[$i];
                    break;
            }
        }
        $pattern[$n] = '/'.preg_quote($pattern[$n]).'/';
 
        $n++;
    }
 
    $output = preg_replace($pattern, $replacement, $output);
    //键值加引号
 
 
    //值加逗号，注意值不能是A,Ar,Arr,Arra,Array
    $array = explode("\n",$output);
    $output = '';
    foreach ($array as $val) {
        if (strpos($val,'=>')){
            if (strpos($val,'Array')) {
                $output .= $val."\n";
            } else {
                $output .= str_replace(['=> '],['=> \''],$val)."',\n";
            }
        } elseif ($val) {
            $output .= $val."\n";
        }
    }
    //值加逗号
 
 
    //括号加逗号
    $pattern = $replacement = [];
    $n = 0;
    preg_match_all('/\)\s+\'/', $output, $match);
 
    foreach ($match[0] as $val) {
        for ($i=0;isset($val[$i]);$i++) {
 
            switch ($val[$i]) {
                case ')':
                    $pattern[$n] = ')';
                    $replacement[$n] = "),";
                    break;
                default:
                    $pattern[$n] .= $val[$i];
                    $replacement[$n] .= $val[$i];
                    break;
            }
        }
        $pattern[$n] = '/'.preg_quote($pattern[$n]).'/';
 
        $n++;
    }
 
    $output = preg_replace($pattern, $replacement, $output);
   
	return $output;
	}
  
  private static function action($file)
  {
    $prefix = G_ACTION_PRE;
    if (file_exists($file)&&!is_dir($file) && pathinfo($file,PATHINFO_EXTENSION) == 'php') {
      $buffer = file_get_contents($file);
      $ptter  = "/public+\s+function+\s+".$prefix."(\w+)\(\)/";
      preg_match_all($ptter, $buffer,$out,PREG_PATTERN_ORDER);
      if (is_array($out[1])) {
        $action = array();
        foreach ($out[1] as $a) {
          $action[$a]['alias'] = isset(self::$action_name[$a])?self::$action_name[$a]:'';
          $action[$a]['seo'] = 1;
        }
      }
      return $action;
    }
  }
  /**
  * 获取模块控制器下面的别名
  * @param string $mod
  *
  * @return array 
  */
  public static function get($mod)
  {
    if (!$mod)return false;
    $conf = Option::get('admin');
    return $conf;
  }
  public static function load($mod_dir)
  {
//    $path = $path ? $path : 'conf/';
	$path=CONF;
    $file = $path.'/'.$mod_dir.".inc.php";
    if (is_file($file)) {
      return include($file);
    }
    return false;
  }
}

?>
