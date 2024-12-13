<?php


namespace ng169\control\api;

use ng169\control\apiv1base;
use ng169\tool\Url as YUrl;
use ng169\tool\Upfile;
use ng169\tool\Image;
use ng169\tool\Out;
use ng169\service\Output;
use ng169\cache\Rediscache;
use ng169\Y;


checktop();

class aysnb extends apiv1base
{
	protected $noNeedLogin = ['*'];
	public function control_check()
	{
		//返回4个状态，
		// 1存在章节需要gx 
		// 2存在，远程已经完结
		// 3存在章节不需要更新或者远程数据大于本都
		// 4不存在
		$get = get(['int' => ['type' => 1, 'secnum' => '1', 'lang' => 1], 'string' => ['book' => 1]]);
		$dbsec =  M('book', 'im')->gettpsec($get['type'], $get['lang']);
		$status = 0;
		$bookid = 0;
		$start = 0;
		$upnum = 0;
		$id = 'book_id';
		if ($get['type'] == 1) {
			//小说
			$book = T('book')->set_field('update_status,' . $id)->set_where(['other_name' => $get['book'], 'lang' => $get['lang']])->get_one();
			if (!$book) {
				// Out::jout(4);
				$status = 4;
				$upnum = $get['secnum'];
			} else {
				$bookid = $book[$id];

				if ($book['update_status'] == 1) {
					// Out::jout(2);
					$status = 2;
				} else {
					$secnum = T($dbsec)->set_where([$id => $bookid, "status" => 1, 'isdelete' => 0])->get_count();

					if ($secnum >= $get['secnum']) {
						$status = 3;
					} else {
						$status = 1;
						$start = $secnum;
						$upnum =$get['secnum']- $secnum  ;
					}
				}
			}
		} else {
			//漫画
		}

		Out::jout(['code' => $status, 'bookid' => $bookid, 'start' => $secnum, 'upnum' => $upnum]);
	}
	public function control_inbook()
	{
		$get = get(['string' => ['wnum', 'type', 'lang', 'secnum', 'bpic', 'update_status', 'other_name', 'desc']]);
		$add = [
			// "fid" => $id,
			// "ftype" => $this->bookdstdesc,
			"writer_name" => "lookstory",
			// "book_name" => $data["other_name"],
			"status" => 2, //下架
			"wordnum"   => $get['wnum'],
			"section"   => $get['secnum'],
			"bpic" => $get['bpic'],
			"isfree" => 1, //没该字段，所有数据都是收费章节
			"desc"  => $get['desc'],
			"money" => $get['type'] == 1 ? 0.6 : 60, //小说就是0.6，漫画就是60
			"lang" => $get['lang'],
			"create_time" => time(),
			"update_time" => time(),
			"update_status" => $get['update_status'],
			// "update_status" => $this->getbookisend($data[$refield["update_status"]], 1), //状态2为完结 ，1为连载
			"other_name" => $get["other_name"],
		];
		if ($get['type'] == 1) {
			$db = 'book';
		} else {
			$db = 'cartoon';
		}

		$bookid = T($db)->add($add);
		// if($bookid){

		// }
		Out::jout($bookid);
	}

	public function control_insec()
	{
		$get = get(['string' => ['title', 'bookid', 'list_order', 'isfree', 'content', 'type', 'lang','secnum']]);
		$dbsec =  M('book', 'im')->gettpsec($get['type'], $get['lang']);
		$dbsecc = M('book', 'im')->gettpseccontent($get['type'], $get['lang']);
		if ($get['type'] == 1) {
			$dbid = 'book_id';
		} else {
			$dbid = 'cartoon_id';
		}
		$secadd = [
			// "section_id"   => "",
			"title"        => $get['title'],
			"list_order"   => $get['list_order'],
			"secnum"   => $get['secnum'],
			$dbid      => $get['bookid'],
			"create_time"  =>  date("Y-m-d H:i:s"),
			"update_time"  =>  date("Y-m-d H:i:s"),
			"status"       => 1,
			"isfree"       => $get['isfree'],
		];
		$secid = T($dbsec)->add($secadd);
		if (!$secid) {
			Out::jerror('章节插入失败');
		}
		if ($get['type'] == 1) {
			$a2 = ["sec_content" => $get['content'], "section_id" => $secid];
			// T($this->dbcontent)->add($a2);
		} else {
			$a2 = ["cart_sec_content" => $get['content'], "cart_section_id" => $secid];
			// T($this->dbcontent)->add($a2);
		}
		// d('插入');
		T($dbsecc,null,"content")->add($a2);
		Out::jout('章节插入成功');
	}
}
