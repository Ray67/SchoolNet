<?php

namespace Entities;

use RayTools\Entity\Entity;

class Authentification extends Entity
{
    const 
        TABLENAME    = '`schoolnet`.`Membre`',
        fld_ID       = '`id`',
        fld_NOM      = '`nom`',
        fld_AUTHID   = '`authen_id`',
        fld_AUTHNOM  = '`schoolnet`.`Authentification`.`nom`',
        fld_PROFILID = '`schoolnet`.`Authentification`.`profil_id`',
        fld_PASSWORD = '`schoolnet`.`Authentification`.`password`',
        fld_CLASSEID = '`classe_id`',
        fld_CLASSENOM= '`schoolnet`.`Classe`.`nom`',
        fld_ECOLEID  = '`schoolnet`.`Classe`.`ecole_id`';

    public
        $id,
        $nom,
        $profil,
        $membres,
        $classes,
        $ecoles;
 

    public function __construct($db)
    {
        parent::__construct($db, self::TABLENAME);
    
     /* Définition des Table Associée : pas sur Profil
        -------------------------------------------------------------------------- */

     /* Définition des Champs
        -------------------------------------------------------------------------- */
        $this->add_Col(self::fld_ID, 'Id',             self::typefld_INT, 5, true);
        $this->add_Col(self::fld_NOM,'Nom',            self::typefld_VARCHAR, 40, false);
        $this->add_Col(self::fld_AUTHID,'AuthId',      self::typefld_INT, 5, true,'Identifiant');
        $this->add_Col(self::fld_AUTHNOM,'AuthNom',    self::typefld_VARCHAR, 40, false);
        $this->add_Col(self::fld_PASSWORD, 'Passe',    self::typefld_PASSWORD, 40, true,'Mot de Passe');
        $this->add_Col(self::fld_PROFILID, 'ProfilID', self::typefld_INT, 5, false,'Profil');
        $this->add_Col(self::fld_CLASSEID,'ClasseId',  self::typefld_INT, 5, false,'Classe');
        $this->add_Col(self::fld_ECOLEID,'EcoleId',    self::typefld_INT, 5, false,'Ecole');
        
     /* Définition des Jointures 
        -------------------------------------------------------------------------- */
        $this->add_Join(self::fld_AUTHID, '`schoolnet`.`Authentification`', '`nom`', 'INNER JOIN');
        $this->add_Join(self::fld_CLASSEID, '`schoolnet`.`Classe`', '`nom`', 'INNER JOIN');
    }
    
    public function Load($id):bool
    {
        $this->add_Constraint(self::fld_AUTHID, $id);
        $results = $this->getAll();
        if ($results !=[]) $this->Fill($results);
        return ($results !=[]);
    }
    
    public function Fill($results)
    {
        parent::Fill($results[0]);
        $this->id       = $this->getValue(self::fld_AUTHID);
        $this->profil   = $this->getValue(self::fld_PROFILID);
        $this->nom      = $this->getValue(self::fld_AUTHNOM);
                
        $this->membres = sub_array([$this->getAlias(self::fld_ID),
                                    $this->getAlias(self::fld_NOM),
                                    $this->getAlias(self::fld_CLASSEID),
                                    $this->getAlias(self::fld_ECOLEID),
                                   ], $results, true, true);
        $this->classes = sub_array([$this->getAlias(self::fld_CLASSEID)], $results, true);
        $this->ecoles  = sub_array([$this->getAlias(self::fld_ECOLEID)], $results, true);

        $_SESSION['id'] = $this->id;
    }

    public function connect($identifiant, $password) : bool
    {
        try
        {
            $this->Add_Constraint(self::fld_AUTHNOM, $identifiant);
            $this->Add_Constraint(self::fld_PASSWORD, $password);
            $results = $this->GetAll();
            
            if ($results != []) $this->Fill($results);
            
            return ($results != []);
            
        } catch (PDOException $e) { 
            return false;
        }
    }
}
