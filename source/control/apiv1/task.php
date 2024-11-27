<?php

namespace ng169\control\api;

use ng169\control\apiv1base;
use ng169\lib\Log;
use ng169\tool\Out;
use ng169\tool\Request;

checktop();



class task extends apiv1base
{

	protected $noNeedLogin = [''];
	public function control_init()
	{
		$uid = $this->get_userid();
		$data = T('usertask')->where(['uid' => $uid, 'dates' => date('Ymd')])->get_all();

		//获取完善用户信息任务状态
		$data7 = T('usertask')->where(['uid' => $uid])->whereIn('type', [7, 9, 10])->get_all();


		$data = array_merge($data, $data7);
		if (sizeof($data)) {
			$data = array_column($data, null, 'type');
		}
		Out::jout($data);
	}
	public function control_read()
	{
		// 1每日分享 2每日阅读 3邀请好友 4替好友充值 5每日看广告 6签到,7完善用户资料,8每日充值
		$get = get(['int' => ['readsec']]);
		$where['dates'] = date('Ymd');
		$where['uid'] = $this->get_userid();
		$where['type'] = 2;
		$read_record = T('usertask')->where($where)->get_one();
		//奖励金额
		$coin = 60;

		if (!$read_record) {
			$where['addtime'] = time();
			$where['num'] = 1;
			$where['readsec'] = $get['readsec'];
			T('usertask')->add($where);
			M('census', 'im')->task_reward_count($where['uid'], $coin, 2);
			M('coin', 'im')->addstar($where['uid'], $coin);
			$user = T('third_party_user')->set_field('remainder,golden_bean')->get_one(['id' => $where['uid']]);
			Out::jout($user);
		} else {
			Out::jerror('今日已经领取了', null, '100123');
		}
	}
	public function control_mark()
	{
		// 1每日分享 2每日阅读 3邀请好友 4替好友充值 5每日看广告 6签到,7完善用户资料,8每日充值
		$get = get(['int' => ['id', 'type', 'c1', 'c2'], 'string' => ['t3']]);

		$where['dates'] = date('Ymd');
		$where['uid'] = $this->get_userid();
		$where['type'] = 11;


		$read_record = T('usertask')->where($where)->get_one();
		//奖励金额
		$coin = 25;
		// $bookid, $type, $cate1id, $cate2id, $tagid
		if (!M('mark', 'im')->log($where['uid'], $get['id'], $get['type'], $get['c1'], $get['c2'], $get['t3'])) {
			Out::jerror('添加书籍标记失败', null, '121123');
		}
		//任务总次数
		$num = 3;
		if (!$read_record) {
			$where['addtime'] = time();
			$where['num'] = 1;
			
			T('usertask')->add($where);
			M('census', 'im')->task_reward_count($where['uid'], $coin, $where['type']);
			M('coin', 'im')->addstar($where['uid'], $coin);
			$user = T('third_party_user')->set_field('remainder,golden_bean')->get_one(['id' => $where['uid']]);
			Out::jout($user);
		} else {
			if ($read_record['num'] >= $num) {

				Out::jerror('今日任务已达上限', null, '120123');
			}
			$where2['addtime'] = time();
			$where2['num'] = 1 + $read_record['num'];
		
			T('usertask')->update($where2, $where);
			M('census', 'im')->task_reward_count($where['uid'], $coin, $where['type']);
			M('coin', 'im')->addstar($where['uid'], $coin);
			$user = T('third_party_user')->set_field('remainder,golden_bean')->get_one(['id' => $where['uid']]);
			Out::jout($user);
		}
	}
	public function control_ad()
	{
		// 1每日分享 2每日阅读 3邀请好友 4替好友充值 5每日看广告 6签到,7完善用户资料,8每日充值
	
		$where['dates'] = date('Ymd');
		$where['uid'] = $this->get_userid();
		$where['type'] = 5;


		$read_record = T('usertask')->where($where)->get_one();
		//奖励金额
		$coin = 50;

		if (!$read_record) {
			$where['addtime'] = time();
			$where['num'] = 1;
		
			T('usertask')->add($where);
			M('census', 'im')->task_reward_count($where['uid'], $coin, $where['type']);
			M('coin', 'im')->addstar($where['uid'], $coin);
			$user = T('third_party_user')->set_field('remainder,golden_bean')->get_one(['id' => $where['uid']]);
			Out::jout($user);
		} else {
			if ($read_record['num'] >= 5) {
				Out::jerror('今日任务已达上限', null, '120123');
			}
			$where2['addtime'] = time();
			$where2['num'] = 1 + $read_record['num'];
		
			T('usertask')->update($where2, $where);
			M('census', 'im')->task_reward_count($where['uid'], $coin, $where['type']);
			M('coin', 'im')->addstar($where['uid'], $coin);
			$user = T('third_party_user')->set_field('remainder,golden_bean')->get_one(['id' => $where['uid']]);
			Out::jout($user);
		}
	}
	public function control_pay()
	{
		// 1每日分享 2每日阅读 3邀请好友 4替好友充值 5每日看广告 6签到,7完善用户资料,8每日充值
	
		$where['dates'] = date('Ymd');
		$where['uid'] = $this->get_userid();
		$where['type'] = 8;


		$read_record = T('usertask')->where($where)->get_one();
		//奖励金额
		$coin = 100;

		if (!$read_record) {
			$where['addtime'] = time();
			$where['num'] = 1;
		
			T('usertask')->add($where);
			M('census', 'im')->task_reward_count($where['uid'], $coin, 8);
			M('coin', 'im')->addstar($where['uid'], $coin);
			$user = T('third_party_user')->set_field('remainder,golden_bean')->get_one(['id' => $where['uid']]);
			Out::jout($user);
		} else {
			Out::jerror('今日已经领取了', null, '100123');
		}
	}
	public function control_endeditinfo()
	{
		// 1每日分享 2每日阅读 3邀请好友 4替好友充值 5每日看广告 6签到,7完善用户资料,8每日充值
		
		$user = parent::$wrap_user;
		// {nickname: เต้าหู้กับซอส, more: 快快快, avater: null, sex: 2, borth: 1991-11-14 00:00:00.000}
		if (!($user['nickname'] && $user['more'] && $user['avater'] && $user['sex'] && $user['borth'])) {
			Out::jerror('资料未完善', null, '2013111');
		}


		$where['uid'] = $this->get_userid();
		$where['type'] = 7;
		$read_record = T('usertask')->where($where)->get_one();
		//奖励金额
		$coin = 100;
		if (!$read_record) {
			$where['dates'] = date('Ymd');
			$where['addtime'] = time();
			$where['num'] = 1;
		
			T('usertask')->add($where);
			M('census', 'im')->task_reward_count($where['uid'], $coin, $where['type']);
			M('coin', 'im')->addstar($where['uid'], $coin);
			$user = T('third_party_user')->set_field('remainder,golden_bean')->get_one(['id' => $where['uid']]);
			Out::jout($user);
		} else {
			Out::jerror('已经领取了', null, '100123');
		}
	}
	public function control_readlocal()
	{
		// 1每日分享 2每日阅读 3邀请好友 4替好友充值 5每日看广告 6签到,7完善用户资料,8每日充值
	



		$where['uid'] = $this->get_userid();
		$where['type'] = 10;
		$read_record = T('usertask')->where($where)->get_one();
		//奖励金额
		$coin = 100;
		if (!$read_record) {
			$where['dates'] = date('Ymd');
			$where['addtime'] = time();
			$where['num'] = 1;
	
			T('usertask')->add($where);
			M('census', 'im')->task_reward_count($where['uid'], $coin, $where['type']);
			M('coin', 'im')->addstar($where['uid'], $coin);
			$user = T('third_party_user')->set_field('remainder,golden_bean')->get_one(['id' => $where['uid']]);
			Out::jout($user);
		} else {
			Out::jerror('已经领取了', null, '100123');
		}
	}
	public function control_gettodaypay()
	{
		// 1每日分享 2每日阅读 3邀请好友 4替好友充值 5每日看广告 6签到,7完善用户资料,8每日充值
		$get = get(['int' => ['readsec']]);
		$where['dates'] = date('Ymd');
		$where['users_id'] = $this->get_userid();
		$where['pay_status'] = 1;
		$where['proxy_id'] = 0;


		$read_record = T('order')->where($where)->set_field('users_id')->get_one();
		//奖励金额
		Out::jout($read_record ? 1 : 0);
	}
	public function control_friendinfo()
	{
		//取随机20个用户
		//取十个子用户
		//取子用户大小
		$rand = T('third_party_user')->set_limit(20)->set_field('nickname')->order_by(['f' => 'id', 's' => 'down'])->get_all();
		$childsize = T('invite')->set_where(['uid' => $this->get_userid()])->set_field('count(DISTINCT inviteid) as num')->get_one();
		//有效邀请
		$childsizeyx = T('invite')->set_where(['uid' => $this->get_userid(), 'flag' => 1])->set_field('count(DISTINCT inviteid) as num')->get_one();
		$user = M('user', 'im')->getchild($this->get_userid());
		Out::jout(['rand' => $rand, 'num' => $childsize['num'], 'nums' => $childsizeyx['num'], 'user' => $user]);
	}
	public function control_friendmore()
	{
		//取随机20个用户
		//取十个子用户
		//取子用户大小
		$get = get(['int' => ['page']]);
		$user = M('user', 'im')->getchild($this->get_userid(), $get['page']);
		Out::jout($user);
	}
	public function control_edit_invite()
	{
		// 1每日分享 2每日阅读 3邀请好友 4替好友充值 5每日看广告 6签到,7完善用户资料,8每日充值
		$get = get(['int' => ['inviteid' => 1]]);
		$user = parent::$wrap_user;
		$inviteuser = T('third_party_user')->get_one(['id' => $get['inviteid']]);
		if (!$inviteuser) Out::jerror('邀请人不存在', null, '2011112');
		if ($user['invite_id']) Out::jerror('你已经有邀请人了', null, '2011110');
		if ($get['inviteid'] == $this->get_userid()) {
			Out::jerror('邀请人不能是自己', null, '2011111');
		}
		T('third_party_user')->update(['invite_id' => $get['inviteid']], ['id' => $this->get_userid()]);
		//检查设备id唯一性
		$idfa = $this->head['idfa'];
		if ($idfa) {
			$idfas = ['idfa' => $idfa];
			$in = T('invite')->set_where($idfas)->get_one();
			if ($in) {
				//设备不唯一
				// T()->update([], []);
				$idfas['uid'] = $get['inviteid'];
				$idfas['inviteid'] = $this->get_userid();
				$idfas['addtime'] = time();
				$idfas['flag'] = 0;
				$idfas['ip'] = Request::getip();
				T('invite')->add($idfas);
			} else {
				//设备唯一
				//记录idfa
				//结算奖励
				$idfas['uid'] = $get['inviteid'];
				$idfas['inviteid'] = $this->get_userid();
				$idfas['addtime'] = time();
				$idfas['flag'] = 1;
				$idfas['ip'] = Request::getip();
				T('invite')->add($idfas);
				//结算奖励
				$coin = 500;
				$uid = $get['inviteid'];
				M('census', 'im')->task_reward_count($uid, $coin, 3);
				M('coin', 'im')->addstar($uid, $coin);
			}
			$user = T('third_party_user')->set_field('invite_id')->set_where(['id' => $this->get_userid()])->get_one();
			Out::jout($user);
		}

		//唯一就加金币
	}
}
