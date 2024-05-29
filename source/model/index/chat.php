<?php

namespace ng169\model\index;

use ng169\Y;
use ng169\tool\Out;
use ng169\tool\Request;

checktop();
//统计埋点
class chat extends Y
{
    public function msgindb($uid,$contenttype,$content)
    {
        //发送消息
       
        
        $msg = [
            // 'fuid',
            // 'tuid' => 0,
            'type' => 0,
            'sendtime' => time(),
            'contenttype' => $contenttype,
            'content' => $content,
        ];
        $mgid = T('msg')->set_where(['fuid' => $uid, 'flag' => 0])->get_one();
        if ($mgid) {
            //更新标识未读
            T('msg')->update(['tread' => $mgid['tread'] + 1, 'fread' => 0], ['fuid' => $uid, 'flag' => 0]);
            $msgid = $mgid['msgid'];
        } else {
            $msgid = T('msg')->add(['tread' => 1, 'fread' => 0,  'flag' => 0, 'addtime' => time(), 'fuid' => $uid, 'tuid' => 0]);
        }
        $msg['msgid'] = $msgid;
        $msg['suid'] = $uid;
        $b = T('msg_content')->add($msg);
        if ($b) {
           return $b;
        } else {
            return false;
        }
    }
}
