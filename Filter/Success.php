<?php

class Filter_Success extends Filter implements Filter_Interface_Negate{
    public function toSql($db){
        return '1';
    }
    
    public function toNegate(){
        return Filter::fail();
    }
}
