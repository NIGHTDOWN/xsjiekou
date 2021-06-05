<?php
namespace ng169\tool;
use ng169\tool\Phpmail as YPhpmail;
use ng169\tool\Smtp as YSmtp;


checktop();
class Mail {
    
    public function sendMail($items, $mailto, $subject, $content, $attachment='') {
        
       
       /*im(TOOL.'class.smtp.php');
         im(TOOL.'class.phpmailer.php');*/
        $mail = new YPhpmail();
        
        $mail->CharSet = G_CHARSET; 
        $mail->Encoding = "base64";
        $mail->Port = $items['port'];
        
        
        if(intval($items['sendtype']) == 1){
        	$mail->IsSMTP();
        }
        
        else{
        	$mail->IsMail();
        }
        $mail->Host = $items['smtp'];
        $mail->SMTPAuth = true;
        $mail->Username = $items['sendmail'];
        $mail->Password = $items['sendpassword'];
        $mail->From = $items['sendmail'];
       
      /* $mail->Username = 'a752942639@163.com';
        $mail->Password = 'y15363210895';
        $mail->From = 'a752942639@163.com';*/
       
       
        $mail->FromName = $items['sendname'];
        if($items['ssl']){
            $mail->SMTPSecure = 'ssl';
        }
        $mail->AddAddress($mailto);
      
        
        $mail->IsHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $content;
     
        
        if (!empty($attachment)) {
            
            if (substr($attachment, 0, 15) == 'data/attachment') {
                $mail->AddAttachment(ROOT.$attachment);
            }
        }
        $mail->AltBody = "This is the body in plain text for non-HTML mail clients";
        if($mail->Host == 'smtp.gmail.com') $mail->SMTPSecure = "ssl";
  
        if(!$mail->Send())
        {
        	
        	echo $mail->ErrorInfo;
        	return false;
        }else{
        	return true;
        }
    }
}
?>
