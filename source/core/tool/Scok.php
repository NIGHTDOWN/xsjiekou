<?php

namespace ng169\tool;

class Sock
{
  private $function = '_Channel';
  private $logfile = 'sock_log.txt';
  private function _choose_channel()
  {
    if (function_exists('stream_socket_client')) {
      return 1;
    }
    else {
      return 2;
    }
  }
  public function fork($file,$array = null)
  {
    $function = $this->function.$this->_choose_channel();
    $this->$function($file,$array);
  }

  private function _Channel1($file,$array = null)
  {
    $host = $_SERVER['HTTP_HOST'];
    $param= null;
    if (is_array($array)) {
      foreach ($array as $name=>$value) {
        $param .= "&$name=$value";
      }
      $param = trim($param,'&');
    }
    $fp = stream_socket_client("$host:80", $errno, $errstr, $timeout, STREAM_CLIENT_ASYNC_CONNECT | STREAM_CLIENT_CONNECT);
    if ($fp) {
      $this->log("$host sock通道1(stream_socket_client)打开成功");
      fwrite($fp, "GET /$file?$param HTTP/1.0\r\nHost: $host\r\nAccept: */*\r\n\r\n");








      fclose($fp);
    }
    else {
      $this->error("$host sock通道1(stream_socket_client)打开失败");

    }

    return 0;
  }

  private function _Channel2($file,$array = null)
  {
    $host = $_SERVER['HTTP_HOST'];
    $param= null;
    if (is_array($array)) {
      foreach ($array as $name=>$value) {
        $param .= "&$name=$value";
      }
      $param = trim($param,'&');
    }

    $fp = fsockopen("$host", 80, $errno, $errstr, 30);
    if (!$fp) {
      $this->error("$host sock通道2(fsockopen)打开失败");
    }
    else {
      $this->log("$host sock通道2(fsockopen)打开成功");
      fwrite($fp, "GET /$file?$param HTTP/1.0\r\nHost: $host\r\nAccept: */*\r\n\r\n");

      fclose($fp);
    }
  }
  private function log($msg)
  {
    $time = date("Y-m-d H:i:s");
    $msg  = "\n日志开始-------------\n".$time."\n".$msg."\n日志结束*****************\n\n";
    $this->_outmsg($msg);
  }


  private function error($msg)
  {
    $time = date("Y-m-d H:i:s");
    $msg  = "\n日志错误开始$$$$$$$$$$$$$-------------\n\n".$time."\n".$msg."\n日志结束$$$$$$$$$$$*****************\n\n";
    $this->_outmsg($msg);
  }
  private function _outmsg($msg)
  {
    $logfile = date("Ymd").$this->logfile;
    $jb      = fopen($logfile,'a+');
    if ($jb) {
      fwrite($jb,$msg,strlen($msg));
      fclose($jb);
    }
    return ;
  }
}

?>
