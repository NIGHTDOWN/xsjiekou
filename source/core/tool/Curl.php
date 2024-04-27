<?php


namespace ng169\tool;

use ng169\lib\Log as YLog;

checktop();
class Curl
{

	private $cookie_path = '/data/cookie/curldata.txt';
	private $curl = '';
	private $head = '';


	public function _init()
	{
		if (!function_exists('curl_init')) {
			die("系统需要CURL支持！");
		}
		if (!$this->curl) {
			$this->cookie_path = './data/cookie/' . md5(AUTH_CODE_KEY) . '_data.txt';
			$this->curl = curl_init();
		}
	}
	public function unset()
	{
		$this->curl = null;
	}
	public function setproxy($proxyip, $proxy_port)
	{
		// $proxy = "80.25.198.25";
		// $proxyport = "8080";
		// $ch = curl_init("http://sfbay.craigslist.org/");

		$this->_init();
		// curl_setopt($ch, curlOPT_RETURNTRANSFER, 1);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->curl, CURLOPT_PROXY, $proxyip);
		curl_setopt($this->curl, CURLOPT_PROXYPORT, $proxy_port);

		// curl_setopt($this->curl, CURLOPT_TIMEOUT, 120);
	}
	public function setuser_agent($user_agent)
	{

		// $user_agent = "Mozilla/4.0";
		$this->_init();
		curl_setopt($this->curl, CURLOPT_USERAGENT, $user_agent);
	}
	// public function get($url)
	// {
	// 	$this->_init();
	// 	if (!$url)
	// 		return false;
	// 	// YLog::txt('25请求' . $url);
	// 	$ssl = substr($url, 0, 8) == 'https://' ? true : false;
	// 	$curl = $this->curl;
	// 	curl_setopt($curl, CURLOPT_URL, $url);
	// 	if ($ssl) {
	// 		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	// 		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	// 	}
	// 	if (ini_get('safe_mode') || ini_get('open_basedir')) {

	// 		$this->curl_redir_exec($curl);
	// 		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	// 		$content = curl_exec($curl);
	// 	} else {

	// 		@curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	// 		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	// 		$content = curl_exec($curl);
	// 	}
	// 	d($curl);
	// 	$status = curl_getinfo($curl);
	// 	curl_close($curl);
	// 	$this->unset();
	// 	if (intval($status['http_code']) == 200) {

	// 		if (ini_get('safe_mode') || ini_get('open_basedir')) {

	// 			if (strpos($content, 'charset=utf-8')) {
	// 				$astring = explode('charset=utf-8', $content);
	// 				$content = trim($astring[1]);
	// 			}
	// 			return $content;
	// 		} else {
	// 			return $content;
	// 		}
	// 	} else {

	// 		return 'fail';
	// 	}
	// }

	public static function isAjax()
	{
		// if (
		// 	self::getService('HTTP_X_REQUESTED_WITH') && strtolower(self::getService('HTTP_X_REQUESTED_WITH')) ==
		// 	'xmlhttprequest'
		// )
		// 	return true;
		// if (
		// 	self::getService('HTTP_REQUEST_TYPE') && strtolower(self::getService('HTTP_REQUEST_TYPE')) ==
		// 	'ajax'
		// )
		// 	return true;
		// if (self::getPost('oe_ajax') || self::getGet('oe_ajax'))
		// 	return true;
		return false;
	}
	public function get($url, $timeout = 0)
	{
		$this->_init();
		if (!$url)
			return false;
		$ssl = substr($url, 0, 8) == 'https://' ? true : false;
		$curl = $this->curl;
		// if (!is_null($proxy))
		// 	curl_setopt($curl, CURLOPT_PROXY, $proxy);
		
		curl_setopt($curl, CURLOPT_URL, $url);
		if ($ssl) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		}
		curl_setopt($curl, CURLOPT_USERAGENT, 'okhttp/3.6.0');
		if ($this->head) {
			curl_setopt($curl, CURLOPT_HTTPHEADER, $this->head);
		}
	
		curl_setopt($curl, CURLOPT_HEADER, false);
		
		if ($timeout) {
			curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
		}
		// curl_setopt($curl, CURLOPT_POST, true);
		// curl_setopt($curl, CURLOPT_POSTFIELDS, ($data));
		@curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
		
		$content = curl_exec($curl);
		$status = curl_getinfo($curl);
		if (curl_errno($curl)) {  
			echo 'Curl error: ' . curl_error($curl);  
		} else {  
			// 分割响应头和响应体  
			list($header, $body) = explode("\r\n\r\n", $response, 2);  
			  
			// 分离各个头信息行  
			$headers = explode("\r\n", $header);  
			  
			// 输出头信息  
			foreach ($headers as $headerLine) {  
				echo $headerLine . "\n";  
			}  
			  
			// 输出响应体（如果需要的话）  
			echo "\nBody:\n" . $body . "\n";  
		}
		

		curl_close($curl);
		$this->unset();
		
		
		if (intval($status['http_code']) == 200) {
			return $content;
		} else {
			return 'fail';
		}
	}
	public function post($url, $data, $proxy = null, $timeout = 0)
	{
		
		/*if($this->curl){
			$curl = $this->curl;
		}else{
			
			$curl = $this->curl;
		}*/

		$this->_init();
		// YLog::txt('85请求' . $url);
		if (!$url)
			return false;
		$ssl = substr($url, 0, 8) == 'https://' ? true : false;
		$curl = $this->curl;
		if (!is_null($proxy))
			curl_setopt($curl, CURLOPT_PROXY, $proxy);
		curl_setopt($curl, CURLOPT_URL, $url);
		if ($ssl) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		}
		// 		$headers[] = 'User-Agent: Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0);';
		// curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		if ($this->head) {

			curl_setopt($curl, CURLOPT_HTTPHEADER, $this->head);
		}
		curl_setopt($curl, CURLOPT_USERAGENT, 'okhttp/3.6.0');
		if ($timeout) {

			curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
		}

		// User-Agent: okhttp/3.12.0
		
		// curl_setopt($curl, CURLOPT_CON, 'okhttp/3.6.0');
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, ($data));
		// curl_setopt(
		// 	$curl,
		// 	CURLOPT_HTTPHEADER,
		// 	array(
		// 		'Content-Type: application/json',
		// 	)
		// );
		@curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		// curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
		$content = curl_exec($curl);
		$status = curl_getinfo($curl);

		curl_close($curl);
		$this->unset();
		if (intval($status['http_code']) == 200) {

			return $content;
		} else {

			return 'fail';
		}
	}
	//head头支持名称=>值
	public function head($arrays)
	{
		//修复支持
		if (!is_array($arrays)) return false;
		if (!sizeof($arrays)) return false;
		$tmp = [];
		foreach ($arrays as $k => $v) {
			if (is_int($k)) {
				$tmp = $arrays;
				break;
			}

			array_push($tmp, $k . ":" . $v);
		}



		$this->head = $tmp;
	}
	public function catchpc($url, $data, $proxy = null, $timeout = 15)
	{
		$this->_init();
		// YLog::txt('85请求' . $url);
		if (!$url)
			return false;
		$ssl = substr($url, 0, 8) == 'https://' ? true : false;
		$curl = $this->curl;
		if (!is_null($proxy))
			curl_setopt($curl, CURLOPT_PROXY, $proxy);
		curl_setopt($curl, CURLOPT_URL, $url);
		if ($ssl) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
		}
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		@curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


		/*  $ch         = curl_init ($path);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.146 Safari/537.36");
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_REFERER, "https:\/\/www.taobao.com\/");
		curl_setopt($ch, CURLOPT_TIMEOUT,120);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$img        = curl_exec ($ch);
		curl_close ($ch);*/





		$content = curl_exec($curl);
		$status = curl_getinfo($curl);
		curl_close($curl);
		$this->unset();
		if (intval($status['http_code']) == 200) {

			return $content;
		} else {

			return 'fail';
		}
	}

	public function put($url, $data, $proxy = null, $timeout = 15)
	{
		$this->_init();
		if (!$url)
			return false;
		$ssl = substr($url, 0, 8) == 'https://' ? true : false;
		$curl = $this->curl;
		if (!is_null($proxy))
			curl_setopt($curl, CURLOPT_PROXY, $proxy);
		curl_setopt($curl, CURLOPT_URL, $url);
		if ($ssl) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
		}
		$cookie_file = $this->get_cookie_file();


		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
		$data = (is_array($data)) ? http_build_query($data) : $data;
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($data)));
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		$content = curl_exec($curl);
		$status = curl_getinfo($curl);
		curl_close($curl);
		$this->unset();
		if (intval($status['http_code']) == 200) {
			return $content;
		} else {
			return 'fail';
		}
	}


	public function del($url, $data, $proxy = null, $timeout = 15)
	{
		$this->_init();
		if (!$url)
			return false;
		$ssl = substr($url, 0, 8) == 'https://' ? true : false;
		$curl = $this->curl;
		if (!is_null($proxy))
			curl_setopt($curl, CURLOPT_PROXY, $proxy);
		curl_setopt($curl, CURLOPT_URL, $url);
		if ($ssl) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
		}
		$cookie_file = $this->get_cookie_file();


		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
		$data = (is_array($data)) ? http_build_query($data) : $data;
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DEL');
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($data)));
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		$content = curl_exec($curl);
		$status = curl_getinfo($curl);
		curl_close($curl);
		$this->unset();
		if (intval($status['http_code']) == 200) {
			return $content;
		} else {
			return 'fail';
		}
	}


	private function get_cookie_file()
	{
		return ROOT . $this->cookie_path;
	}


	public function fileGet($url)
	{
		$content = '';
		if (ini_get('allow_url_fopen') == '1') {
			$content = @file_get_contents($url);
			if (empty($content)) {
				$content = $this->get($url);
			}
		} else {
			$content = $this->get($url);
		}
		return $content;
	}


	private function curl_redir_exec($ch)
	{
		static $curl_loops = 0;
		static $curl_max_loops = 20;
		if ($curl_loops++ >= $curl_max_loops) {
			$curl_loops = 0;
			return false;
		}

		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		list($header, $data) = explode("\n\n", $data, 2);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($http_code == 301 || $http_code == 302) {
			$matches = array();
			preg_match('/Location:(.*?)\n/', $header, $matches);
			$url = @parse_url(trim(array_pop($matches)));
			if (!$url) {

				$curl_loops = 0;
				return $data;
			}
			$last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
			if (!$url['scheme'])
				$url['scheme'] = $last_url['scheme'];
			if (!$url['host'])
				$url['host'] = $last_url['host'];
			if (!$url['path'])
				$url['path'] = $last_url['path'];
			$new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query'] ?
				'?' . $url['query'] : '');
			curl_setopt($ch, CURLOPT_URL, $new_url);

			return $this->curl_redir_exec($ch);
		} else {
			$curl_loops = 0;
			return $data;
		}
	}
}
