<?php


namespace ng169\tool;
use ng169\tool\Filter as YFilter;
use ng169\tool\Cookie;
checktop();
class Page{
	public static $offset=[];
	public static $num=[];
	public static $perpage=[];
	public static $curr_page=[];
	public static $mpurl=[];
	public static $maxpage=[];
	public static $page;
	private static $obj=null;
    private static function  getcookindex(){
		$index=D_MEDTHOD.'_'.D_FUNC;
		return $index;
	}
	public static function init($num, $perpage, $curr_page, $mpurl, $maxpage){
		//写cookie，有cookie就初始化，无cookie，就不管
		self::$num=$num;
		self::$perpage=$perpage;
		self::$curr_page=$curr_page;
		self::$mpurl=$mpurl;
		self::$maxpage=$maxpage;
		/*$ck=Cookie::get(self::getcookindex());
		
		if($ck){
			self::$offset=json_decode($ck,1);
		}*/
	
		return self::$obj;
    	
    	
    
	}

	//注入偏移量
	public static function  injection_offset($first,$last){
  	if(!self::$curr_page)return false;
  	self::$offset[self::$curr_page]=$first;
  	self::$offset[self::$curr_page+1]=$last;
  	
  	self::init_page(self::$num, self::$perpage, self::$curr_page, self::$mpurl, self::$maxpage);
  
//  	Cookie::set(self::getcookindex(),json_encode(self::$offset),null,G_DAY);
  	return self::$obj;
	}
	//初始化分页
	private static function init_page($num, $perpage, $curr_page, $mpurl, $maxpage){
		/*$num=100000;*/
		
		$mpurl .= strpos($mpurl, '?') ? '&amp;' : '?';
           
		$fh='=';
		$mpurl=YFilter::filterXSS($mpurl);
       
		if($num > $perpage){
			$page    = $maxpage;
			$offset  = floor($page * 0.5);
			$pages   = @ceil($num/$perpage);
			$from    = $curr_page -$offset;
			$to      = $curr_page + $page - $offset - 1;
			if($page > $pages){
				$from = 1;
				$to   = $pages;
			}
			else{
				if($from<1){
					$to   = $curr_page + 1 - $from;
					$from = 1;
					if(($to - $from) < $page && ($to - $from) < $pages){
						$to = $page;
					}
				}
				elseif($to>$pages){
					$from = $curr_page - $pages + $to;
					$to   = $pages;
					if(($to - $from) < $page && ($to - $from) < $pages){
						$from = $pages - $page + 1;
					}
				}
			}
            
            
            
            
			#页数>1执行
			if($pages > 1){
				#分页 [1][2][3]...
				$block_step_size = '';
				for($i=$from; $i<=$to; $i++){
					if($i!=$curr_page){
						$offset=@self::$offset[$i];
						$block_step_size .= "<a href='{$mpurl}page{$fh}{$i}&offset{$fh}{$offset}'>{$i}</a>&nbsp;";
					}
					else{
						$block_step_size .= "<b>{$i}</b>&nbsp;";
					}
				}
				#上一页
				$block_pre = '';
				if($curr_page > 1){
					$pre_page = $curr_page-1;
					if($pre_page < 2){$pre_page = 1;}
					$offset_s="offset{$fh}".@self::$offset[0];
					$offset_sl="offset{$fh}".@self::$offset[$pre_page];
					$block_pre = "<a href='{$mpurl}page{$fh}1&{$offset_s}' class='p_first' />&nbsp;</a>&nbsp;".
					"<a href='{$mpurl}page{$fh}{$pre_page}&{$offset_sl}' class='p_pre' />&nbsp;</a>&nbsp;";
				}
				#下一页
				$block_next = '';
				if($pages > 1 && $curr_page < $pages){
					$next_page = $curr_page+1;
					if($next_page >=$pages){$next_page = $pages;}
					$offset_s="offset{$fh}".@self::$offset[$next_page];
					$offset_sl="offset{$fh}".@self::$offset[$pages];
					$block_next = "<a href='{$mpurl}page{$fh}{$next_page}&{$offset_s}' class='p_next' />&nbsp;</a>&nbsp;".
					"<a href='{$mpurl}page{$fh}{$pages}&{$offset_sl}' class='p_last' />&nbsp;</a>&nbsp;";
				}
			}
			$block_sl='';
			if($pages>$maxpage*2 && $curr_page<90){
				$offset_s="offset{$fh}".@self::$offset[100];
				$block_sl = "<a href='###' class='p_slh' />...</a>&nbsp;".
				"<a href='{$mpurl}page{$fh}100&{$offset_s}' class='p_100' />100</a>&nbsp;";
			}
			$show_page='';
			#showpage
			//            $show_page = "<span>记录：{$num}&nbsp;&nbsp;页次：{$curr_page}/{$pages}&nbsp;&nbsp;&nbsp;</span>";
			//$show_page = "<span>记录：{$num}&nbsp;&nbsp;页次：{$curr_page}/{$pages}&nbsp;&nbsp;&nbsp;</span>";
			$show_page .= $block_pre.$block_step_size.$block_sl.$block_next;
			if($pages > 1){
				/*$show_page .= "<span>&nbsp;跳转：<input style='width:24px!important;text-align:center'   type='text' id='inputpage' name='page' onkeypress=\"if(event.keyCode==13) window.location.href='".$mpurl."page{$fh}'+value\" value='{$curr_page}' />&nbsp;页</span>";*/
				
			}
		}
		else{
			//  $show_page = "<span>记录：{$num}&nbsp;&nbsp;</span>";
		}


		if(isset($show_page)){
			self::$page= $show_page;
		}else{
			self::$page= false;
		}
		
		return self::$obj;
	}
	public function getpage(){
		return self::$page;
	}
	//获取分页
	private function __construct(){
		
	}
	private function __clone(){
		return FALSE;
	}
	public static function getobj(){
		if(isset(self::$obj)){
			return self::$obj;
		}else{
			self::$obj=new self();
			return new self();
		}
	}
}
?>
