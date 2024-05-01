<?php



namespace ng169\control;
use ng169\control\general;
use ng169\Y;
use ng169\TPL;
use ng169\tool\Out as YOut;
use ng169\tool\Request as YRequest;
use ng169\tool\Cookie as YCookie;
use ng169\tool\Url as YUrl;
checktop();
im(CONTROL.'public/night.php');
class userbase extends general
{
    private $pagesize = 15;
    public $tpl_path = 'tpl/user';
    	protected $noNeedLogin = [];//默认全部要登入
    public function _getuserid()
    {
        $userid = parent::$wrap_user['uid'];
        if ($userid == null) {
            
        }
        return $userid;
    }
    public function _getarea()
    {
        $m = M('city', 'im');
        return $m->_getarea();
    }
    public function __construct()
    {

        $this->userpath = 'tpl/';
        $resource_path=parent::$urlpath.'tpl/';
        $c=D_MEDTHOD;	$a=D_FUNC;
        if ($c != 'login') {
            $login = $this->checkLogin(); 
        }
        $path='user/';
        if(YUrl::ismoible()){ $path='muser/';}
        $this->tpl_path= $this->userpath.$path;
        unset($m_login);
        unset($login['password']);

        $var_array = array(
            'user' => parent::$wrap_user,
            'time' => time(),
            'c' => $c,
            'a' => $a,
            'group'=>$this->group,
            'login' => $login,
            'city' => $city,
            'ip'=>$_SERVER['SERVER_ADDR']?$_SERVER['SERVER_ADDR']:$_SERVER['LOCAL_ADDR'],
            'port'=>DB_SOCKPOST,
            'seo'=>$this->seoinit(),
           	'usertpl'=>PATH_URL.$this->tpl_path.'/',
			'realusertpl'=>ROOT.$this->tpl_path.'/',
            );
       
       
        
        TPL::assign($var_array);
       
    }
    
    public function existsTplFile($tplname)
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
    
    public function gettree($uid=null){
        $uid=$uid?$uid:$this->get_userid();
        if(!$uid){
            error('用户ID丢失');
        }
        $model = T('user');
        $data = $model->order_by(array('f' => array('orders')));
        
        $choose=userinfo($uid);
        $data = $data->get_child('uid', $uid, 'gid');
        $data=array_merge(array($choose),$data);
        return $data;
    }
    public function gettreeid($uid=null){
        $data=$this->group;
        $i=array();
        foreach( $data as $k=>$v){
            if($v['uid'] )
                array_push($i,$v['uid']);
        }
        return $i;
    }
    public function getTPLFile($tplname)
    {
        $tplfile = $this->userpath . $tplname;
        if (!file_exists(ROOT . './' . $tplfile . '.tpl') && !file_exists(ROOT .
            './' . $tplfile . '.html')) {
            error('模板文件[' . $tplfile . ']不存在，请检查！', '', 1);
        } else {
            $tplfile = file_exists(ROOT . './' . $tplfile . '.tpl') ? $tplfile . '.tpl' :
                $tplfile . '.html';
            return $tplfile;
        }
    }
    
    public function getMeta($idmark)
    {
        $model_seo = parent::model('seo', 'im');
        $data = $model_seo->getOneData($idmark);
        unset($model_seo);
        $this->metawrap = $data;
        return $data;
    }
    
    private function _loadMenu()
    {
        $model_seo = parent::model('seo', 'im');
        $model_seo->loadChLabel();
        unset($model_seo);
    }

    public function _page()
    {

        $thispage = $this->_thispage();
        
        $start = ($thispage - 1) * $this->pagesize;
        $end = $this->pagesize;

        $limit = array($start, $this->pagesize);
        return $limit;
    }
    public function _thispage()
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
    
    
    
    
    
    
    
    public function tojson($msg)
    {
        return json_encode($msg);
    }
    
    public function json_out($msg)
    {
        echo $this->tojson($msg);
        die();
    }
    public function getcookie()
    {
        Y::loadTool('cookie');
        $admininfo = YCookie::get('userinfo');
        $Xcode =Y::import('code', 'tool');
        $admininfo = $Xcode->authCode($admininfo, 'DECODE');
        $admininfo = unserialize($admininfo);
        return $admininfo;
    }
    
    
    public function log($status, $up = null,$where=null)
    {
        M('log','im')->log($status,$where,$up);
    }
    
    
    public function savecookie($infoarr)
    {
        $Xcode =Y::import('code', 'tool');
        $infostr = serialize($infoarr);
        $infocode = $Xcode->authCode($infostr, 'EECODE');
        Y::loadTool('cookie');
        YCookie::set('userinfo', $infocode);
    }
    
    public function getcurrentinfo()
    {
       $c=D_MEDTHOD;	$a=D_FUNC;
        $array = array(
            'dowhat' => $c . '&' . $a,
            'addtime' => time(),
            'ip' => YRequest::getip(),
            'opuser' => parent::$wrap_user['userid']);
        return $array;
    }
    public function checkLogin()
    {
        if(isset(Y::$conf['closesite'])){
            YOut::redirect(geturl(null,null,'login','index'), 0);
        }
        
        $userinfo = $this->getcookie();
      
        if (!empty($userinfo)) {
            $user = T('user');
            $w = array_filter(array(
                'username' => $userinfo['username'],
                'password' => $userinfo['password']));
            $userdbinfo = $user->join_table(array('t'=>'merchant','uid','uid'))->set_where($w,'=')->get_one();
         
            if ($userinfo['password'] != $userdbinfo['password']) {
                YOut::redirect(geturl(null,null,'login','index'), 0);
            }
            if ($userdbinfo['flag'] == 1) {
                out('账号已被列入黑名单',geturl(null,null,'index','index'),0);
            }
            if ($userdbinfo['exit'] == 1) {
                out('您的账号已经取消',geturl(null,null,'index','index'),0);
            }
            if ($userdbinfo == null) {
            } else {
                parent::$wrap_user = $userdbinfo;
                return 1;
            }
        } else {
            YOut::redirect(geturl(null,null,'login','index'), 0);
        }
        return 0;
    }

    
    public function getusertype($string)
    {
        if (ereg('^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+', $string)) {
            return 'email';
        }
        if (ereg('^([0-9_-])+$', $string)) {
            return 'mobile';
        }
    }


    
    
    private function _savewhere($val)
    {
        global $c;
        $ckname = $c . 'where';
        $val = json_encode($val);
        $code =Y::import('code', 'tool');
        $val = $code->authCode($val, 'ENCODE');
        YCookie::set($ckname, $val, 0, time() + 3600 * 24);
    }
    
    private function _getwhere()
    {
        global $c;
        $ckname = $c . 'Iwhere';
        $val = YCookie::get($ckname);
        $code =Y::import('code', 'tool');
        $val = $code->authCode($val, 'DECODE');
        $val = stripslashes($val);
        $val = json_decode($val, true);
        return $val;
    }
    public function _delwhere()
    {
        global $c;
        $ckname = $c . 'Iwhere';
        YCookie::del($ckname);
    }
    public function global_list($table, $where = null,$ys=null)
    {
        
        $this->init_where($table,$ys);
        if ($where) {
            $table->set_global_where($where);
        }
        $num = $table->get_count(null,0);
        $page = $this->make_page($num);
        $data = $table->set_limit($this->_page())->get_all(null,0,0);
        $var_array = array('data' => $data, 'page' => $page);
        
        $this->log(1,null,parent::$wrap_where);
        $this->_view(null, $var_array);
    }
    
    public function _initwhere($table)
    {
        
        $havewhere = G(array('int' => array('condition')))->get(); 
        switch ($havewhere['condition']) {
            case null:

                $w = $this->_getwhere();
                
                $table->set_where($w);
                
                break;
            
            case '0':
                $this->_delwhere();
                break;
            
            case '1':
                $filed_arr = $table->get_filed();
                $w = G(array('array' => $filed_arr))->get();
                $this->_savewhere($w);
                
                $table->set_where($w);
                
                break;
            
        }
        parent::$wrap_where = $w;
        
        

        
        return $table;
    }
    public function checksafe($name){
        $safepwd=parent::$wrap_user['safepwd'];
        $p=get(array('string'=>array($name=>'md5')));
        
        $p=$p[$name];

        if($p==$safepwd){
            return true;
        }
        else{
            error('安全密码错误');
        }
        
    }
}

?>
