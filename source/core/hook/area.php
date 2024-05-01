<?php


namespace  ng169\hook;
use ng169\Y;
use ng169\TPL;

checktop();

function area()
{
    
    $data = T('area')->set_field(array('areaname', 'areaid' => 'id'))->order_by(array
        ('f' => array('orders','areaname'), 's' => 'up'))->get_all(array('flag' => 0, 'depth' => 1));
    return json_encode($data);
    

}

function areaname($id)
{
    
    $data = T('area')->set_field(array('areaname', 'areaid' => 'id'))->order_by(array
        ('f' => array('orders','areaname'), 's' => 'up'))->get_one(array('flag' => 0, 'depth' => 2,'areaid'=>$id));
    if($data['areaname']){
        return ($data['areaname']);
    }else{
        return '';
    }
    
    

}

function areaid($name)
{

    $data = T('area')->set_field(array('areaname', 'areaid' => 'id'))->order_by(array
        ('f' => array('orders','areaname'), 's' => 'up'))->get_all(array('flag' => 0, 'depth' => 1,'areaname'=>$name));
    if($data['id']){
        return ($data['id']);
    }else{
        return '';
    }

}
TPL::regFunction('areaname', 'areaname');
TPL::regFunction('areaid', 'areaid');
TPL::regFunction('area', 'area');
TPL::regFunction('areahtml', 'areahtml');
function areahtml($name=null,$id=null,$zindex=4,$width=292){
    if($name==null){$name='areaid';}
    $data=area();
    if($id){$cn=getareaname($id);}else{
        $cn='请选择区域';
    }
    $html=' <script>'.
          ' $msg = jta(\''.$data.'\');'.
          ' load_area_box($msg, "'.$cn.'", "'.$name.'", "'.$id.'","'.$zindex.'","'.$width.'");</script>';
    
    return $html;
}
?>
