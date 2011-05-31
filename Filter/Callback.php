<?php

class Filter_Callback extends Filter{
    /**
     * callback function to be called for each comparison
     * 
     * @var callback
     */
    private $callback;
    
    public function __construct($callback){
        if(!is_callable($callback)){
            throw new Filter_Exception('Invalid callback function');
        }
        $this->callback = $callback;
    }
    
    public function __toString(){
        $callback = $this->callback;
        if(is_array($callback)){
            if(!is_string($callback[0])){
                $callback = '$'.lcfirst(get_class($callback[0])).'->'.$callback[1];
            }else{
                $callback = $callback[0].'::'.$callback[1];
            }
        }
        return $callback.'(…)';
    }
    
    public function matches($value){
        return call_user_func($this->callback,$value,$this);
    }
}
