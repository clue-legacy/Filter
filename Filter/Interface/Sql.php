<?php

/**
 * simple interface to convert object to SQL query string
 */
interface Filter_Interface_Sql{
    /**
     * convert filter to SQL query string
     * 
     * @param Db $db current database handle
     * @return string SQL string
     */
    public function toSql($db);
}
