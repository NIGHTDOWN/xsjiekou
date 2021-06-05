<?php


namespace ng169\control\admin;

use ng169\control\adminbase;

checktop();

class frame extends adminbase
{
	private $mod = null;

	public function control_run()
	{
		
        $model = T('backstage_menu');
        $topmenu = $model->order_by(array('f' => array('orders'),'s'=>'up'))->set_where(array('depth' => 1,'flag'=>0))->get_all();
       
		$var_array = array('data' => $topmenu, 'admin' => parent::$wrap_admin);
		$admiid=$this->get_adminid();
        $this->view('',$var_array);
    }
	public function control_main()
	{
		

		/*$data=\ng169\tool\Code::encode('eyJuYW1lIjoiMTIzNDU2IiwiY29va2llIjpudWxsfQ==','123456789');
		d($data);
		$data= \ng169\tool\Code::decode('Xk9Q5y3QSyzPvNC8wCUmqGVydtHi5I/B7wDfxqsedrreJBVC8qGn7UyvP8/GQPNq','123456789');
		d($data,1);*/
		$model = parent::model('index', 'am');
		$data = $model->doGetSysData();
		
		unset($model);
		$var_array = array('count' => @$count, 'sysdata' => $data,'sockport'=>DB_SOCKPOST);
		
        $this->view('',$var_array);
	}
}

?>
