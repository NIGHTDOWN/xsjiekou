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
    //获取分类树
    public function getcate($lang = 0)
    {
        $index = "category:" . $lang;
        list($bool, $cache) = Y::$cache->get($index);

        if ($bool) {

            return $cache;
            // Out::jout($cache);
        } else {
            $data = T('category')->get_all(['depth' => 1]);
            foreach ($data as $k => $v) {
                $data[$k]['child'] = T('category')->get_all(['depth' => 2, 'pid' => $v['category_id']]);
                foreach ($data[$k]['child'] as $k2 => $v2) {
                    $catetmp = T('category')->get_all(['depth' => 3, 'pid' => $v2['category_id']]);
                    $data[$k]['child'][$k2]['child'] = $catetmp;
                    $data[$k]['child'][$k2]['tag'] = $this->getlable(array_column($catetmp, 'category_name'), $lang);
                }
            }
            Y::$cache->set($index, $data, G_DAY * 31);

            return $data;
        }
    }
}
