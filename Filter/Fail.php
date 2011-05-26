<?php

class Filter_Fail extends Filter{
    public function toSql($db){
        return '0';
    }
}