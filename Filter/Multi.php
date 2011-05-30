<?php

/**
 * combination filters allow for a simple construction of AND/OR-conditions
 * 
 * @author mE
 */
abstract class Filter_Multi extends Filter implements Filter_Interface_Sql{
    /**
     * method used to combine the filter elements
     * 
     * @var string
     */
    protected $combine;
    
    /**
     * array of filter elements
     * 
     * @var array[Filter]
     */
    protected $elements;
    
    /**
     * instanciate new combination filter (AND/OR)
     * 
     * @param array  $elements array of filter elements
     */
    public function __construct($elements){
        $this->elements = array();
        foreach($elements as $name=>$value){
            if(!($value instanceof Filter)){
                if(!is_string($name)){
                    throw new Filter_Exception();
                }
                $this->elements []= self::eq($name,$value);
            }else if($value instanceof $this){
                foreach($value->getElements() as $element){
                    $this->elements[] = $element;
                }
            }else{
                $this->elements []= $value;
            }
        }
    }
    
    public function getElements(){
        return $this->elements;
    }
    
    public function toSql($db){
        $ret = '(';
        $first = true;
        foreach($this->elements as $filter){
            if($first){
                $first = false;
            }else{
                $ret .= ' '.$this->combine.' ';
            }
            $ret .= $filter->toSql($db);
        }
        if($first){
            return '1';
        }
        $ret .= ')';
        return $ret;
    }
}
