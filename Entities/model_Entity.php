<?php

namespace Entities;

use RayTools\Entity\Entity;


// Exemple pour concevoir les autres

class model_Entity extends Entity    
{
    const 
        TABLENAME  = '',
        fld_ID     = '`id`',
        fld_NOM    = '`nom`';

    public function __construct($db)
    {
        parent::__construct($db,self::TABLENAME);

     /* Définition des Champs 
            -> le premier est toujours id et est index primaire
        -------------------------------------------------------------------------- */   
        $this->addCol(self::fld_ID, 'Numéro',self::type_INT, 5, true);
        $this->addCol($dbname, $name, $type, $size, $null, $label='', $SQLStatement='');
        
     /* Définition des Jointures
        -------------------------------------------------------------------------- */   
        $this->addJoin($fldname, $table, $fldlabel, $type='INNER JOIN');
    }

}
