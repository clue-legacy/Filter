<?php

/**
 * adapter to provide an interface to SPL FilterIterator
 * 
 * @see Filter::toIterator()
 */
class Filter_Adapter_Iterator extends FilterIterator{
    /**
     * filter to apply to iterator
     *  
     * @var Filter
     */
    private $filter;
    
    /**
     * instanciate new iterator adapter (SHOULD NOT be called manually!)
     * 
     * @param Iterator $iterator
     * @param Filter   $filter
     */
    public function __construct(Iterator $iterator,Filter $filter){
        parent::__construct($iterator);
        $this->filter = $filter;
    }
    
    /**
     * check whether the current element of the the iterator is acceptable
     * 
     * @return boolean
     * @uses FilterIterator::current() to get current element
     * @uses Filter::matches() to check for match
     */
    public function accept(){
        return $this->filter->matches(parent::current());
    }
}
