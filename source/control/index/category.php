<?php
namespace ng169\control\index;

use ng169\control\indexbase;

checktop();

class category  extends indexbase
{

    protected $noNeedLogin = ['*'];
    
    public function control_run()
    {
      
        $this->view();
    }
  
 
}
