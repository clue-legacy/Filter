<?php

/**
 * simple negation wrapper for filters that do not support negation themselves
 * 
 * @author mE
 */
class Filter_Negate extends Filter implements Filter_Interface_Negate{
    /**
     * filter to be negated
     * 
     * @var Filter
     */
    protected $filter;
    
    /**
     * instanciate new negation-wrapper for given filter
     * 
     * @param Filter $filter
     */
    public function __construct($filter){
        $this->filter = $filter;
    }
    
    /**
     * return original filter (double negation = no negation)
     * 
     * @return Filter
     */
    public function toNegate(){
        return $this->filter;
    }
    
    public function toSql($db){
        return 'NOT('.$this->filter->toSql($db).')';
    }
}
