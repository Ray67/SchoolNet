<?php

namespace Entities;

use RayTools\Entity\Entity;
use Entities\Destinataire;
use Entities\Type_notification;


class Notification extends Entity
{
    const 
        TABLENAME      = '`schoolnet`.`Notification`',
        fld_ID         = '`id`',
        fld_TYPEID     = '`type_id`',
        fld_CREATION   = '`date_creation`',
        fld_VALIDITE   = '`date_validite`',
        fld_EMETEUR    = '`emeteur_id`',
        fld_CHAINID    = '`chain_notif_id`',
        fld_VISUPUBLIC = '`visu_public`',
        fld_VISUECOLE  = '`visu_ecole`',
        fld_VISUCLASSE = '`visu_classe`',
        fld_TITRE      = '`titre`',
        fld_CONTENT    = '`contenu`';

    /* Valeurs jointes
    ------------------------------------------------------------------------------ */   
    const 
//         fld_TYPECODE   = '`schoolnet`.`Type_Notification`.`code`',
//         fld_TYPEACTION = '`schoolnet`.`Type_Notification`.`chain_action`',
//         fld_TYPEOBJETS = '`schoolnet`.`Type_Notification`.`chain_objets`',
        fld_DESTINATAIRE = Destinataire::TABLENAME .'.'. Destinataire::fld_MEMBREID;
    
    protected 
        $ecoles,
        $classes,
        $membres;
    
    public 
        $types,
        $typesprimaires;
    
    /**
     * 
     * @param unknown $db
     * @param unknown $ecoles  tableau indexé par membre
     * @param unknown $classes tableau indexé par membre
     * @param unknown $membres tableau indexé par membre
     */
    public function __construct($db, $ecoles, $classes, $membres)
    {
        parent::__construct($db,self::TABLENAME);

     /* Définition des Champs
        -------------------------------------------------------------------------- */   
        $this->addCol(self::fld_ID, 'num_notif',           self::type_INT, 5, true,'Num');
        $this->addCol(self::fld_TYPEID, 'type',            self::type_INT, 5, false,'Type');
        $this->addCol(self::fld_CREATION, 'dte_creation',  self::type_DATE, 10, true, 'Créer le');
        $this->addCol(self::fld_VALIDITE, 'dte_validite',  self::type_DATE, 10, false, 'Validité');
        $this->addCol(self::fld_EMETEUR, 'emeteur_id',     self::type_INT, 5, false, $label='Emeteur');
        $this->addCol(self::fld_CHAINID, 'chain_id',       self::type_INT, 5, true, $label='Notif mère');
        $this->addCol(self::fld_VISUPUBLIC, 'visu_public', self::type_INT, 5, false, $label='Public');
        $this->addCol(self::fld_VISUECOLE, 'visu_ecole',   self::type_INT, 5, false, $label='Ecole');
        $this->addCol(self::fld_VISUCLASSE, 'visu_classe', self::type_INT, 5, false, $label='Classe');
        $this->addCol(self::fld_TITRE, 'titre',            self::type_VARCHAR, 60, true, $label='Titre');
        $this->addCol(self::fld_CONTENT, 'contenu',        self::type_TEXT, 1000, true, $label='Contenu');
        $this->addCol(self::fld_DESTINATAIRE,'Destinataire', self::type_INT, 5);
        
     /* Définition des Jointures
        -------------------------------------------------------------------------- */  
        //$this->addCol(self::fld_TYPECODE, 'contenu',       self::type_TEXT, 15, true, $label='Contenu');
        //$this->addCol(self::fld_TYPEACTION, 'contenu',     self::type_TEXT, 60, true, $label='Contenu');
        
        //$this->addJoin(self::fld_TYPEID, '`schoolnet`.`Type_Notification`', '`code`', 'INNER JOIN');
        //$this->addJoin(self::fld_VISUECOLE, '`schoolnet`.`Ecole`', '`nom`', 'INNER JOIN');
        //$this->addJoin(self::fld_VISUCLASSE, '`schoolnet`.`Classe`', '`nom`', 'INNER JOIN');
        //$this->addJoin(self::fld_EMETEUR, '`schoolnet`.`Membre`', '`nom`', 'INNER JOIN');
        $this->addJoin(self::fld_ID, Destinataire::TABLENAME, '', 'LEFT JOIN',Destinataire::fld_NOTIFID);
        
     /* Chargement des types de notifications primaires 
        -------------------------------------------------------------------------- */
        $zero = '0';
        $typenotif = new Type_notification($db);
        $this->types =$typenotif->GetAll(true);
        $this->typesprimaires = $typenotif->addConstraint(Type_notification::fld_CHAINTYPE, $zero)
                              ->GetAll(true);
        
     /* Contraintes par défaut : $ecoles, classes, membres
        -------------------------------------------------------------------------- */
        $this->ecoles=$ecoles;
        $this->classes=$classes;
        $this->membres=$membres;
        $this->setFilter(0,0,0);
    }
    
    /**
     * 
     * @param number $perimetre (0: Tous, 1: Public, 2:Privé)
     * @param number $type    (ID de type_notification, 0 pour tous)
     * @param number $membres (ID de Membre, 0 pour tous)
     */
    public function setFilter($perimetre=0, $type=0, $membre=0)
    {  
        $this->addConstraint(self::fld_DESTINATAIRE, (($membre!=0) ? $membre : $this->membres));
        $this->removeClause('filter_type');
        $this->removeClause('tous');
        $this->removeClause('prive');
        $this->removeClause('public');
        
        var_dump($membre);
        var_dump($this->ecoles);
        
        if ($type != 0) $this->addClause('filter_type',self::fld_TYPEID,'=',$type);
        
             
        $clause_prive = $this->Clause($this->Clause(self::fld_VISUPUBLIC,'= 0'),
                                      ' AND ',
                                      $this->Clause(self::fld_DESTINATAIRE, 'IS NOT NULL'));
        
        $clause_ecole = ($membre != 0 
                      ? $this->Clause(self::fld_VISUECOLE, ' = ',  firstItem($this->ecoles[$membre]))
                      : (count($this->ecoles)==1 
                             ? $this->Clause(self::fld_VISUECOLE, ' = ', firstItem($this->ecoles))
                             : $this->Clause(self::fld_VISUECOLE, ' IN ', $this->ecoles)));
        $clause_classe = ($membre != 0 
                       ? $this->Clause(self::fld_VISUCLASSE, ' = ',  firstItem($this->classes[$membre]))
                       : (count($this->classes)==1 
                              ? $this->Clause(self::fld_VISUCLASSE, ' = ', firstItem($this->classes))
                              : $this->Clause(self::fld_VISUCLASSE, ' IN ', $this->classes)));
        $clause_public = $this->Clause($this->Clause(self::fld_VISUPUBLIC,'<> 0'),
                                       ' AND ',
                                       $this->Clause($clause_ecole, ' OR ', $clause_classe));
        
        if ($perimetre==0) $this->addClause('tous', $clause_public, ' OR ', $clause_prive);
        if ($perimetre==1) $this->addClause('public',$clause_public);
        if ($perimetre==2) $this->addClause('prive',$clause_prive);
            
    }

    /**
     * retourne les notifications primaires autorisées
     * @return unknown[]
     */
    public function getPrims()
    {
        $this->addClause('primaire', self::fld_TYPEID, 'IN', $this->typesprimaires);
        $res = $this->GetAll();
        $this->removeClause('primaire');
        return $res;
    }
    
    /**
     * retourne les notifications chainées autorisées
     * @param int $id
     * @return \RayTools\Entity\unknown[]
     */
    public function getChains($id)
    {
        $this->addClause('chains', self::fld_CHAINID, '=', '"'.$id.'"');
        $res= $this->GetAll();
        $this->removeClause('chains');
        return $res;
    }
    
}
