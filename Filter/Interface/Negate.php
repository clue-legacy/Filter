<?php

interface Filter_Interface_Negate{
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
