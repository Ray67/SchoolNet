<?php

namespace RayTools\Item;

interface ItemInterface
{
    
    const 
        type_INT      = 'INT',
        type_DATE     = 'DATE',
        type_EMAIL    = 'EMAIL',
        type_VARCHAR  = 'VARCHAR',
        type_PASSWORD = 'PASSWORD',
        type_TEXT     = 'LONGTEXT';
    
 /* Regex pour type de champs
    --------------------------------------------------------------------------------- */
    const 
        type_REGEX = [self::type_EMAIL => '#^[-.\w]{1,}@[-.\w]{2,}\.[a-zA-Z]{2,4}$#',
                     ];
        
}