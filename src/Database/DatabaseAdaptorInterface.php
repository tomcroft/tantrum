<?php

namespace tantrum\Database;

interface DatabaseAdaptorInterface
{   
    
    function FormatSelect(Query $query);
    
    function FormatInsert(Query $query);
    
    function FormatUpdate(Query $query);
    
    function FormatDelete(Query $query);
    
    function GetColumnDefinitions($table);
}
