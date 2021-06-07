<?php

namespace ng169\control\api;

use ng169\control\apibase;
use ng169\tool\Code;
use ng169\tool\Out;
use ng169\tool\Request;
use ng169\Y;

checktop();
class common extends apibase
{

    protected $noNeedLogin = ['*'];
    //获取分类
    public function control_run()
    {

        Out::jout('1');
    }
    // 搜索书籍
    public function control_book_search()
    {


        $size = 5;
        $this->page_size = $size;
        $get = get(['string' => ['keyword' => 1, 'page']]);
        $cityid = $this->head['cityid'];
        $other_name = $get['keyword'];
        //$where['status'] = 1;
        $where = "`status`=1 and (other_name like \"%$other_name%\" or writer_name like \"%$other_name%\")";
        $devicetype = $this->head['devicetype'];
        if ($devicetype == 'iphone') {
            $order = "i_recharge desc";
        } else {
            $order = "recharge desc";
        }
        $book = T('book')
            ->field('book_id,other_name,`desc`,bpic,writer_name,isfree,update_status,1 as type')
            ->where($where)
            ->order($order)->set_global_where(['lang' => $cityid])
            ->set_limit([$get['page'], $size])
            ->get_all();
        $cartoon = T('cartoon')
            ->field('cartoon_id,other_name,`desc`,bpic,writer_name,isfree,update_status,2 as type')
            ->where($where)->set_global_where(['lang' => $cityid])
            ->order($order)
            ->set_limit([$get['page'], $size])
            ->get_all();

        foreach ($book as $key => $value) {
            $book[$key]['desc'] = str_replace("&quot;", "\"", $value['desc']);
            $book[$key]['type'] = 1;
        }
        foreach ($cartoon as $key => $value) {
            $cartoon[$key]['desc'] = str_replace("&quot;", "\"", $value['desc']);
            $cartoon[$key]['type'] = 2;
            $cartoon[$key]['book_id'] = $value['cartoon_id'];
            unset($cartoon[$key]['cartoon_id']);
        }
        $data = array_merge($book, $cartoon);
        M('book', 'im')->keylog($this->get_userid(), $other_name);
        $this->returnSuccess($data);
    }
    public function control_testandroid()
    {
        // d(bindec('0110100010010111100001110010100110010110010001011'));
        // d(4 ^ 7, 1);
        // $get = get(['string' => ['requesttime', 'devicesinfo', 'apps', 'utime']]);
        // $code = \ng169\tool\Code();
        $get2 = get(['string' => ['data']]);
        // $this->log($get2);
        $code      = Y::import('code', 'tool');
        $user = parent::$wrap_user;
       
        if ($this->head['token']) {
            $key = $this->head['token'];
        } else {
            $key = 'lookstory';
        }
        // $key = 'lookstory';

        $userinfo = $code->appdecode($get2['data'], $key);
        
        $get = json_decode($userinfo, 1);
       
        $get['idfa'] = $this->head['idfa'];
        // $get['apps'] = $get2['data'];
        $get['uid'] = $this->head['uid'];
        $get['addtime'] = time();
        $get['ip'] = Request::getip();
        $w['idfa'] = $this->head['idfa'];
        $w['isinda'] = 1;

        if (!T('user_info')->get_one($w)) {
            $get['isinda'] = 1;
            //记录app
            $apps = json_decode($get['apps'], 1);

            if ($apps) {
                foreach ($apps as $key => $app) {
                    # code...
                    $names = array_keys($app);
                    $name = $names[0];
                    $package = $app[$name];

                    $w2['package'] = $package;
                    $in = T('apps')->get_one($w2);
                    if ($in) {
                        T('apps')->update(['num' => $in['num'] + 1], $w2);
                    } else {
                        $insert = $w2;
                        $insert['num'] = 1;
                        $insert['name'] = $name;
                        T('apps')->add($insert);
                    }
                }
            } else {
                //app列表为空，直接推出
                return false;
            }
        }
        T('user_info')->add($get);
        //判断idfa是否已经录入

        Out::jout('1');
    }
    // 获取更多用户评论
    public function control_getmore_discuss()
    {
        // $data = $this->request->param();
        $data = get(['int' => ['book_id', 'cartoon_id', 'page']]);
        $users_id = $this->uid;

        if (($data['book_id']) && isset($data['page'])) {
            $w = ['book_id' => $data['book_id']];
            if ($users_id != "") {
                $list = T('discuss')
                    ->field('discuss_id,star,nick_name,discuss_time,content,users_id')
                    ->where($w)
                    ->where(['status' => 1])
                    ->order('star,discuss_time desc')
                    ->set_limit([$data['page'], 10]);
                $arr = $list->get_all();
                $owenDiscuss = T('discuss')
                    ->field('discuss_id,star,nick_name,discuss_time,content,users_id')
                    ->where($w)
                    ->where(['status' => 0])
                    ->where(['users_id' => $users_id])
                    ->order('discuss_time desc')
                    ->set_limit([$data['page'], 10]);
                $owen = $owenDiscuss->get_all();
                $discuss = array_merge($owen, $arr);
            } else {
                $list = T('discuss')
                    ->field('discuss_id,star,nick_name,discuss_time,content,users_id')
                    ->where($w)
                    ->where(['status' => 1])
                    ->order('star ,discuss_time desc')
                    ->set_limit([$data['page'], 10]);
                $discuss = $list->get_all();
            }
        } else if (($data['cartoon_id']) && isset($data['page'])) {
            $w = ['cartoon_id' => $data['cartoon_id']];

            if (!$users_id) {
                $list = T('discuss')
                    ->field('discuss_id,star,nick_name,discuss_time,content,users_id')
                    ->where($w)
                    ->where(['status' => 1])
                    ->order('star ,discuss_time desc')
                    ->set_limit([$data['page'], 10]);
                $arr = $list->get_all();

                $owenDiscuss = T('discuss')
                    ->field('discuss_id,star,nick_name,discuss_time,content,users_id')
                    ->where($w)
                    ->where(['status' => 0])
                    ->where(['users_id' => $users_id])
                    ->order('discuss_time desc')
                    ->set_limit([$data['page'], 10]);
                $owen = $owenDiscuss->get_all();
                $discuss = array_merge($owen, $arr);
            } else {
                $list = T('discuss')
                    ->field('discuss_id,star,nick_name,discuss_time,content,users_id')
                    ->where('cartoon_id', $data['cartoon_id'])
                    ->where('status', 1)
                    ->order('star ,discuss_time desc')
                    ->set_limit([$data['page'], 10]);
                $discuss = $list->get_all();
            }
        } else {
            Out::jerror('无效ID', null, '100133');
        }
        $user_id = "";
        foreach ($discuss as $key => $value) {
            $discuss_time = substr($value['discuss_time'], 0, -3);
            $time = strtotime($discuss_time);
            $discuss[$key]['discuss_time'] = date('d-m-Y H:i', $time);
            $discuss[$key]['content'] = str_replace("&quot;", "\"", $value['content']);
            $user_id .= $value['users_id'] . ",";
            $answers = T('further_discuss')->field('further_id,discuss_id,further_comment,reply_name')->where(['discuss_id' => $value['discuss_id']])->get_all();

            $discuss[$key]['answer'] = $answers;
        }

        $user_id = rtrim($user_id, ",");
        $avaters = T('third_party_user')->field('id,avater')->whereIn('id', $user_id)->get_all();

        foreach ($discuss as $key => $value) {
            foreach ($avaters as $k => $v) {
                if ($value['users_id'] == $v['id']) {
                    $discuss[$key]['avater'] = $v['avater'];
                }
            }
        }
        $this->returnSuccess($discuss);
    }
    public function control_get_sign()
    {
        $res = T('sign')
            ->field('sign_id,sign_day,sign_icon')->set_limit(7)
            ->get_all();
        // $str = "";
        // foreach ($res as $key => $value) {
        //     if ($value['sign_day'] == "8") {
        //         $str = $value['sign_icon'];
        //     }
        //     $res[$key]['sign_icon'] = $value['sign_icon'];
        // }
        // array_pop($res);
        // $resa = [
        //     'str' => $str,
        //     'res' => $res,
        // ];
        $this->returnSuccess($res);
    }
    // 添加点赞信息
    public function control_add_hits()
    {
        $data = get(['int' => ['book_id', 'cartoon_id', 'type']]);
        $users_id = $this->uid;
        if ($data['type'] == 1) {
            M('census', 'im')->hitcounts($data['book_id']);
            // $res = T('racks')->field('rack_id')->where(['book_id' => $data['book_id']])->where(['users_id' => $users_id])->find();
            // T('racks_1')->where(['rack_id' => $res['rack_id']])->update(['read_time' => date('Y-m-d H:i:s', time())]);
        } else {
            M('census', 'im')->cartoonhitcounts($data['cartoon_id']);
            // $res = T('racks')->field('rack_id')->where(['book_id' => $data['cartoon_id']])->where(['users_id' => $users_id])->find();
            // T('racks_1')->where(['rack_id' => $res['rack_id']])->update(['read_time' => date('Y-m-d H:i:s', time())]);
        }
        $res = [];
        $this->returnSuccess($res);
    }

    // 获取启动页广告
    public function control_begin_advert()
    {

        $plat = $this->head['devicetype'];
        if ($plat == 'iphone') {
            $where3 = "plat!='android'";
        } elseif ($plat == 'android') {
            $where3 = "plat!='ios'";
        }
        $where['status'] = 2;
        $data = T('advert')
            ->field('goal_type,goal_window,list_order,book_id,other_name,`desc`,advert_pic,advert_url,advert_name,cartoon_id,stop_time,`status`,end_time')
            ->where($where)->where($where3)
            ->where('end_time>' . time())
            ->get_one();
        if ($data && !$data['book_id']) {
            $book = T('cartoon')->field('isfree')->where(['cartoon_id' => $data['cartoon_id']])->find();
            $data['book_id'] = $data['cartoon_id'];
            $data['isfree'] = $book['isfree'];
        }
        if ($data && !$data['cartoon_id']) {
            $book = T('book')->field('isfree')->where(['book_id' => $data['book_id']])->find();
            $data['book_id'] = $data['book_id'];
            $data['isfree'] = $book['isfree'];
        } else {
            $data = [];
        }
        unset($data['cartoon_id']);
        $this->returnSuccess($data);
    }
    // 弹窗
    public function control_popup()
    {
        $plat = $this->head['Devicetype'];
        if ($plat == 'iphone') {
            $where3 = "plat!='android'";
        } elseif ($plat == 'android') {
            $where3 = "plat!='ios'";
        }
        $deviceToken = $this->head['Devicetoken'];
        $user_tag = T("third_party_user")->field('user_tag')->where(['deviceToken' => $deviceToken])->get_one();
        $where['activity_type'] = $user_tag['user_tag'];
        $where['status'] = 2;
        $data = T('pop')
            ->field('goal_type,goal_window,list_order,book_id,other_name,`desc`,advert_pic,advert_url,advert_name,cartoon_id,stop_time,`status`,end_time')
            ->where($where)->where($where3)
            ->where('end_time>' . time())
            ->get_one();
        if ($data && !$data['book_id']) {
            $book = T('cartoon')->field('isfree')->where(['cartoon_id' => $data['cartoon_id']])->find();
            $data['book_id'] = $data['cartoon_id'];
            $data['isfree'] = $book['isfree'];
        }
        if ($data && !$data['cartoon_id']) {
            $book = T('book')->field('isfree')->where(['book_id' => $data['book_id']])->find();
            $data['book_id'] = $data['book_id'];
            $data['isfree'] = $book['isfree'];
        }
        if ($data) {
            unset($data['cartoon_id']);
            // $data['goal_type'] = 4;
            // $data['other_name'] = str_replace("&quot;","\"",$data['other_name']);
            // unset($data['cartoon_id']);
            $this->returnSuccess($data);
        } else {
            $data = [];
            $this->returnSuccess($data);
        }
    }

    // 对比版本，进行更新
    public function control_add_version()
    {
        $deviceType = $this->head['devicetype'];
        $version = $this->head['version'];
        if ($deviceType == 'iphone') {
            $data['type'] = '1';
            $res = T('version_upgrade')->field('*')->where(['type' => $data['type']])->order('id desc')->find();
            $res['upgrade_point'] = str_replace("&quot;", "\"", $res['upgrade_point']);
            if (version_compare($version, $res['version_code'], 'ge')) {
                $res['apk_url'] = '';
                $res['types'] = 0;
                $this->returnSuccess($res);
            } else {
                $this->returnSuccess($res);
            }
            if (version_compare($version, $res['version_code'], '=')) {
                $res['apk_url'] = '';
                $res['types'] = 0;
                $this->returnSuccess($res);
            }
        } else {
            $data['type'] = '2';
            $res = T('version_upgrade')->field('*')->where(['type' => $data['type']])->order('id desc')->find();
            $res['upgrade_point'] = str_replace("&quot;", "\"", $res['upgrade_point']);
            if (version_compare($version, $res['version_code'], 'ge')) {
                $res['apk_url'] = '';
                $res['types'] = 0;
                $this->returnSuccess($res);
            } else {
                $res['apk_url'] = htmlspecialchars_decode($res['apk_url']);
                $this->returnSuccess($res);
            }
            if (version_compare($version, $res['version_code'], '=')) {
                $res['apk_url'] = '';
                $res['types'] = 0;
                $this->returnSuccess($res);
            }
        }
    }
    // 获取用户昵称和头像

    public function control_get_user_mess()
    {

        $users_id = get(['int' => ['users_id' => 1]]);
        $user = T('third_party_user')->field('id,avater,nickname')->where(['id' => $users_id['users_id']])->get_one();
        if ($user) {
            $this->returnSuccess($user);
        } else {
            $user = [];
            $this->returnSuccess($user);
        }
    }
    // 获取系统时间
    public function control_get_system_time()
    {
        $result['system_time'] = time();
        $this->returnSuccess($result);
    }
    public function control_get_wrongtype()
    {
        $info = [
            '1' => [
                '0' => 'content loading failed',
                '1' => 'wrong code and wrong characters',
                '2' => 'directory order error ',
                '3' => 'typography is chaotic ',
                '4' => 'content is blank or missing',
                '5' => 'repeat content or chapter ',
                '6' => 'the title is wrong ',
                '7' => 'bad information',
            ],
            '2' => [
                '0' => 'pemuatan isi gagal',
                '1' => 'kode yang salah dan karakter yang salah',
                '2' => 'error perintah direktori',
                '3' => 'tipografi adalah kekacauan',
                '4' => 'isi kosong atau hilang',
                '5' => 'ulangi isi atau bab',
                '6' => 'judul salah',
                '7' => 'informasi buruk',
            ],
            '3' => [
                '0' => "lỗi nạp nội dung",
                '1' => "Mã sai và các nhân vật sai",
                '2' => "lỗi trật tự thư mục",
                '3' => 'lỗi in hỗn loạn',
                '4' => "nội dung là rỗng hay thiếu.",
                '5' => "Lặp lại nội dung hay chương ",
                '6' => "Cái tựa bị sai",
                '7' => "thông tin xấu"
            ],
            '5' => [
                '0' => '内容加载失败',
                '1' => '乱码错别字',
                '2' => '目录顺序错误',
                '3' => '排版混乱',
                '4' => '内容空白或缺失',
                '5' => '重复内容或章节',
                '6' => '文题不对',
                '7' => '不良信息',
            ],
            '4' => [
                '0' => '내용 로드 실패',
                '1' => '코드 오류',
                '2' => '디 렉 터 리 순서 오류',
                '3' => '레이아웃 혼란',
                '4' => '내용 이 공백 또는 결여',
                '5' => '내용 이나 장 절 을 반복 한다',
                '6' => '문 제 는 틀 렸 다',
                '7' => '불량 정보',
            ],
            //错误类型泰文
            '0' => [
                '0' => 'โหลดเนื้อหาล้มเหลว',
                '1' => 'คำผิดอ่านไม่ออก',
                '2' => 'ลำดับสารบัญผิดพลาด',
                '3' => 'เรียงความผิดพลาด',
                '4' => 'เนื้อหาว่างเปล่าหรือขาดไป',
                '5' => 'เนื้อหาซ้ำกัน',
                '6' => ' หัวข้อกับเนื้อหาไม่ตรงกัน',
                '7' => 'ข้อมูลที่ไม่พึงประสงค์',
            ],
        ];

        // $result = Y::$newconf['wrongtypetw'];
        $result = $info[$this->head['cityid'] . ''];
        Out::jout($result);
        //        $this->returnSuccess($result);
    }
    // 疑难解答
    public function control_get_faq()
    {
        $deviceType = $this->head['deviceType'];
        // d($deviceType,1);
        $bookModel = M('book', "im");
        $data = $bookModel->get_faq($deviceType);

        if ($deviceType == 'iphone') {
            //$where['device_type'] = array(array('eq','ios'),array('eq','all'), 'or');
            $w = "device_type!='android'";
        } elseif ($deviceType == 'android') {
            //$where['device_type'] = array(array('eq','android'),array('eq','all'), 'or');
            $w = "device_type!='ios'";
        }
        $data = T('user_faq')->field('faq_id,faq_question,faq_answer')->where($w)->get_all();

        $this->returnSuccess($data);
    }
    // public function get_vip()
    // {
    //     $device_type = $this->request->header('deviceType');
    //     $where = [];
    //     if ($device_type == 'android') {
    //         $where['device_type'] = '2';
    //     } else {
    //         $where['device_type'] = '1';
    //     }

    //     $res = T('vip_position')->field('duration,original_price,discount_price,gift_certificate,applepayId,invite')->where($where)->select()->toArray();
    //     if ($res) {
    //         $this->returnSuccess($res);
    //     } else {
    //         $this->returnData(200, Lang::get('Belum Ada Data'));
    //     }
    // }

    // // 获取vip特权
    // public function get_vip_privilege()
    // {
    //     $res = T('vip_privilege')->field('privilege_name,privilege_reward,privilege_pic')->select()->toArray();
    //     if ($res) {
    //         $this->returnSuccess($res);
    //     } else {
    //         $this->returnData(200, Lang::get('Belum Ada Data'));
    //     }
    // }

}
