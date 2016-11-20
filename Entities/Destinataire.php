<?php

namespace Entities;

use RayTools\Entity\Entity;

class Destinataire extends Entity
{
    const
    TABLENAME      = '`schoolnet`.`Destinataire`',
    fld_ID         = '`id`',
    fld_NOTIFID    = '`notification_id`',
    fld_MEMBREID   = '`membre_id`';
    
    public function __construct($db)
    {
        parent::__construct($db,self::TABLENAME);
    
     /* Définition des Champs
        -------------------------------------------------------------------------- */
        $this->addCol(self::fld_ID, 'num_dest',       self::type_INT, 5);
        $this->addCol(self::fld_NOTIFID, 'notif_id',  self::type_INT, 5);
        $this->addCol(self::fld_MEMBREID, 'membre_id',self::type_INT, 5);
    }

}
        
