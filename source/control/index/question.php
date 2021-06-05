<?php
namespace ng169\control\index;

use ng169\control\indexbase;

checktop();

class question extends indexbase
{

    protected $noNeedLogin = ['*'];
    //分享小说
    public function control_index()
    {
        $users_id = get(['int'=>['users_id'=>1]]);
    	$question = T('question')->set_field('question_id,question_title,question_type')->get_all();
        $answer =T('answer')->set_field('answer_id,answer_title,answer_option,question_id')->get_all();
        // $this->assign('question',$question);
        // $this->assign('answer',$answer);
        // $this->assign('users_id',$users_id);
        // return $this->fetch();
        $this->view(null,['question'=>$question,'answer'=>$answer,'users_id'=>$users_id]);
    }
    //分享漫画
    public function control_answer()
    {
    	$data = $_POST;
        $answer_1 = explode("_",$data['answer_1']);
        $answer_3 = explode("_",$data['answer_3']);
        $arr['answer_id'] = $answer_1[0].','.$answer_3[0]; 
        if ($answer_3[0] == 8) {
            $arr['answer_option'] = $data['answer_3_user'];
        }
        $arr['question_id'] = $answer_1[1].','.$answer_3[1];
        $arr['user_answer'] = $data['answer_2'].','.$data['answer_4_1'].','.$data['answer_4_2'].','.$data['answer_5'];
        $arr['users_id'] = $data['users_id'];
        $arr['reply_coin'] = 50;
        $arr['reply_time'] = date('Y-m-d H:i:s',time());
        $arr['reply_date'] = date('Y-m-d');
        $reply = T('reply')->where(['users_id'=>$data['users_id']])->get_one();

        if (!$reply) {
          
                T('reply')->add($arr);
                T('answer')->whereIn('answer_id',$arr['answer_id'])->setInc('select_nums');
                T('third_party_user')->where('id',$data['users_id'])->setInc('remainder',50);
              
           
            if ($error == true) {
                $result['code'] = "0";
                $result['message'] = "ส่งสำเร็จ";
                echo json_encode($result);
            }
        }  else {
                $result['code'] = "1";
                $result['message'] = "คุณเคยส่งแล้วโปรดอย่าส่งซ้ำ";
                echo json_encode($result);
        }
        
    	die();
    }
}
