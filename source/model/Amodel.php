<?php

namespace ng169\model;

use ng169\db\daoClass;
use ng169\Y;

checktop();

class Amodel extends Y
{
    private $t;
    public $table;
    private $dbdao;

    public function __construct($table, $filedar = null,$dbconf=null)
    {

        $this->t = $table;
      
        $this->dbdao = new daoClass($dbconf);
        $this->table = $this->dbdao->t($this->t, $filedar);

    }
    public function getby()
    {

        return $this->table->getbs();
    }
    public function getbyword()
    {

        return $this->table->getbyword();
    }
    public function get_tree_depth($new_parentid, $index = 'catid')
    {
        $id = $this->set_field(array('depth'))->get_one(array($index => $new_parentid));
        if (is_array($id) && sizeof($id)) {
            if ($id['depth'] == 4) {
                out('本系统目前只支持四级节点', null, 0);
            }
            return $id['depth'];
        } else {
            return 0;
        }
    }

    public function get_all_tree_id($id, $index = 'catid')
    {
        $where = array();
        $del_id = array();

        if (is_array($id)) {
            foreach ($id as $i) {
                $child = $this->get_child($index, $i);

                if (is_array($child) && sizeof($child) > 0) {
                    foreach ($child as $v) {
                        array_push($del_id, $v[$index]);
                    }
                }
            }
            $del_id = array_unique(array_merge($id, $del_id));
        } else {
            $child = $this->get_child($index, $id);

            if (is_array($child) && sizeof($child) > 0) {
                foreach ($child as $v) {
                    array_push($del_id, $v[$index]);
                }
            }
            array_push($del_id, $id);
            $del_id = array_unique($del_id);
        }

        return $del_id;

    }

    public function change_tree($source_id, $new_parentid = 0, $index = 'catid')
    {
        $mod = $this;

        if ($new_parentid == $source_id) {
            out('保存失败,不能移动到自己的节点下面', null, 0);
        }

        $new_depth = $this->get_tree_depth($new_parentid, $index) + 1;

        $where = array($index => $source_id);
        $Original_depth = $mod->set_field(array('depth'))->get_one($where);
        $Change_depth = $new_depth - $Original_depth['depth'];

        $child = $mod->set_field(array($index))->get_child($index, $source_id);

        if (is_array($child) && sizeof($child) > 0) {
            foreach ($child as $id) {
                if ($new_parentid == $id[$index]) {
                    out('保存失败,不能移动到自己的节点下面', null, 0);
                }
            }
            foreach ($child as $id) {

                $depth_where = $id;
                $depth_change = array('depth' => "depth+{$Change_depth}");

                $mod->updata(array('v' => $depth_change, 'w' => $depth_where), false);
            }
        }

    }

    public function group_by($name)
    {
        $this->table = $this->table->g($name);
        return $this;
    }

    public function join_table($join, $type = false)
    {
        $this->table = $this->table->j($join, $type);
        return $this;
    }

    public function order_by($order)
    {
        $this->table = $this->table->b($order);
        return $this;
    }
    public function fixby()
    {
        $this->table = $this->table->fixby();
        return $this;
    }

    public function get_count($whereArr = null)
    {
        if ($whereArr != null) {

            return $this->table->w($whereArr)->s('2');
        } else {
            return $this->table->s('2');
        }
    }
    public function get_count2()
    {
        return $this->table->s('3');

    }

    public function get_groom($whereArr = null)
    {
        $whereArr['order'] = '1';
        return $this->table->w($whereArr)->s('1');
    }

    public function get_one($idArr = null, $bool = 0, $sql = null)
    {
        if (!$idArr) {
            return $this->table->s('1', $bool, null, $sql);
        }

        return $this->table->w($idArr)->s('1', $bool);
    }
    public function set_where($whereArr, $operator = null, $type = 0, $andor = 'and')
    {

        $this->table = $this->table->w($whereArr, $operator, $type, $andor);

        return $this;
    }
    public function where($whereArr, $operator = null, $type = 0, $andor = 'and'){

		return $this->set_where($whereArr, $operator, $type, $andor);
		// $this;

	}
    public function union($sql, $sq2)
    {

        $this->table = $this->table->union($sql, $sq2);
        return $this;

    }
    public function unionall($sql, $sq2)
    {

        $this->table = $this->table->unionall($sql, $sq2);
        return $this;

    }
    public function search($field, $str)
    {
        if (!$str) {
            return $this;
        }

        $this->table = $this->table->w("match({$field}) against ('*{$str}*' IN BOOLEAN MODE)");

        return $this;
    }
    public function set_global_where($whereArr, $operator = null, $type = 0, $andor = 'and')
    {

        if (is_array($whereArr) && sizeof($whereArr) > 0) {
            $this->table = $this->table->Gw($whereArr, $operator, $type, $andor);

        }

        return $this;
    }

    public function get_sql()
    {
        return $this->table->s('4');
    }
    public function set_limit($limit, $key = null, $fh = null)
    {

        $this->table = $this->table->l($limit, $key, $fh);
        return $this;
    }

    public function get_all($whereArr = null, $bool = null, $sql = null)
    {

        if ($whereArr != null) {
            if ($this->table->b == null) {
                $a = $this->table->w($whereArr)->s('0', $bool, null, $sql);
            } else {
                $a = $this->table->w($whereArr)->s('0', $bool);

            }

        } else {

            if ($this->table->b == null) {
                $a = $this->table->b(array('s' => 'down'))->s('0', $bool, null, $sql);
            } else {
                $a = $this->table->s('0', $bool);
            }

        }
        return $a;
    }

    public function add($inArr, $auto = 1)
    {
        return $this->dbdao->i($this->t, $inArr);
    }
    public function addid($inArr, $auto = 1)
    {
        $ids = T($this->t)->set_field('id')->order_by(['s' => 'down', 'f' => 'id'])->get_one();
        $id = $ids['id'] + 1;
        $inArr['id'] = $id;
        $f = $this->dbdao->i($this->t, $inArr);
        return $id;
        // if ($f) {
        //     return $id;
        // } else {
        //     return false;
        // }

    }

    public function updata($inArr, $bool = true)
    {

        return $this->dbdao->u($this->t, $inArr['v'], $inArr['w'], $bool);
    }
    /**
     *
     * @param undefined $insert 更新数据
     * @param undefined $where 条件
     * @param undefined $bool
     *
     * @return boolean
     */
    public function update($insert, $where, $bool = 1)
    {

        return $this->dbdao->u($this->t, $insert, $where, $bool);
    }

    public function del($whereArr = null, $op = null)
    {
        if ($whereArr == null) {

            return $this->dbdao->t($this->t)->d();
        } else {

            return $this->dbdao->t($this->t)->w($whereArr, $op)->d();
        }

    }

    public function delfile($whereArr, $field_arr)
    {
        $files = $this->dbdao->t($this->t)->set_field($field_arr)->w($whereArr, null, 1, 'and', 1)->s(0);
        $this->_delfile($files);
    }
    private function _delfile($file)
    {
        if (is_array($file)) {
            foreach ($file as $files) {
                $this->_delfile($files);
            }
        } else {
            $val = explode(',', $file);
            foreach ($val as $f) {
                @unlink($f);
            }
        }
    }

    public function sort($type, $where)
    {
        switch ($type) {
            case 'up':
                return $this->dbdao->u($this->t, array('orders' => 'orders+1'), $where);
            case 'down':
                return $this->dbdao->u($this->t, array('orders' => 'orders-1'), $where);
            case '1':
                return $this->dbdao->u($this->t, array('orders' => 'orders+1'), $where);
            case '0':
                return $this->dbdao->u($this->t, array('orders' => 'orders-1'), $where);

        }
    }
    public function set_field($filedar, $flag = 1)
    {
        $this->table = $this->table->set_field($filedar, $flag);

        return $this;
    }

    public function get_child($index, $id = 0, $parent = 'parentid', $cache = 1)
    {

        if (is_array($index)) {

            return $this->table->get_child($index[0], $index[1], $index[2], $cache);
        }

        return $this->table->get_child($index, $id, $parent, $cache);

    }
    public function get_primarykey()
    {
        return $this->table->k();
    }

    public function get_field()
    {
        return $this->table->f();

    }

    public function check_filed($filedar, $wherear)
    {
        $get = $this->table->w($wherear)->s('1');
        foreach ($filedar as $key => $value) {
            if ($get[$key] != $value) {
                return false;

            }

        }
        return true;
    }

    public function check_exist($filedar)
    {
        $get = $this->table->w($filedar)->s('1');
        if ($get) {
            return $get;
        } else {
            return false;
        }
    }
}
