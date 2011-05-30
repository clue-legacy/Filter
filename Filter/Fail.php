<?php

class Filter_Fail extends Filter implements Filter_Interface_Negate, Filter_Interface_Sql{
    public function toSql($db){
        return '0';
    }
    
    public function toNegate(){
        return Filter::success();
    }
}