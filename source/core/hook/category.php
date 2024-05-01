<?php


namespace  ng169\hook;
use ng169\Y;
use ng169\TPL;
checktop();

function hook_get_category($params) {
    if (!empty($params)) {
        @extract($params);
		$type = empty($params['type']) ? 'url' : trim($params['type']);
		$id = intval($params['id']);
		$name = empty($params['name']) ? '' : trim($params['name']);
		$class = empty($params['class']) ? '' : trim($params['class']);
		$target = empty($params['target']) ? '_self' : trim($params['target']);
		$title = empty($params['title']) ? '' : trim($params['title']);
        #catalog栏目URL路由
		if ($type == 'url') {
            #栏目标识
			if (true === YValid::isSpChar($name)) {
				$val = $name;
				$valtype = 2;
			}
            #栏目ID
			else {
				$val = $id;
				$valtype = 1;
			}
			$cat_model = X::model('category', 'im');
			return $cat_model->getCategoryUrl($val, $valtype, $title, $class, $target);
			unset($cat_model);
		}
    } 
}
TPL::regFunction('category', 'hook_get_category');


function vo_category($extracts) {
    $params = YHandle::buildTagArray($extracts);
    if (!empty($params)) {
        @extract($params);
		#钩子类型
		$type = strtolower(trim($params['type']));
        #参数组合
        $args = array();
        if (true === YValid::isSpChar($params['module'])) {
            
            $args['module'] = trim($params['module']);
        }
        if (true === YValid::isNumber($params['treeid'])) {
            $args['treeid'] = $params['treeid'];
        }
        if (true === YValid::isNumber($params['rootid'])) {
            $args['rootid'] = $params['rootid'];
        }
        if (true === YValid::isNumber($params['ismenu'])) {
            $args['ismenu'] = $params['ismenu'];
        }
        if (true === YValid::isNumber($params['isaccessory'])) {
            $args['isaccessory'] = $params['isaccessory'];
        }
		if (true === YValid::isNumber($params['num'])) {
			$args['num'] = $params['num'];
		}
        #-----新参数 BeGIN 2013.09.25------#
		$where = YFilter::filterSql(trim($params['where']));
		$orderby = YFilter::filterSql(trim($params['orderby']));
		$num = intval($params['num']);
        $limit = empty($params['limit']) ? '' : YFilter::filterSql(trim($params['limit']));
        #-----新参数 End 2013.09.25------#
        
        $cat_model = X::model('category', 'im');
        #一级导航
        if ($type == 'rootmenu') {
            return $cat_model->rootMenu();
        }
        #一级+二级导航
        elseif ($type == 'sedmenu') {
			return $cat_model->sedMenu();
        }
		#底部附加导航
		elseif ($type == 'submenu') {
			return $cat_model->subMenu($args);
		}
		#模块一级分类
		elseif ($type == 'rootcat') {
			return $cat_model->rootCategory($args);
		}
		#模块一级+二级分类
		elseif ($type == 'treecat') {
			return $cat_model->treeCategory($args);
		}
        elseif ($type == 'left') {
			return $cat_model->getrootid();
		}
        #万能volist导航 2013.09.25
        elseif ($type == 'volist') {
            return $cat_model->volistAll($where, $orderby, $num, $limit);
        }
		unset($cat_model);
	}
}
?>
