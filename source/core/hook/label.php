<?php



namespace  ng169\hook;
use ng169\Y;
use ng169\TPL;
checktop();

function hook_get_label($params) {
    if (!empty($params)) {
        @extract($params);
        $name = strtolower(trim($params['name']));
        if (true === YValid::isSpChar($name)){
            
            $model_label = X::model('label', 'im');
            return $model_label->getOne($name);
            unset($model_label);        
        }
    } 
    
}
TPL::regFunction('label', 'hook_get_label');
?>
