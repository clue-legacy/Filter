<?php

/**
 * filter factory class
 * 
 * @author mE
 */
abstract class Filter{
    /**
     * create new filter checking for equality
     * 
     * @param string    $name  field name
     * @param mixed,... $value single value to match field or match either of multiple values (array of multiple arguments)
     * @return Filter_Named|Filter_Array|Filter_Null depending on value(s) given
     */
    public static function eq($name,$value){
        if(func_num_args() > 2){
            $value = func_get_args();
            unset($value[0]);
        }
        if($value === NULL){
            return new Filter_Null($name);
        }
        if(is_array($value)){
            return new Filter_Array($name,$value);
        }
        return new Filter_Named($name,$value,'=');
    }
    
    /**
     * create new filter checking for UNequality (NOT equal)
     * 
     * @param string $name
     * @param mixed,... $value
     * @return Filter
     * @uses Filter::eq()
     * @uses Filter::negate()
     */
    public static function neq($name,$value){
        if(func_num_args() > 2){
            $value = func_get_args();
            unset($value[0]);
        }
        return self::negate(self::eq($name,$value));
    }
    
    /**
     * create new filter searching given name for value
     * 
     * @param string $name  field to search in
     * @param string $value value to search for (may include widcards such as '*' and '?')
     * @return Filter_search
     */
    public static function search($name,$value){
        return new Filter_Search($name,$value);
    }
    
    public static function gt($name,$value){
        return new Filter_Named($name,$value,'>');
    }
    
    public static function ge($name,$value){
        return new Filter_Named($name,$value,'>=');
    }
    
    public static function lt($name,$vaue){
        return new Filter_Named($name,$value,'<');
    }
    
    public static function le($name,$vaue){
        return new Filter_Named($name,$value,'<=');
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
            return $this->toSql(Db::singleton());
        }
        throw new Filter_Exception('Unable to convert to string');
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
}
