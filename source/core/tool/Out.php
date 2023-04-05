<?php


namespace ng169\tool;

use ng169\Y;
use ng169\TPL;
use ng169\tool\Url as YUrl;
use ng169\service\Output;
use ng169\lib\Log;

checktop();
class Out
{


	private static function _html404($msg = null)
	{

		if ($msg == null) {
			$msg = "非常抱歉，您要查看的页面没有办法找到";
		}
		$var_array = array('msg' => $msg);
		TPL::assign($var_array);

		TPL::display('tpl/general/404/index.html', 1);
	}
	public static function page404($msg = null)
	{

		@header("http/1.1 404 not found");
		@header("status: 404 not found");
		self::_html404();
		die();
	}
	public static function development($msg = null)
	{
		if ($msg == null) {
			$msg = "功能开发中，敬请期待";
		}
		$var_array = array('msg' => $msg);
		TPL::assign($var_array);
		TPL::display('tpl/general/development/index.html');
	}
	/*	public static function error($error){

		echo "<meta http-equiv='Content-Type' content='text/html; charset=" .
		OEPHP_CHARSET . "' />
		<style>body{font-size:12px;line-height:25px;}</style>
		<body>
		" . $error . "
		</body>
		";
		die();
	}*/


	public static function redirect($url, $time = 0)
	{
		if (!headers_sent()) {
			if ($time === 0)
				header("Location:{$url}");
			header("refresh:" . $time . ";url=" . $url . "");
			die();
		} else {
			exit("<meta http-equiv='Refresh' content='{$time};URL={$url}'>");
			die();
		}
	}

	public static function out($message, $url = null, $flag = true, $auto_go_url = true)
	{
		/* M('log','am')->logtxt($message,$flag);*/
		// if($flag){
		// 	self::jout($message);
		// }else{
		// 	self::jerror($message);
		// }
		// ob_start();
		if (!YUrl::isAjax()) {
			if ($url == null) {
				$url = @$_SERVER['HTTP_REFERER'];
			}
			if (YUrl::ismoible()) {
				require_once TPL . 'general' . '/mhalt/halt.php';
			} else {
				require_once TPL . 'general'  . '/halt/halt.php';
			}
		} else {

			if (!$flag) {
				self::jerror($message);
				// $ret = array('error' => array('errormsg' => $message), 'flag' => $flag,'url'=>$url);

			} else {
				self::jout($message);
				// $ret = array('data' => $message, 'flag' => $flag,'url'=>$url);
			}
			// echo json_encode($ret);
		}
		// $out=ob_get_contents();
		// ob_end_clean();
		// Output::out($out);
		die();
	}
	public  static function jout($data)
	{
		$msg = ['code' => 1, 'msg' => 'success', 'result' => $data];
		//Log::txt($msg);
		echo json_encode($msg);
		die();
	}
	public  static function jpout($data)
	{
		$msg = ['code' => 1, 'msg' => 'success', 'result' => $data];
		//Log::txt($msg);
		$jsonp = $_GET["callback"];
		echo json_encode($jsonp.$msg);
		die();
	}
	public static function jerror($message, $data = null, $code = 0)
	{
		$msg = ['code' => $code, 'msg' => $message, 'result' => $data];
		Log::txt($msg);
		echo json_encode($msg);
		die();
	}
}
