<?php

/**
 * filter NULL values
 * 
 * @author mE
 */
class Filter_Null extends Filter implements Filter_Interface_Negate, Filter_Interface_Sql{
    /**
     * field to search in
     * 
     * @var string
     */
    protected $name;
    
    /**
     * whether to negate the filter
     * 
     * @var boolean
     */
    protected $negate;
    
    /**
     * instanciate new NULL filter
     * 
     * @param string $name
     */
    public function __construct($name){
        $this->name = $name;
        $this->negate = false;
    }
    
    public function toNegate(){
        $this->negate = !$this->negate;
        return $this;
    }
    
    public function toSql($db){
        return $this->escapeDbName($this->name,$db) . ($this->negate ? ' IS NOT NULL' : ' IS NULL');
    }
}
