<?php

namespace ng169\model\index;

use ng169\Y;
use ng169\tool\Out;
use ng169\tool\Request;

checktop();
//统计埋点
class book extends Y
{
    public function  trimhtml($str)
    {
        //过滤掉html
        $str = htmlspecialchars_decode($str);
        $str = str_replace('</p>', "\r\n\n ", $str);
        $str = str_replace('<p />', "\r\n\n ", $str);
        $str = str_replace('<br/>', "\r\n\n", $str);
        $str = str_replace('<br />', "\r\n\n", $str);
        $str = str_replace("\n\s", "\r\n", $str);

        $search = array(
            "'<script[^>]*?>.*?</script>'si", // 去掉 javascript 

            "'<[\/\!]*?[^<>]*?>'si", // 去掉 HTML 标记 

            // "'([\r\n])[\s]+'", // 去掉空白字符 

            "'&(quot|#34);'i", // 替换 HTML 实体 

            "'&(amp|#38);'i",

            "'&(lt|#60);'i",

            "'&(gt|#62);'i",

            "'&(nbsp|#160);'i"

        ); // 作为 PHP 代码运行 

        // $replace = array("", "", "\\1", "\"", "&", "<", ">", " ");
        $replace = array("", "",  "\"", "&", "<", ">", "  ");

        $str = preg_replace($search, $replace, $str);
        $kg5 = "/^\s{3,}/"; //把多个空格替换成2个

        $str = preg_replace($kg5, " ", $str);
        // str = $str.replace(/^ +/gm, '');
        // d($str);
        // $str = preg_replace("/<([a-z\/]+)[^>]*>/i", "", $str);

        return $str;
    }
    public function nl2p($str, $br = true)
    {
        // 分隔字符
        $str_array = preg_split('/\n\s*\n/', $str, -1, PREG_SPLIT_NO_EMPTY);
        $str = '';
        foreach ($str_array as $tinkle)
            $str .= '<p>' . trim($tinkle) . "</p>";
        //是否将单个换行符转化成br
        if ($br)
            $str = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $str); // optionally make line breaks
        return $str;
    }
    // 用户阅读历史,书架记录，更新书籍阅读数量
    public function user_read_history($users_id = '', $book_id = "", $cartoon_id = "", $sec_id = '')
    {
        $w = ['users_id' => $users_id];
        if ($book_id) {
            $w1['book_id'] = $book_id;
            $w['book_id'] = $book_id;
            $w3['book_id'] = $book_id;
            $type = 1;
            $book = T('book')->field('book_id,other_name,`desc`,bpic,writer_name,isfree,`lang`,section')->where($w1)->get_one();
            T('book')->update('`read`=`read`+1', $w1);
        } elseif ($cartoon_id) {
            $w1 = ['cartoon_id' => $cartoon_id];
            $w['book_id'] = $cartoon_id;
            $w3['cartoon_id'] = $cartoon_id;
            $type = 2;
            $book = T('cartoon')->field('cartoon_id,other_name,`desc`,bpic,writer_name,isfree,`lang`,section')->where($w1)->find();
            T('cartoon')->update('`read`=`read`+1', $w1);
        }
        $w['type'] = $type;
        if (!$users_id) return false;
        if (!$sec_id) return false;
        //添加历史记录
        $user_history = T('user_history')->set_field('watch_nums')->set_where($w)->get_one();
        if ($user_history) {
            //更新
            $insert['watch_time'] = time();
            $insert['watch_nums'] = $user_history['watch_nums'] + 1;
            T('user_history')->update('watch_nums=`watch_nums`+1,watch_sec=' . $sec_id . ',watch_time=' . time(), $w);
        } else {
            //插入
            $data = [
                'book_id' => $w['book_id'],
                'other_name' => $book['other_name'],
                'desc' => $book['desc'],
                'bpic' => $book['bpic'],
                'writer_name' => $book['writer_name'],
                'watch_time' => time(),
                'watch_sec' => $sec_id,
                'watch_nums' => 1,
                'isfree' => $book['isfree'],
                'type' => $type,
                'users_id' => $users_id,
            ];
            T('user_history')->add($data);
        }
        //更新书架记录
        //判断是否书架
        $groomdata = T('user_groom')->set_field('readpoinstion,booknumlast,readsec')->set_where($w)->find();
        if ($groomdata) {
            //统计当前章节总数
            // T()
            $tpsec = M('book', 'im')->gettpsec($type, $book['lang']);

            $data = T($tpsec)->set_field('list_order')->set_where($w3)->order_by(['s' => 'down', 'f' => 'list_order'])->get_one();
            //判断下章节跟记录里面的是否相同，不相同就更新
            if ($data['list_order'] != $book['section']) {
                $up['section'] = $data['list_order'];
                if ($type == 1) {
                    T('book')->update($up, $w3);
                } else {
                    T('cartoon')->update($up, $w3);
                }
            }

            T('user_groom')->update(['readsec' => $sec_id, 'readpoinstion' => $data['list_order'], 'booknumlast' => $data['list_order'], 'readtime' => time()], $w);
        }
        return;
    }
    //修复小说字数
    public function fixbooknum($bookid)
    {
        $w = ['book_id' => $bookid, 'status' => 1];
        $lang = T('book')->set_where(['book_id' => $bookid,])->set_field('lang')->get_one();
        $tpsec = M('book', 'im')->gettpsec(1, $lang['lang']);
        $nums = T($tpsec)->set_field('sum(secnum) as wordnum')->get_one($w);

        if (!$nums) return false;
        T('book')->update(['wordnum' => $nums['wordnum']], $w);
        return $nums['wordnum'];
        // $data['wordnum'] = $nums['wordnum'];
    }
    public function keylog($uid, $key)
    {
        if (!$key) return false;
        $log['addtime'] = time();
        $log['uid'] = $uid;
        $log['ip'] = Request::getip();
        $log['key'] = $key;
        $log['date'] = date('Ymd');
        T('keyword_log')->add($log);
        $in['key'] = $key;
        $isin = T('keyword')->get_one($in);

        if ($isin) {
            $up['createtime'] = time();
            $up['num'] = $isin['num'] + 1;
            T('keyword')->update($up, $in);
        } else {
            $in['createtime'] = time();
            T('keyword')->add($in);
        }
        return;
    }
    public function getsectionlist($bookid, $money)
    {
        if (!$bookid) return false;
        $w['isdelete'] = 0;
        $w['status'] = 1;
        $w['book_id'] = $bookid;
        $lang = T('book')->set_field('lang')->set_where(['book_id' => $bookid])->get_one();
        if (!$lang) {
            return false;
        } else {
            if ($lang['lang'] == 0) {
                $tpsec = 'section';
            } else {
                $tpsec = 'section_' . $lang['lang'];
            }
        }
        $data = T($tpsec)->field('section_id,title,book_id,isfree,secnum,update_time,coin,0 as ispay,list_order')->where($w)->order('list_order asc')->get_all();
        foreach ($data as $key => $val) {
            if ($val['isfree'] == 1 || $val['isfree'] == 4) {
                if (!$data[$key]['coin']) {
                    $data[$key]['coin'] = M('coin', 'im')->bookcalculate($val['secnum'], $money);
                    T($tpsec)->update(['coin' => $data[$key]['coin']], ['section_id' => $data['section_id']]);
                }
            }
            $data[$key]['update_time'] = strtotime($val['update_time']);
        }
        return $data;
    }
    public function getcartsectionlist($bookid)
    {
        if (!$bookid) return false;
        $w['isdelete'] = 0;
        $w['status'] = 1;
        $w['cartoon_id'] = $bookid;
        $lang = T('cartoon')->set_field('lang')->set_where(['cartoon_id' => $bookid])->get_one();
        if (!$lang) {
            return false;
        } else {
            if ($lang['lang'] == 0) {
                $tpsec = 'cartoon_section';
            } else {
                $tpsec = 'cartoon_section_' . $lang['lang'];
            }
        }
        $data = T($tpsec)->field('cart_section_id as section_id,title,cartoon_id as book_id,isfree,charge_coin,update_time,2 booktype,0 as ispay,list_order')->where($w)->order('list_order asc')->get_all();

        return $data;
    }
    //修复书籍章节
    public function fixbooksecnum($bookid)
    {
        if (($bookid)) {
            $lang = T('book')->set_where(['book_id' => $bookid,])->set_field('lang')->get_one();
            $tpsec = M('book', 'im')->gettpsec(1, $lang['lang']);
            $tmp = T($tpsec)->set_field('book_id,list_order')->whereIn('book_id', $bookid)->order_by('list_order desc')->set_where(['status' => 1])->get_sql();
            $tmp = T([$tmp])->group_by('v.book_id')->get_all();
            foreach ($tmp as $key => $value) {
                if ($value['book_id']) {
                    T('book')->update(['section' => $value['list_order']], ['book_id' => $value['book_id']]);
                }

                # code...
                // $data[$value['book_id']]['newsec'] = $value['title'];
                // $data[$value['book_id']]['newsecid'] = $value['section_id'];
                // $data[$value['book_id']]['newnum'] = $value['list_order'];
            }
        }
    }
    public function clearcache($type, $bookid)
    {
        if ($type == 1) {
            $index1 = 'bid' . $bookid;
            $index2 = 'bseclist_' . $bookid;
        } else {
            $index1 = 'cid' . $bookid;
            $index2 = 'cseclist_' . $bookid;
        }
        Y::$cache->del($index1);
        Y::$cache->del($index2);
    }
    public function clearrackcache($lang)
    {

        $index1 = 'torackcache' . $lang;
        // $index2 = 'cseclist_' . $bookid; _torackcache6.cache

        Y::$cache->del($index1);
        // Y::$cache->del($index2);
    }
    public function fixcartsecnum($bookid)
    {
        if (($bookid)) {
            $lang = T('cartoon')->set_where(['cartoon_id' => $bookid])->set_field('lang')->get_one();
            $tpsec = M('book', 'im')->gettpsec(2, $lang);
            $tmp = T($tpsec)->set_field('cartoon_id,list_order ')->whereIn('cartoon_id', $bookid)->order_by('list_order desc')->set_where(['status' => 1])->get_sql();
            $tmp = T([$tmp])->group_by('v.cartoon_id')->get_all();
            foreach ($tmp as $key => $value) {
                if ($value['cartoon_id']) {
                    T('cartoon')->update(['section' => $value['list_order']], ['cartoon_id' => $value['cartoon_id']]);
                }

                # code...
                // $data[$value['book_id']]['newsec'] = $value['title'];
                // $data[$value['book_id']]['newsecid'] = $value['section_id'];
                // $data[$value['book_id']]['newnum'] = $value['list_order'];
            }
        }
    }
    public function encrypt($data)
    {
        // print_r($data);die;
        $public_key = "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDEgUxp7SvEzOLIe4TsXbat1mQn
44294iNKyIg/YpVgAO6nmlrMUCPMskosumBlbGOGBfwjTbKYEcfFTvBGSONv4a85
XFDgfwE4NZuzJbD2J9h8EkWeio57dpFIh/xxrHJOJYcDHQ6KnTfv3aPsJDWLxex+
LnUG4Z69pKZHtL6ljwIDAQAB
-----END PUBLIC KEY-----";
        $key = openssl_pkey_get_public($public_key);

        if (!$key) {
            Out::jerror('公钥不可用', null, '100131');
            //die('公钥不可用');
            // oooooooooo 
        }

        if (openssl_public_encrypt($data, $encrypted, $key)) {
            //由于加密后为二进制数据，为了展示和传输，base64_en一下，解密同
            $datas = base64_encode($encrypted);
        } else {
            Out::jerror('encrypt wrong', null, '100132');
            //throw new Exception('encrypt wrong');
        }
        return $datas;
    }
    public function aes_encrypt($content = null, $privateKey = null, $iv = null)
    {
        //$aes = Config::get('aes');
        $aes = Y::$newconf['aes'];
        if (empty($privateKey)) {
            $privateKey = $aes['key'];
        }
        if (empty($iv)) {
            $iv = $aes['aes_str_iv'];
        }
        return base64_encode(openssl_encrypt($content, "AES-128-CBC", $privateKey, OPENSSL_RAW_DATA, $iv));
    }

    public function aes_decrypt($data = '', $privateKey = null, $iv = '')
    {
        $aes = Y::$newconf['aes'];
        if (empty($privateKey)) {
            $privateKey = $aes['key'];
        }
        if (empty($iv)) {
            $iv = $aes['aes_str_iv'];
        }
        return openssl_decrypt(base64_decode($data), "AES-128-CBC", $privateKey, OPENSSL_RAW_DATA, $iv);
    }
    public function gettpsec($type, $lang)
    {
        if ($type != 2) {
            $tppre = 'section';
        } else {
            $tppre = 'cartoon_section';
        }
        if (!$lang) return $tppre;
        if ($lang == 0) {
            return $tppre;
        } else {
            return $tppre . '_' . $lang;
        }
    }
    public function gettpseccontent($type, $lang)
    {
        if ($type != 2) {
            $tppre = 'sec_content';
        } else {
            $tppre = 'cart_sec_content';
        }
        if (!$lang) return $tppre;
        if ($lang == 0) {
            return $tppre;
        } else {
            return $tppre . '_' . $lang;
        }
    }
    // 疑难解答
    public function get_faq($device_type = '')
    {
        if ($device_type == 'iphone') {
            $where['device_type'] = array(array('eq', 'ios'), array('eq', 'all'), 'or');
        } elseif ($device_type == 'android') {
            $where['device_type'] = array(array('eq', 'android'), array('eq', 'all'), 'or');
        }
        $list = T('user_faq')->field('faq_id,faq_question,faq_answer')->where($where)->select()->toArray();
        if ($list) {
            return $list;
        } else {
            return false;
        }
    }
    public function detail($bookid,  $uid = 0, $type = 1)
    {
        $index = 'webbid:' . $bookid . '_' . $type;
        if ($type == 1) {
            $w = ['book_id' => $bookid, 'status' => 1];        //取书籍
            //取最新章节
            //取评论
            //判断是否登入
            //登入 修复书籍书架状态
            //修复未审核的评论
            $cache = Y::$cache->get($index);

            if ($cache[0]) {
                $arr = $cache[1];
            } else {
                $data = T('book')
                    // ->field('other_name,book_id,bpic,bpic_dsl,writer_name,`desc`,section,update_status,isfree,wordnum,end_share,share_banner,lang,cate_id,lable,category_id,collect,`read`')
                    ->where($w)
                    ->get_one();

                if (!$data) {
                    return false;
                    // Out::jerror('书籍不存在', null, '100143');
                }

                $data['isgroom'] = 0;
                $w['status'] = 1;
                // $star = T('discuss')->where($w)->get_count();
                $w['isdelete'] = 0;
                if ($data['lang'] == 0) {
                    $tpsec = 'section';
                } else {
                    $tpsec = 'section_' . $data['lang'];
                }
                $new_section = T($tpsec)->where($w)->set_field('title,section_id,list_order,update_time')->order_by(['s' => 'down', 'f' => 'list_order'])->get_one();
                // $seclist = T($tpsec)->where($w)->set_field('title,section_id,list_order,isfree')->order_by(['s' => 'up', 'f' => 'list_order'])->get_all();
                $update_section = $new_section['list_order'];
                unset($w['isdelete']);
                $sumss = T('discuss')->set_field('sum(star) as sums,count(1) as counts')->where($w)->get_one();
                $sums = $sumss['sums'];
                $star = $sumss['counts'];
                $replynum = "";
                if ($star == 0 && $sums == 0) {
                    $replynum = 5;
                } else {
                    $replynum = $sums / $star;
                    $replynum = round($replynum, 1);
                }
                $discuss = T('discuss')
                    ->field('discuss_id,star,nick_name,discuss_time,content,users_id')
                    ->where($w)
                    ->order('discuss_time desc')
                    ->limit(5)
                    ->get_all();

                $discuss = $this->getdiscussavater($discuss);

                $count = $star;
                $data['desc'] = str_replace("&quot;", "\"", $data['desc']);
                $data['section_count'] = $data['section'];
                $data['replynum'] = $replynum;
                $data['update_section'] = $update_section;
                $data['new_section_title'] = $new_section['title'];
                $data['new_section_id'] = $new_section['section_id'];
                $data['new_section_time'] = strtotime($new_section['update_time']);
                $data['type'] = $type;
                unset($data['section']);
                $arr = [
                    'data' => $data,
                    // 'seclist' => $seclist,
                    'discussd' => [
                        'discuss' => $discuss,
                        'count' => $count,
                    ],
                ];
                if (!$arr['data']['wordnum']) {
                    // 修复字数
                    M('book', 'im')->fixbooknum($bookid);
                }
                Y::$cache->set($index, $arr, 43200);
            }
            
            if ($uid) {
                if (T('user_groom')->set_field('isgroom')->get_one(['status' => 1, 'book_id' => $bookid, 'users_id' => $uid, 'type' => 1])) {
                    $arr['data']['isgroom'] = 1;
                }
                $owenDiscuss = T('discuss')
                    ->field('discuss_id,star,nick_name,discuss_time,content,users_id')
                    ->where($w)
                    ->where(['users_id' => $this->uid])
                    ->order('discuss_time desc')
                    // ->limit(1)
                    ->get_one();
                if ($owenDiscuss) {
                    // foreach ($owenDiscuss as  $key => $value) {
                    # code...
                    $owenDiscuss['avater'] = parent::$wrap_user['avater'];
                    // }

                    $arr['discussd']['discuss'] = array_merge([$owenDiscuss], $arr['discussd']['discuss']);
                }
            }
            // 点击统计
            M('census', 'im')->hitcounts($bookid);
            M('bookcensus', 'im')->readnum($uid, 1, $bookid);
        } else {
            $cartoon_id = $bookid;
            $cache = Y::$cache->get($index);
            if ($cache[0]) {
                $arr = $cache[1];
            } else {
                $w = ['cartoon_id' => $cartoon_id];
                $data = T('cartoon')
                    // ->field('other_name,cartoon_id as book_id,cartoon_id,bpic,bpic_dsl,writer_name,`desc`,cate_id,lable,category_id,likes,update_time,hits,collect,bpic_detail,update_status,isfree,`read`,end_share,share_banner,0 as isgroom,lang,`read`')
                    ->where($w)
                    ->get_one();

                $data['like'] = $data['likes'];
                $data['share_banner'] = $data['bpic_detail'];

                unset($data['likes']);
                $update_time = strtotime($data['update_time']);
                $time = time() - $update_time;
                if ($time < 86400) {
                    $data['update_time'] = date("H:i:s", $update_time);
                } else {
                    $data['update_time'] = date("d-m-Y", $update_time);
                }
                $w['status'] = 1;
                // $star = T('discuss')->where($w)->get_count();
                $sumss = T('discuss')->set_field('sum(star) as sums,count(1) as counts')->where($w)->get_one();
                $sums = $sumss['sums'];
                $star = $sumss['counts'];
                $replynum = "";
                if ($star == 0 && $sums == 0) {
                    $replynum = 5;
                } else {
                    $replynum = $sums / $star;
                    $replynum = round($replynum, 1);
                }


                // 获取本漫画的评论信息

                $discuss = T('discuss')
                    ->field('discuss_id,star,nick_name,discuss_time,content,users_id')
                    ->where($w)
                    ->order('discuss_time desc')
                    ->limit(5)
                    ->get_all();

                $discuss = $this->getdiscussavater($discuss);
                $count = $star;

                $data['desc'] = str_replace("&quot;", "\"", $data['desc']);
                $data['replynum'] = $replynum;
                $w = [];
                $w['isdelete'] = 0;
                $w['cartoon_id'] = $cartoon_id;
                $w['status'] = 1;
                if ($data['lang'] == 0) {
                    $tpsec = 'cartoon_section';
                } else {
                    $tpsec = 'cartoon_section_' . $data['lang'];
                }
                $new_section = T($tpsec)->where($w)->set_field('title,cart_section_id as section_id,list_order')->order_by(['s' => 'down', 'f' => 'list_order'])->get_one();
                // $seclist = T($tpsec)->where($w)->set_field('title,cart_section_id as section_id,list_order')->order_by(['s' => 'up', 'f' => 'list_order'])->get_all();

                $update_section = $new_section['list_order'];
                $data['update_section'] = $update_section;
                $data['new_section_title'] = $new_section['title'];
                $data['new_section_id'] = $new_section['section_id'];
                $data['type'] = $type;
                $arr = [
                    'data' => $data,
                    // 'seclist' => $seclist,
                    'discussd' => [
                        'discuss' => $discuss,
                        'count' => $count,
                    ]
                ];
                Y::$cache->set($index, $arr, 43200);
            }


            if ($uid) {

                if (T('user_groom')->set_field('isgroom')->get_one(['status' => 1, 'book_id' => $cartoon_id, 'users_id' => $uid, 'type' => 1])) {
                    $arr['data']['isgroom'] = 1;
                }
                unset($w['isdelete']);

                $w = ['cartoon_id' => $cartoon_id];
                $owenDiscuss = T('discuss')
                    ->field('discuss_id,star,nick_name,discuss_time,content,users_id')
                    ->where($w)
                    ->where(['users_id' => $this->uid])
                    ->order('discuss_time desc')
                    // ->limit(1)
                    ->get_one();

                if ($owenDiscuss) {

                    $owenDiscuss['avater'] = parent::$wrap_user['avater'];
                    $arr['discussd']['discuss'] = array_merge([$owenDiscuss], $arr['discussd']['discuss']);
                }
            }

            M('bookcensus', 'im')->readnum($uid, 2, $cartoon_id);
            M('census', 'im')->cartoonhitcounts($cartoon_id);
        }
       
        if ($type == 1) {
            $arr['seclist'] = M('content', 'im')->book_section($uid, $bookid);
        } else {
            $arr['seclist'] = M('content', 'im')->cart_section($uid, $bookid);
        }
        // d($arr,1);
        if ($arr['data']) {
            $arr['data']['tags'] = M('cate', 'im')->getlable($arr['data']['lable'], $arr['data']['lang']);
        }
        return $arr;
    }
    private function getdiscussavater($discuss)
    {
        $discusstmp = array_column($discuss, null, 'users_id');
        $userids = array_column($discuss, 'users_id');
        if ($userids && sizeof($userids)) {
            $us = T('third_party_user')->field('id,avater')->whereIn('id', $userids)->get_all();
            $ua = array_column($us, 'avater', 'id');
            foreach ($discuss as $k => $dis) {
                $discuss[$k]['avater'] = $ua[$dis['users_id']];
            }
        } else {
        }
        return $discuss;
    }
    //获取书籍同类木书籍id集合
    public function getsimilar($bookid, $type, $num)
    {
        $similarcacheindex = 'similarcacheindex' . $type;
        if ($type == 1) {
            $table = 'book';
            $tableid = 'book_id';
        } else {
            $table = 'cartoon';
            $tableid = 'cartoon_id';
        }
        $data = T($table)->set_where([$tableid => $bookid])->set_field('category_id,lang,cate_id,lable')->get_one();
        if (!$data) return false;
        $similarcacheindex .= $data['category_id'] . $data['cate_id'] . $data['lang'];
        list($bool, $cache) = Y::$cache->get($similarcacheindex);
        if (!$bool) {
            $w['category_id'] = $data['category_id'];
            $w['cate_id'] = $data['cate_id'];
            $w['lang'] = $data['lang'];
            $w['status'] = 1;
            $list = T($table)->set_where($w)->set_field($tableid)->set_limit(500)->order_by(['f' => 'collect', 's' => 'down'])->get_all();
            $cache = array_column($list, $tableid);
            Y::$cache->set($similarcacheindex, $cache, G_DAY * 5);
        }
        $cache = array_diff($cache, [$bookid]);
        $ret = array_rand($cache, $num);
        $retbookid = [];
        foreach ($ret as $val) {
            array_push($retbookid, $cache[$val]);
        }
        return $retbookid;
    }
    //获取书籍同作者书籍id集合
    public function getsimilarauthor($bookid, $type, $num)
    {
        $similarcacheindex = 'getsimilarauthor' . $type;
        if ($type == 1) {
            $table = 'book';
            $tableid = 'book_id';
        } else {
            $table = 'cartoon';
            $tableid = 'cartoon_id';
        }
        $data = T($table)->set_where([$tableid => $bookid])->set_field('category_id,lang,writer_name')->get_one();
        if (!$data) return false;
        $similarcacheindex .= $data['category_id'] . $data['cate_id'] . $data['lang'];
        list($bool, $cache) = Y::$cache->get($similarcacheindex);
        if (!$bool) {
            $w['category_id'] = $data['category_id'];
            $w['writer_name'] = $data['writer_name'];
            $w['lang'] = $data['lang'];
            $w['status'] = 1;
            $list = T($table)->set_where($w)->set_field($tableid)->set_limit(500)->order_by(['f' => 'collect', 's' => 'down'])->get_all();
            $cache = array_column($list, $tableid);
            Y::$cache->set($similarcacheindex, $cache, G_DAY * 5);
        }
        $cache = array_diff($cache, [$bookid]);
        $ret = array_rand($cache, $num);
        $retbookid = [];
        foreach ($ret as $val) {
            array_push($retbookid, $cache[$val]);
        }
        return $retbookid;
    }
}
