<?php

/**
 * filter factory class
 * 
 * @author mE
 */
abstract class Filter{
    public static function key($name,$filter){
        return new Filter_Keyed(name,$filter);
    }
    
    public static function __callStatic($function,$args){
        if($args && substr($function,0,3) === 'key'){
            $function = substr($function,3);
            if(is_callable(array('Filter',$function))){
                $name = array_shift($args);
                $filter = call_user_func_array(array('Filter',$function),$args);
                return new Filter_Keyed($name,$filter);
            }
        }
        throw new Filter_Exception('Invalid bla');
    }
    /**
     * create new filter checking for equality
     * 
     * @param mixed,... $value single value to match field or match either of multiple values (array of multiple arguments)
     * @return Filter_Named|Filter_Array|Filter_Null depending on value(s) given
     */
    public static function eq($value){
        if(func_num_args() > 1){
            $value = func_get_args();
            return new Filter_Array($name,$value);
        }
        if($value === NULL){
            return new Filter_Null();
        }
        return new Filter_Eq($value);
    }
    
    /**
     * create new filter checking for UNequality (NOT equal)
     * 
     * @param mixed,... $value
     * @return Filter
     * @uses Filter::eq()
     * @uses Filter::negate()
     */
    public static function neq($value){
        if(func_num_args() === 1){
            return self::negate(self::eq($value));
        }
        $value = func_get_args();
        return self::negate(call_user_func_array(array('Filter','eq'),$value));
    }
    
    /**
     * create new filter using wildcard search for given value
     * 
     * @param string $value value to search for (may include widcards such as '*' and '?')
     * @return Filter_search
     */
    public static function search($value){
        return new Filter_Search($value);
    }
    
    public static function gt($value){
        return new Filter_Gt($value);
    }
    
    public static function ge($value){
        return new Filter_Ge($value);
    }
    
    public static function lt($value){
        return new Filter_Lt($value);
    }
    
    public static function le($value){
        return new Filter_Le($value);
    }
    
    /**
     * compare to given number (may include a comparator)
     * 
     * possible formats include "0" (equals 0), ">=1", ">2", "<3", "<=4",
     * "!5" (not 5), "6+" (same as ">=6") and "7-" (same as "<=7")
     * 
     * @param string $value value to compare to
     * @return Filter
     * @throws Filter_Exception if comparator or value is invalid
     */
    public static function compareNumber($value){
        $v = $value;
        $c = '=';
        $t = substr($value,0,2);
        if($t === '<=' || $t === '>='){                                         // two character comparator
            $c = $t;
            $v = substr($value,2);
        }else{
            $t = substr($t,0,1);
            if($t === '!' || $t === '<' || $t === '>'){                         // single character comparator
                $c = $t;
                $v = substr($value,1);
            }else{
                $t = substr($value,-1);
                if($t === '+' || $t === ' '){                                   // ends with '+' (non-urlencoded '+' results in a space)
                    $c = '>=';
                    $v = substr($value,0,-1);
                }else if($t === '-'){                                           // ends with '-'
                    $c = '<=';
                    $v = substr($value,0,-1);
                }
            }
        }
        if(!preg_match('/^\d+(?:\.\d+)?$/',$v)){
            throw new Filter_Exception('Has to be a valid number');
        }
        $v = (float)$v;
        
        if($c === '='){
            return new Filter_Eq($v);
        }else if($c === '!'){
            return self::negate(new Filter_Eq($v));
        }else if($c === '<='){
            return new Filter_Le($v);
        }else if($c === '<'){
            return new Filter_Lt($v);
        }else if($c === '>='){
            return new Filter_Ge($v);
        }else if($c === '>'){
            return new Filter_Gt($v);
        }
        throw new Filter_Exception('Invalid comparator');
    }
    
    /**
     * make sure any (some / at least one / logical OR) filter of the given filters match
     * 
     * may be called as either any(Filter,Filter) or any(array(Filter,Filter))
     *  
     * @param array|Filter,... $filter
     * @return Filter_Multi
     */
    public static function any($filter){
        if(func_num_args() > 1){
            $filter = func_get_args();
        }
        return new Filter_Multi_Or($filter);
    }
    
    /**
     * make sure all (logical AND) of the given filters match
     * 
     * @param array|Filter,... $filter
     * @return Filter_Multi
     */
    public static function all($filter){
        if(func_num_args() > 1){
            $filter = func_get_args();
        }
        return new Filter_Multi_And($filter);
    }
    
    /**
     * make sure neither of the given filters match
     * 
     * @param array|Filter,... $filter
     * @return Filter
     * @uses Filter::any()
     * @uses Filter::negate()
     */
    public static function neither($filter){
        if(func_num_args() > 1){
            $filter = func_get_args();
        }
        return self::negate(self::any($filter));
    }
    
    /**
     * negate the given filter (i.e. return a filter that filters the opposite of the given filter)
     * 
     * @param Filter $filter
     * @return Filter
     * @uses Filter_Interface_Negate::toNegate() where applicable
     */
    public static function negate(Filter $filter){
        if($filter instanceof Filter_Interface_Negate){
            $filter = clone $filter;
            return $filter->toNegate();
        }
        return new Filter_Negate($filter);
    }
    
    /**
     * get simplified filter
     * 
     * @param Filter $filter
     * @return Filter
     */
    public static function simplify(Filter $filter){
        if($filter instanceof Filter_Interface_Simplify){
            $filter = clone $filter;
            $filter = $filter->toSimplify();
            if($filter === true){
                return self::success();
            }else if($filter === false){
                return self::fail();
            }
        }
        return $filter;
    }
    
    /**
     * return a filter which always filters everything
     * 
     * @return Filter_Fail
     */
    public static function fail(){
        return new Filter_Fail();
    }
    
    /**
     * return a filter which never filters anything
     * 
     * @return Filter_Success
     */
    public static function success(){
        return new Filter_Success();
    }
    
    /**
     * make sure key with name starts with value
     * 
     * @param string $name
     * @param string $value
     * @return Filter_Begins
     */
    public static function begins($name,$value){
        return new Filter_Begins($name,$value); 
    }
    
    ////////////////////////////////////////////////////////////////////////////
    
    /**
     * convert filter to sql where clause
     * 
     * @return string
     * @uses Filter::toSql()
     */
    public function __toString(){
        if($this instanceof Filter_Interface_Sql){
            try{
                return $this->toSql(Db::singleton());
            }
            catch(Exception $e){
                var_dump($e);
            }
        }
        return var_export($this,true);
        //throw new Filter_Exception('Unable to convert to string');
    }
    
    protected function escapeDbName($name,$db){
        return $db->escape($name,Db::ESCAPE_NAME);
        
        return '`'.$name.'`';
    }
    
    protected function escapeDbValue($value,$db){
        if($value instanceof Filter_Interface_Sql){
            return $value->toSql($db);
        }
        return $db->escape($value);
        
        if(is_int($value) || is_float($value)){
            return $value;
        }
        if($value === true || $value === false){
            return (int)$value;
        }
        if($value === NULL){
            return 'NULL';
        }
        return Db::singleton()->quote($value);
    }
    
    /**
     * check whether this filter matches the given value
     * 
     * @param mixed $row
     * @return boolean
     */
    abstract public function matches($row);
    
    /**
     * apply filter to given data/collection/iterator
     * 
     * @param array $data complete input data
     * @return array filtered result
     * @uses Filter::matches()
     */
    public function apply($data){
        $ret = array();
        foreach($data as $key=>$row){
            if($this->matches($row)){
                $ret[$key] = $row;
            }
        }
        return $ret;
    }
    
    /**
     * get filtered SPL iterator for given data/iterator
     * 
     * @param Iterator|Traversable|array $data complete input data
     * @return Iterator iterator with only filtered values left
     * @uses Filter_Adapter_Iterator
     * @link http://www.php.net/manual/en/class.filteriterator.php
     */
    public function toIterator($data){
        if(!($data instanceof Iterator)){
            $data = new IteratorIterator($data);
        }
        return new Filter_Adapter_Iterator($data,$this);
    }
}
