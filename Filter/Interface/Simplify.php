<?php

interface Filter_Interface_Simplify{
    /**
     * (try to) simplify this filter condition
     * 
     * should not be called manually
     * 
     * @return Filter|boolean negated filter (CAN be modified $this, but does NOT have to!) or boolean shortcut for success/fail
     * @see Filter::simplify()
     */
    public function toSimplify();
}
