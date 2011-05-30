<?php

class Filter_Begins extends Filter implements Filter_Interface_Sql{
    public function __construct($name,$begin){
        $this->name = $name;
        $this->begin = $begin;
        parent::__construct();
    }
    public function toSql($db){
        return $this->escapeDbName($this->name,$db).' LIKE '.$this->escapeDbLike($this->begin,$db);
    }
    
    protected function escapeDbLike($begin,$db){
        if(is_string($begin)){
            $begin = str_replace(array('%','_'),array('\\%','\\_'),$begin);
        }
        return $this->escapeDbValue($begin.'%',$db);
    }
    
    public function matches($row){
        if(!array_key_exists($this->name,$row)){
            throw new Filter_Exception('Invalid key');
        }
        return (substr($row[$this->name],0,strlen($this->begin)) === $this->begin);
    }
}
