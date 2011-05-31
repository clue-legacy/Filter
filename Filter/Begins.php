<?php

class Filter_Begins extends Filter implements Filter_Interface_Sql{
    public function __construct($begin){
        $this->begin = $begin;
    }
    public function toSql($db){
        return 'LIKE '.$this->escapeDbLike($this->begin,$db);
    }
    
    protected function escapeDbLike($begin,$db){
        if(is_string($begin)){
            $begin = str_replace(array('%','_'),array('\\%','\\_'),$begin);
        }
        return $this->escapeDbValue($begin.'%',$db);
    }
    
    public function matches($data){
        return (substr($data,0,strlen($this->begin)) === $this->begin);
    }
}
