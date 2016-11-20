<?php

namespace Entities;


use RayTools\Entity\Entity;


class Membre extends Entity
{
    const 
        TABLENAME = '`schoolnet`.`Membre`',
        fld_ID = '`id`',
        fld_NOM = '`nom`',
        fld_CLASSE = '`classe_id`',
        fld_AUTH = '`authen_id`';
    
 /* Valeurs jointes
    ------------------------------------------------------------------------------ */   
    const 
        fld_PROFIL    = '`schoolnet`.`Authentification`.`profil_id`',
        fld_NOMCLASSE = '`schoolnet`.`Classe`.`nom`',
        fld_ECOLE     = '`schoolnet`.`Classe`.`ecole_id`';
        
    public function __construct($db)
    {
        parent::__construct($db,self::TABLENAME);

     /* Définition des Champs simples
        -------------------------------------------------------------------------- */
        $this->addCol(self::fld_ID, 'Id',           self::type_INT, 5, true);
        $this->addCol(self::fld_NOM,'Nom',          self::type_VARCHAR, 60, false);
        $this->addCol(self::fld_AUTH,'Identifiant', self::type_INT, 5, false);

     /* Définition des Champs avec foreign key ordonnés
        -------------------------------------------------------------------------- */
        $this->addCol(self::fld_AUTH,'Identifiant', self::type_INT, 5, false);
        $this->addCol(self::fld_PROFIL, 'Profil',   self::type_INT, 5, false);
        $this->addCol(self::fld_CLASSE, 'Classe',   self::type_INT, 5, false);
        $this->addCol(self::fld_NOMCLASSE, 'Classes', self::type_VARCHAR, 15, false);
        $this->addCol(self::fld_ECOLE, 'Ecole',     self::type_INT, 5, false);
        
     /* Définition des Jointures 
         -------------------------------------------------------------------------- */
        $this->addJoin(self::fld_CLASSE, Classe::TABLENAME,
                                         Classe::fld_NOM, 'INNER JOIN');
        $this->addJoin(self::fld_ECOLE,  Ecole::TABLENAME,
                                         Ecole::fld_NOM, 'INNER JOIN'); 
        $this->addJoin(self::fld_AUTH,   Connexion::TABLENAME, 
                                         Connexion::fld_NOM, 'INNER JOIN');
    }

}


