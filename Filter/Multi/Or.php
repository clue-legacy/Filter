<?php

/**
 * OR-combination (SOME/at least one filter has to match)
 * 
 * @author mE
 */
class Filter_Multi_Or extends Filter_Multi implements Filter_Interface_Simplify{
    public function __construct($elements){
        parent::__construct($elements);
        $this->combine = 'OR';
    }
    
    public function toSimplify(){
        foreach($this->elements as $key=>$element){
            $s = self::simplify($element);
            if($s instanceof Filter_Success){ // element always succeeds => result always succeeds
                return $s;
            }else if($s instanceof Filter_Fail){ // element always fails => ignore
                unset($this->elements[$key]);
            }
        }
        if(!$this->elements){ // no more elements => always succeed
            return true;
        }
        return $this;
    }
}
