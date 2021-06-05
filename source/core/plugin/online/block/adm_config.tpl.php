<?php



if(!defined('IN_OECMS')) {
	exit('Access Denied');
}
;echo '<h3 class="title"><a href="'; echo __ADMIN_FILE__;;echo '?c=plugin&plugin_id=online&a=setting&do=preview" class="btn-general" target="_blank"><span>预览效果</span></a><a href="'; echo __ADMIN_FILE__;;echo '?c=plugin&plugin_id=online&a=setting&do=list" class="btn-general"><span>添加客服</span></a>在线客服配置</h3>
<form name="myform" id="myform" method="post" action="'; __ADMIN_FILE__;;echo '?c=plugin&a=save&plugin_id=online&do=savesetting" />
<table cellpadding=\'1\' cellspacing=\'1\' class=\'tab\'>
  <tr>
	<td class=\'hback_1\' width="15%">在线客服位置：<span class=\'f_red\'></span></td>
	<td class=\'hback\' width="85%">
	<input type=\'radio\' name="type" value="1"'; if ($data['type']=='1') echo " checked";;echo ' />页面左侧浮动，
	<input type=\'radio\' name="type" value="2"'; if ($data['type']=='2') echo " checked";;echo ' />页面右侧浮动，
	<input type=\'radio\' name="type" value="0"'; if ($data['type']=='0') echo " checked";;echo ' />关闭在线客服
	</td>
  </tr>

  <tr>
	<td class=\'hback_1\'>左侧浮动位置：<span class=\'f_red\'></span></td>
	<td class=\'hback\'>
	距离浏览器左边：<input type="text" name="left_leftpr" class="input-s" value="'; echo $data['left_leftpr'];;echo '" />像素，距离浏览器顶部：<input type="text" name="left_toppr" class="input-s" value="'; echo $data['left_toppr'];;echo '" />像素
	</td>
  </tr>
  <tr>
	<td class=\'hback_1\'>右侧浮动位置：<span class=\'f_red\'></span></td>
	<td class=\'hback\'>
	距离浏览器右边：<input type="text" name="right_rightpr" class="input-s" value="'; echo $data['right_rightpr'];;echo '" />像素，距离浏览器顶部：<input type="text" name="right_toppr" class="input-s" value="'; echo $data['right_toppr'];;echo '" />像素
	</td>
  </tr>
  <tr>
	<td class=\'hback_1\'>提示设置：<span class=\'f_red\'></span></td>
	<td class=\'hback\'>标题：<input type="text" name="title" id="title" value="'; echo $data['title'];;echo '" class="input-100" />关闭浮动提示：<input type="text" name="close" id="close" value="'; echo $data['close'];;echo '" class="input-s" /></td>
  </tr>
  <tr>
	<td class=\'hback_1\'>在线客服风格：<span class=\'f_red\'></span></td>
	<td class=\'hback\'>
	<select name="skin" id="skin">
	<option value="1"'; if ($data['skin'] == '1') echo ' selected';;echo '>风格一</option>
	<option value="2"'; if ($data['skin'] == '2') echo ' selected';;echo '>风格二</option>
	<option value="3"'; if ($data['skin'] == '3') echo ' selected';;echo '>风格三</option>
	<option value="4"'; if ($data['skin'] == '4') echo ' selected';;echo '>风格四</option>
	</select>
	<select name="color" id="color">
	<option value="1"'; if ($data['color'] == '1') echo ' selected';;echo '>浅蓝</option>
	<option value="2"'; if ($data['color'] == '2') echo ' selected';;echo '>淡红</option>
	<option value="3"'; if ($data['color'] == '3') echo ' selected';;echo '>紫色</option>
	<option value="4"'; if ($data['color'] == '4') echo ' selected';;echo '>绿色</option>
	<option value="5"'; if ($data['color'] == '5') echo ' selected';;echo '>灰色</option>
	</select>
	</td>
  </tr>
  <tr>
	<td class=\'hback_1\'>QQ图标：<span class=\'f_red\'></span></td>
	<td class=\'hback\'>
	<select name="qqicon" id="qqicon">
	<option value="1"'; if ($data['qqicon'] == '1') echo ' selected';;echo '>图标1</option>
	<option value="2"'; if ($data['qqicon'] == '2') echo ' selected';;echo '>图标2</option>
	<option value="3"'; if ($data['qqicon'] == '3') echo ' selected';;echo '>图标3</option>
	<option value="4"'; if ($data['qqicon'] == '4') echo ' selected';;echo '>图标4</option>
	<option value="5"'; if ($data['qqicon'] == '5') echo ' selected';;echo '>图标5</option>
	<option value="6"'; if ($data['qqicon'] == '6') echo ' selected';;echo '>图标6</option>
	<option value="7"'; if ($data['qqicon'] == '7') echo ' selected';;echo '>图标7</option>
	<option value="8"'; if ($data['qqicon'] == '8') echo ' selected';;echo '>图标8</option>
	<option value="9"'; if ($data['qqicon'] == '9') echo ' selected';;echo '>图标9</option>
	<option value="10"'; if ($data['qqicon'] == '10') echo ' selected';;echo '>图标10</option>
	<option value="11"'; if ($data['qqicon'] == '11') echo ' selected';;echo '>图标11</option>
	<option value="12"'; if ($data['qqicon'] == '12') echo ' selected';;echo '>图标12</option>
	<option value="13"'; if ($data['qqicon'] == '13') echo ' selected';;echo '>图标13</option>
	</select>
	</td>
  </tr>
  <tr>
	<td class=\'hback_1\'>MSN图标：<span class=\'f_red\'></span></td>
	<td class=\'hback\'>
	<select name="msnicon" id="msnicon">
	<option value="1"'; if ($data['msnicon'] == '1') echo ' selected';;echo '>图标1</option>
	<option value="2"'; if ($data['msnicon'] == '2') echo ' selected';;echo '>图标2</option>
	<option value="3"'; if ($data['msnicon'] == '3') echo ' selected';;echo '>图标3</option>
	<option value="4"'; if ($data['msnicon'] == '4') echo ' selected';;echo '>图标4</option>
	<option value="5"'; if ($data['msnicon'] == '5') echo ' selected';;echo '>图标5</option>
	<option value="6"'; if ($data['msnicon'] == '6') echo ' selected';;echo '>图标6</option>
	<option value="7"'; if ($data['msnicon'] == '7') echo ' selected';;echo '>图标7</option>
	<option value="8"'; if ($data['msnicon'] == '8') echo ' selected';;echo '>图标8</option>
	<option value="9"'; if ($data['msnicon'] == '9') echo ' selected';;echo '>图标9</option>
	<option value="10"'; if ($data['msnicon'] == '10') echo ' selected';;echo '>图标10</option>
	<option value="11"'; if ($data['msnicon'] == '11') echo ' selected';;echo '>图标11</option>
	<option value="12"'; if ($data['msnicon'] == '12') echo ' selected';;echo '>图标12</option>
	<option value="13"'; if ($data['msnicon'] == '13') echo ' selected';;echo '>图标13</option>
	</select>
	</td>
  </tr>
  <tr>
	<td class=\'hback_1\'>SKYPE图标：<span class=\'f_red\'></span></td>
	<td class=\'hback\'>
	<select name="skypeicon" id="skypeicon">
	<option value="1"'; if ($data['skypeicon'] == '1') echo ' selected';;echo '>图标1</option>
	<option value="2"'; if ($data['skypeicon'] == '2') echo ' selected';;echo '>图标2</option>
	<option value="3"'; if ($data['skypeicon'] == '3') echo ' selected';;echo '>图标3</option>
	<option value="4"'; if ($data['skypeicon'] == '4') echo ' selected';;echo '>图标4</option>
	<option value="5"'; if ($data['skypeicon'] == '5') echo ' selected';;echo '>图标5</option>
	<option value="6"'; if ($data['skypeicon'] == '6') echo ' selected';;echo '>图标6</option>
	<option value="7"'; if ($data['skypeicon'] == '7') echo ' selected';;echo '>图标7</option>
	<option value="8"'; if ($data['skypeicon'] == '8') echo ' selected';;echo '>图标8</option>
	<option value="9"'; if ($data['skypeicon'] == '9') echo ' selected';;echo '>图标9</option>
	<option value="10"'; if ($data['skypeicon'] == '10') echo ' selected';;echo '>图标10</option>
	<option value="11"'; if ($data['skypeicon'] == '11') echo ' selected';;echo '>图标11</option>
	<option value="12"'; if ($data['skypeicon'] == '12') echo ' selected';;echo '>图标12</option>
	<option value="13"'; if ($data['skypeicon'] == '13') echo ' selected';;echo '>图标13</option>
	<option value="14"'; if ($data['skypeicon'] == '14') echo ' selected';;echo '>图标14</option>
	<option value="15"'; if ($data['skypeicon'] == '15') echo ' selected';;echo '>图标15</option>
	<option value="16"'; if ($data['skypeicon'] == '16') echo ' selected';;echo '>图标16</option>
	<option value="17"'; if ($data['skypeicon'] == '17') echo ' selected';;echo '>图标17</option>
	<option value="18"'; if ($data['skypeicon'] == '18') echo ' selected';;echo '>图标18</option>
	<option value="19"'; if ($data['skypeicon'] == '19') echo ' selected';;echo '>图标19</option>
	<option value="20"'; if ($data['skypeicon'] == '20') echo ' selected';;echo '>图标20</option>
	<option value="21"'; if ($data['skypeicon'] == '21') echo ' selected';;echo '>图标21</option>
	<option value="22"'; if ($data['skypeicon'] == '22') echo ' selected';;echo '>图标22</option>
	<option value="23"'; if ($data['skypeicon'] == '23') echo ' selected';;echo '>图标23</option>
	<option value="24"'; if ($data['skypeicon'] == '24') echo ' selected';;echo '>图标24</option>
	</select>
	</td>
  </tr>

  <tr>
	<td class=\'hback_1\'>淘宝旺旺图标：<span class=\'f_red\'></span></td>
	<td class=\'hback\'>
	<select name="taobaoicon" id="taobaoicon">
	<option value="1"'; if ($data['taobaoicon'] == '1') echo ' selected';;echo '>图标1</option>
	<option value="2"'; if ($data['taobaoicon'] == '2') echo ' selected';;echo '>图标2</option>
	</select>

	与淘宝官方网站对应 <a href="http://www.taobao.com/wangwang/2010_fp/world.php" target="_blank">查看</a>
	</td>
  </tr>


  <tr>
	<td class=\'hback_1\'>阿里旺旺图标：<span class=\'f_red\'></span></td>
	<td class=\'hback\'>
	<select name="aliicon" id="aliicon">
	<option value="1"'; if ($data['aliicon'] == '1') echo ' selected';;echo '>图标1</option>
	<option value="2"'; if ($data['aliicon'] == '2') echo ' selected';;echo '>图标2</option>
	<option value="3"'; if ($data['aliicon'] == '3') echo ' selected';;echo '>图标3</option>
	<option value="4"'; if ($data['aliicon'] == '4') echo ' selected';;echo '>图标4</option>
	</select>

	与阿里巴巴官方网站对应 <a href="http://club.china.alibaba.com/club/block/alitalk/alitalkfire.html" target="_blank">查看</a>
	</td>
  </tr>


  <tr>
	<td class=\'hback_1\'>其他文字说明：<span class=\'f_red\'></span></td>
	<td class=\'hback\'><textarea name="remark" id="remark" style="width:40%;height:60px;overflow:auto;">'; echo stripslashes($data['remark']);;echo '</textarea> （支持HTML语法）</td>
  </tr>

  <tr>
	<td class=\'hback_none\'></td>
	<td class=\'hback_none\'><input type="submit" name="btn_save" class="button" value="保存设置" /></td>
  </tr>
</table>
</form>';?>
