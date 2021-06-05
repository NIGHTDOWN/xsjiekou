<?php



namespace  ng169\hook;
use ng169\Y;
use ng169\TPL;

checktop();

function hook($extracts)
{

    $params = YHandle::buildTagArray($extracts);
   
    if (!empty($params)) {
        @extract($params);
        $mod = empty($params['mod']) ? '' : strtolower(trim($params['mod']));
        $fun = empty($params['fun']) ? 'run' : strtolower(trim($params['fun']));
        #类型 备用
        $type = empty($params['type']) ? '' : strtolower(trim($params['type']));
        #SQL where 查询条件 过滤注入标识
        $where = empty($params['where']) ? '' : (($params['where']));
        #orderby SQL排序
        $orderby = empty($params['orderby']) ? '' : ($params['orderby']);
        #LIMIT SQL语句
        $limit = empty($params['limit']) ? '' :(($params['limit']));
        
        $num = empty($params['num']) ? 0 : intval($params['num']);
        
        $childnum = empty($params['childnum']) ? 0 : intval($params['childnum']);
        
        $catid = empty($params['catid']) ? 0 : intval($params['catid']);
        
        $value = empty($params['value']) ? 0 : intval($params['value']);
        
        $rootid = empty($params['rootid']) ? 0 : intval($params['rootid']);
        
        $child = empty($params['child']) ? 0 : intval($params['child']);
        #hook var
        $param1 = empty($params['param1']) ? null : trim($params['param1']);
        $param2 = empty($params['param2']) ? null : trim($params['param2']);
        $param3 = empty($params['param3']) ? null : trim($params['param3']);
        $param4 = empty($params['param4']) ? null : trim($params['param4']);
        $array = empty($params['array']) ? null : $params['array'];
        $link = empty($params['link']) ? null : $params['link'];
        $field = empty($params['field']) ? null : $params['field'];
        $select = empty($params['select']) ? 0 : intval($params['select']);
        $html = empty($params['html']) ? 0 : ($params['html']);
        $name = empty($params['name']) ? 0 : ($params['name']);
        $empty = empty($params['empty']) ? 0 : ($params['empty']);
        
        $v = empty($params['svalue']) ? 'catid' : ($params['svalue']);
        
       
        $n = empty($params['sname']) ? 'catname' : ($params['sname']);
        
        $value = null;
        
      
        if ($mod != null && $type == null) {

            $table = T($mod);

        } elseif ($mod != null && $type != null) {
            $table = M($mod, $type);
        }

        if (gettype($table) != object) {
            error("{$mod}模型调用错误");
        }
        
        if (method_exists($table, $fun)) {
            
            if (strrpos('get', $fun)) {
                error("{$mod}模型{$fun}不可调用");
            }

        } else {
            error("{$mod}模型{$fun}不存在");
        }
        if ($link) {
            
            if (method_exists($table, 'join_table')) {
                $table = $table->join_table($link);
            }
        }
        if ($orderby) {
            if (method_exists($table, 'order_by')) {
                $table = $table->order_by($orderby);
            }
        }
        if ($num) {
            if (method_exists($table, 'set_limit')) {
                $table = $table->set_limit($num);
            }
        }
        
        if ($limit) {
            
            if (method_exists($table, 'set_limit')) {
                
                $table = $table->set_limit($limit);
            }
        }
        if ($field) {
            $value = $table->set_field($field);
        }
        if ($array || $where) {
            $where=$where?$where:$array;
            $value = $table->$fun($where);
        }
        if ($param1) {
            $value = $table->$fun($param1, $param2, $param3, $param4);
        }
        if (!$param1 and !$array) {
            $value = $table->$fun();
        }
        unset($table);
     
        if ($value) {

            if ($num == 0 || $num == '') {

                $return=$value;
            } else {

                $return=array_slice($value, 0, $num);
            }
           
            if(is_array($return) ){

                switch ($html)
                {
                    case "select":
                        $head='<select name="'.$name.'">';
                        $tail='</select>';
                        foreach($return as $key){
                            if($key[$v]==$select){
                                $selecth=" selected ";
                            }else{
                                $selecth='';
                            }
                            $qz=array('','','| --','||-- --','||| -- -- --');
                          
                            $body.="<option value='".$key[$v]."' ".$selecth." >".$qz[$key['depth']].$key[$n]."</option>"; 
                        }
                        if($empty){$body="<option value=''>".$empty."</option>".$body;}
                        $html=$head.$body.$tail;
                        break;
                }
                return   $html;
            }
        }
    }
}

?>
