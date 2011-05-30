<?php

/**
 * AND-combination (ALL filters have to match)
 * 
 * @author mE
 */
class Filter_Multi_And extends Filter_Multi implements Filter_Interface_Simplify{
    public function __construct($elements){
        parent::__construct($elements);
        $this->combine = 'AND';
    }
    
    public function toSimplify(){
        foreach($this->elements as $key=>$element){
            $s = self::simplify($element);
            if($s instanceof Filter_Success){ // element always succeeds => ignore
                unset($this->elements[$key]);
            }else if($s instanceof Filter_Fail){ // element always fails => result always fails
                return $s;
            }
        }
        if(!$this->elements){ // no more elements => always succeed
            return true;
        }
        return $this;
    }
    
    public function matches($row){
        foreach($this->elements as $element){
            if(!$element->matches($row)){
                return false;
            }
        }
        return true;
    }
}
