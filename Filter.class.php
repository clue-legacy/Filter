<?php

/**
 * filter factory class
 * 
 * @author mE
 */
abstract class Filter implements Interface_Sql{
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
     * @uses Interface_Filter_Negate::toNegate() where applicable
     */
    public static function negate(Filter $filter){
        if($filter instanceof Interface_Filter_Negate){
            return $filter->toNegate();
        }
        return new Filter_Negate($filter);
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
     * @param Db $db
     * @return string
     */
    //abstract public function toSql($db);
    
    /**
     * convert filter to sql where clause
     * 
     * @return string
     * @uses Filter::toSql()
     */
    public function __toString(){
        return $this->toSql(Db::singleton());
    }
    
    protected function escapeDbName($name,$db){
        return $db->escape($name,Db::ESCAPE_NAME);
        
        return '`'.$name.'`';
    }
    
    protected function escapeDbValue($value,$db){
        if($value instanceof Interface_Sql){
            $value = $value->toSql($db);
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

/**
 * combination filters allow for a simple construction of AND/OR-conditions
 * 
 * @author mE
 */
abstract class Filter_Multi extends Filter{
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
            throw new Filter_Exception();
        }
        $ret .= ')';
        return $ret;
    }
}

/**
 * AND-combination (ALL filters have to match)
 * 
 * @author mE
 */
class Filter_Multi_And extends Filter_Multi{
    public function __construct($elements){
        parent::__construct($elements);
        $this->combine = 'AND';
    }
}

/**
 * OR-combination (SOME/at least one filter has to match)
 * 
 * @author mE
 */
class Filter_Multi_Or extends Filter_Multi{
    public function __construct($elements){
        parent::__construct($elements);
        $this->combine = 'OR';
    }
}

/**
 * filter NULL values
 * 
 * @author mE
 */
class Filter_Null extends Filter implements Interface_Filter_Negate{
    /**
     * field to search in
     * 
     * @var string
     */
    protected $name;
    
    /**
     * whether to negate the filter
     * 
     * @var boolean
     */
    protected $negate;
    
    /**
     * instanciate new NULL filter
     * 
     * @param string $name
     */
    public function __construct($name){
        $this->name = $name;
        $this->negate = false;
    }
    
    public function toNegate(){
        $this->negate = !$this->negate;
        return $this;
    }
    
    public function toSql($db){
        return $this->escapeDbName($this->name,$db) . ($this->negate ? ' IS NOT NULL' : ' IS NULL');
    }
}

/**
 * create new search filter which supports wildcards
 * 
 * @author mE
 */
class Filter_Search extends Filter{
    /**
     * field to search in
     * 
     * @var string
     */
    protected $name;
    
    /**
     * text to search for
     * 
     * @var string
     */
    protected $search;
    
    /**
     * instanciate new search filter
     * 
     * @param string $name   field to search in
     * @param string $search text to search for
     */
    public function __construct($name,$search){
        $this->name   = $name;
        $this->search = $search;
    }
    
    public function toSql($db){
        return $this->escapeDbName($this->name,$db) . ' LIKE ' . $this->escapeDbSearch($this->search,$db);
    }
    
    protected function escapeDbSearch($search,$db){
        return $db->escape($this->search,Db::ESCAPE_ENCLOSE|Db::ESCAPE_SEARCH);
    }
}

/**
 * check whether the field value is an element of the given array
 * 
 * @author mE
 */
class Filter_Array extends Filter implements Interface_Filter_Negate{
    /**
     * field to search in
     * 
     * @var string
     */
    protected $name;
    
    /**
     * array of values to search for
     * 
     * @var string
     */
    protected $array;
    
    /**
     * whether to negate the filter
     * 
     * @var boolean
     */
    protected $negate;
    
    /**
     * instanciate new array filter
     * 
     * @param string $name  field to search in
     * @param array  $array values to search for
     */
    public function __construct($name,$array){
        $this->name   = $name;
        $this->array  = $array;
        $this->negate = false;
    }
    
    public function toNegate(){
        $this->negate = !$this->negate;
        return $this;
    }
    
    public function toSql($db){
        $ret = $this->escapeDbName($this->name,$db);
        if($this->negate){
            $ret .= ' NOT';
        }
        $ret .= ' IN (';
        $first = true;
        foreach($this->array as $value){
            if($first){
                $first = false;
            }else{
                $ret .= ',';
            }
            $ret .= $this->escapeDbValue($value,$db);
        }
        if($first){ // empty array given, no need to actually filter
            return $this->negate ? '1' : '0'; // searching in empty array will always fail
        }
        $ret .= ')';
        return $ret;
    }
}

class Filter_Named extends Filter implements Interface_Filter_Negate{
    protected $name;
    protected $value;
    protected $comparator;
    
    const COMPARATOR_EQ = '=';
    const COMPARATOR_GE = '>=';
    const COMPARATOR_GT = '>';
    const COMPARATOR_LE = '<=';
    const COMPARATOR_LT = '<';
    
    public function __construct($name,$value,$comparator){
        $this->name       = $name;
        $this->comparator = $comparator;
        $this->value      = $value;
    }
    
    public function toNegate(){
        if($this->comparator === self::COMPARATOR_GE){
            $this->comparator = self::COMPARATOR_LT;
        }else if($this->comparator === self::COMPARATOR_GT){
            $this->comparator = self::COMPARATOR_LE;
        }else if($this->comparator === self::COMPARATOR_LE){
            $this->comparator = self::COMPARATOR_GT;
        }else if($this->comparator === self::COMPARATOR_LT){
            $this->comparator = self::COMPARATOR_GE;
        }else{
            throw new Exception('Something went horribly wrong');
        }
        return $this;
    }
    
    public function toSql($db){
        return $this->dbEscapeName($this->name,$db) . $this->comparator . $this->escapeDbValue($this->value,$db);
    }
}

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

class Filter_Success extends Filter{
    public function toSql($db){
        return '1';
    }
}

class Filter_Fail extends Filter{
    public function toSql($db){
        return '0';
    }
}

class Filter_Begins extends Filter{
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
}

/**
 * simple negation wrapper for filters that do not support negation themselves
 * 
 * @author mE
 */
class Filter_Negate extends Filter implements Interface_Filter_Negate{
    /**
     * filter to be negated
     * 
     * @var Filter
     */
    protected $filter;
    
    /**
     * instanciate new negation-wrapper for given filter
     * 
     * @param Filter $filter
     */
    public function __construct($filter){
        $this->filter = $filter;
    }
    
    /**
     * return original filter (double negation = no negation)
     * 
     * @return Filter
     */
    public function toNegate(){
        return $this->filter;
    }
    
    public function toSql($db){
        return 'NOT('.$this->filter->toSql($db).')';
    }
}

interface Interface_Filter_Negate{
    /**
     * negate this filter condition
     * 
     * should not be called manually
     * 
     * @return Filter negated filter (CAN be modified $this, but does NOT have to!)
     * @see Filter::negate()
     */
    public function toNegate();
}

/**
 * lightweight filter exception
 * 
 * @author mE
 */
class Filter_Exception extends Exception { }
