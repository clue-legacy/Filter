<?php

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
