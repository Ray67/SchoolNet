<?php

namespace Entities;

use RayTools\Entity\Entity;


class Ecole extends Entity
{
    const 
        TABLENAME = '`schoolnet`.`Ecole`',
        fld_ID = '`id`',
        fld_NOM = '`nom`';

    public function __construct($db)
    {
        parent::__construct($db,self::TABLENAME);
        
     /* Définition des champs
        -------------------------------------------------------------------------- */   
        $this->addCol(self::fld_ID, 'Num',   self::type_INT, 5, true);
        $this->addCol(self::fld_NOM,'Ecole', self::type_VARCHAR, 60, false);
    }
    
}