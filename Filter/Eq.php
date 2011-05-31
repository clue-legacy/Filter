<?php

class Filter_Eq extends Filter implements Filter_Interface_Sql, Filter_Interface_Negate{
    /**
     * 
     * @var unknown_type
     */
    private $value;
    
    private $negate = false;
    
    public function __construct($value){
        $this->value = $value;
    }
    
    public function toNegate(){
        $this->negate = !$this->negate;
        return $this;
    }
    
    public function toSql($db){
        return ($this->negate ? '<> ' : '= ').$this->escapeDbValue($this->value,$db);
    }
    
    public function matches($data){
        return ($data === $this->value XOR $this->negate);
    }
}
