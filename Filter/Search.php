<?php

/**
 * create new search filter which supports wildcards
 * 
 * @author mE
 */
class Filter_Search extends Filter implements Filter_Interface_Sql{
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
    
    public function matches($row){
        if(!array_key_exists($this->name,$row)){
            throw new Filter_Exception('Invalid key');
        }
        $wildcards = false;
        $subject = '/';
        for($i=0,$l=strlen($this->search);$i<$l;++$i){
            $c = $this->search[$i];
            if($c === '*'){
                $subject  .= '.*';
                $wildcards = true;
            }else if($c === '?'){
                $subject  .= '.';
                $wildcards = true;
            }else{
                $subject .= '\\'.$c;
            }
        }
        if($wildcards){
            $subject = '^'.$subject.'$';
        }
        $subject .= '/i';
        return !!preg_match($subject,$row[$this->name]);
    }
}
