<?php




checktop();

;echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
';

if ($auto_go_url)
{

;echo '<meta http-equiv="refresh" content="1;URL=';

    echo $url;

;echo '" />
';

}

;echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>信息提示</title>
<link href="';

echo PATH_URL;

;echo 'tpl/general/halt/style.css" rel="stylesheet" />
</head>
<body>
<div class="halt-body halt-box">
  <h2>信息提示</h2>
  <div class="halt-content">
	<div class="halt-left">
	   ';

if ($flag == '0')
{

;echo '	   <span class="halt-fail"></span>
	   ';

} else
{

;echo '	   <span class="halt-ok"></span>
	   ';

}

;echo '	</div>
	<div class="halt-right">
	  <p class="p-text">';

echo $message;

;echo '</p>
	  <p class="p-refresh">
	  ';

if ($auto_go_url)
{

;echo '	  <a href="';

    echo $url;

;echo '">1秒后，浏览器没有自动跳转，请点击此链接</a>
	  ';

} else
{

;echo '	  【<a href="javascript:history.back();">点击返回上一页</a>】
	  ';

}

;echo '	  </p>
	</div>
    <div style="clear:both;"></div>
  </div>
</div>
</body>
</html>';?>
