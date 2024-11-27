<?php

namespace ng169\control\apiv1;

use ng169\control\apiv1base;
use ng169\tool\Out;
use ng169\Y;
use ng169\tool\Request;

checktop();
class groom extends apiv1base
{

    protected $noNeedLogin = ['to_rack'];
    //获取分类
    public function control_to_rack()
    {
        M('census', 'im')->_install(); //安装统计
        $cityid = $this->head['cityid'];

        $index = 'torackcache' . $cityid;
        $cache = Y::$cache->get($index);
        if ($cache[0]) {
            $data = $cache[1];
        } else {
            $data = T('groom')
                ->field('other_name,bpic,book_id,type,isgroom,`desc`')
                ->where('status=1 and groom_type=2')
                ->set_global_where(['lang' => $cityid])
                // ->order_by('grooms_id desc')
                ->limit(5)
                ->get_all();
            foreach ($data as $key => $value) {
                $data[$key]['desc'] = str_replace("&quot;", "\"", $value['desc']);
            }
            Y::$cache->set($index, $data, G_DAY);
        }

        $this->returnSuccess($data);
    }
    // 加入书架
    public function control_add_rack()
    {
        $data = get(['int' => ['book_id' => 1, 'type' => 1]]);
        $flag = M('rack', 'im')->addrack($this->get_userid(1), $data['type'], $data['book_id']);
        if ($flag) {
            Out::jout('加入成功');
        } else {
            Out::jerror('添加失败', null, '1001291');
        }
        // $arr = $data;
        // // $arr['rack_time'] = date('Y-m-d H:i:s', time());
        // // $arr['read_time'] = date('Y-m-d H:i:s', time());
        // $racks1['read_time'] = date('Y-m-d H:i:s', time());
        // $arr['users_id'] = $this->get_userid(1);
        // $where['users_id'] = $arr['users_id'];
        // $where['book_id'] = $arr['book_id'];
        // $where['type'] = $arr['type'];
        // $where['status'] = 1;
        // //$where['isdelete'] = '0';
        // $res = T('user_groom')->set_field('grooms_id')->where($where)->find();
        // if ($res) {
        //     Out::jerror('已經加入書架了', null, '100128');
        // }
        // if ($arr['type'] == '1') {
        //     $w = ['book_id' => $arr['book_id']];
        //     $book = T('book')->field('other_name,bpic,`desc`,isfree')->where($w)->find();

        //     if ($book) {
        //         $w['other_name'] = $book['other_name'];
        //         $w['bpic'] = $book['bpic'];
        //         $w['desc'] = $book['desc'];
        //         //$racks1['rack_id'] = T('racks')->addid($arr);
        //         //T('racks_1')->add($racks1);
        //         M('census', 'im')->collectcounts($w['book_id']);
        //         // M('census', 'im')->freecollectcounts($w['book_id']);

        //         $w['status'] = 1;
        //         $w['users_id'] = $this->users_id;
        //         $w['type'] = $arr['type'];
        //         T('user_groom')->add($w);
        //         M('bookcensus', 'im')->addgroom($this->get_userid(), 1, $data['book_id']);
        //         Out::jout('加入成功');
        //     }
        //     Out::jerror('ID不存在', null, '100129');
        // } else {
        //     $w1 = ['cartoon_id' => $arr['book_id']];
        //     $cartoon = T('cartoon')->field('other_name,bpic,`desc`,isfree')->where($w1)->find();
        //     if ($cartoon) {
        //         $w = ['book_id' => $arr['book_id']];
        //         $w['other_name'] = $cartoon['other_name'];
        //         $w['bpic'] = $cartoon['bpic'];
        //         $w['desc'] = $cartoon['desc'];
        //         // $racks1['rack_id'] = T('racks')->add($w);
        //         // T('racks_1')->add($racks1);

        //         M('census', 'im')->cartooncollectcounts($w['book_id']);

        //         // M('census', 'im')->cartoonfreecollectcounts($w['book_id']);

        //         $w['users_id'] = $this->get_userid(1);
        //         $w['type'] = $arr['type'];
        //         $w['status'] = 1;

        //         T('user_groom')->add($w);
        //         M('bookcensus', 'im')->addgroom($this->get_userid(), 2, $data['book_id']);
        //         Out::jout('加入成功');
        //     }
        //     Out::jerror('ID不存在', null, '100129');
        // }
    }
    // 删除书架书籍
    public function control_delrack()
    {
        $data = get(['string' => ['book_id', 'cartoon_id']]);
        $data = M('rack', 'im')->del($this->get_userid(), $data['book_id'], $data['cartoon_id']);

        // if ($data['book_id']) {
        //     T('user_groom')->where(['type' => 1,  'users_id' => $this->get_userid()])->whereIn('book_id', $data['book_id'])->del();
        // }

        // if ($data['cartoon_id']) {
        //     T('user_groom')->where(['type' => 2,  'users_id' => $this->get_userid()])->whereIn('book_id', $data['cartoon_id'])->del();
        // }
        Out::jout($data);
    }
    // 获取书架信息
    public function control_get_rack()
    {
        // M('census', 'im')->logcount($this->get_userid()); //安装统计
        // //更新用户登入时间，版本号
        // T('third_party_user')->update(['last_login_time' => time(),], ['id' => $this->get_userid()]);
        // $buw = ['users_id' => $this->uid];
        // $book_ids = [];
        // $cartoon_ids = [];
        // $datas = T('user_groom')
        //     ->field('other_name,bpic,book_id,type,isgroom')
        //     ->where('status=1')
        //     ->order_by('grooms_id desc')
        //     ->where($buw)
        //     ->limit(100)
        //     ->get_all();
        // foreach ($datas as $k => $book) {
        //     if ($book['type'] == 1) {
        //         array_push($book_ids, $book['book_id']);
        //     } else {
        //         array_push($cartoon_ids, $book['cartoon_id']);
        //     }
        // }
        // $data = array_column($datas, null, 'book_id');

        // if (sizeof($book_ids)) {
        //     $tmp = T('book')->set_field('book_id,section,update_status')->whereIn('book_id', $book_ids)->get_all();

        //     foreach ($tmp as $key => $value) {
        //         if ($data[$value['book_id']]) {
        //             $data[$value['book_id']]['newnum'] = $value['section'];
        //         }
        //     }
        // }
        // if (sizeof($cartoon_ids)) {
        //     $tmp = T('cartoon')->set_field('cartoon_id,section,update_status')->whereIn('cartoon_id', $cartoon_ids)->get_all();
        //     foreach ($tmp as $key => $value) {
        //         if ($data[$value['cartoon_id']]) {
        //             $data[$value['cartoon_id']]['newnum'] = $value['section'];
        //         }
        //     }
        // }
        // $datas = array_values($data);
        // 合并用户自主添加书架和系统分配书籍
        $datas = M('rack', 'im')->list($this->get_userid());
        $this->returnSuccess($datas);
    }
}
