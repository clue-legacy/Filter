<?php

/**
 * filter NULL values
 * 
 * @author mE
 */
class Filter_Null extends Filter implements Filter_Interface_Negate, Filter_Interface_Sql{
    /**
     * whether to negate the filter
     * 
     * @var boolean
     */
    protected $negate = false;
    
    public function toNegate(){
        $this->negate = !$this->negate;
        return $this;
    }
    
    public function toSql($db){
        return ($this->negate ? 'IS NOT NULL' : 'IS NULL');
    }
    
    public function matches($data){
        return (isset($data) === $this->negate);
    }
}
