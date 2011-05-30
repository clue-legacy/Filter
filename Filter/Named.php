<?php

class Filter_Named extends Filter implements Filter_Interface_Negate, Filter_Interface_Sql{
    protected $name;
    protected $value;
    protected $comparator;
    
    const COMPARATOR_EQ = '=';
    const COMPARATOR_GE = '>=';
    const COMPARATOR_GT = '>';
    const COMPARATOR_LE = '<=';
    const COMPARATOR_LT = '<';
    
    public function __construct($name,$value,$comparator){
        $this->name       = $name;
        $this->comparator = $comparator;
        $this->value      = $value;
    }
    
    public function toNegate(){
        if($this->comparator === self::COMPARATOR_GE){
            $this->comparator = self::COMPARATOR_LT;
        }else if($this->comparator === self::COMPARATOR_GT){
            $this->comparator = self::COMPARATOR_LE;
        }else if($this->comparator === self::COMPARATOR_LE){
            $this->comparator = self::COMPARATOR_GT;
        }else if($this->comparator === self::COMPARATOR_LT){
            $this->comparator = self::COMPARATOR_GE;
        }else{
            throw new Exception('Something went horribly wrong');
        }
        return $this;
    }
    
    public function toSql($db){
        return $this->escapeDbName($this->name,$db) . $this->comparator . $this->escapeDbValue($this->value,$db);
    }
    
    public function matches($row){
        throw new Filter_Exception();
    }
}
