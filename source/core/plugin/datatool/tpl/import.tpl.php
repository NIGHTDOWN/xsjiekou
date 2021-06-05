<?php



if(!defined('IN_OECMS')) {
	exit('Access Denied');
}
;echo '
<h3 class="title"><a href="'; echo __ADMIN_FILE__;;echo '?c=plugin&a=setting&plugin_id=datatool" class="btn-general"><span>数据备份</span></a>数据恢复</h3>
<div class="search-area ">
  <div class="item">
本功能在恢复备份数据的同时，将覆盖原有数据，请确定是否需要恢复，以免造成数据损失；<br />
数据恢复功能只能恢复由当前版本导出的数据文件，其他软件导出格式可能无法识别；<br />
如果备份文件太大需要一些时间导入，请耐心等待直到程序提示全部导入完成；<br />
<font color="green"><b>背景色相同的文件为同一次备份的文件，导入时只需要点导入任意一个文件，程序会自动导入剩余文件。</b></font><br />
  </div>
</div>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table" align="center">
  <thead class="tb-tit-bg">
  <tr>
	<th width="12%"><div class="th-gap">ID</div></th>
	<th width="30%"><div class="th-gap">文件名</div></th>
	<th width="15%"><div class="th-gap">文件大小</div></th>
	<th width="20%"><div class="th-gap">备份时间</div></th>
	<th width="10%"><div class="th-gap">卷号</div></th>
	<th><div class="th-gap">操作</div></th>
  </tr>
  </thead>
  <tfoot class="tb-foot-bg"></tfoot>
  ';
  if (is_array($data)) {
  foreach ($data as $key=>$value){
  ;echo '  <tr bgcolor="'; echo $value['bgcolor'];;echo '">
	<td align="center">'; echo ($key+1);;echo '</td>
	<td align="left"><a href="'; __ADMIN_FILE__;;echo '?c=plugin&plugin_id=datatool&a=save&do=down&filename='; echo $value['filename'];;echo '">'; echo $value['filename'];;echo '</a></td>
	<td align="center">'; echo $value['filesize'];;echo '</td>
	<td align="left">'; echo $value['maketime'];;echo '</td>
	<td align="center">'; echo $value['number'];;echo '</td>
	<td align="center">
	<a href="'; echo __ADMIN_FILE__;;echo '?c=plugin&plugin_id=datatool&a=save&do=restore&pre='; echo $value['pre'];;echo '">导入</a> | 
	<a href="'; echo __ADMIN_FILE__;;echo '?c=plugin&plugin_id=datatool&a=save&do=del&filename='; echo $value['filename'];;echo '" onClick="{if(confirm(\'确定要删除该备份文件吗？一旦删除不可恢复！\')){return true;} return false;}">删除</a> | 
	<a href="'; echo __ADMIN_FILE__;;echo '?c=plugin&plugin_id=datatool&a=save&do=down&filename='; echo $value['filename'];;echo '">下载</a>
    </td>
  </tr>
  ';
   }
   }
  ;echo '</table>


'; 
if(is_array($infos)){
	foreach($infos as $id => $info){
$id++;
;echo '  <tr bgcolor="'; echo $info['bgcolor'];echo '"  align="center">
    <td><input type="checkbox" name="filenames[]" value="'; echo $info['filename'];echo '"></td>
    <td>'; echo $id;echo '</td>
    <td class="px10" align="left">&nbsp;<a href="./data/'; echo $info['filename'];echo '">'; echo $info['filename'];echo '</a></td>
    <td class="px10">'; echo $info['filesize'];echo ' M</td>
	<td class="px10">'; echo $info['maketime'];echo '</td>
    <td class="px10">'; echo $info['number'];echo '</td>
    <td>
	<a href="?action='; echo $action;echo '&pre='; echo $info['pre'];echo '&dosubmit=1">导入</a> | 
	<a href="?action=delete&filenames='; echo $info['filename'];echo '">删除</a> | 
	<a href="?action=down&filename='; echo $info['filename'];echo '">下载</a>
	</td>
</tr>
'; 
	}
}
?>
