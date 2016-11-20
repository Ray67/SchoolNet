<?php

namespace Entities;

use RayTools\Entity\Entity;

class Classe extends Entity
{
    const 
        TABLENAME   = '`schoolnet`.`Classe`',
        fld_ID      = '`id`',
        fld_NOM     = '`nom`',
        fld_ECOLEID = '`ecole_id`';
    
    protected 
        $ecole;

    public function __construct($db)
    {
        parent::__construct($db, self::TABLENAME);

     /* Définition des Table Associée
        -------------------------------------------------------------------------- */
        
     /* Définition des Champs
        -------------------------------------------------------------------------- */
        $this->addCol(self::fld_ID, 'Num',        self::type_INT, 5, true);
        $this->addCol(self::fld_NOM,'Classe',     self::type_VARCHAR, 60, false);
        $this->addCol(self::fld_ECOLEID, 'Ecole', self::type_INT, 5, false);

     /* Définition des Jointures
        -------------------------------------------------------------------------- */
        $this->addJoin(self::fld_ECOLEID, '`schoolnet`.`Ecole`', Ecole::fld_NOM, 'INNER JOIN');
    }

}
