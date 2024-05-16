<?php



namespace ng169\tool;

checktop();

class Ngmatch
{
   /**
    * $str 被提取的字符串
    * $clear 去除URl里面的参数
    */
    public static function geturlimg($str,$clear=false)
    {
        $pattern = '/\bhttps?:\/\/[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/))/i';

        preg_match_all($pattern, $str, $matches);
        if($clear){
            if(is_array($matches[0])){
              foreach($matches[0] as $k=>$v){
                $matches[0][$k]=self::fiximgstr($v);
              }
            }
        }

       return $matches[0];
    }
    public static function geturlimgone($str,$clear=false){
      $data=self::geturlimg($str,$clear);
      if(sizeof($data)>0){
        return $data[0];
      }
      return $data;
    }
    /**
     * 去除图片URl后面的参数
     */
    public static function fiximgstr($str)
    {
        $pattern = '/\?.*$/';
        // 使用 preg_replace 移除 URL 末尾的查询字符串
        $cleanedUrl = preg_replace($pattern, '', $str);
        return $cleanedUrl;
    }
    public static function getnum($str)
    {
        $pattern = '/\d+(\.\d+)?/';
        preg_match_all($pattern, $str, $matches);
       return $matches[0];
    }
    public static function geturllast($str,$neednum=1)
    {
      $a= explode("/",$str);
      if(sizeof($a)>0 && $neednum==1){
        return $a[sizeof($a)-1];
      }
      $str="";
      if(sizeof($a)>0 && $neednum>1){
        for ($i=1; $i <= $neednum; $i++) { 
          $str=$a[sizeof($a)-$i]."/".$str;
        }
        $str=trim($str,"/");
        return $str;
      }
      return "";
    }

}

?>
