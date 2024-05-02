<?php


namespace ng169\lib;

checktop();
class Loader
{
  private static $namespace = null;
  /**
  * 命名空间初始化
  *
  * @return void
  */
  private static function _init()
  {
    $dir       = ROOT.FG.'source';
    self::$namespace = [
      'ng169'=>$dir,
      'lib'=>$dir.FG.'core/lib',
      'core'=>$dir.FG.'core',
      'cache'=>$dir.FG.'core/cache',
      'tool'=>$dir.FG.'core/tool',
      'db'=>$dir.FG.'core/db',
      'service'=>$dir.FG.'core/service',
      'hook'=>$dir.FG.'core/hook',
      'model'=>$dir.FG.'model',
      'control'=>$dir.FG.'control',
      'sock'=>$dir.FG.'sock',
      'sockmodel'=>$dir.FG.'sockmodel',

    ];
   
  }
  /**
  * 注册自动加载机制
  * @access public
  * @param  callable $autoload 自动加载处理方法
  * @return void
  */
  public static function register($autoload = null)
  {

    // 注册系统自动加载
    spl_autoload_register($autoload ?: 'ng169\\lib\\Loader::autoload', true, true);
    //加载空间位置
    if (!self::$namespace)self::_init();

  }
  /**
  * 自动加载
  * @access public
  * @param  string $class 类名
  * @return bool
  */

  public static function autoload($class)
  {
   
  //获得空间类名
  //拆分命名空间
  //倒序加载
  //加载指定文件
    if ($file = self::findFile($class)) {
    	
      // 非 Win 环境不严格区分大小写
     
      if (!IS_WIN || pathinfo($file, PATHINFO_FILENAME) == pathinfo(realpath($file), PATHINFO_FILENAME)) {
      
        im($file);
        return true;
      }
    }

    return false;
  }
  /**
  * 查找文件
  * @access private
  * @param  string $class 类名
  * @return bool|string
  */
  private static function findFile($class)
  {
   
    // 检测命名空间别名
    if (!self::$namespace)self::_init();


    $logicalPathPsr4 = strtr($class, '\\', FG) . G_EXT;

    $files           = array_reverse(explode(FG,$logicalPathPsr4));
    $filename        = $files[0];
    $realdir         = null;
   
    unset($files[0]);
   
    foreach ($files as $key=>$dir) {
    	//这里兼容cli类
    //  d($class);
      if ($files[count($files)-1]=="cli" && count($files)>2) {
        // d("匹配cli");
        $df="";
        unset($files[count($files)]);
        // unset($files[count($files)]);
        foreach ($files as $key => $dr) {
          # code...
          $df=$dr.FG.$df;
        }
        $realdir = strtolower(self::$namespace[$dir].FG.$df.$filename);
       
        goto end;
      }
      if (isset(self::$namespace[$dir])) {
        $realdir = self::$namespace[$dir].FG.$realdir.$filename;
        goto end;
      }
      else {
        $realdir = $dir.FG.$realdir;
      }
    }
    end :
    //空间映射路劲
    return $realdir;
  }
}
?>
