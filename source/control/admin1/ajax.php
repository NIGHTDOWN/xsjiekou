<?php


namespace ng169\control\admin;

use ng169\control\adminbase;

checktop();
class ajax extends adminbase
{
    private $a = null;
    private $c = null;
    private $tableobj = null;
    private $pri = null;
    public function _run($mod)
    {
        if ($mod != '' || $mod != 'undefined') {
            $this->c = $mod;
        } else {
           $c=D_MEDTHOD;	$a=D_FUNC;
            $this->a = $a;
            $this->c = $c;
        }      
        if ($this->c) {
            $this->tableobj = T($this->c);
        }
        $this->pri = $this->tableobj->get_primarykey();
    }
    public function control_run()
    {
        $table = G(array('string' => array('who')))->get();
        if ($table['who'] != 'undefined') {
       
            $this->_run($table['who']);
        }
        $where = G(array('int' => array('key' => 1)))->get();
        $cg = G(array('string' => array('name', 'value')))->get();

        $cg['name'] = trim($cg['name']);
        $cg['value'] = trim($cg['value']);
        $set = array($cg['name'] => $cg['value']);
        
        $debar =array('money','cash','integral');
        if (in_array($cg['name'], $debar)) {
            out('更新失败',null,0);
        }
        $boolarr = array(
            'flag',
            'elite',
            'canbes',
            'cansignup',
            'emailrz',
            'mobilerz',
            'useurl',
            'urlflag',
            'ishot',
            'usestatus');
       
        if (in_array($cg['name'], $boolarr)) {
            $set[$cg['name']] = "abs({$cg['name']}-1)";
        }
        $where = array($this->pri => $where['key']);
        
        if ($this->tableobj->get_one($where)) {
        } else {
            $this->tableobj->add($where);
        }
        $insert=array('v' => $set, 'w' => $where);
        if (in_array($cg['name'], $boolarr)) {
            $b = $this->tableobj->updata($insert, false);
           
        } else {
            $b = $this->tableobj->updata($insert);
          
        }
        $ret = (bool)$b;
        upcache($table['who']);
        $set['idfity'] = 'ajax';
        M('log','am')->log($ret,$where,$insert,parent::$wrap_admin['adminid']);
        if($ret){
            $mark='成功';
        }else{
            $mark='操作失败';
        }
        out($mark,null,$ret);
        die();
    }
}

?>
