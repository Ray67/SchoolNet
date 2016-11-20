<?php

namespace Entities;

use RayTools\Entity\Entity;

require_once 'RayTools\Raytools.php'; 

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
        fld_CLASSENOM = '`schoolnet`.`Classe`.`nom`',
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
        $this->addCol(self::fld_ID, 'Id',             self::type_INT, 5, true);
        $this->addCol(self::fld_NOM,'Nom',            self::type_VARCHAR, 40, false);
        $this->addCol(self::fld_AUTHID,'AuthId',      self::type_INT, 5, true,'Identifiant');
        $this->addCol(self::fld_AUTHNOM,'AuthNom',    self::type_VARCHAR, 40, false);
        $this->addCol(self::fld_PASSWORD, 'Passe',    self::type_PASSWORD, 40, true,'Mot de Passe');
        $this->addCol(self::fld_PROFILID, 'ProfilID', self::type_INT, 5, false,'Profil');
        $this->addCol(self::fld_CLASSEID,'ClasseId',  self::type_INT, 5, false,'Classe');
        $this->addCol(self::fld_ECOLEID,'EcoleId',    self::type_INT, 5, false,'Ecole');

        /* Définition des Jointures
         -------------------------------------------------------------------------- */
        $this->addJoin(self::fld_AUTHID, '`schoolnet`.`Authentification`', '`nom`', 'INNER JOIN');
        $this->addJoin(self::fld_CLASSEID, '`schoolnet`.`Classe`', '`nom`', 'INNER JOIN');
    }

    public function Load($id):bool
    {
        $this->addConstraint(self::fld_AUTHID, $id);
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
            $this->AddConstraint(self::fld_AUTHNOM, $identifiant);
            $this->AddConstraint(self::fld_PASSWORD, $password);
            $results = $this->GetAll();

            if ($results != []) $this->Fill($results);

            return ($results != []);

        } catch (PDOException $e) {
            return false;
        }
    }
}
