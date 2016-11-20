<?php

namespace Entities;

use RayTools\Entity\Entity;

class Contact extends Entity
{
    const
        TABLENAME = '`schoolnet`.`Amis`',
        fld_ID = '`id`',
        fld_MEMBRE = '`membre_id`',
        fld_AMIID = '`ami_id`',
        fld_AMINOM = '`schoolnet`.`Membre`.`nom`';

    public function __construct($db)
    {
        parent::__construct($db,self::TABLENAME);

     /* Définition des Champs
        -------------------------------------------------------------------------- */   
        $this->addCol(self::fld_ID, 'id',         self::type_INT, 5, true);
        $this->addCol(self::fld_MEMBRE, 'membre', self::type_INT, 5, true);
        $this->addCol(self::fld_AMIID, 'ami',     self::type_INT, 5, true,'Ami');
        
     /* Définition des Jointures
        -------------------------------------------------------------------------- */   
        $this->addJoin(self::fld_AMIID, '`schoolnet`.`Membre`', `nom`, $type='INNER JOIN');
    }

    public function Table_actions($URL)
    {
        return '<A HREF="'.$URL.'Delete">Supprimer</A> ';
    }
    
    public function Ajouter($id, $ami)
    {
        $this->fields->id = null;
        $this->fields->membre_id = $id;
        $this->fields->ami_id = $ami;
        $res = $this->Save();
        
        $this->fields->id = null;
        $this->fields->membre_id = $id;
        $this->fields->ami_id = $ami;
        return $this->Save() && $res;
    }
    
}
