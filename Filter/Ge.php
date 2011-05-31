<?php

class Filter_Ge extends Filter implements Filter_Interface_Sql, Filter_Interface_Negate{
    /**
     * number to compare to
     * 
     * @var number
     */
    private $value;
    
    public function __construct($value){
        $this->value = $value;
    }
    
    public function toNegate(){
        return Filter::lt($this->value);
    }
    
    public function toSql($db){
        return '>= '.$this->escapeDbValue($this->value,$db);
    }
    
    public function matches($data){
        return ($data >= $this->value);
    }
}
