<?php

class Filter_Keyed extends Filter implements Filter_Interface_Negate{
    /**
     * array key name
     * 
     * @var string
     */
    private $name;
    
    /**
     * filter to apply to key
     * 
     * @var Filter
     */
    private $filter;
    
    public function __construct($name,$filter){
        $this->name   = $name;
        $this->filter = $filter;
    }
    
    public function toNegate(){
        $this->filter = Filter::negate($this->filter);
        return $this;
    }
    
    public function toSql($db){
        if(!($this->filter instanceof Filter_Interface_Sql)){
            throw new Filter_Exception('Unable to convert '.get_class($this->filter).' to SQL statement');
        }
        $ret = $this->filter->toSql($db);
        if($ret === '1' || $ret === '0'){
            return $ret;
        }
        return $this->escapeDbName($this->name,$db) . ' ' . $ret;
    }
    
    public function matches($entry){
        if(!is_array($entry)){
            throw new Filter_Exception('Not an array');
        }
        if(!array_key_exists($this->name,$entry)){
            if($this->filter instanceof Filter_Null){
                return $this->filter->matches(NULL);
            }
            throw new Filter_Exception('Requested key does not exist');
        }
        return $this->filter->matches($entry[$this->name]);
    }
}
