<?php

namespace ng169\control\index;

use ng169\control\indexbase;
use ng169\tool\Out;
use ng169\lib\Lang;

checktop();

class down extends indexbase
{

    protected $noNeedLogin = ['*'];

    public function control_run()
    {
        // \ng169\lib\Lang->load();
        // Lang::init('ms');
        // Lang::echo();
        // d(Lang::echo(), 1);
        $users_id = get(['int' => ['uid' => 1, 'nap']]);
        $info = [
            'description' => __('多语言免费、流行、浪漫、小说,漫画阅读器.'),
            'locale' =>      __('zh_CN'),
            'zl' =>          __('助力好友'),
            'downplay' =>    __('下载'),
            'down' =>        __('本地下载'),
            'alert' =>        __('请在浏览器打开'),
  

        ];
        $apk = T('version_upgrade')->set_field('apk_url')->order_by(['s' => 'down', 'f' => 'id'])->set_where(['type' => 2])->get_one();
        $ret = array_merge($users_id, $apk, $info, ['url' => geturl($users_id, null, 'down'),]);
        $this->view(null, $ret);
    }
    public function control_downiphone()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $invite = T('user_invite')->where('ip', $ip)->find();
        if (!($invite)) {
            $data = get(['int' => ['users_id', 'type']]);
            $data['invite_time'] = date('Y-m-d H:i:s', time());
            $data['status'] = 0;
            $data['u_id'] = $data['users_id'];
            $data['ip'] = $ip;
            unset($data['users_id']);
            $res = T('user_invite')->add($data);
            if ($res) {
                Out::jout('邀请成功', '');
            } else {
                Out::jout('邀请成功', '');
            }
        } else {
            Out::jout('邀请成功', '');
        }
    }
}
