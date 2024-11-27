<?php

namespace ng169\control\api;

use ng169\control\apiv1base;
use ng169\tool\Out;

checktop();
class chat extends apiv1base
{
    protected $noNeedLogin = ['set'];
    //获取消息记录
    public function control_list()
    {

        $pages = get(['int' => ['page', 'msgid']]);
        $mgid = T('msg')->set_where(['fuid' => $this->get_userid(), 'flag' => 0])->get_one();
        if (!$mgid) $this->returnSuccess([]);
        T('msg')->update(['fread' => 0], ['fuid' => $this->get_userid(), 'flag' => 0]);
        $list = T('msg_content')
            ->where(['msgid' => $mgid['msgid']])->where('id>' . ($pages['msgid'] ? $pages['msgid'] : 0))->order('sendtime desc')->set_limit([$pages['page'], 100])->get_all();
        $this->returnSuccess($list);
    }
    //获取消息
    public function control_havemsg()
    {
        $pages = get(['int' => ['page']]);
        $mgid = T('msg')->set_where(['fuid' => $this->get_userid(), 'flag' => 0])->set_field('fread')->get_one();
        if ($mgid && $mgid['fread'] > 0) {
            //有消息
            $this->returnSuccess($mgid['fread']);
        }
        //没消息
        $this->returnSuccess(0);
    }
    //语言设置开关 0关闭，1显示
    public function control_set()
    {
        //关闭语言选择
        $hide=T("option")->set_where(['option_name'=>'hidecity'])->get_one();
        // if ($this->head['cityid'] == 1) {
        //     //美国直接关闭语言选择 0关闭，1显示
        //     $this->returnSuccess(1);
        // }
        $v= !$hide['option_value'];
        $this->returnSuccess($v);
    }
    public function control_send()
    {
        //发送消息
        $get = get(['string' => [
            // 'fuid',
            // 'msgid',
            // 'tuid',
            // 'type',
            // 'sendtime',
            // 'contenttype',
            'content' => 1,
        ], 'int' => ['contenttype' => 1]]);
        $uid = $this->get_userid();
        $msg = [
            // 'fuid',
            // 'tuid' => 0,
            'type' => 0,
            'sendtime' => time(),
            'contenttype' => $get['contenttype'],
            'content' => $get['content'],
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
            Out::jout($b);
        } else {
            Out::jerror('发送失败', null, '150155');
        }
    }
}
