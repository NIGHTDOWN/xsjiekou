<?php





checktop();
require_once ('powercheck.php');


class powercheck extends Y
{
	
	
	public $actionpower, $gid, $actionid, $filedpower;
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function getactionid()
	{
		global $a, $c;

		$action = T('action_list');
		
		


		$ar = array('mods' => $c, 'func' => $a);

		$action = $action->get_one($ar);

		return $action['actionid'];


	}
	public function getallaction()
	{

		$arr = null;

		$strid = parent::$wrap_admin['roleids'];
		
		$arrid = explode(',', $strid);
		

		foreach ($arrid as $value)
		{

			$ar = M('power', 'am')->get_actionid($value);
			
			if ($ar)
			{

				$arr = ($arr . ',' . $ar);

			}

		}

		$arr = explode(',', $arr);

		$arr = array_filter($arr);
		$arr = array_unique($arr);

		return $arr;
	}
	public function check_power()
	{
		if (parent::$wrap_admin['super'] == 1)
		{

			return 0;
		}

		$c=D_MEDTHOD;$a=D_FUNC;
		$useraction = $this->getallaction();
		$thisaction = $this->getactionid();
		
		if (!empty($b))
		{
			error($c . '中' . $a . '该操作不涉及权限');
		}
		
		
		if (in_array($thisaction, $useraction))
		{
			
		}
		else
		{
			
		}
		;

	} 
}

?>
