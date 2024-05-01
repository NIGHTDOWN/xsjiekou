<?php


namespace  ng169\hook;
use ng169\Y;
use ng169\TPL;

checktop();

function count_chart($extracts='') {
    $params = YHandle::buildTagArray($extracts);
    if (!empty($params)) {
        @extract($params);
        $mod = empty($params['mod']) ? '' : strtolower(trim($params['mod'])); 
        $type = empty($params['type']) ? '' : strtolower(trim($params['type'])); 
		#SQL where 查询条件 过滤注入标识
        $where = empty($params['where']) ? '' : YFilter::filterSql(trim($params['where']));

		
        if ($mod == 'customer') {
			$m_customer = M('customer', 'am');
			return $m_customer->countReport($type, $where);
			unset($m_customer);
		}
		
		elseif ($mod == 'contact') {
			$m_contact = M('contact', 'am');
			return $m_contact->countReport($type, $where);
			unset($m_contact);
		}
        
        elseif ($mod == 'order') {
            $m_order = M('order', 'am');
            return $m_order->countReport($type, $where);
            unset($m_order);
        }
        
        elseif ($mod == 'admin') {
            $m_admin = M('admins', 'am');
            return $m_admin->countReport($type, $where);
            unset($m_admin);
        }
        
        elseif ($mod == 'project') {
            $m_project = M('project', 'am');
            return $m_project->countReport($type, $where);
            unset($m_project);
        }
        
        elseif ($mod == 'task') {
            $m_task = M('task', 'am');
            return $m_task->countReport($type, $where);
            unset($m_task);
        }
		
		elseif ($mod == 'tasklog') {
			$m_tasklog = M('tasklog', 'am');
            return $m_tasklog->countReport($type, $where);
            unset($m_tasklog);
		}
        
        elseif ($mod == 'gather') {
            $m_gather = M('gather', 'am');
            return $m_gather->countReport($type, $where);
            unset($m_gather);
        }
        
        elseif ($mod == 'gatherlog') {
            $m_gatherlog = M('gatherlog', 'am');
            return $m_gatherlog->countReport($type, $where);
            unset($m_gatherlog);
        }
        
        elseif ($mod == 'charge') {
            $m_charge = M('charge', 'am');
            return $m_charge->countReport($type, $where);
            unset($m_charge);
        }
        
        elseif ($mod == 'chargelog') {
            $m_chargelog = M('chargelog', 'am');
            return $m_chargelog->countReport($type, $where);
            unset($m_chargelog);
        }
        
        elseif ($mod == 'billing') {
            $m_billing = M('billing', 'am');
            return $m_billing->countReport($type, $where);
            unset($m_billing);
        }

	}
}


function draw_chart($extracts='') {
    $params = YHandle::buildTagArray($extracts);
    if (!empty($params)) {
        @extract($params);
        $mod = empty($params['mod']) ? '' : strtolower(trim($params['mod'])); 
		$contday = empty($params['contday']) ? '' : strtolower(trim($params['contday'])); 
        $type = empty($params['type']) ? '' : strtolower(trim($params['type'])); 
		#SQL where 查询条件 过滤注入标识
        $where = empty($params['where']) ? '' : YFilter::filterSql(trim($params['where']));

		
        if ($mod == 'customer') {
			$m_customer = M('customer', 'am');
			return $m_customer->drawCharts($contday, $type, $where);
			unset($m_customer);
		}
		
		elseif ($mod == 'contact') {
			$m_contact = M('contact', 'am');
			return $m_contact->drawCharts($contday, $type, $where);
			unset($m_contact);
		}
        
        elseif ($mod == 'order') {
            $m_order = M('order', 'am');
            return $m_order->drawCharts($contday, $type, $where);
            unset($m_order);
        }
        
        elseif ($mod == 'project') {
            $m_project = M('project', 'am');
            return $m_project->drawCharts($contday, $type, $where);
            unset($m_project);
        }
        
        elseif ($mod == 'task') {
            $m_task = M('task', 'am');
            return $m_task->drawCharts($contday, $type, $where);
            unset($m_task);
        }
		
		elseif ($mod == 'tasklog') {
			$m_tasklog = M('tasklog', 'am');
            return $m_tasklog->drawCharts($contday, $type, $where);
            unset($m_tasklog);
		}
        
        elseif ($mod == 'gather') {
            $m_gather = M('gather', 'am');
            return $m_gather->drawCharts($contday, $type, $where);
            unset($m_gather);
        }
        
        elseif ($mod == 'gatherlog') {
            $m_gatherlog = M('gatherlog', 'am');
            return $m_gatherlog->drawCharts($contday, $type, $where);
            unset($m_gatherlog);
        }
        
        elseif ($mod == 'charge') {
            $m_charge = M('charge', 'am');
            return $m_charge->drawCharts($contday, $type, $where);
            unset($m_charge);
        }
        
        elseif ($mod == 'chargelog') {
            $m_chargelog = M('chargelog', 'am');
            return $m_chargelog->drawCharts($contday, $type, $where);
            unset($m_chargelog);
        }
        
        elseif ($mod == 'billing') {
            $m_billing = M('billing', 'am');
            return $m_billing->drawCharts($contday, $type, $where);
            unset($m_billing);
        }

	}
}



function get_diff_months($extracts='') {
    $params = YHandle::buildTagArray($extracts);
    if (!empty($params)) {
        @extract($params);
        $num = empty($params['num']) ? '' : intval($params['num']); 
        $type = empty($params['type']) ? '' : intval($params['type']); 
        if ($num <= 0) {$num = 6;}
        return XDate::getDiffMonths($num, $type);
    }
}


function get_cont_months($extracts='') {
    $params = YHandle::buildTagArray($extracts);
    if (!empty($params)) {
        @extract($params);
        $startmonth = empty($params['startmonth']) ? '' : trim($params['startmonth']); 
        $endmonth = empty($params['endmonth']) ? '' : trim($params['endmonth']); 
        return XDate::getContMonths($startmonth, $endmonth);
    }
}
?>
