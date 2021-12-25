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
    /**
     * 获取对应分类小说
     * lang 国家id
     * booktype 书籍类型，1小说，2漫画
     * cate1 男女分类id
     * cate2 分类id
     * cate3 标签id
     * orderby 排序类型 1最新，2最热，3完结
     */
    public function getlist($lang, $booktype, $cate1, $cate2, $cate3, $orderby, $page = 0)
    {
        // $data = get(['int' => ['c1', 'c2', 'c3', 'c4', 'page']]);

        $arr = [];
        // $cityid = $this->head['cityid'];
        $where['lang'] = $lang;
        $where['status'] = 1;
        if (!$booktype) return ($arr);
        if (!$lang) return ($arr);
        // if (!$data['c2']) return ($arr);
        $filed = "other_name,`desc`,bpic_dsl,bpic,writer_name,isfree,update_status,lable,`read`";
        if ($booktype < 2) {
            $type = 'book';
            $filed .= ",book_id,1 as type";
        } else {
            $type = 'cartoon';
            $filed .= ",cartoon_id as book_id,2 as type";
        }
        $where['cate_id'] = $cate2;
        $where['category_id'] = $cate1;
        $where = array_filter($where);
        $list = T($type)
            ->field($filed)
            ->where($where);
        $lable = "";
        if ($cate3) {
            $lable = ' lable like  "%L' . $cate3 . ',%" ';
            $list = $list->where($lable);
        }
        $desc = "update_time desc";
        switch ($orderby) {
            case '1':
                # code...
                $desc = "update_time desc";
                break;
            case '2':
                $desc = "collect desc";
                break;
            case '3':
                # code...
                $list = $list->where(['update_status' => 1]);
                break;
            default:
                # code...
                break;
        }
        $list = $list->order($desc)->set_limit([$page, 10]);

        $arr = $list->get_all();
        return ($arr);
    }
}
