<?php

namespace ng169\control;

use FacebookAds\Http\Request;
use ng169\control\general;
use ng169\tool\Cookie as YCookie;
use ng169\tool\Request as YRequest;
use ng169\tool\Session as XSession;
use ng169\tool\Out;
use ng169\lib\Lang;
use ng169\Y;
use ng169\tool\Url as YUrl;
use ng169\TPL;

checktop();
im(CONTROL . 'public/night.php');
class indexbase extends general
{
	public $pagesize = 15;
	public $page = 1;
	public $tpl_path = 'tpl/templets/default/';
	#模板文件
	public $smtpl = null;
	#Meta容器
	public $metawrap = null;
	public $uid = null;
	public $cpfile = null;
	public $lang = null;
	public $langid = null;
	public static $city = null;
	public $head;
	protected $noNeedLogin = ['*']; //默认全部无须登入
	private $byword = array(
		'hits',
		'addtime',
		'price',
		'scores'
	);
	public
	function _checkcode($name, $url = null)
	{
		Y::loadTool('session');
		$getcode = G(array('string' => array($name)))->get();
		if ($getcode[$name] != XSession::get('verifycode')) {
			if ($url) {
				error('验证码错误', $url);
			} else {
				error('验证码错误');
			}
		}
	}

	public
	function hits($table, $id)
	{
		$table->updata(array('v' => array('hits' => 'hits+1'), 'w' => $id), false);
	}
	public
	function _getuserid()
	{
		$userid = parent::$wrap_user['userid'];
		if ($userid == null) {
		}
		return $userid;
	}
public function getClientLanguage(){
	$http_accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	// 使用内置函数preg_match_all来提取语言代码
	preg_match_all('/(af|am|ar|as|az|be|bg|bn|br|bs|ca|cs|cy|da|de|el|en|eo|es|et|eu|fa|fi|fr|fy|ga|gd|gl|gu|ha|he|hi|hr|hu|hy|ia|id|is|it|ja|jv|ka|kk|km|kn|ko|ku|ky|la|lb|ln|lo|lt|lv|mg|mk|ml|mn|mr|ms|mt|nb|nl|nn|no|oc|om|or|pa|pl|ps|pt|qu|rm|ro|ru|rw|se|si|sk|sl|sn|so|sq|sr|ss|st|sv|sw|ta|te|th|ti|tl|tn|to|tr|ts|tt|ug|uk|ur|uz|vi|wo|xh|xx|yi|yo|zh|zu)-(\w{2})/', $http_accept_language, $matches);
	// 语言代码数组
	if(isset($matches[0]) && isset($matches[0][0])){
		$languages = $matches[0][0];
		$languages = substr($languages, 0, 2);
		return $languages;
	}
	return "";
}
	public
	function __construct()
	{

		$c = D_MEDTHOD;
		$a = D_FUNC;
		$login = 0;
		$this->head = YRequest::get_head();
		$this->head['version']="web1.0";
		$this->head['lang']=$this->getClientLanguage();
		
		parent::$wrap_head = $this->head;
		d($this->head,1);
		if ($this->needlogin()) {

			$login = $this->checkLogin();
		} else {
			$this->inituser();
			// parent::$wrap_user = $this->getcookie();
		}

		unset($login['password']);

		//		parent::$wrap_city = M('city','im')->getinfo();

		$this->seoinit();
		$this->loadlang();
		/*if(YUrl::ismoible()){
			$this->tpl_path='tpl/mtpl/';
		}*/

		$var_array = array(
			'user'  => parent::$wrap_user,
			'time'  => time(),
			'c'     => $c,
			'a'     => $a,
			'login' => $login,
			'ip' => $_SERVER['SERVER_ADDR'] ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'],  'port' => DB_SOCKPOST,
			'city'  => parent::$wrap_city,
			'indextpl' => PATH_URL . $this->tpl_path,
			'realindextpl' => ROOT . $this->tpl_path,
			'url' => \ng169\tool\Request::curPageURL(),
		);

		$this->init($this->tpl_path);

		TPL::assign($var_array);
	}
	public function loadlang()
	{
		$lang = get(['string' => ['lang']]);
		if (!$lang['lang']) {
			$head = $this->header();
			$lang = $head['accept-language'];
		}

		$lang = substr($lang, 0, 2);
		$this->lang = $lang;
		
		$in = M('city',"im")->getinfo($lang);
		
		if ($in) {
			$this->langid = $in['cityid'];
		}
		Lang::init($lang);
	}
	public function header($name = '', $default = null)
	{
		if (empty($this->header)) {
			$header = [];
			if (function_exists('apache_request_headers') && $result = apache_request_headers()) {
				$header = $result;
			} else {
				$server = $this->server ?: $_SERVER;
				foreach ($server as $key => $val) {
					if (0 === strpos($key, 'HTTP_')) {
						$key = str_replace('_', '-', strtolower(substr($key, 5)));
						$header[$key] = $val;
					}
				}
				if (isset($server['CONTENT_TYPE'])) {
					$header['content-type'] = $server['CONTENT_TYPE'];
				}
				if (isset($server['CONTENT_LENGTH'])) {
					$header['content-length'] = $server['CONTENT_LENGTH'];
				}
			}
			$this->header = array_change_key_case($header);
		}
		if (is_array($name)) {
			return $this->header = array_merge($this->header, $name);
		}
		if ('' === $name) {
			return $this->header;
		}
		$name = str_replace('_', '-', strtolower($name));
		return isset($this->header[$name]) ? $this->header[$name] : $default;
	}
	public
	function filtertool($table)
	{

		$key     = $table->get_field(1);
		$thisurl = 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
		$var_array['thisurl'] = $thisurl;
		if (in_array('cityid', $key)) {
			$m = M('city', 'im');
			$var_array['area'] = $m->get_area();
		}
		if (in_array('catid', $key) && in_array('alias', $key)) {
			$catearr = array(
				'store'   => 'merchant_category',
				'product' => 'product_category',
				'server'  => 'product_category',
				'need'    => 'need_category',
				'activity' => 'product_category',
				'article' => 'article_category',
				'photo'   => 'photo_category'
			);
			$get = G(array('string' => array(
				'c',
				'mod',
				'a'
			)))->get();
			if (($catearr[$get['c']] != null) || ($catearr[$get['mod']] != null)) {
				$tbname = $catearr[$get['c']] ? $catearr[$get['c']] : $catearr[$get['mod']];
				$mod    = T($tbname);
				$catid  = parent::$wrap_where['catid'];
				if ($catid) {
					$select = $mod->get_one(array('catid' => $catid));
				} elseif (parent::$wrap_where['alias']) {
					$select = $mod->get_one(array('alias' => parent::$wrap_where['alias']));
				}
				$catid = $select['catid'];
				if ($get['a'] == null || $get['a'] == 'list' || $get['a'] == "run") {
					if ($catid) {
						$where = array('parentid' => $catid);
					} else {
						$where = array('depth' => 1);
					}
					$var_array['catetype'] = $mod->get_all($where);


					if ($select['parentid']) {
						$var_array['cateselect'] = $mod->get_one(array('catid' => $select['parentid']));
					} else {
						$var_array['cateselect'] = $select;
					}
					if (sizeof($var_array['catetype']) == 0) {
						$where = array('parentid' => $select['parentid']);
						$var_array['catetype'] = T($tbname)->get_all($where);
					}
				}
			}
		}
		$byword = $this->byword;

		$key    = array_unique($key);
		$by     = array_intersect($byword, $key);

		if (is_array($by)) {
			$var_array['by'] = $by;
		}
		TPL::assign($var_array);
	}

	public
	function existsTplFile($tplname)
	{
		$res = false;
		if (!empty($tplname)) {
			$tplfile = parent::$tplpath . $tplname . '.tpl';
			if (file_exists(ROOT . './' . $tplfile)) {
				$res = true;
			}
		}
		return $res;
	}


	public
	function getTPLFile($tplname)
	{
		$tplfile = parent::$tplpath . $tplname;

		if (!file_exists(ROOT . './' . $tplfile . '.tpl') && !file_exists(ROOT .
			'./' . $tplfile . '.html')) {
			error('模板文件[' . $tplfile . ']不存在，请检查！', '', 1);
		} else {
			$tplfile = file_exists(ROOT . './' . $tplfile . '.tpl') ? $tplfile . '.tpl' :
				$tplfile . '.html';
			return $tplfile;
		}
	}


	public
	function getMeta($idmark)
	{
		$model_seo = parent::model('seo', 'im');
		$data = $model_seo->getOneData($idmark);
		unset($model_seo);
		$this->metawrap = $data;
		return $data;
	}


	private
	function _loadMenu()
	{
		$model_seo = parent::model('seo', 'im');
		$model_seo->loadChLabel();
		unset($model_seo);
	}

	public
	function getusermenu($muid)
	{
		$powername = 'custommenuflag';
		$m         = M('power', 'im');

		$menuobj   = T('custom_store_menu');
		$where     = array('muid' => $muid);
		$menu = $menuobj->get_all($where);

		if ($m->getpower($powername, $muid) && sizeof($menu) > 0) {
		} else {
			$where = array('muid' => 0);
			$menu = $menuobj->get_all($where);
		}
		return $menu;
	}
	public
	function _page()
	{
		$lmt = $this->get_page_limit();
		return   $lmt;
	}
	public
	function _thispage()
	{
		$thispage = G(array('int' => array('page')))->get();
		if (count($thispage) != 0) {
			$thispage = $thispage['page'];
		} else {
			$thispage = 1;
		}
		if ($thispage < 1) {
			$thispage = 1;
		}
		return $thispage;
	}



	public
	function tojson($msg)
	{
		return json_encode($msg);
	}

	public
	function json_out($msg)
	{
		echo $this->tojson($msg);
		die();
	}
	public
	function getcookie()
	{

		$usercode = YCookie::get('userinfo');

		$Xcode     = Y::import('code', 'tool');
		$userinfo = $Xcode->authCode($usercode, 'DECODE');

		$userinfo = unserialize($userinfo);

		return $userinfo;
	}



	public
	function log($status, $up = null, $where = null)
	{

		M('log', 'im')->log($status, $up, $where);
	}


	public
	function savecookie($infoarr)
	{
		$Xcode   = Y::import('code', 'tool');
		$infostr = serialize($infoarr);
		$infocode = $Xcode->authCode($infostr, 'ENCODE');


		parent::loadTool('cookie');
		YCookie::set('userinfo', $infocode);
	}

	public
	function getcurrentinfo()
	{

		$c     = D_MEDTHOD;
		$a     = D_FUNC;
		$array = array(
			'dowhat' => $c . '&' . $a,
			'addtime' => time(),
			'ip'     => YRequest::getip(),
			'opuser' => parent::$wrap_user['uid']
		);
		return $array;
	}

	public
	function checkLogin()
	{
		$userinfo = $this->getcookie();
		if (!empty($userinfo)) {

			$user = T('user_token');
			$w    = array_filter(array(
				'device_type' => $userinfo['devicetype'],
				'user_id' => $userinfo['uid'],
				'token' => $userinfo['token']
			));
			$usertoken = $user->set_where($w)->get_one();
			if ($usertoken) {
				$user = T('third_party_user')->set_where(['id' => $userinfo['uid']])->get_one();
				if ($user) {
					parent::$wrap_user = $user;
					return 1;
				}
			}
		}
		Out::redirect(geturl(null, null, 'login', 'index'), 0);
	}
	public
	function inituser()
	{
		$userinfo = $this->getcookie();
		if (!empty($userinfo)) {

			$user = T('user_token');
			$w    = array_filter(array(
				'device_type' => $userinfo['devicetype'],
				'user_id' => $userinfo['uid'],
				'token' => $userinfo['token']
			));
			$usertoken = $user->set_where($w)->get_one();
			if ($usertoken) {
				$user = T('third_party_user')->set_where(['id' => $userinfo['uid']])->get_one();
				if ($user) {
					parent::$wrap_user = $user;
					return 1;
				}
			}
		}
		// Out::redirect(geturl(null, null, 'login', 'index'), 0);
	}
	public
	function getusertype($string)
	{

		// if(@ereg('^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+', $string)){
		// 	return 'email';
		// }
		// if(@ereg('^([0-9_-])+$', $string)){
		// 	return 'mobile';
		// }else{
		// 	error('帐号类型错误');
		// }
	}

	public
	function _view($tplfile = null, $var_array = null)
	{

		$this->view($tplfile, $var_array);
	}


	private
	function _savewhere($val)
	{
		global $c;
		$ckname = $c . 'where';
		$val    = json_encode($val);
		$code   = Y::import('code', 'tool');

		$val    = $code->authCode($val, 'ENCODE');

		YCookie::set($ckname, $val, 0, time() + 3600 * 24);
	}

	private
	function _getwhere()
	{
		global $c;
		$ckname = $c . 'Iwhere';
		$val    = YCookie::get($ckname);
		$code   = Y::import('code', 'tool');
		$val    = $code->authCode($val, 'DECODE');
		$val    = stripslashes($val);

		$val    = json_decode($val, true);


		return $val;
	}
	public
	function _delwhere()
	{
		global $c;
		$ckname = $c . 'Iwhere';
		YCookie::del($ckname);
	}
	public
	function _initby($table)
	{
		$by = G(array('string' => array('upflag', 'downflag')))->get();

		if (sizeof($by) == 1) {
			foreach ($by as $key => $v) {
				$keyarr = $table->get_field();
				if (in_array($v, $keyarr) && in_array($v, $this->byword)) {
				} else {
					return $table;
				}
				switch ($key) {
					case 'upflag':
						$word = array('f' => $v, 's' => 'up');
						$table = $table->order_by($word);

						break;

					case 'downflag':
						$word = array('f' => $v, 's' => 'down');
						$table = $table->order_by($word);
						break;
				}
			}
			if (is_array($word)) {
				$var_array = array('byword' => $word);
				TPL::assign($var_array);
			}
		}

		return $table;
	}
	private
	function _set_category()
	{
		$catearray = array(
			'article' => 'article_category',
			'store'   => 'merchant_category',
			'product' => 'product_category',
			'photo'   => 'photo_category',
			'activity' => 'product_category'
		);
		$cate = G(array('int' => array('catid')))->get();
		if ($cate['catid'] != null && is_array($cate)) {
			$c = D_MEDTHOD;
			$a = D_FUNC;

			if ($catearray[$c] != null) {
				$category = T($catearray[$c])->get_one($cate);
				$var_array = array('category' => $category);
				if (is_array($category)) {

					TPL::assign($var_array);
				}
			}
		}
	}

	public
	function _initwhere($table, $as = null)
	{
		return $this->init_where($table);
	}
}
