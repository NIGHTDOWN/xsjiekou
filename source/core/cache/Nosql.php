<?php
namespace ng169\cache;

checktop();
class Nosql
{
    #缓存目录
    private $nosqlobj ;
    #超时时长
    private  $timeout;
    #编码设置
    private $charset;
    private $flag = false;
    private $host;
    private $post;
    public
    function __construct($host = null,$port = null,$timeout = null)
    {
        $this->host = $host?$host:CACHE_NOSQL_HOST;
        $this->port = $port?$port:CACHE_NOSQL_PORT;
        $this->timeout = $timeout?$timeout:CACHE_TIMEOUT;        
        $this->nosqlobj = new Memcache;
       /* $this->nosqlobj->auth('mypasswords123sdfeak'); //密码验证
   		$this->nosqlobj->select(2);//选择数据库2*/
        $this->nosqlobj->connect($this->host, $this->port);
        return $this;
    }
    public
    function set($name,$value,$timeout = null)
    {
        if (!$name)return false;
        $timeout = $timeout?$timeout:$this->timeout;
        return  $this->nosqlobj->set($name,$value,$this->flag,$timeout);

    }
    public
    function get($name)
    {
        if (!$name)return false;
        $data = $this->nosqlobj->get($name,$this->flag);
        return array((bool)($data),$data);
    }
    public
    function del($name = null)
    {
        if (!$name)return $this->nosqlobj->flush();
        return   $this->nosqlobj->delete($name);
    }




}
?>
