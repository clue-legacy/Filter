<?php

class Filter_Success extends Filter implements Filter_Interface_Negate, Filter_Interface_Sql{
    public function toSql($db){
        return '1';
    }
    
    public function toNegate(){
        return Filter::fail();
    }
    
    public function matches($row){
        return true;
    }
}
