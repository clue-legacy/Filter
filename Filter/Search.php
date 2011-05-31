<?php

/**
 * create new search filter which supports wildcards
 * 
 * @author mE
 */
class Filter_Search extends Filter implements Filter_Interface_Sql{
    /**
     * text to search for
     * 
     * @var string
     */
    protected $search;
    
    /**
     * instanciate new search filter
     * 
     * @param string $search text to search for
     */
    public function __construct($search){
        $this->search = $search;
    }
    
    public function toSql($db){
        return 'LIKE ' . $this->escapeDbSearch($this->search,$db);
    }
    
    protected function escapeDbSearch($search,$db){
        return $db->escape($this->search,Db::ESCAPE_ENCLOSE|Db::ESCAPE_SEARCH);
    }
    
    public function matches($data){
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
        return !!preg_match($subject,$data);
    }
}
