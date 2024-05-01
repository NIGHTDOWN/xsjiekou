<?php


namespace ng169\model\admin;
use ng169\Y;


checktop();
class backstage_menu extends Y {
    
    private $db_name="backstage_menu";
    public function getone($where){
        $db=T($this->db_name);
        return $db->get_one($where);
    }
    public function del($where){
        
    }
    public function save($inset,$where=null){
       
        $seo=T($this->db_name);
        $inset['depth']=$this->get_depth($inset['parentid']);
    
        if($seo->check_exist($where) && is_array($inset)&&$where!=null){  
            $b=$seo->updata(array('v'=>$inset,'w'=>$where));
            
        }else{
            $other=array('addtime'=>time(),'creatid'=>parent::$wrap_admin['adminid']);
            $data=array_merge($inset,$other);
            $b=$seo->add($data);
        }
//        M('log','am')->log($b,$where,$insert);
        return $b;
    }
    public function get_depth($id){
        
        if(!$id){
            return 1;
        }else{
            $where=array('catid'=>$id);
            $depth= T($this->db_name)->set_field(array('depth'))->get_one($where);
            

            if($depth){
               
                return $depth['depth']+1;
            }else{
                return 1;
            }
        }
        
    }
    
}
?>
