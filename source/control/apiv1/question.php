<?php

namespace ng169\control\apiv1;

use ng169\control\apiv1base;
use ng169\Y;
use ng169\tool\Out;

checktop();

require API . 'facebookbm/vendor/autoload.php';

class question extends apiv1base
{

    protected $noNeedLogin = ['*'];
    public function control_get_question_coin()
    {
        // $commonModel = new CommonModel();
        
        $this->returnSuccess(['question_coin'=>Y::$newconf['task']['question_coin']]);
    }
    public function control_get_question()
    {
        // $commonModel = new CommonModel();
        $data = M('task', 'im')->get_question();
        $this->returnSuccess($data);
    }
    public function control_answer()
    {
        $data = get(['array' => ['answer_arr' => 1],'string'=>['users_id']]);
        // print_r($data);die;
        $question_id = "";
        $answer_id = "";
        $answer = "";
        $bill = "";
        foreach ($data['answer_arr'] as $key => $val) {

            if ($val['question_type'] == 1) {
                $question_id .= $val['question_id'] . ",";
                foreach ($val['answer_all'] as $k => $v) {
                    $answer_id .= $val['question_id'] . "_" . $v['answer_id'] . ",";
                    $answer .= $v['answer_id'] . ",";
                    M('census', 'im')->answercount($v['answer_id']);
                }
            } else {
                $bill .= $val['question_id'] . "_" . $val['answer_desc'];
            }
        }       
        $question_coin = Y::$newconf['task'];
        $answer = rtrim($answer, ",");
        $arr['question_id'] = rtrim($question_id, ",");
        $arr['answer_id'] = rtrim($answer_id, ",");
        $arr['users_id'] = $data['users_id'];
        $arr['reply_coin'] = $question_coin['question_coin'];
        $arr['reply_time'] = date('Y-m-d H:i:s', time());
        $arr['reply_date'] = date('Y-m-d');
        $arr['user_answer'] = $bill;
      
        $reply = T('reply')->where(['users_id' => $data['users_id']])->find();
       $uid=$data['users_id'];
        if (!$reply) {
        
            T('reply')->add($arr);
           
            M('coin', 'im')->addstar($uid, $question_coin['question_coin']);
            M('census', 'im')->task_reward_count($uid, $question_coin['question_coin'], $question_coin['question_task']);
            Out::jout('提交成功');
        } else {
        	 
            Out::jerror('已经提交过了', null, '100136');
        }
    }
}
