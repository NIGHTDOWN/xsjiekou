<?php



if(!defined('IN_OECMS')) {
	exit('Access Denied');
}


;echo ''; 

if ($online['skin'] == 1 OR $online['skin'] == 3) {
	$qqcolor = array(
		'1'=>'#025f9d',
		'2'=>'#a4381f',
		'3'=>'#21198F',
		'4'=>'#048720',
		'5'=>'#666666',
	);
	$qqwidth = ($online['skin'] == 1) ? '112' : '130';
;echo '
<style type="text/css">
#floatDiv{ display:none;}
#floatDivr{ display:none;}
.floatonline_1{ padding:1px; width:'; echo $qqwidth;;echo 'px; }
.scroll_title_1{font-weight:bold; padding-top:12px; text-align:left; color:'; echo $qqcolor[$online['color']];;echo '; background:url('; echo PATH_URL;;echo 'source/plugin/online/images/qq/online'; echo $online['skin'];;echo '_1_'; echo $online['color'];;echo '.gif) no-repeat 0px 0px; height:22px; padding-left:12px; }
.scroll_title_1{ position:relative;}
.scroll_title_1 a{ display:block; position:absolute; right:10px; top:10px; height:15px; width:20px;}
.scroll_title_1 a:hover{ text-decoration:none !important; cursor:pointer;}
.scroll_qq_1{padding:5px 10px 0px 10px; text-align:left; font-weight:bold; color:#333333; }
.scroll_qq_1 img{padding:5px 0px 0px 0px;}
.scroll_skype_1{ padding:5px 0px 5px 0px; text-align:center;}
.scroll_alibaba_1{ padding:5px 0px 5px 0px; text-align:center;}
.online_left_1{ background:url('; echo PATH_URL;;echo 'source/plugin/online/images/qq/online'; echo $online['skin'];;echo '_3_'; echo $online['color'];;echo '.gif) no-repeat 0px 0px; width:'; echo $qqwidth;echo 'px;}
.online_right_1{ background: #FFFFFF url('; echo PATH_URL;;echo 'source/plugin/online/images/qq/online'; echo $online['skin'];;echo '_5_'; echo $online['color'];;echo '.gif) no-repeat  right top;}
.scroll_foot1_1{ height:14px; font-size:0px; background:url('; echo PATH_URL;;echo 'source/plugin/online/images/qq/online'; echo $online['skin'];;echo '_4_'; echo $online['color'];;echo '.gif) no-repeat 0px 0px;}
.scroll_foot2_1{ height:auto; padding:0px 10px; color:#666; text-align:center; min-height:18px;   line-height:18px; background:url('; echo PATH_URL;;echo 'source/plugin/online/images/qq/online'; echo $online['skin'];;echo '_6_'; echo $online['color'];;echo '.gif) repeat-y 0px 0px;}
.scroll_foot3_1{ height:8px; font-size:0px; background:url('; echo PATH_URL;;echo 'source/plugin/online/images/qq/online'; echo $online['skin'];;echo '_7_'; echo $online['color'];;echo '.gif) no-repeat 0px 0px;}
</style>
';
}
;echo '
';

if ($online['skin'] == 2 OR $online['skin'] == 4){
	$qqcolor[1]=array(1=>'#c5e2f8',2=>'#498bcf',3=>'#a7d8d7');
	$qqcolor[2]=array(1=>'#ffe5e5',2=>'#d27762',3=>'#f7c6c6');
	$qqcolor[3]=array(1=>'#E7E1FF',2=>'#624db3',3=>'#C2B6F0');
	$qqcolor[4]=array(1=>'#E6FFE5',2=>'#46bd43',3=>'#ADF3AC');
	$qqcolor[5]=array(1=>'#DFDFDF',2=>'#9a9a99',3=>'#CCCCCC');
	$qqwidth=($online['skin'] == 2) ? '112' : '130';
;echo '
<style type="text/css">
#floatDivr{ display:none;}
#floatDiv{ display:none;}
.floatonline_1{ padding:1px; width:'; echo $qqwidth;;echo 'px; text-align:left;}
.scroll_title_2{height:25px; line-height:25px; background:url('; echo PATH_URL;;echo 'source/plugin/online/images/qq/online'; echo $online['skin'];;echo '_'; echo $online['color'];;echo '.gif) no-repeat 0px 0px; position:relative;}
.scroll_title_2 span{ padding-left:15px; font-weight:bold; color:#FFFFFF;}
.scroll_title_2 a{ display:block; position:absolute; right:8px; top:6px; line-height:15px;  width:11px; height:11px; background:url('; echo PATH_URL;;echo 'source/plugin/online/images/qq/close2_'; echo $online['color'];;echo '.gif) no-repeat 0px 0px;}
.scroll_main2{ padding:4px; background:'; echo $qqcolor[$online['color']][1];;echo '; border:1px solid '; echo $qqcolor[$online['color']][2];;echo ';}
.scroll_text2{ background:#FFFFFF; border:1px solid '; echo $qqcolor[$online['color']][3];;echo '; padding:3px;}
.scroll_qq_1{padding:2px 2px 0px 2px; text-align:left; font-weight:bold; color:#333333; }
.scroll_qq_1 img{padding:5px 0px 0px 0px;}
.scroll_skype_1{padding:5px 0px 5px 0px; text-align:center;}
.scroll_alibaba_1{padding:5px 0px 5px 0px; text-align:center;}
.scroll_foot_2{ background:#FFFFFF; border:1px solid '; echo $qqcolor[$online['color']][3];echo '; text-align:center; padding:3px; line-height:18px; margin-top:5px;}}
</style>

';
}
?>
