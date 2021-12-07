<?php

namespace ng169\model\index;

use ng169\Y;

checktop();
//统计埋点
class cate
{
    private $tagindex = 'tagindex';
    /**获取书籍标签对应语言 */
    public function getlable($lable, $lang)
    {
        if (!$lable) return false;
        list($bool, $tags) = Y::$cache->get($this->tagindex);


        if (!$bool) {
            $tags = T('tag')->get_all();

            $tags = array_column($tags, null, 'tagid');
            //缓存一个月
            Y::$cache->set($this->tagindex, $tags, G_DAY * 31);
        }
        if (is_array($lable)) {
            $lbs = $lable;
        } else {
            $lbs = explode(',', $lable);
            $lbs = array_filter($lbs);
        }
        $ret = [];
        foreach ($lbs as $tag) {
            $index = trim($tag, 'L');
            $ret[$index]['id'] = $tags[$index]['tagid'];
            $ret[$index]['tag'] = $tags[$index]['lang' . $lang];
            $ret[$index]['zh_name'] = $tags[$index]['tagname'];
        }
        return $ret;
    }
}
