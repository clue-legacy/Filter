<?php

abstract class Filter_Expression { }

abstract class Filter_Operand extends Filter_Expression{
    abstract public function toSql($db);
}

class Filter_Name extends Filter_Operand {
    public function __construct($name){
        $this->name = $name;
    }
    
    public function toSql($db){
        return $db->escape($this->name,Db::ESCAPE_NAME);
    }
}

class Filter_Value extends Filter_Operand{
    private $escape;
    
    public function __construct($value){
        $this->value = $value;
        $this->escape = Db::ESCAPE_ENCLOSE;
    }
    
    public function toSql($db){
        return $db->escape($this->value,$this->escape);
    }
}

class Filter_Operation extends Filter_Expression{
    public function __construct($operand1,$operation,$operand2){
        if(!($operand1 instanceof Filter_Expression)){
            $operand1 = new Filter_Name($operand1);
        }
        if(!($operand2 instanceof Filter_Expression)){
            $operand2 = new Filter_Value($operand2);
        }
        $this->operand1  = $operand1;
        $this->operation = $operation;
        $this->operand2  = $operand2;
    }
    
    public function toSql($db){
        return $this->operand1->toSql($db) . ' ' . $this->operation . ' ' . $this->operand2->toSql($db);
    }
}
