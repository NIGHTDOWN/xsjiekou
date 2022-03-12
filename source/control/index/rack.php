<?php

namespace ng169\control\index;

use FacebookAds\Api;
use ng169\cache\Rediscache;
use ng169\control\indexbase;
use ng169\lib\Log;
use ng169\tool\Out;

checktop();



class rack extends indexbase
{

    protected $noNeedLogin = ['add', 'readpoint'];
    //然后阅读记录
    public function control_readpoint()
    {
        $get = get(['int' => ['bookid', 'type', 'groom', 'ajax']]);
        if (!$get['bookid']) Out::jout(0);
        if (!$get['type']) Out::jout(0);
        $uid = $this->get_userid();
        if (!$uid) Out::jout(0);
        $sid = M('rack', 'im')->getpoint($uid, $get['bookid'], $get['type'], $get['groom']);
        Out::jout($sid);
        // $data = M('rack', 'im')->readhis($this->get_userid(), $get['page']);
    }
    public function control_run()
    {
        $data = M('rack', 'im')->list($this->get_userid());
        $this->view(null, ['data' => $data]);
    }
    public function control_his()
    {
        $get = get(['int' => ['page', 'ajax']]);

        $data = M('rack', 'im')->readhis($this->get_userid(), $get['page']);
        // foreach ($data as $k => $book) {
        //     $data[$k]['tags'] =  M('cate', 'im')->getlable($book['lable'], $this->langid);
        // }
        if ($get['ajax']) {
            Out::jout($data);
        } else {
            $this->view(null, ['data' => $data]);
        }
        // $this->view(null, ['data' => $data]);
    }
    public function control_del()
    {
        $data = get(['string' => ['book_id', 'cartoon_id']]);
        $data = M('rack', 'im')->del($this->get_userid(), $data['book_id'], $data['cartoon_id']);
        Out::jout($data);
    }
    //清空历史记录
    public function control_clearhis()
    {
        // $data = get(['string' => ['his_id']]);
        $data = M('rack', 'im')->clearhis($this->get_userid());
        Out::jout($data);
    }
    //删除历史记录
    public function control_delhis()
    {
        $data = get(['string' => ['his_id']]);
        $data = M('rack', 'im')->delhis($this->get_userid(), $data['his_id']);
        Out::jout($data);
    }
    /**加入书架 */
    public function control_add()
    {
        $data = get(['int' => ['book_id' => 1, 'type' => 1]]);
        $flag = M('rack', 'im')->addrack($this->get_userid(1), $data['type'], $data['book_id']);
        if ($flag) {
            Out::jout(__('加入成功'));
        } else {
            Out::jerror(__('加入失败'), null, '1001291');
        }
    }
}
