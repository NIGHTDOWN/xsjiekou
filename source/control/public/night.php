<?php
namespace ng169\control;

use ng169\tool\Cookie as YCookie;
use ng169\tool\Out as YOut;
use ng169\tool\Page as YPage;
use ng169\tool\Request as YRequest;
use ng169\TPL;
use ng169\Y;

checktop();
class general extends Y
{
    public $tpl_path, $page_size = 28, $pagestartid = 0, $log, $pagearray = array(), $pagekey = null, $fh = null;
    public $group = null;
    private $callback = '_seo';
    public $orderby = array();
    public $chattype = array();
    public function init_chattype()
    {
        $this->chattype = include CONF . '/type.php';

        TPL::assign($this->chattype);
    }
    /**
     * 检查是否需要登入
     * @return boolean
     */
    public function needlogin()
    {
        $action = D_FUNC;
        if (in_array(strtolower($action), $this->noNeedLogin) || in_array('*', $this->noNeedLogin)) {
            return false;
        }
        return true;
    }
    /**
     * 检查是否需要权限
     * @return boolean
     */
    public function needpower()
    {
        $action = D_FUNC;
        if (in_array(strtolower($action), $this->noNeedPower) || in_array('*', $this->noNeedPower)) {
            return false;
        }
        return true;
    }
    public function vlog($uid = null, $muid = null, $pid = null)
    {
        $logid = M('vlog', 'im')->log($uid, $muid, $pid);
        TPL::assign(array('vlogid' => $logid));
        return $logid;
    }
    public function init($tpl_path, $size = 20, $db_log_name = null)
    {
        $this->tpl_path = $tpl_path;
        $this->page_size = $size;
        TPL::assign(array('tpl_path' => $this->tpl_path, 'res' => parent::$urlpath . $this->tpl_path));
    }
    public function seoinit()
    {

        $this->seo['title'] = @Y::$conf['site']['site_name'];
        $this->seo['keyword'] = @Y::$conf['site']['site_keywords'];
        $this->seo['desc'] = @Y::$conf['site']['site_description'];

        TPL::assign(array('seo' => $this->seo));
    }
    public function seoset($title = null, $keyword = null, $desc = null)
    {

        $this->seo['title'] = $title;

        $this->seo['keyword'] = $keyword;

        $this->seo['desc'] = $desc;

        TPL::assign(array('seo' => $this->seo));
    }
    public function seoadd($title = null, $keyword = null, $desc = null)
    {

        $this->seo['title'] = $title . ' - ' . $this->seo['title'];

        $this->seo['keyword'] = $keyword . ' - ' . $this->seo['keyword'];

        $this->seo['desc'] = $desc . ' - ' . $this->seo['desc'];

        TPL::assign(array('seo' => $this->seo));
    }
    public function fix_db_field($data_in_arr = null, $fix_arr = null)
    {
        if (is_array($fix_arr) && is_array($data_in_arr)) {
            foreach ($fix_arr as $key => $val) {
                if ($val != null && !is_numeric($key) && isset($data_in_arr[$val])) {
                    $data_in_arr[$key] = $data_in_arr[$val];
                    unset($data_in_arr[$val]);
                }
            }
        }
        return $data_in_arr;
    }
    public function initlevel()
    {
        $level = explode(',', @Y::$conf['pt_level_name']);
        return $level;
    }
    public function globaldoing()
    {
        if (D_MEDTHOD == 'wallet') {

            Y::loadTool('asyn');
            $url = geturl(null, 'automfl', 'aysnqian', 'admin');

            YAsyn::start($url, null);
        }

    }
    public function getcache($tplfile = null, $id = 1)
    {
        if ($tplfile == null) {
            $c = D_MEDTHOD;
            $a = D_FUNC;

            if ($a == 'run') {
                $a = 'index';
            }
            $tplfile = "{$c}_{$a}";
        }

        return TPL::getCache($this->_getTPLFile($tplfile), $id);
    }
    public function clear_tpl_cache($tplfile)
    {
        if ($tplfile == null) {
            $c = D_MEDTHOD;
            $a = D_FUNC;

            if ($a == 'run') {
                $a = 'index';
            }
            $tplfile = "{$c}_{$a}";
        }

        $file = $this->_getTPLFile($tplfile);

        TPL::clearCache($file);
    }
    public function view($tplfile = null, $var_array = null, $after_call = null, $cache = null)
    {

        if ($tplfile == null) {
            $c = D_MEDTHOD;
            $a = D_FUNC;

            if ($a == 'run') {
                $a = 'index';
            }
            $tplfile = "{$c}_{$a}";
        }
        $this->_tplfile = $this->_getTPLFile($tplfile);
        //如果存在page

        if (isset($var_array['page']) && ($var_array['page'] instanceof \ng169\tool\Page)) {

            $var_array['page'] = $var_array['page']->getpage();
            //获取分页
        }
        if ($var_array) {
            TPL::assign($var_array);
        }
       
        if ($after_call == null) {

            $group = YRequest::getGpc('m') ? YRequest::getGpc('m') : 'index';
            if ($group == 'index') {

                call_user_func(array($this, $this->callback));
            }
        } elseif ($after_call == 1) {

            call_user_func(array($this, $this->callback));
        } else {
        }
       
        $html = TPL::display($this->_tplfile, $cache);

        return;
    }

    public function get_userid($type = 0)
    {
        $userid = @parent::$wrap_user['uid'];

        if ($userid == null && $type) {
            error('请登入在操作', geturl(null, null, 'login', 'index'), 1);
        }

        return $userid;
    }
    public function get_muid($type = 0)
    {
        $muid = @parent::$wrap_user['muid'];
        if ($muid == null && $type) {
            error('请登入在操作', geturl(null, null, 'login', 'index'), 1);
        }
        return $muid;
    }
    public function get_name($uid)
    {
        $id = array('userid' => $uid);
        $type = T('user')->set_field(array('type'))->get_one($id);
        switch ($type['type']) {

            case '1':
                $k = 'username';
                $msg = T('member')->set_field(array($k))->get_one($id);
                return $msg[$k];
                break;
            case '2':
                $k = 'merchantname';
                $msg = T('member')->set_field(array($k))->get_one($id);
                return $msg[$k];
                break;

        }

    }

    public function get_adminid()
    {
        $adminid = parent::$wrap_admin['adminid'];
        if ($adminid == null) {

            out('登入超时，请重新登入', geturl(null, 'login', 'login', 'admin'), 0, 1);

        }
        return $adminid;

    }

    public function get_area()
    {
        $m = M('city', 'im');
        return $m->_getarea();
    }
    /**
     * 初始化可以模糊匹配的字段
     * @param undefined $word
     *
     * @return
     */
    private $likeword = [];
    public function init_like(array $word)
    {
        $this->likeword = $word;
    }
    public function getlikeword()
    {
        if (sizeof($this->likeword)) {
            return $this->likeword;
        }

        return false;
    }
    public function init_where($table, $filder = null, $op = '')
    {

        $keyarr = [];
        /**
         * 加入可排序字段
         * 排序字段前缀识别
         */
        $bool = ($filder != null && is_array($filder));
        if ($bool) {
            foreach ($filder as $index => $string) {
                $string = explode('.', $string);
                if (sizeof($string) > 1) {
                    $keyarr[$index] = $string[1];
                } else {
                    $keyarr[$index] = $string[0];
                }
            }
        } else {
            $keyarr = $table->get_field();
        }
        if (gettype($table) == 'object') {
            $filed_arr = $keyarr;

            $w = get(array('string' => $filed_arr));

            foreach ($w as $key => $val) {

                if (isset($w[$key])) {

                    if (!is_array($val) && preg_match('/^\[(\d*,\d*)\]$/', $val, $info)) {
                        $w[$key] = explode(',', $info[1]);
                        $w[$key] = array_filter($w[$key]);
                    }
                }
            }

            $sw = G(array('string' => array('word')))->get();
            $like = $this->getlikeword();
            foreach ($w as $k => $v) {
                if ($v) {
                    if ($like && in_array($k, $like)) {
                        $table->set_where([$k => $v], 'like');
                    } else {
                        $table->set_where([$k => $v], $op);
                    }
                }
            }

            /*$table->set_where($w,$op);*/
            parent::$wrap_where = $w;
            $w = array_filter($w);
            $var_array = array('where' => $w, 'word' => @$sw['word']);
            TPL::assign($var_array);
        }
        $flag = get(array('int' => array('sflag')));
        if (isset($flag['sflag']) && $flag['sflag']) {
            $prm = array_merge($_GET, $_POST, $w);
            unset($prm['m']);
            unset($prm['a']);
            unset($prm['c']);
            unset($prm['sflag']);
            $url = geturl($prm, D_FUNC, $args['c'], $args['m']);

            YOut::redirect($url);
        }

        return $table;
    }
    /**
     * 排序
     * @param modelobject $table 模型实例
     * @param array $filder 排序字段
     *
     * @return object 排序模型对象
     */
    public function init_order($table, $filder = null)
    {
        $by = get(array('string' => array('up', 'down')));

        $keyarr = [];
        /**
         * 加入可排序字段
         * 排序字段前缀识别
         */
        $bool = ($filder != null && is_array($filder));
        if ($bool) {
            foreach ($filder as $index => $string) {
                $string = explode('.', $string);
                if (sizeof($string) > 1) {
                    $keyarr[$index] = $string[1];
                } else {
                    $keyarr[$index] = $string[0];
                }
            }
        } else {
            $keyarr = $table->get_field();
        }

        //存在就排序

        if (sizeof($by) >= 1) {

            foreach ($by as $key => $v) {

                if (!in_array($v, $keyarr)) {
                    return $table;
                }
                if ($bool) {
                    $index = array_search($v, $keyarr);
                    $orderkey = $filder[$index];
                } else {
                    $orderkey = $v;
                }

                switch ($key) {
                    case 'up':
                        $word = array('f' => $orderkey, 's' => 'up');
                        $table = $table->order_by($word);

                        break;

                    case 'down':
                        $word = array('f' => $orderkey, 's' => 'down');

                        $table = $table->order_by($word);
                        break;

                }
            }

            if (is_array($word)) {
                $this->orderby = $word;

                $var_array = array('orderby' => $word);
                TPL::assign($var_array);
            }

        }

        return $table;
    }
    public function get_page_limit($page_size = null)
    {
        if ($page_size != null) {
            $this->page_size = $page_size;
        }
        $thispage = $this->_thispage();
        $index = ($thispage - 1);
        $limit = array(intval($index), intval($this->page_size));

        return $limit;
        $index2 = ($thispage) * $this->page_size - 1;
        $start = [];

        if (isset($this->pagearray[$index])) {

            $start = $this->pagearray[$index][$this->pagekey];
/*
if (isset($this->pagearray[$index2])) {

$array = array_column($this->pagearray,$this->pagekey);

$start2= array_slice($array,$index,$this->page_size);

}
 */

        } else {
            if ($this->fh != '>') {

                $start = $this->pagestartid - ($thispage - 1) * $this->page_size;
            }

        }

        $end = $thispage * $this->page_size;

        /* if (!isset($start2)) {
        $in = $start;
        }
        else {
        $in = $start2;
        }*/

        $limit = array(intval($start), intval($this->page_size));

        return $limit;
    }
    public function set_pagesize($size)
    {
        if ($size) {
            $this->page_size = $size;
        }
    }

    public function make_page($table, $maxpage = 4, $pagesize = null, $key = null, $iscache = 1)
    {
        $this->havepage = true;
        $num = $table->get_count();
        $agrs = $_GET;
        unset($agrs['page']);
        unset($agrs['offset']);
        $a = D_FUNC;
        $url = geturl($agrs, $a);
        Y::loadTool('page');
        $pagearray['total'] = $num;
        $pagearray['szie'] = isset($pagearray['szie']) ? $pagearray['szie'] : $this->page_size;
        $pagearray['pagenum'] = $this->_thispage();
        TPL::assign(array('pagearray' => $pagearray, 'pagesize' => $num));

        $page = YPage::getobj()->init($num, $pagearray['szie'], $pagearray['pagenum'], $url, $maxpage);

        return $page;

    }

    public function make_category($table, $link_db_arr)
    {
        $search = array();
        $key = $table->get_filed();
        foreach ($link_db_arr as $field => $val) {
            if (in_array($field, $key)) {

                $field_dbobj = T($val['dbname']);
                if ($val['as'] != null) {

                    $id = parent::$wrap_where[$val['as']];
                    if ($id != null && $id != '' && $showchoose) {
                        $f = $field_dbobj->get_field();
                        $f = array_merge($f, array($val['as'] => $f = $field_dbobj->get_primarykey()));
                        $f_get = $field_dbobj->set_field($f)->get_all();

                    } else {

                        if ($child) {
                            $f = $field_dbobj->get_field();
                            $f = array_merge($f, array($val['as'] => $f = $field_dbobj->get_primarykey()));
                            $f_get = $field_dbobj->set_field($f)->set_where(array('parentid' => $id))->get_all();
                        }

                    }
                } else {

                    $id = parent::$wrap_where[$field];
                    if ($id != null && $id != '' && $showchoose) {

                        $f_get = $field_dbobj->get_all();

                    } else {

                        if ($child) {

                            $f_get = $field_dbobj->set_where(array('parentid' => $id))->get_all();
                        }

                    }
                }

            }

            $out = array('$field' => array('alais' => $alais, 'data' => $f_get));
            $search = array_merge($search, $out);
        }
        return $search;
    }

    private function _getTPLFile($tplname, $path = null)
    {

        $tplfile = $path ? $path . $tplfile : $this->tpl_path . $tplname;
        if (!file_exists(ROOT . './' . $tplfile . '.tpl') && !file_exists(ROOT .
            './' . $tplfile . '.html')) {
            out('模板文件[' . $tplname . ']不存在，请检查！', '', 0, 0);
        } else {
            $tplfile = file_exists(ROOT . './' . $tplfile . '.tpl') ? $tplfile . '.tpl' :
            $tplfile . '.html';
            return $tplfile;
        }
    }
    private function _seo()
    {
        // \ng169\hook\seo();

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
    public function _getcookie($name)
    {

        parent::loadTool('cookie');
        $admininfo = YCookie::get($name);
        $Xcode = Y::import('code', 'tool');
        $admininfo = $Xcode->authCode($admininfo, 'DECODE');

        $admininfo = unserialize($admininfo);

        return $admininfo;
    }

    public function _savecookie($name, $val)
    {
        $Xcode = Y::import('code', 'tool');
        $infostr = serialize($val);
        $infocode = $Xcode->authCode($infostr, 'EECODE');
        parent::loadTool('cookie');
        YCookie::set($name, $infocode);
    }
    public function _delcookie($name)
    {
        parent::loadTool('cookie');
        YCookie::del($name);
    }
}
