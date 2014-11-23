<?php

namespace tantrum\Database;

interface DatabaseAdaptorInterface
{   
    
    function formatSelect(Query $query);
    
    function formatInsert(Query $query);
    
    function formatUpdate(Query $query);
    
    function formatDelete(Query $query);
    
    function getColumnDefinitions($table);
}
