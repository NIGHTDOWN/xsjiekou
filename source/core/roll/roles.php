<?php





function init_roles_elements() {
	$roles_elements = array();
	$roles_elements[] = array(
		'value'=>'menu_setting', 
		'name'=>'系统设置(左边菜单导航)',
		'base'=>array(
		),
		'other'=>array(
		),
	);

	$roles_elements[] = array(
		'value'=>'setting', 
		'name'=>'基础设置',
		'base'=>array(
			'setting_edit'=>'编辑', 
		),
		'other'=>array(
		),
	);

	$roles_elements[] = array(
		'value'=>'admin', 
		'name'=>'系统用户',
		'base'=>array(
			'admin_view'=>'查看', 
			'admin_add'=>'添加', 
			'admin_edit'=>'编辑', 
			'admin_del'=>'删除', 
		),
		'other'=>array(
			'admin_editpassword'=>'修改密码', 
		),
	);

	$roles_elements[] = array(
		'value'=>'department', 
		'name'=>'部门管理',
		'base'=>array(
			'department_view'=>'查看', 
			'department_add'=>'添加', 
			'department_edit'=>'编辑', 
			'department_del'=>'删除', 
		),
		'other'=>array(
		),
	);

	$roles_elements[] = array(
		'value'=>'roles', 
		'name'=>'角色管理',
		'base'=>array(
			'roles_view'=>'查看', 
			'roles_add'=>'添加', 
			'roles_edit'=>'编辑', 
			'roles_del'=>'删除', 
		),
		'other'=>array(
		),
	);

	$roles_elements[] = array(
		'value'=>'goodscat', 
		'name'=>'产品分类',
		'base'=>array(
			'goodscat_view'=>'查看', 
			'goodscat_add'=>'添加', 
			'goodscat_edit'=>'编辑', 
			'goodscat_del'=>'删除', 
		),
		'other'=>array(
		),
	);

	$roles_elements[] = array(
		'value'=>'goods', 
		'name'=>'产品管理',
		'base'=>array(
			'goods_view'=>'查看', 
			'goods_add'=>'添加', 
			'goods_edit'=>'编辑', 
			'goods_del'=>'删除', 
			'goods_approved'=>'审批', 
		),
		'other'=>array(
		),
	);


	$roles_elements[] = array(
		'value'=>'menu_kehu', 
		'name'=>'客户模块(左边菜单导航)',
		'base'=>array(
		),
		'other'=>array(
		),
	);

	$roles_elements[] = array(
		'value'=>'customer', 
		'name'=>'客户管理',
		'base'=>array(
			'customer_view'=>'查看', 
			'customer_add'=>'添加', 
			'customer_edit'=>'编辑', 
			'customer_del'=>'删除', 
			'customer_approved'=>'审批', 
		),
		'other'=>array(
		),
	);

	$roles_elements[] = array(
		'value'=>'contact', 
		'name'=>'联系人管理',
		'base'=>array(
			'contact_view'=>'查看', 
			'contact_add'=>'添加', 
			'contact_edit'=>'编辑', 
			'contact_del'=>'删除', 
			'contact_approved'=>'审批', 
		),
		'other'=>array(
			'contact_pools'=>'放入客户池',
			'contact_trans'=>'转移客户',
		),
	);

	$roles_elements[] = array(
		'value'=>'pools', 
		'name'=>'客户池管理',
		'base'=>array(
			'pools_view'=>'查看',
		),
		'other'=>array(
			'pools_allot'=>'分配客户',
			'pools_pull'=>'领取客户',
		),
	);

	$roles_elements[] = array(
		'value'=>'contactlog', 
		'name'=>'客户联系记录',
		'base'=>array(
			'contactlog_view'=>'查看',
			'contactlog_add'=>'添加',
			'contactlog_edit'=>'编辑',
			'contactlog_del'=>'删除',
		),
		'other'=>array(
		),
	);

	$roles_elements[] = array(
		'value'=>'order', 
		'name'=>'订单管理',
		'base'=>array(
			'order_view'=>'查看',
			'order_add'=>'添加',
			'order_edit'=>'编辑',
			'order_del'=>'删除',
			'order_approved'=>'审批', 
		),
		'other'=>array(
			'order_status'=>'更改订单状态',
			'order_trans'=>'转移订单',
			'order_annex'=>'管理订单附件',
			'order_gather'=>'创建应收款',
			'order_charge'=>'创建应退款',
			'order_project'=>'创建项目单',
			'order_billing'=>'申请开票',
		),
	);

	$roles_elements[] = array(
		'value'=>'orderlog', 
		'name'=>'订单跟踪记录',
		'base'=>array(
			'orderlog_view'=>'查看',
			'orderlog_add'=>'添加',
			'orderlog_del'=>'删除',
		),
		'other'=>array(
		),
	);

	$roles_elements[] = array(
		'value'=>'project', 
		'name'=>'项目管理',
		'base'=>array(
			'project_view'=>'查看',
			'project_edit'=>'编辑', 
			'project_del'=>'删除',
		),
		'other'=>array(
			'project_status'=>'操作项目状态', 
			'project_task'=>'分配任务', 
			'project_annex'=>'管理项目附件',
		),
	);

	$roles_elements[] = array(
		'value'=>'task', 
		'name'=>'任务管理',
		'base'=>array(
			'task_view'=>'查看',
		),
		'other'=>array(
			'task_start'=>'实施任务', 
			'task_appproved'=>'提交进度申请',
		),
	);


	$roles_elements[] = array(
		'value'=>'tasklog', 
		'name'=>'任务日志管理',
		'base'=>array(
			'tasklog_view'=>'查看',
			'tasklog_add'=>'添加',
			'tasklog_del'=>'删除',
		),
		'other'=>array(
		),
	);


	$roles_elements[] = array(
		'value'=>'menu_caiwu', 
		'name'=>'财务模块(左边菜单导航)',
		'base'=>array(
		),
		'other'=>array(
		),
	);

	$roles_elements[] = array(
		'value'=>'gather', 
		'name'=>'应收款',
		'base'=>array(
			'gather_view'=>'查看',
			'gather_edit'=>'编辑',
			'gather_del'=>'删除', 
		),
		'other'=>array(
		),
	);

	$roles_elements[] = array(
		'value'=>'gatherlog', 
		'name'=>'收款单',
		'base'=>array(
			'gatherlog_view'=>'查看',
			'gatherlog_add'=>'添加',
			'gatherlog_del'=>'删除', 
		),
		'other'=>array(
		),
	);

	$roles_elements[] = array(
		'value'=>'charge', 
		'name'=>'退款计划',
		'base'=>array(
			'charge_view'=>'查看',
			'charge_edit'=>'编辑',
			'charge_del'=>'删除', 
		),
		'other'=>array(
		),
	);

	$roles_elements[] = array(
		'value'=>'chargelog', 
		'name'=>'退款单',
		'base'=>array(
			'chargelog_view'=>'查看',
			'chargelog_add'=>'添加',
			'chargelog_del'=>'删除', 
		),
		'other'=>array(
		),
	);

	$roles_elements[] = array(
		'value'=>'billing', 
		'name'=>'开票管理',
		'base'=>array(
			'billing_view'=>'查看',
			'billing_edit'=>'编辑',
			'billing_del'=>'删除', 
			'billing_approved'=>'审批', 
		),
		'other'=>array(
			'billing_bill'=>'开票',
		),
	);

	$roles_elements[] = array(
		'value'=>'menu_report', 
		'name'=>'报表分析(左边菜单导航)',
		'base'=>array(
		),
		'other'=>array(
			'salereport'=>'销售报表',
			'financereport'=>'财务报表',
			'projectreport'=>'项目报表',
			'taskreport'=>'任务报表',
		),
	);

	$roles_elements[] = array(
		'value'=>'sysmsg', 
		'name'=>'我的消息',
		'base'=>array(
			'sysmsg_view'=>'查看消息',
			'sysmsg_del'=>'删除消息',
		),
		'other'=>array(
		),
	);

	$roles_elements[] = array(
		'value'=>'myinfo', 
		'name'=>'我的帐号',
		'base'=>array(
		),
		'other'=>array(
			'myinfo_data'=>'查看我的资料',
			'myinfo_roles'=>'查看我的权限',
			'myinfo_subordinate'=>'查看我的下属',
			'myinfo_editpassword'=>'修改密码',
		),
	);

	return $roles_elements;
}
?>
