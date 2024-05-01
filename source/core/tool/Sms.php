<?php

namespace ng169\tool;

checktop();


class Sms
{
    public $uid = '';
    public $pwd = '';
	public $url = 'http://qian1998.com/index.php?'; 	
   	/*public $url = 'http://hz.com/index.php?';*/
    private function _init()
    {
        if (!Y::$conf['smsflag']) {
            YLog::txt('短信接口已经关闭');
            die();
        }
        $this->uid = Y::$conf['smssender'];
        $this->pwd = (Y::$conf['smspassword']);
    }


    public function send($phone,$message,$user = null,$pwd = null)
    {
        $this->_init();
        $username = $user?$user:$this->uid;
        $pwd      = $pwd?$pwd:$this->pwd;
 
   		$message=$message."【".Y::$conf['site_name']."】";
		
        $url      = $this->url."c=sms&a=send&account={$username}&password={$pwd}&mobile=".$phone."&content=".rawurlencode($message);
       
        $back     = $this->_back($this->_send($url));
        
        return $back;
    }

    public function getinfo()
    {
        $this->_init();
        $username = $this->uid;
        $pwd      = $this->pwd;
        $url      = $this->url."c=sms&a=getnum&account={$username}&password={$pwd}";
     
        $back     = $this->_back($this->_send($url));
        return $back;
    }
     
    private  function _send($url)
    {
        
            $ch            = curl_init();
            $timeout       = 3000;
            curl_setopt ($ch, CURLOPT_URL, $url);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file_contents = curl_exec($ch);
            curl_close($ch);
        
        return $file_contents;
    }
    private function _back($xml)
    {
    
        $data= json_decode($xml);
      
        if($data->flag==0){
			return array('code'=>0,'msg'=>$data->data);
		}else{
			return array('code'=>2,'msg'=>$data->data,'num'=>$data->num);
		}
    }



}


?>
