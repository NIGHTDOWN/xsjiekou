<?php

namespace ng169\db;

use ng169\db\Dbsql;
use ng169\lib\Option;
use ng169\tool\Page;
use ng169\Y;

class daoClass
{
    public $_db = null;
    public $debug = '';
    public $t, $w, $b, $l, $j, $g, $Gw;
    public $f = '*,v.*';
    private $tablename;
    private $joinword = ' left join ';
    private $table_key = array();
    private $table_all_key = array();
    private $pri_key = null;
    public $dbqz = ''; //表前缀
    private $xh = array();
    /* private $cache = '';*/
    private $loop = array();
    private $notgetkey = null;
    private $bs = 'DESC';
    private $oldbs = null;
    private $iscache = false;
    private $cachetime = '';
    private static $loopcache = 0;
    private $bsword = '';
    private $limit_where = '';
    private $havepage = false;
    private $thispage = 0;

    /**
     * 初始化pdo
     * @param undefined $dbconf
     *
     * @return
     */
    public function __construct($dbconf = 'main')
    {
        $this->dbqz = DB_PREFIX;
        $this->debug = G_DB_DEBUG;
        $dbs = Option::get('db');
        if (isset($dbs[$dbconf])) {
            $conf = $dbs[$dbconf];
            $this->dbqz = $conf['dbpre'];
            try {
                $this->_db = new Dbsql($conf['dbhost'], $conf['dbuser'], $conf['dbpwd'], $conf['dbname'], $conf['charset']) or error(__('数据库配置不存在'));
                //code...
            } catch (\Throwable $th) {
                //throw $th;
                error(__('数据库连接失败/或者超时'));
            }
        } else {
            error(__('数据库配置不存在'));
        }
    }
    /**
     * 初始化表模型
     * @param string $table
     * @param array $filedar字段名称
     *
     * @return modelobj 返回模型
     */
    public function tabel($table, $filedar = null)
    {
        $this->t($table, $filedar = null);
    }
    private function _get_child($index, $val = 0, $parent = null)
    {
        $val = intval($val);
        if (in_array(($val), $this->loop)) {
            return false;
        } else {
            array_push($this->loop, $val);
        }
        if ($parent == null) {
            $parent = 'parentid';
        }
        $where = array($parent => $val);
        $by = array('f' => 'orders');
        $info = $this->w($where)->b($by)->s(0);
        $info_back = $info;
        $i = 0;
        $offer = 0;
        if (is_array($info)) {
            foreach ($info as $key => $v) {
                $i++;
                if (!isset($v[$index])) {
                    return $info;
                }
                if (!in_array($v[$index], $this->xh)) {
                    $child = $this->_get_child($index, $v[$index], $parent);
                }
                if (is_array($child)) {
                    array_splice($info_back, $offer + $i, '0', $child);
                    $offer = $offer + sizeof($child);
                }
            }
        } else {
            return $info;
        }
        return $info_back;
    }
    public function get_child($index, $val = 0, $parent = null, $cachebool = 1)
    {
        if ($index) {
            $val = intval($val);
            $cachename = $this->tablename . "_tree" . $val;

            $cache = Y::$cache;

            list($bool, $info) = $cache->get($cachename);
            if (($bool) && $cachebool) {
            } else {
                $info = $this->_get_child($index, $val, $parent);

                if ($info) {

                    $cache->set($cachename, $info);
                }

                return $info;
            }
        } else {
            return null;
        }
        return $info;
    }
    /**
     * 初始化表模型
     * @param string $table
     * @param array $filedar字段名称
     *
     * @return modelobj 返回模型
     */
    public function t($table, $filedar = null)
    {
        if (is_array($table)) {
            $this->tablename = "(" . ($table[0]) . ")";
            $this->f = '*';
            $t = "select " . $this->f . " from " . $this->tablename . " as v ";
            $this->notgetkey = true;
            $this->t = $t;
            $newobj = clone $this;
            return $newobj;
        } else {
            $this->tablename = $this->dbqz . $table;
            $t = "select " . $this->f . " from `" . $this->tablename . "` as v ";
        }
        if ($filedar != null) {
            $this->set_field($filedar);
        }
        $this->t = $t;
        $newobj = clone $this;
        return $newobj;
    }
    /**
     * 设置取得字段
     * @param undefined $field_arr
     * @param undefined $check
     *
     * @return obj
     */
    public function set_field($field_arr, $check = 1)
    {
        if ($field_arr != null && is_array($field_arr) && $check) {
            $filed = null;
            foreach ($field_arr as $key => $a) {
                if (is_numeric($key)) {
                    $filed .= ',' . $this->fix($a);
                } else {
                    $filed .= ',' . $this->fix($key) . ' as ' . $a;
                }
            }
            $filed = trim($filed, ',');
            $this->t = str_replace($this->f, $filed, $this->t);
            $this->f = $filed;
        } else {

            $this->t = str_replace($this->f, $field_arr, $this->t);
            $this->f = $field_arr;
        }
        return $this;
    }
    public function join($join, $type = false)
    {
        $this->j($join, $type = false);
    }
    public function rjoin($join, $type = false)
    {
        $this->joinword = ' right join ';
        $this->j($join, $type = false);
    }
    public function ljoin($join, $type = false)
    {
        $this->joinword = ' left join ';
        $this->j($join, $type = false);
    }
    public function union($sql, $sql2)
    {
        $sql = $sql . ' union ' . $sql2;
        return $this->q($sql);
    }
    public function unionall($sql, $sql2)
    {
        $sql = $sql . ' union all ' . $sql2;
        return $this->q($sql);
    }
    private function isneedfix($str)
    {
        if (strrpos($str, ".") || strrpos($str, "`")) {
            return false;
        } else {
            return true;
        }
    }
    /**
     * 连表
     * @param undefined $join
     * @param undefined $bool
     *
     * @return obj
     */
    public function j($join, $bool = false)
    {
        $dbqz = $this->dbqz;
        $word = $this->joinword;
        $t = $word . '`' . $dbqz . $join['t'] . '`';
        if (is_array($join)) {
            if ($this->isneedfix($join[0])) {
                $join[0] = 'v.' . $join[0];
            }
            if (!isset($join['as'])) {
                $j1 = $t . ' as `' . $join['t'] . '`  on ' . $join[0] . " = `" . $join['t'] .
                    "`." . $join[1];
            } else {

                $t = $word . " ( " . $join['t'] . " ) ";
                $j1 = $t . ' as `' . $join['as'] . '`  on ' . $join[0] . " = `" . $join['as'] .
                    "`." . $join[1];
            }
        }

        if ($bool) {
            $this->j .= $j1;
        } else {
            $this->j = $j1;
        }

        return $this;
    }
    /**
     * 执行查询
     * @param undefined $type
     *
     * @return data
     */
    public function select($type)
    {
        switch ($type) {
            case 'all':
                $type = 0;
                break;
            case 'one':
                $type = 1;
                break;
            case 'count':
                $type = 2;
                break;
        }
        $this->s($type);
    }
    /**
     * 设置缓存
     * @param undefined $boolean 开启缓存
     * @param undefined $time 缓存时效/秒；默认为永久缓存
     *
     * @return obj
     */
    public function cache($boolean, $time = 0)
    {
        if ($boolean) {
            $this->iscache = true;
            if ($time == 0) {
                $this->cachetime = 0;
            } else {
                $this->cachetime = $time;
            }
        } else {
            $this->cache = false;
        }
    }
    public function s($type, $debug = null, $cache = null, $sql = null)
    {
        $w = $this->w ? ' where  ' . $this->w : ' ';
        if ($this->Gw != null && trim($this->Gw) != '') {
            if ($w != null && trim($w) != '') {
                $w = $w . ' and (' . $this->Gw . ')';
            } else {
                $w = ' where  ' . $this->Gw;
            }
        }
        if ($this->limit_where != null && trim($this->limit_where) != '') {
            if ($w != null && trim($w) != '') {
                $w = $w . ' and (' . $this->limit_where . ')';
            } else {
                $w = ' where  ' . $this->limit_where;
            }
        }
        if (!$sql) {
            $sql = $this->t . $this->j . $w . $this->g . ' ' . $this->b . $this->l;
        }

        $Stime = 0;
        if ($this->debug) {

            $Stime = microtime(true);
        }

        switch ($type) {
            case 0:

                $ret = $this->_db->query($sql);
                //在这里记录偏移量保存在cookie 保存类型为当前筛选列，

                if ($this->havepage) {
                    //注入

                    Page::getobj()->injection_offset(@$ret[0][$this->getkey()], @$ret[count($ret) - 1][$this->getkey()]);
                }

                break;
            case 1:

                $ret = $this->_db->getone($sql);

                break;
            case 2:
                $sql = str_replace($this->f, 'count(*) as num', $sql);
                $sql = preg_replace("/select([\s\S]*?)from/is", "select count(*) as num from", $sql);
                $ret = $this->_db->getone($sql);
                $ret = $ret['num'];
                break;
            case 3:
                $sql = $this->t;
                $sql = preg_replace("/select([\s\S]*?)from/is", "select count(1) as num from", $sql);

                $ret = $this->_db->getone($sql);

                $ret = $ret['num'];
                break;
            case 4:
                $ret = $sql;
                break;
            default:
        }


        $this->showsqllog($sql, $Stime, $debug);
        if ($this->debug) {
            return $ret;
        } else {
            return @$ret;
        }
    }
    public function where($where)
    {
        $this->w($where);
    }
    private function fix($str, $ispix = 1)
    {
        if (!$this->isneedfix($str)) {
            return $str;
        }
        $pix = '';
        if ($ispix) {
            $pix = '`v`.';
        }

        $str = explode('.', $str);

        if (sizeof($str) != 1) {

            $str = '`' . $str[0] . '`.`' . $str[1] . '`';
        } else {
            $keys = $this->getfiled(0);

            if (in_array($str[0], $keys)) {
                $str = $pix . '`' . $str[0] . '`';
            } else {
                $str = '`' . $str[0] . '`';
            }
        }

        /* }*/
        return $str;
    }
    public function set_global_where($where, $operator = null, $type = 1, $andor = 'and', $break =
    0)
    {
        $this->Gw($where, $operator, $type, $andor, $break);
    }
    public function Gw($where, $operator = null, $type = 1, $andor = 'and', $break =
    0)
    {
        $w = $this->wherearray($where, $operator);
        if ($type) {
            $this->Gw = $w;
        } else {
            if ($this->Gw == null) {
                $this->Gw .= $w;
            } else {
                $this->Gw .= " " . $andor . '  (' . $w . ') ';
            }
        }

        return $this;
    }
    /**
     * 设置where
     * @param undefined $name
     * @param undefined $val
     * @param undefined $op
     *
     * @return string
     */
    private function setstr($name, $val, $op = '=')
    {

        if ($val === '') {
            return false;
        }

        if (!$name) {
            return false;
        }

        $name = $this->fix($name);
        $string = '';

        switch ($op) {
            case '':
                $string = "{$name} = '{$val}'";

                break;
            case 'like':
                $string = "{$name} like \"{$val}%\"";
                break;
            case 'in':
                if (is_array($val)) {
                    $in = implode(',', $val);
                } else {
                    $in = $val;
                }

                $in = trim($in, ',');

                if (!$in) {
                    return false;
                }

                $string = "{$name} in ({$in})";
                break;
            case '>=':
                $string = "{$name} >= '{$val}'";
                break;
            case '>':
                $string = "{$name} > '{$val}'";
                break;
            case '<':
                $string = "{$name} < '{$val}'";
                break;
            case '<=':
                $string = "{$name} <= '{$val}'";
                break;
            case 'between':
                if (is_array($val) && sizeof($val) == 2) {
                    $string = "{$name} between '{$val[0]}' and '{$val[1]}'";
                } else {
                    $string = "{$name} between ({$val})";
                }

                break;
            case 'notin':
                if (is_array($val)) {
                    $in = implode(',', $val);
                }
                $in = trim($val, ',');
                if (!$in) {
                    return false;
                }

                $string = "{$name} not in ({$in})";
                break;
            case '!=':
                $string = "{$name} != '{$val}'";
                break;
            case '=':
                if (!is_array($val)) {
                    $string = "{$name} = '{$val}'";
                } else {

                    $in = implode(',', $val);

                    $in = trim($in, ',');

                    if (!$in) {
                        return false;
                    }

                    $string = "{$name} in ({$in})";
                }

                break;
        }

        return $string;
    }
    /**
     * 查询条件识别
     * @param undefined $where
     * @param undefined $type
     *
     * @return
     */
    private function wherearray($where, $type = '=')
    {

        $wherestr = ' ';
        $and = 'and ';
        if (is_array($where)) {
            foreach ($where as $index => $val) {

                if ($val !== '') {

                    if (is_array($type) && isset($type[$index])) {
                        $str = $this->setstr($index, $val, $type[$index]);
                    } else {
                        if (is_string($type)) {

                            $str = $this->setstr($index, $val, $type);
                        } else {
                            $str = $this->setstr($index, $val);
                        }
                    }

                    /* $str = $this->setstr($index,$val,$type[$index]);*/
                    if ($str != '') {

                        $wherestr .= $and . "($str)";
                    }
                }
            }
            $wherestr = trim($wherestr, $and);
        } else {
            $wherestr = $where;
        }
        return $wherestr;
    }
    public function w($where, $operator = null, $type = 1, $andor = 'and', $break =
    0)
    {

        $w = $this->wherearray($where, $operator);

        if ($type) {
            $this->w = $w;
        } else {
            if ($this->w == null) {
                $this->w .= $w;
            } else {

                if ($w) {
                    $this->w .= " " . $andor . '  (' . $w . ') ';
                }
            }
        }

        return $this;
    }
    /**
     * 获取表结构主键（使用缓存 ）
     *
     * @return string
     */
    public function getkey()
    {

        if ($this->pri_key == null) {
            $ar = $this->gettableinfo();

            foreach ($ar as $key => $v) {
                if ($v['Key'] == 'PRI') {
                    $this->pri_key = $v['Field'];
                    return $this->pri_key;
                }
            }
        }
        return $this->pri_key;
    }
    public function k()
    {
        return $this->getkey();
    }
    /**
     * 获取表所有健名
     * @param boolean $p
     * 1为所有字段取值，包括连表字段，0表示仅仅取主表
     * @return array
     */
    public function getfiled($p = 1)
    {

        if ($this->notgetkey) {
            return false;
        }
        if ($p == 1) {
            $key = &$this->table_all_key;
        } else {
            $key = &$this->table_key;
        }

        $b = array();
        if ($p == 1) {
            $sql = "select * from " . $this->tablename . ' as v ' . $this->j . ' limit  1';
            $ar = $this->_db->getone($sql);
        }

        if ($p == 1 && is_array($ar)) {
            foreach ($ar as $key => $v) {
                if (!in_array($key, $b)) {
                    array_push($b, $key);
                }
            }
        } else {

            $ar = $this->gettableinfo();

            foreach ($ar as $key => $v) {

                if ($p == 0) {
                    if ($v['Key'] != 'PRI' && $v['Extra'] != 'auto_increment') {
                        array_push($b, $v['Field']);
                    }
                } else {
                    array_push($b, $v['Field']);
                }
            }
        }
        $key = array_unique($b);

        return $key;
    }
    /**
     * 获取表结构
     *
     * @return array
     */
    private function gettableinfo()
    {

        $tbname = $this->tablename;
        $sql = 'DESCRIBE ' . $tbname;
        $index = md5($sql);

        /*if(self::$loopcache>self::LOOPMAX)return false;*/
        //这里的缓存必须非mysql缓存；否则死循环
        // $cache = new \ng169\cache\File;
        $cache = Y::$cache;
        list($bool, $data) = $cache->get($index);

        if (!$bool) {
            $data = $this->_db->query($sql);
            $cache->set($index, $data);
        }
        /* d($data);*/
        return $data;
    }
    public function f($p = 1)
    {

        return $this->getfiled($p);
    }
    /**
     * 直接查询sql
     * @param string $sql
     *
     * @return string 查询的数据
     */
    public function query($sql, $cache = false)
    {
        $this->q($sql);
    }
    public function q($sql, $cache = false)
    {
        $res = $this->_db->query($sql);

        return $res;
    }
    /**
     *排序
     * @param undefined $order
     *
     * @return
     */
    public function order($order)
    {
        $this->b($order);
    }
    public function getbs()
    {

        return $this->bs;
    }
    public function b($order)
    {
        $this->oldbs = $this->b;
        $word = ' order by ';
        if (is_array($order)) {

            switch (isset($order['s']) ? $order['s'] : '') {
                case 'up':
                    $bword = 'ASC';
                    break;
                case 'u':
                    $bword = 'ASC';
                    break;

                case 'down':
                    $bword = 'DESC';
                    break;
                case 'd':
                    $bword = 'DESC';
                    break;
                default:
                    $bword = 'ASC';
                    break;
            }
            $this->bs = $bword;

            $tablekey = $this->getfiled(1);
            $b = null;
            $bb = '';
            if (isset($order['f']) && is_array($order['f'])) {

                foreach ($order['f'] as $key => $b1) {
                    $b1 = trim($b1, '`');
                    $field = explode('.', $b1);

                    if (in_array($field[0], $tablekey) || in_array($field[1], $tablekey)) {
                        $b = $this->fix($b1);

                        if (isset($order['s']) && is_array($order['s'])) {
                            switch ($order['s'][$key]) {
                                case 'up':
                                    $bwordtmp = 'ASC';
                                    break;
                                case 'down':
                                    $bwordtmp = 'DESC';
                                    break;
                                default:
                                    $bwordtmp = 'ASC';
                                    break;
                            }
                            $bb .= ", " . $b . ' ' . $bwordtmp . ' ';
                        } else {

                            $bb .= ", " . $b . ' ' . $bword . ' ';
                        }
                    }
                }
                $bb = trim($bb, ",");
                if ($bb) {
                    $this->b = $word . $bb . ' ';
                }
            } else {
                if (isset($order['f']) && $order['f'] != '') {
                    $field = explode('.', $order['f']);
                    $b = $order['f'];
                    $this->b = $word . ' ' . $order['f'] . ' ' . $bword;
                }
            }
        } else {
            if ($order) {
                $this->b = $word . $order;
            }
        }
        $this->bsword = $b;
        return $this;
    }

    public function fixby()
    {
        $this->b = $this->oldbs;
        return $this;
    }
    public function getbyword()
    {
        return $this->bsword;
    }
    public function limit($limit, $key = null)
    {
        $this->l($limit);
    }

    public function l($limit, $key = null, $fh = null)
    {
        $this->havepage = true;

        if (!DB_OLD_LIMIT) {

            $word = ' limit ';
            if (!$limit) {
                return false;
            }

            if (!is_array($limit)) {

                $this->l = $word . intval($limit);
                return $this;
            }
            $this->thispage = $limit[0];

            if (!$key) {
                $key = 'v.' . $this->getkey();
            }
            $get = get(['int' => ['offset']]);
            if (isset($get['offset']) && $get['offset']) {
                $offset = (int) $get['offset'];
            } else {
                $offset = $limit[0] * $limit[1];
            }

            if ($this->getbs() == 'ASC') {
                $fh = ">";
            } else {
                $fh = "<";
            }
            $offset = (int) @$offset;
            if ($offset > 0) {
                $w = "$key $fh {$offset}";
                $this->set_limit_where($w);
            }

            $this->l = $word . intval($limit[1]);

            return $this;
        } else {
            $l = null;
            $word = ' limit ';
            if (!is_array($limit) && $limit) {
                $l = $word . intval($limit);
                $this->l = $l;
                return $this;
            }
            if (count($limit) != 1) {
                $l = $word . intval($limit[0] * $limit[1]) . ',' . intval($limit[1]);
                $this->l = $l;
            } else {
                $l = $word . intval($limit[0]);
                $this->l = $l;
            }
            return $this;
        }
    }

    private function set_limit_where($where)
    {
        $this->limit_where = $where;
    }

    public function g($name)
    {
        $word = ' group by ';
        $this->g = $word . $name;
        return $this;
    }
    /**
     * 删除
     *
     * @return row
     */
    public function del()
    {
        $this->d();
    }
    /**
     * 删除
     *
     * @return row
     */
    public function d()
    {
        $Stime = 0;
        if ($this->debug) {

            $Stime = microtime(true);
        }
        $w = $this->w ? ' where  ' . $this->w : ' ';

        //        if(trim($w)=='')return false;//防止清空数据
        $sql = $this->t . $w;
        $sql = str_replace('select *,v.*', ' delete ', $sql);
        $sql = str_replace('as v', '', $sql);
        $sql = str_replace('`v`.', '', $sql);
        $ret = $this->_db->exec($sql);;
        $this->showsqllog($sql, $Stime);
        return $ret;
    }
    public function showsqllog($sql, $time, $debug = false)
    {
        if ($this->debug || $debug) {

            $Etime = microtime(true);
            $Ttime = $Etime - $time;
            $str_total = var_export($Ttime, true);
            if (substr_count($str_total, "E")) {
                $float_total = floatval(substr($str_total, 5));
                $Ttime = $float_total / 100000;
            }
            $toms = round($Ttime * 1000, 2);
            d("\n语句:" . $sql . "\n耗时:" . $toms . '毫秒', null, false, 3);
        }
    }
    public function clear()
    {

        $w = $this->w ? ' where  ' . $this->w : ' ';

        $sql = $this->t . $w;
        $sql = str_replace('select *,v.*', ' delete ', $sql);
        $sql = str_replace('as v', '', $sql);
        $sql = str_replace('`v`.', '', $sql);

        return $this->_db->exec($sql);
    }
    /**
     * 插入
     * @param undefined $t
     * @param undefined $ar
     * @param undefined $auto
     *
     * @return
     */
    public function i($t, $ar, $auto = 1)
    {
        $Stime = 0;
        if ($this->debug) {

            $Stime = microtime(true);
        }
        $t = $this->dbqz . $t;
        $name = null;
        $value = null;
        $douhao = ',';

        foreach ($ar as $index => $val) {
            $name .= "`" . $index . "`" . $douhao;
            if ($val == '') {
                $val = 0; //修复高版本mysql不能为空
            }
            $val = (addslashes($val));
            $value .= "'" . $val . "'" . $douhao;
        }
        $name = trim($name, $douhao);
        $value = trim($value, $douhao);

        $sql = "insert into $t ($name) values ($value)";
        $ret = $this->_db->insert($sql);
        $this->showsqllog($sql, $Stime);
        return $ret;
        /*return $this->_db->exec($t, $ar, $auto);*/
    }

    public function insert($t, $ar)
    {
        $this->i($t, $ar);
    }
    public function updata($t, $ar, $where = null)
    {

        return $this->u($t, $ar, $where);
    }
    public function u($t, $ar, $where = null, $bool = true)
    {
        if (sizeof($ar) == 0) {
            return 0;
        }
        $Stime = '';
        if ($this->debug) {

            $Stime = microtime(true);
        }
        $t = $this->dbqz . $t;
        $name = null;
        $value = null;
        $douhao = ',';
        if (is_array($ar)) {
            foreach ($ar as $index => $val) {
                if ($bool) {
                    $name .= "`" . $index . "`='" . $val . "'" . $douhao;
                } else {
                    $name .= "`" . $index . "`=" . $val . "" . $douhao;
                }
            }
            $name = trim($name, $douhao);
        } else {
            if (!$ar) {
                //更新为空退出
                return false;
            }
            $name = ' ' . $ar . ' ';
        }


        $sql = "update  $t set $name ";

        if ($where) {
            $where = $this->_arraytostring($where);
        }

        if ($where) {
            $sql = $sql . " where " . $where;
        }
        if (!$where && $this->w) {
            $sql = $sql . " where " . $this->w;
        }
        //  d($sql);

        $ret = $this->_db->exec($sql);
        $this->showsqllog($sql, $Stime);
        return $ret;
    }
    private function _arraytostring($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        $string = '';
        if (is_array($array) && sizeof($array)) {
            foreach ($array as $key => $w1) {
                $key = $this->fix($key, 0);
                if (is_array($w1)) {
                    $andor = 'and';
                    $operator = '=';
                    $w = '';
                    switch (sizeof($w1)) {
                        case '0':
                            break;
                        case '1':
                            if (isset($w1[0])) {

                                if ($operator == ' = ') {
                                    $w .= ' ' . $andor . " {$key}>='{$w1[0]}'  ";
                                } else {
                                    $w .= ' ' . $andor . " {$key}{$operator}'{$w1[0]}'  ";
                                }
                            } else {
                                $w .= ' ' . $andor . " {$key}<={$w1[1]}  ";
                            }
                            break;

                        default:
                            $z = '';
                            foreach ($w1 as $v) {
                                $z .= " or {$key}{$operator}'{$v}'  ";
                            }
                            $z = trim($z);
                            $z = trim($z, 'or');
                            $z = " (" . $z . ")";
                            $w .= ' ' . $andor . $z;
                            break;
                    }
                    $string .= $w;
                } else {
                    if (preg_match('/\>|\<|like\s|=|in\s|between\s|not\s/', strtolower($w1))) {
                        $string .= " and " . $key . $w1;
                    } else {
                        $string .= " and $key='$w1'";
                    }
                }
            }
        }
        $string = trim(trim($string), 'and');
        return $string;
    }
    public function starttransaction()
    {
        return $this->_db->starttransaction();
    }
    public function commit()
    {
        return $this->_db->commit();
    }
    public function rollback()
    {
        return $this->_db->rollback();
    }
}
