<?php

namespace ng169\control\api;

use ng169\control\apibase;
use ng169\tool\Out;
use ng169\Y;

checktop();
class mark extends apibase
{
    protected $noNeedLogin = ['getcategory'];
    //houqusyouweidianping de shu
    public function control_getbook()
    {
        $list = T('mark')->set_filed(['bookid'])->group_by('bookid')->set_where(['uid' => $this->get_userid()])->get_all();
        $list = array_column($list, 'bookid');
        $cityid = $this->head['cityid'];
        //这里是自动阅读历史给无标签的加标签。
        Out::jout(['selectin' => $list, 'list' => []]);
        //后台自定义需要加标签的书
        Out::jout(['selectin' => $list, 'list' => M('mark', 'im')->getnonecate($cityid)]);
    }
    public function control_getcategory()
    {
        //缓存
        // list($bool, $cache) = Y::$cache->get('category');
        // if ($bool) {
        //     Out::jout($cache);
        // } else {
        //     $data = T('category')->get_all(['depth' => 1]);
        //     foreach ($data as $k => $v) {
        //         $data[$k]['child'] = T('category')->get_all(['depth' => 2, 'pid' => $v['category_id']]);
        //         foreach ($data[$k]['child'] as $k2 => $v2) {
        //             $data[$k]['child'][$k2]['child'] = T('category')->get_all(['depth' => 3, 'pid' => $v2['category_id']]);
        //         }
        //     }
        //     Y::$cache->set('category', $data, '-1');
        //     Out::jout($data);
        // }
        $cityid = $this->head['cityid'];
        $data = M('cate', 'im')->getcate($cityid);
        Out::jout($data);
    }
    public function control_mark()
    {
    }
}
