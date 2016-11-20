<?php

namespace Entities;

use RayTools\Entity\Entity;


class Type_notification extends Entity
{
    const
    TABLENAME       = '`schoolnet`.`Type_Notification`',
    fld_ID          = '`id`',
    fld_CODE        = '`code`',
    fld_CHAINTYPE   = '`chain_type`',
    fld_CHAINACTION = '`chain_action`',
    fld_CHAINOBJETS = '`chain_objets`';
    
    public function __construct($db)
    {
        parent::__construct($db,self::TABLENAME);
    
        /* Définition des Champs
         -------------------------------------------------------------------------- */
        $this->addCol(self::fld_ID, 'TypeID',          self::type_INT, 5);
        $this->addCol(self::fld_CODE, 'Code',          self::type_VARCHAR, 15);
        $this->addCol(self::fld_CHAINTYPE, 'ChainID',  self::type_INT, 5);
        $this->addCol(self::fld_CHAINACTION, 'Action', self::type_VARCHAR, 15);
        $this->addCol(self::fld_CHAINOBJETS, 'Objets', self::type_VARCHAR, 15);
    }
        
}
        
        