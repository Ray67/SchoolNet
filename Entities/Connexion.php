<?php

namespace Entities;

use RayTools\Entity\Entity;

class Connexion extends Entity
{
    const 
        TABLENAME    = '`schoolnet`.`Authentification`',
        fld_ID       = '`id`',
        fld_NOM      = '`nom`',
        fld_PASSWORD = '`password`',
        fld_DTECNX   = '`der_connexion`',
        fld_IPCNX    = '`ip_connexion`',
        fld_PROFILID = '`profil_id`';

    public function __construct($db)
    {
        parent::__construct($db, self::TABLENAME);
    
     /* Définition des Table Associée : pas sur Profil
        -------------------------------------------------------------------------- */

     /* Définition des Champs
        -------------------------------------------------------------------------- */
        $this->addCol(self::fld_ID, 'ID',                 self::type_INT, 5, true);
        $this->addCol(self::fld_NOM,'Nom',                self::type_VARCHAR, 40, false);
        $this->addCol(self::fld_PASSWORD, 'Passe',        self::type_PASSWORD, 40, true,'Mot de Passe');
    
        $this->addCol(self::fld_DTECNX, 'Date_connexion', self::type_DATE, 10, true, 'Dernière connexion');
        $this->addCol(self::fld_IPCNX, 'IP_connexion',    self::type_VARCHAR, 15, true,'IP de la connexion');
        $this->addCol(self::fld_PROFILID, 'ProfilID',     self::type_INT, 5, false,'Profil');
        
     /* Définition des Jointures 
        -------------------------------------------------------------------------- */
        //$this->addJoin(self::fld_PROFILID, '`schoolnet`.`Profil`', '`nom`', 'INNER JOIN');
    }
    
}
