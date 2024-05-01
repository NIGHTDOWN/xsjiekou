<?php



if(!defined('IN_OECMS')) {
	exit('Access Denied');
}
;echo '<h3 class="title"><a href="'; echo __ADMIN_FILE__;;echo '?c=plugin&plugin_id=online&a=setting&do=preview" class="btn-general" target="_blank"><span>预览效果</span></a><a href="'; echo __ADMIN_FILE__;echo '?c=plugin&plugin_id=online&a=setting" class="btn-general"><span>在线客服设置</span></a>添加在线客服</h3>
<form name="myform" id="myform" method="post" action="'; echo __ADMIN_FILE__;echo '?c=plugin&a=save&plugin_id=online&do=saveadd" onsubmit=\'return checkform();\' />
<table cellpadding=\'3\' cellspacing=\'3\' class=\'tab\'>
  <tr>
	<td class=\'hback_1\'>排序 <span class=\'f_red\'>*</span></td>
	<td class=\'hback_1\'>客服名称 <span class=\'f_red\'>*</span></td>
	<td class=\'hback_1\'>客服类型 <span class=\'f_red\'>*</span></td>
	<td class=\'hback_1\'>客服号码 <span class=\'f_red\'>*</span></td>
	<td class=\'hback_1\' align=\'center\'>名称</td>
	<td class=\'hback_1\' align=\'center\'>保存</td>
  </tr>
  <tr>
	<td class=\'hback\'>
	<input type=\'text\' name=\'orders\' id=\'orders\' class=\'input-s\' />(数字越小越靠前)
	<span class=\'f_red\' id="dorders"></span>
	</td>
	<td class=\'hback\'>
	<input type=\'text\' name=\'name\' id=\'name\' class=\'input-100\' />
	<span class=\'f_red\' id="dname"></span>
	</td>
	<td class=\'hback\'>
	<select name="type" id="type">
	<option value="qq">QQ</option>
	<option value="msn">MSN</option>
	<option value="skype">Skype</option>
	<option value="taobao">淘宝旺旺</option>
	<option value="alibaba">阿里旺旺</option>
	</select>
	<span class=\'f_red\' id="dtype"></span>
	</td>
	<td class=\'hback\'>
	<input type=\'text\' name=\'number\' id=\'number\' class=\'input-150\' />
	<span class=\'f_red\' id="dnumber"></span>
	</td>
	<td class=\'hback\' align=\'center\'>
	<select name="show" id="show">
	<option value="1">显示</option>
	<option value="0">隐藏</option>
	</select>
	</td>
	<td class=\'hback\' align=\'center\'><input type="submit" name="btn_save" class=\'button\' value="添加保存" /></td>
  </tr>
</table>
</form>
<script type="text/javascript">
function checkform() {
	var t = "";
	var v = "";

	t = "orders";
	v = $("#"+t).val();
	if(v=="") {
		dmsg("排序不能为空", t);
		return false;
	}

	t = "name";
	v = $("#"+t).val();
	if(v=="") {
		dmsg("客服名称不能为空", t);
		return false;
	}


	t = "type";
	v = $("#"+t).val();
	if(v=="") {
		dmsg("客服类型不能为空", t);
		return false;
	}

	t = "number";
	v = $("#"+t).val();
	if(v=="") {
		dmsg("客服号码不能为空", t);
		return false;
	}

	return true;
}
</script>
<br />


<h3 class="title">共有在线客服（'; echo $count;;echo '）个 </h3>
<form name="myform" id="myform" method="post" action="'; echo __ADMIN_FILE__;echo '?c=plugin&a=save&plugin_id=online&do=saveupdate" />
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table" align="center">
  <thead class="tb-tit-bg">
  <tr>
	<th width="10%"><div class="th-gap">ID</div></th>
	<th><div class="th-gap">排序 <span class=\'f_red\'>*</span></div></th>
	<th><div class="th-gap">客服名称 <span class=\'f_red\'>*</span></div></th>
	<th><div class="th-gap">客服类型 <span class=\'f_red\'>*</span></div></th>
	<th><div class="th-gap">客服号码 <span class=\'f_red\'>*</span></div></th>
	<th width="10%"><div class="th-gap">名称</div></th>
	<th><div class="th-gap">操作</div></th>
  </tr>
  </thead>
  <tfoot class="tb-foot-bg"></tfoot>
  ';
	if (empty($data)) {
		echo "<tr><td colspan='7' class='hback' align='center'>暂无客服信息</td></tr>";
	}
	else {
	$i = 1;
	foreach($data as $key=>$value)	{
		
  ;echo '  <tr onMouseOver="overColor(this)" onMouseOut="outColor(this)">
	<td align="center"><input type="hidden" name=\'id[]\' id=\'id[]\' value="'; echo $i;;echo '" />'; echo $i;;echo '</td>
	<td><input type="text" name="orders_'; echo $i;;echo '" value="'; echo $value['orders'];;echo '" class="input-s" /></td>
	<td><input type="text" name="name_'; echo $i;;echo '" value="'; echo stripslashes($value['name']);;echo '" class="input-100" /></td>
	<td align="center">
	<select name="type_'; echo $i;;echo '" id="'; echo $i;;echo '">
	<option value="qq"'; if ($value['type']=='qq') echo ' selected';;echo '>QQ</option>
	<option value="msn"'; if ($value['type']=='msn') echo ' selected';;echo '>MSN</option>
	<option value="skype"'; if ($value['type']=='skype') echo ' selected';;echo '>Skype</option>
	<option value="taobao"'; if ($value['type']=='taobao') echo ' selected';;echo '>淘宝旺旺</option>
	<option value="alibaba"'; if ($value['type']=='alibaba') echo ' selected';;echo '>阿里旺旺</option>
	</select>
    </td>
	<td><input type="text" name="number_'; echo $i;;echo '" value="'; echo $value['number'];;echo '" class="input-150" /></td>
	<td align="center">
	<select name="show_'; echo $i;;echo '" id="show_'; echo $i;;echo '">
	<option value="1"'; if ($value['show'] == '1') echo ' selected';;echo '>显示</option>
	<option value="0"'; if ($value['show'] == '0') echo ' selected';;echo '>隐藏</option>
	</select>
	</td>
	<td align="center"><a href="'; echo __ADMIN_FILE__;;echo '?c=plugin&a=save&plugin_id=online&do=del&id='; echo $i;;echo '" onClick="{if(confirm(\'确定要删除？\')){return true;} return false;}" class="icon-del">删除</a></td>
  </tr>
  ';
	  $i = $i+1;
	}
  ;echo '  <tr>
    <td></td>
	<td colspan="6"><input type="submit" name="btn_save" value="批量更新保存" class="button" /></td>
  </tr>
  ';
	}
  ;echo '</table>
</form>
';?>
