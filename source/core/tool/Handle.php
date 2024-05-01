<?php
namespace ng169\tool;
checktop();
class Handle
{
  public static function page404($msg = null)
  {
    @header("http/1.1 404 not found");
    @header("status: 404 not found");
    self::_html404();
    die();
  }
  public static function development($msg = null)
  {
    if ($msg == null) {
      $msg = "功能开发中，敬请期待";
    }
    $var_array = array('msg'=> $msg);
    TPL::assign($var_array);
    TPL::display(ROOT . './tpl/general/development/index.html');
  }
  private static function _html404($msg = null)
  {

    if ($msg == null) {
      $msg = "非常抱歉，您要查看的页面没有办法找到";
    }
    $var_array = array('msg'=> $msg);
    TPL::assign($var_array);
    TPL::display('tpl/general/404/index.html');
  }

  public static function error($error)
  {
    echo "<meta http-equiv='Content-Type' content='text/html; charset=" .
    OEPHP_CHARSET . "' />
    <style>body{font-size:12px;line-height:25px;}</style>
    <body>
    " . $error . "
    </body>";
    die();
  }

  public static function redirect($url, $time = 0)
  {
    if (!headers_sent()) {
      if ($time === 0) {
        header("Location:{$url}");
      }

      header("refresh:" . $time . ";url=" . $url . "");
      die();
    }
    else {
      exit("<meta http-equiv='Refresh' content='{$time};URL={$url}'>");
      die();
    }
  }

  public static function getLength($str)
  {
    $len = 0;
    if (!empty($str)) {
      preg_match_all('#(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+)#s', $str, $array,
        PREG_PATTERN_ORDER);
      foreach ($array[0] as $val) {
        $len += ord($val) >= 128 ? 2 : 1;
      }
    }
    return $len;
  }

  public static function getWordLength($value)
  {
    $len = 0;
    if (!empty($value)) {
      preg_match_all('#(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+)#s', $value, $array,
        PREG_PATTERN_ORDER);
      foreach ($array[0] as $val) {
        $len += ord($val) >= 128 ? 1 : 1;
      }
    }
    return $len;
  }

  public static function getRndChar($length, $type = 0)
  {
    switch ($type) {
      case 1:
      $pattern = "1234567890";
      break;
      case 2:
      $pattern = "abcdefghijklmnopqrstuvwxyz";
      break;
      case 3:
      $pattern = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
      break;
      case 4:
      $pattern = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890~!@#$%^&*()_-+=";
      break;
      case 5:
      $pattern = "123456789";
      break;
      default:
      $pattern = "1234567890abcdefghijklmnopqrstuvwxyz";
    }
    $size = strlen($pattern) - 1;
    $key  = $pattern{
      rand(0, $size)
    };
    for ($i = 1; $i < $length; $i++) {
      $key .= $pattern{
        rand(0, $size)
      };
    }
    return $key;
  }

  public static function foundInChar($string, $tofound, $split = ',')
  {
    $flag = false;
    if (!empty($string) && !empty($tofound)) {
      $args = explode($split, $string);
      for ($i = 0; $i < sizeof($args); $i++) {
        if (trim(strtolower($args[$i])) == trim(strtolower($tofound))) {
          $flag = true;
          break;
        }
      }
    }
    return $flag;
  }

  public static function getStrpos($s_str, $s_needlechar)
  {
    if (empty($s_str)) {
      return;
    }
    if (empty($s_needlechar)) {
      return;
    }
    $s_temparray = explode($s_needlechar, $s_str);
    if (count($s_temparray) > 0) {
      return true;
    }
    else {
      return false;
    }
  }

  public static function recBr($s_content)
  {
    $s_content = str_replace("\n", "<br />", $s_content);
    return $s_content;
  }

  public static function filterHtml($_obfuscate_R2_b, $_obfuscate_KT_ujQ = false)
  {
    if ($_obfuscate_KT_ujQ) {
      $_obfuscate_dcwit = array(
        "/<img[^\\<\\>]+src=['\"]?([^\\<\\>'\"\\s]*)['\"]?/is",
        "/<a[^\\<\\>]+href=['\"]?([^\\<\\>'\"\\s]*)['\"]?/is",
        "/on[a-z]+[\\s]*=[\\s]*\"[^\"]*\"/is",
        "/on[a-z]+[\\s]*=[\\s]*'[^']*'/is");
      $_obfuscate_77tGbWOiZg = array(
        "\\1<br>\\0",
        "\\1<br>\\0",
        "",
        "");
      $_obfuscate_R2_b = preg_replace($_obfuscate_dcwit, $_obfuscate_77tGbWOiZg, $_obfuscate_R2_b);
    }
    $_obfuscate_dcwit = array(
      "/([\r\n])[\\s]+/",
      "/\\<br[^\\>]*\\>/i",
      "/\\<[\\s]*\\/p[\\s]*\\>/i",
      "/\\<[\\s]*p[\\s]*\\>/i",
      "/\\<script[^\\>]*\\>.*\\<\\/script\\>/is",
      "/\\<[\\/\\!]*[^\\<\\>]*\\>/is",
      "/&(quot|#34);/i",
      "/&(amp|#38);/i",
      "/&(lt|#60);/i",
      "/&(gt|#62);/i",
      "/&(nbsp|#160);/i",
      "/&#(\\d+);/",
      "/&([a-z]+);/i");
    $_obfuscate_77tGbWOiZg = array(
      " ",
      "\r\n",
      "",
      "\r\n\r\n",
      "",
      "",
      "\"",
      "&",
      "<",
      ">",
      " ",
      "-",
      "");
    $_obfuscate_R2_b = preg_replace($_obfuscate_dcwit, $_obfuscate_77tGbWOiZg, $_obfuscate_R2_b);
    $_obfuscate_R2_b = strip_tags($_obfuscate_R2_b);
    return $_obfuscate_R2_b;
  }

  public static function cutStrLen($string, $sublen, $start = 0)
  {
    if (OEPHP_CHARSET == 'UTF-8' or OEPHP_CHARSET == 'utf-8') {
      $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
      preg_match_all($pa, $string, $t_string);
      if (count($t_string[0]) - $start > $sublen) {
        return join('', array_slice($t_string[0], $start, $sublen)) . "";
      }

      return join('', array_slice($t_string[0], $start, $sublen));
    }
    else {
      $start = $start * 2;
      $sublen= $sublen * 2;
      $strlen= strlen($string);
      $tmpstr= '';
      for ($i = 0; $i < $strlen; $i++) {
        if ($i >= $start && $i < ($start + $sublen)) {
          if (ord(substr($string, $i, 1)) > 129) {
            $tmpstr .= substr($string, $i, 2);
          }
          else {
            $tmpstr .= substr($string, $i, 1);
          }
        }
        if (ord(substr($string, $i, 1)) > 129) {
          $i++;
        }

      }
      return $tmpstr;
    }
  }

  public static function utfToGbk($value)
  {
    if (function_exists('iconv')) {
      return iconv('UTF-8', 'GBK', $value);
    }
    else {
      if (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($value, 'UTF-8', 'GBK');
      }
      else {
        return $value;
      }
    }
  }

  public static function gbkToUtf($value)
  {
    if (function_exists('iconv')) {
      return iconv('GBK', 'UTF-8', $value);
    }
    else {
      if (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($value, 'GBK', 'UTF-8');
      }
      else {
        return $value;
      }
    }
  }

  public static function formatNumber($price, $pricetype = 1, $change_price = true)
  {
    if ($change_price) {
      switch ($pricetype) {
        case 0:
        $price = number_format($price, 2, '.', '');
        break;
        case 1:
        $price = preg_replace('/(.*)(\\.)([0-9]*?)0+$/', '\1\2\3', number_format($price,
            2, '.', ''));
        if (substr($price, - 1) == '.') {
          $price = substr($price, 0, - 1);
        }
        break;
        case 2:
        $price = substr(number_format($price, 2, '.', ''), 0, - 1);
        break;
        case 3:
        $price = intval($price);
        break;
        case 4:
        $price = number_format($price, 1, '.', '');
        break;
        case 5:
        $price = round($price);
        break;
      }
    }
    else {
      $price = number_format($price, 2, '.', '');
    }
    return $price;
  }

  public static function mb($_string, $_comurl, $_gotype)
  {
    echo ("<meta http-equiv='Content-Type' content='text/html; charset=" .
      OEPHP_CHARSET . "' />");
    echo "<script language=javascript>alert('{$_string}');";
    if ($_gotype == 1) {
      echo "window.history.go(-1);";
    }
    else {
      echo "window.location.href='{$_comurl}';";
    }
    echo "</script>";
    die();
  }

  public static function halt($message, $url = null, $flag = true, $auto_go_url = true)
  {

    if (!X::isajax()) {
      if ($url == null) {
        $url = $_SERVER['HTTP_REFERER'];
      }
      require_once ROOT . './tpl/general/halt/halt.php';
    }
    else {
      echo json_encode(array(
          'msg' => $message,
          'flag'=> $flag,
          'url' => $url,
          'obj' => $obj));
    }

    die();
  }

  public static function tbDialog($msg = '', $type = 1)
  {
    echo ("<meta http-equiv='Content-Type' content='text/html; charset=" .
      OEPHP_CHARSET . "' />");
    if (!empty($msg)) {
      echo ("<script language=javascript>alert('{$msg}');</script>");
      if ($type == 1) {

        echo ("<script>parent.location.reload();</script>");
      }
      else {

        echo ("<script>parent.tb_remove();</script>");
      }
    }
    else {
      if ($type == 1) {

        echo ("<script>parent.location.reload();</script>");
      }
      else {

        echo ("<script>parent.tb_remove();</script>");
      }
    }
    die();
  }

  public static function combinSql($asname, $field, $sqlitem)
  {
    if (self::isChar($sqlitem)) {
      if (self::isNumber($sqlitem)) {
        if (self::isChar($asname)) {
          $temp = " AND " . $asname . "." . $field . "=" . intval($sqlitem) . "";
        }
        else {
          $temp = " AND " . $field . "=" . intval($sqlitem) . "";
        }
      }
      else {
        $splitarray = explode(",", $sqlitem);
        for ($i = 0; $i < sizeof($splitarray); $i++) {
          if (self::isChar($asname)) {
            $temp .= " " . $asname . "." . $field . "=" . intval($splitarray[$i]) . " OR";
          }
          else {
            $temp .= " " . $field . "=" . intval($splitarray[$i]) . " OR";
          }
        }
        $temp = substr($temp, 0, (strlen($temp) - 3));
        $temp = " AND (" . $temp . " )";
      }
    }
    else {
      $temp = " ";
    }
    return $temp;
  }

  public static function sysSortArray($ArrayData, $KeyName1, $SortOrder1 =
    "SORT_ASC", $SortType1 = "SORT_REGULAR")
  {
    if (!is_array($ArrayData)) {
      return $ArrayData;
    }
    $ArgCount = func_num_args();
    for ($I = 1; $I < $ArgCount; $I++) {
      $Arg = func_get_arg($I);
      if (!preg_match("/SORT/i", $Arg)) {
        $KeyNameList[] = $Arg;
        $SortRule[] = '$' . $Arg;
      }
      else {
        $SortRule[] = $Arg;
      }
    }
    foreach ($ArrayData as $Key => $Info) {
      foreach ($KeyNameList as $KeyName) {
        ${
          $KeyName
        }[$Key] = $Info[$KeyName];
      }
    }
    $EvalString = 'array_multisort(' . join(",", $SortRule) . ',$ArrayData);';
    eval($EvalString);
    return $ArrayData;
  }

  public static function fileExists($fliename)
  {
    if (file_exists($fliename)) {
      return true;
    }
    else {
      return false;
    }
  }

  public static function forbidChar($string)
  {
    $forbidchar = array(
      'select',
      'update',
      'union',
      'insert',
      'load_file',
      'outfile',
      'where',
      'char',
      'concat',
      '#');
    if (self::isChar($string)) {
      foreach ($forbidchar as $key) {
        if (strpos(strtolower($string), $key)) {
          $string = "";
        }
      }
    }
    return $string;
  }

  public static function checkOdayChar($string)
  {
    $checkflag = false;
    $forbidchar = array(
      'select',
      'update',
      'union',
      'insert',
      'load_file',
      'outfile',
      'char',
      'concat',
      '#');
    if (self::isChar($string)) {
      foreach ($forbidchar as $key) {
        if (strpos(strtolower($string), $key)) {
          $checkflag = true;
        }
      }
    }
    return $checkflag;
  }

  public static function checkTable($tablename)
  {
    if (preg_match("/^[0-9a-zA-Z_]+$/u", $tablename)) {
      return true;
    }
    else {
      return false;
    }
  }

  public static function formatSize($size)
  {
    if ($size < 1000) {
      $size_BKM = (string) $size . ' B';
    }
    elseif ($size < (1000 * 1000)) {
      $size_BKM = number_format((double) ($size / 1000), 1) . ' KB';
    }
    else {
      $size_BKM = number_format((double) ($size / (1000 * 1000)), 1) . ' MB';
    }
    return $size_BKM;
  }

  public static function getDiffTime($endtime)
  {
    $res       = '';
    $validtime = ($endtime - time());
    if ($validtime > 0) {

      if ($validtime >= 86400) {
        $res = ceil($validtime / 86400) . '天';
      }

      if ($validtime >= 3600 && $validtime < 86400) {
        $res = ceil($validtime / 3600) . '小时';
      }

      if ($validtime >= 60 && $validtime < 3600) {
        $res = ceil($validtime / 60) . '分钟';
      }

      if ($validtime < 60) {
        $res = $validtime . '秒';
      }
    }
    return $res;
  }

  public static function getUnixTime($type = 'time')
  {
    if ($type == 'time') {
      return time();
    }
    else {
      return strtotime(date("Y-m-d", time()));
    }
  }

  public static function diffDate($timeline, $days, $difftype = 1, $returntype = 1)
  {
    $dateline = date('Y-m-d', $timeline);
    if ($difftype == 1) {
      $diff_timeline = strtotime($dateline) + (3600 * 24 * $days);
    }
    else {
      $diff_timeline = strtotime($dateline) - (3600 * 24 * $days);
    }
    if ($returntype == 1) {
      return strtotime(date('Y-m-d', $diff_timeline));
    }
    else {
      return date('Y-m-d', $diff_timeline);
    }
  }

  public static function lastDate($date)
  {
    $lastdays = 0;
    if (is_numeric($date)) {
      $lastdays = ($date - strtotime(date("Y-m-d", time()))) / 3600 / 24;
    }
    else {
      $lastdays = (strtotime($date) - (strtotime('Y-m-d', time()))) / 3600 / 24;
    }
    return $lastdays;
  }

  public static function dounSerialize($string)
  {
    if (!empty($string)) {
      if (strtolower(OEPHP_CHARSET) == 'utf-8') {
        return self::utf_unserialize($string);

      }
      else {
        return self::gbk_unserialize($string);
      }
    }
    else {
      return '';
    }
  }
  private static function utf_unserialize($serial_str)
  {
    $serial_str = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'",
      $serial_str);
    $serial_str = str_replace("\r", "", $serial_str);
    return @unserialize($serial_str);
  }
  private static function gbk_unserialize($serial_str)
  {
    $serial_str = preg_replace('!s:(\d+):"(.*?)";!se', '"s:".strlen("$2").":\"$2\";"',
      $serial_str);
    $serial_str = str_replace("\r", "", $serial_str);
    return @unserialize($serial_str);
  }

  public static function getOsType()
  {
    $os      = explode(" ", php_uname());
    $os_name = $os[0];
    if ('/' == DIRECTORY_SEPARATOR) {
      $ver = $os[2];
    }
    else {
      $ver = $os[1];
    }
    return $os_name . ' ' . $ver;
  }

  public static function tplarray($tplarray)
  {

    if (!($tplarray)) {
      return $tplarray;
    }
    if (is_array($tplarray)) {
      return $tplarray;
    }
    $_value = explode(',', $tplarray);
    $out    = array();
    foreach ($_value as $key => $v) {
      if (preg_match("/(=>|:|=)/", $v)) {
        $tmp = preg_split("/(=>|:|=)/", $v);

        $tmp[1] = trim($tmp[1], '\'');
        if (preg_match("/^\[(.*)\]$/", $tmp[1], $tmp1)) {

          $tmp[1] = explode('|', $tmp1['1']);

        }
        $tmpa[trim($tmp[0], '\'')] = $tmp[1];

        $out = array_merge($out, $tmpa);
        unset($tmpa);
        unset($tmp);
      }
      else {
        $tmpa = (array(trim($v, '\'')));
        $out = array_merge($out, $tmpa);
        unset($tmpa);
        unset($tmp);
      }
    }

    return $out;

  }

  public static function buildTagArray($params = '')
  {
    $attributes = array();
    $_value = '';
    if (!empty($params)) {
      #mod
      if (strstr($params, 'mod={')) {
        $_attr       = explode('mod={', $params);
        $_attr_value = $_attr[1];
        #get value
        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $attributes['mod'] = $_value;
        }
      }
      if (strstr($params, 'fun={')) {
        $_attr       = explode('fun={', $params);
        $_attr_value = $_attr[1];
        #get value
        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $attributes['fun'] = $_value;
        }
      }
      if (strstr($params, 'svalue={')) {
        $_attr       = explode('svalue={', $params);
        $_attr_value = $_attr[1];
        #get value
        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $attributes['svalue'] = $_value;
        }
      }
      if (strstr($params, 'sname={')) {
        $_attr       = explode('sname={', $params);
        $_attr_value = $_attr[1];
        #get value
        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $attributes['sname'] = $_value;
        }
      }
      if (strstr($params, 'param1={')) {
        $_attr       = explode('param1={', $params);
        $_attr_value = $_attr[1];
        #get value

        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];

          $attributes['param1'] = $_value;
        }
      }
      if (strstr($params, 'param={')) {
        $_attr       = explode('param={', $params);
        $_attr_value = $_attr[1];
        #get value

        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];

          $attributes['param'] = $_value;
        }
      }
      if (strstr($params, 'field={')) {
        $_attr       = explode('field={', $params);
        $_attr_value = $_attr[1];

        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $out               = self::tplarray($_value);

          $attributes['field'] = $out;

        }
      }
      if (strstr($params, 'array={')) {
        $_attr       = explode('array={', $params);
        $_attr_value = $_attr[1];

        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $out               = self::tplarray($_value);

          $attributes['array'] = $out;

        }
      }
      if (strstr($params, 'limit={')) {

        $_attr       = explode('limit={', $params);
        $_attr_value = $_attr[1];

        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];

          $out               = self::tplarray($_value);
          $attributes['limit'] = $out;

        }

      }
      if (strstr($params, 'vton={')) {

        $_attr       = explode('vton={', $params);

        $_attr_value = $_attr[1];

        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $out               = self::tplarray($_value);

          $attributes['vton'] = $out;

        }
      }
      if (strstr($params, 'param3={')) {
        $_attr       = explode('param3={', $params);
        $_attr_value = $_attr[1];
        #get value
        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $attributes['param3'] = $_value;
        }
      }
      if (strstr($params, 'param4={')) {
        $_attr       = explode('param4={', $params);
        $_attr_value = $_attr[1];
        #get value
        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $attributes['param4'] = $_value;
        }
      }

      #type
      if (strstr($params, 'type={')) {
        $_attr       = explode('type={', $params);
        $_attr_value = $_attr[1];
        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $attributes['type'] = $_value;
        }
      }

      #where
      if (strstr($params, 'where={')) {
        $_attr       = explode('where={', $params);
        $_attr_value = $_attr[1];
        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $attributes['where'] = $_value;
        }
      }

      #order by
      if (strstr($params, 'orderby={')) {

        $_attr       = explode('orderby={', $params);
        $_attr_value = $_attr[1];

        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $out               = self::tplarray($_value);

          if (sizeof($out) == 1) {
            $attributes['orderby'] = $out[0];
          }
          else {
            $attributes['orderby'] = $out;
          }

        }
      }

      #num
      if (strstr($params, 'num={')) {
        $_attr       = explode('num={', $params);

        $_attr_value = $_attr[1];
        if (strstr($_attr_value, '}')) {

          $_attr_right_array = explode('}', $_attr_value);

          $_value            = $_attr_right_array[0];
          $attributes['num'] = $_value;
        }
      }

      #child num
      if (strstr($params, 'childnum={')) {
        $_attr       = explode('childnum={', $params);
        $_attr_value = $_attr[1];
        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $attributes['childnum'] = $_value;
        }
      }

      #cid
      if (strstr($params, 'catid={')) {
        $_attr       = explode('catid={', $params);
        $_attr_value = $_attr[1];
        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $attributes['catid'] = $_value;
        }
      }

      #value
      if (strstr($params, 'value={')) {
        $_attr       = explode('value={', $params);
        $_attr_value = $_attr[1];
        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $attributes['value'] = $_value;
        }
      }

      if (strstr($params, 'contday={')) {
        $_attr       = explode('contday={', $params);
        $_attr_value = $_attr[1];
        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $attributes['contday'] = $_value;
        }
      }
      if (strstr($params, 'html={')) {
        $_attr       = explode('html={', $params);
        $_attr_value = $_attr[1];
        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $attributes['html'] = $_value;
        }
      }
      if (strstr($params, 'name={')) {
        $_attr       = explode('name={', $params);
        $_attr_value = $_attr[1];
        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $attributes['name'] = $_value;
        }
      }
      if (strstr($params, 'select={')) {
        $_attr       = explode('select={', $params);
        $_attr_value = $_attr[1];
        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $attributes['select'] = $_value;
        }
      }
      if (strstr($params, 'empty={')) {
        $_attr       = explode('empty={', $params);
        $_attr_value = $_attr[1];
        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $attributes['empty'] = $_value;
        }
      }

      if (strstr($params, 'startmonth={')) {
        $_attr       = explode('startmonth={', $params);
        $_attr_value = $_attr[1];
        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $attributes['startmonth'] = $_value;
        }
      }

      if (strstr($params, 'endmonth={')) {
        $_attr       = explode('endmonth={', $params);
        $_attr_value = $_attr[1];
        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $attributes['endmonth'] = $_value;
        }
      }

      if (strstr($params, 'link={')) {
        $_attr       = explode('link={', $params);
        $_attr_value = $_attr[1];
        if (strstr($_attr_value, '}')) {
          $_attr_right_array = explode('}', $_attr_value);
          $_value            = $_attr_right_array[0];
          $out               = self::tplarray($_value);
          $attributes['link'] = $out;

        }
      }

    }
    return $attributes;
  }
  //把模板参数转出数组返回
  /*
  *@$params 分割参数

  */
  public static function fixTag($params = '')
  {
    if (!$params)return $params;


    $fix = array('@!','!@');
    $fixto = array('{','}');
    return str_replace($fix,$fixto,$params);

  }
}
