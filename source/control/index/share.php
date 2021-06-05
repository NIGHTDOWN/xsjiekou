<?php

namespace ng169\control\index;

use ng169\control\indexbase;
use ng169\tool\Out;
use ng169\tool\Request;

checktop();

class share extends indexbase
{

    protected $noNeedLogin = ['*'];
    //分享小说
    public function control_book()
    {
        $get = get(['int' => ['uid' => 1, 'bid' => 1, 'nap']]);
        $users_id = $get['uid'];
        $book_id = $get['bid'];

        $w = ['book_id' => $book_id];
        $list = T('book')->field('other_name,bpic,`desc`,share_banner,book_id,lang')->where($w)->find();
        if (!$list) {
            Out::page404();
        }
        $tpsec = M('book', 'im')->gettpsec(1, $list['lang']);
        $tpsecc = M('book', 'im')->gettpseccontent(1, $list['lang']);
        M('census', 'im')->shareclick($users_id, $book_id, 1, $get['nap']);
        $section = T($tpsec)->field('section_id,title,list_order')->where($w)->order('section_id')->find();
        $len = mb_strlen($section['title'], 'utf-8');
        $section_content = T($tpsecc)->field('sec_content')->where(['section_id' => $section['section_id']])->order('sec_content_id')->find();
        // $section_content['sec_content'] = htmlspecialchars_decode($section_content['sec_content']);
        //把换行替换成<p />
        // $section_content['sec_content'] = str_replace(array("\r\n", "\r", "\n"), ['<br/>',"<p/>"],  $section_content['sec_content']);
        // $section_content['sec_content'] = preg_replace('/[\n,\r\n]+/', '<p/>', $section_content['sec_content']);
        // $section_content['sec_content'] = preg_replace('/\s+/', '&nbsp;', $section_content['sec_content']);
        // d($section_content['sec_content']);
        // $section_content['sec_content'] = preg_replace("#[\x{04}-\x{15}]#u", "", $section_content['sec_content']);
        // d($section_content['sec_content']);
        // $section_content['sec_content'] = preg_replace('/\s+/', '&nbsp;', $section_content['sec_content']);
        $boj = M('book', 'im');
        $section_content['sec_content'] = $boj->nl2p($boj->trimhtml($section_content['sec_content']));
        $data = ['uid' => $users_id, 'book' => $list, 'content' => $section_content, 'section' => $section];
        $shareinfo = T('n_share')->get_one(['book_id' => $book_id, 'type' => 1]);
        $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if (!$shareinfo) {
            $shareinfo['shareimg'] = $list['bpic'];
            $shareinfo['sharetitle'] = $list['other_name'];
            $shareinfo['sharecontent'] = $list['desc'];
        }

        $shareinfo['url'] = $url;
        $data['share'] = $shareinfo;
        $ret = array_merge($data, $get);
        $up = T($tpsec)->set_where(['book_id' => $get['bid'], 'isdelete' => 0])->set_where('list_order>' . $section['list_order'])->set_field('section_id')->order_by(['f' => 'list_order', 's' => 'up'])->get_one();
        $ret['next'] = $up['section_id'] ? $up['section_id'] : '0';
        $ret['nap'] = get(['string' => ['nap']])['nap'] ?: 0;

        $ret['locale'] = __('zh_CN');
        $ret['down'] =   __('本地下载');
        $ret['downplay'] = __('下载');
        $ret['description'] = __('多语言免费、流行、浪漫、小说,漫画阅读器.');
        $ret['alert'] = __('请在浏览器打开');
        $ret['open'] = __('APP内打开');
        $this->view(null, $ret);
    }
    //分享漫画
    public function control_cartoon()
    {
        $get = get(['int' => ['uid' => 1, 'cid' => 1, 'nap']]);
        $users_id = $get['uid'];
        $cartoon_id = $get['cid'];

        $w = ['cartoon_id' => $cartoon_id];
        $list = T('cartoon')->field('other_name,bpic,`desc`,hits,collect,cartoon_id,bpic_detail,lang')->where($w)->find();
        if (!$list) {
            Out::page404();
        }
        $tpsec = M('book', 'im')->gettpsec(2, $list['lang']);
        $tpsecc = M('book', 'im')->gettpseccontent(2, $list['lang']);
        M('census', 'im')->shareclick($users_id, $cartoon_id, 2, $get['nap']);
        $cartoon_section = T($tpsec)->field('cart_section_id,list_order')->where($w)->order('cart_section_id')->find();
        $cart_section_content = T($tpsecc)

            ->field('v.cart_sec_content,v.cart_section_id')->where(['v.cart_section_id' => $cartoon_section['cart_section_id']])->order('cart_sec_content_id')->get_one();
        if ($cart_section_content) {
            $cart_section_content['cart_sec_content'] = json_decode($cart_section_content['cart_sec_content'], true);
        }
        // $groom = T('groom')->field('book_id,bpic,other_name')->where(['status' => 1])->where(['type' => 2])->where('cartoon_id!=' . $cartoon_id)->limit([0, 4])->get_all();
        $data = ['uid' => $users_id, 'book' => $list, 'content' => $cart_section_content, 'section' => $cartoon_section];
        $shareinfo = T('n_share')->get_one(['book_id' => $cartoon_id, 'type' => 2]);
        $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if (!$shareinfo) {
            $shareinfo['shareimg'] = $list['bpic'];
            $shareinfo['sharetitle'] = $list['other_name'];
            $shareinfo['sharecontent'] = $list['desc'];
        }
        $shareinfo['url'] = $url;
        $data['share'] = $shareinfo;
        $up = T($tpsec)->set_where(['cartoon_id' => $cartoon_id, 'isdelete' => 0])->set_where('list_order>' . $cartoon_section['list_order'])->set_field('cart_section_id')->order_by(['f' => 'list_order', 's' => 'up'])->get_one();

        $ret = array_merge($data, $get);
        $ret['next'] = $up['cart_section_id'] ? $up['cart_section_id'] : '0';
        $ret['nap'] = get(['string' => ['nap']])['nap'] ?: 0;
        // $this->jiesuanjl();
        $ret['locale'] = __('zh_CN');
        $ret['down'] =   __('本地下载');
        $ret['downplay'] = __('下载');
        $ret['description'] = __('多语言免费、流行、浪漫、小说,漫画阅读器.');
        $ret['alert'] = __('请在浏览器打开');
        $ret['open'] = __('APP内打开');
        $this->view(null, $ret);
    }
    //结算分享奖励
    function control_jiesuanjl()
    {
        $get = get(['string' => ['uid', 'cid', 'bid', 'nap']]);


        //每种分享点击判断
        // uid, addtime, bookid, type
        $ww['addtime'] = $get['nap'];
        $ww['uid'] = $get['uid'];
        if ($get['cid']) {
            $ww['bookid'] = $get['cid'];
            $ww['type'] = 2;
        }
        if ($get['bid']) {
            $ww['bookid'] = $get['bid'];
            $ww['type'] = 1;
        }
        if (!$ww['bookid']) return false;
        $find = T('n_shareclick')->set_field('id,sharetype,isgetcoin,num,otherdaynum')->set_where($ww)->get_one();
        if (!$find) {
            //找不到记录直接退出
            echo json_encode(['code' => 0]);
            return false;
        }

        //记录点击次数加1
        T('share_visit')->add(['shareid' => $find['id'], 'visittime' => time(), 'ip' => Request::getip(), 'datestime' => date('YmdH'), 'type' => $find['sharetype']]);


        if (!$get['nap']) return false;
        $up['num'] = $find['num'] + 1;
        if (date('Ymd') != date('Ymd', $get['nap'])) {
            $up['otherdaynum'] = $find['otherdaynum'] + 1;
            T('n_shareclick')->update($up, $ww);
            return false;
        } //非当日分享不结算奖励
        T('n_shareclick')->update($up, $ww);
        if ($find['isgetcoin']) {
            echo json_encode(['code' => 0]);
            return false;
        }
        if (!$get['uid']) return false;
        $w2['sharetype'] = $find['sharetype'];
        $w2['uid'] = $get['uid'];
        $w2['dates'] = date('Ymd', $ww['addtime']);
        $w2['isgetcoin'] = 1;

        $find2 = T('n_shareclick')->set_field('isgetcoin')->set_where($w2)->get_one();
        if ($find2) {
            echo json_encode(['code' => 0]);
            return false;
        } else {
            //当前类型没奖励，所有结算奖励
            $w3['isgetcoin'] = 1;
            unset($w2['isgetcoin']);
            T('n_shareclick')->update($w3, $w2);
        }
        if ($get['cid'] || $get['bid']) {
            // $coin = 50;
            $coin = 100;
            //结算
            //先判断是否已经结算了
            $w['uid'] = $get['uid'];
            $w['type'] = 1;
            $w['dates'] = date('Ymd');
            $check = T('usertask')->set_where($w)->get_one();
            if (!$check) {
                $w['addtime'] = time();
                $w['num'] = 1;
                T('usertask')->add($w);
            } else {
                if ($check['num'] >= 4) {
                    echo json_encode(['code' => 0]);
                    return false;
                }
                $up['num'] = $check['num'] + 1;
                T('usertask')->update($up, $w);
            }

            M('census', 'im')->task_reward_count($w['uid'], $coin, 1);
            M('coin', 'im')->change($w['uid'], $coin);
            // $user = T('third_party_user')->set_field('remainder,golden_bean')->get_one(['id' => $w['uid']]);
            echo json_encode(['code' => 1]);
            return;
        }
    }
}
