<?php

use ng169\tool\File;

if (!defined('PATH_URL')) define('PATH_URL', '/');
define('FG', '/');
// @set_time_limit(0);
global $Stime;
$Stime = microtime(true);
function getip()
{
    return $_SERVER['SERVER_ADDR'] ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
}
/**
 * 加载文件
 * @param file $F_file
 *
 * @return boolean
 */
function im($F_file)
{
    
    if (file_exists($F_file)) {

        return require_once $F_file;
    } else {
        return (FALSE);
    }
}
/**
 * 加载目录下的所有文件
 * @param undefined $F_path
 * @param undefined $A_filename
 *
 * @return
 */
function loaddir($F_path, $A_filename = null)
{
    if (is_array($A_filename)) {
        foreach ($A_filename as $F) {
            $realfile = $F_path . $F . '.php';
            im($realfile);
        }
        return true;
    }
    if (!is_dir($F_path)) {
        d($F_path . '不是有效的目录');
    }
    $dir = new DirectoryIterator(($F_path));
    foreach ($dir as $F) {
        if ($F->isFile()) {
            im($F->getPathname());
        }
    }
    return true;
}
/**
 * 初始化系统常量
 *
 * @return void
 */
function loadsysconf()
{
    $F_compile = ROOT . 'source/compile/define.inc.php';
    $F_config  = ROOT . 'conf/conf.inc.php';
    $F_tool  = ROOT . 'source/core/tool/File.php';

    if (!im($F_compile)) {

        $config = include($F_config);

        foreach ($config as $k => $v) {
            $index = strtoupper($k);
            $S_text .= "define('{$index}','{$v}');\n";
        }

        $S_content = "<?php \n" . $S_text . "\n?>";
        // $O_handle  = fopen($F_compile, 'w');

        // $O_handle  = fopen($F_compile, 'w');

        im($F_tool);
        // fwrite($O_handle, $S_content);
        // fclose($O_handle);
        ng169\tool\File::writeFile($F_compile, $S_content);
        header('location:' . PATH_URL);
    }
}
/**
 * 非法访问检测
 *
 * @return
 */
function checktop()
{

    if (!defined('INSTALL')) {
        exit('[' . INSTALL . '] Access Denied');
    }
}
/**
 * 输出耗时
 *
 * @return
 */
function debugtime()
{
    global $Stime;
    $msg      = debug_backtrace();
    $backinfo = "(文件:{$msg[0]['file']}; 代码行号:{$msg[0]['line']}; 调用函数名称:{$msg[0]['function']})";
    $Etime    = microtime(true);
    $Ttime     = $Etime - $Stime;
    $str_total = var_export($Ttime, true);
    if (substr_count($str_total, "E")) {
        $float_total = floatval(substr($str_total, 5));
        $Ttime       = ($float_total / 100000);
    }
    $toms = round($Ttime * 1000, 2);
    if (!is_cli()) {
        d('请求耗时：<b style="color:red">' . ($toms) . '</b>毫秒   ' . '消息' . $backinfo);
    } else {
        p('请求耗时：' . $toms);
    }

    return $toms;
}
/**
 * 输出即时调试信息
 * @param object $name
 * @param boolean $interrupt
 * @param boolean $format
 *
 * @return void
 */
function d($name = null, $interrupt = false, $format = true, $debugindex = 0)
{
    $msg      = debug_backtrace();
    $backinfo = "\n(file: {$msg[$debugindex]['file']};\n line: {$msg[$debugindex]['line']};\n function: {$msg[$debugindex]['function']})\n";
    if (!is_cli()) {
        if ($format) {
            echo "<pre >";
        } else {
            echo "<div >";
        }
    }

    // $name = $format && !is_cli() ? var_export($name) : $name;
    $name = $format?var_export($name):$name;
    echo $name;
    echo $backinfo;
    if (!is_cli()) {
        if ($format) {
            echo "</div>";
        } else {
            echo "</pre>";
        }
    }
    if ($interrupt) {
        die();
    }
}

//加载环境常量
function is_cli()
{
    return preg_match("/cli/i", php_sapi_name()) ? true : false;
}
loadsysconf();

im(LIB . 'loader.php');


im(SHORTCUT . 'function.php');

\ng169\lib\Loader::register();
im(CORE . 'run.php');
