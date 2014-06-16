<?php

namespace TomChaton\clingDB\Database;

interface DatabaseAdaptorInterface
{   
    
    function FormatSelect(Query $objQuery);
    
    function FormatInsert(Query $objQuery);
    
    function FormatUpdate(Query $objQuery);
    
    function FormatDelete(Query $objQuery);
    
    function GetColumnDefinitions($strTable);
}
