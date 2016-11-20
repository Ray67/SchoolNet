<?php

namespace RayTools\Entity;

use PDO;
use RayTools\Entity\SQLEntity;
use RayTools\Item\ItemInterface;

/**
 * Entity : Permet de gérer des Entités-relations
 * 
 * Utilisation : Cet objet pointe sur une table principale (constructeur)
 *      1) le nom de tables est préfixé par la base de donnée
 *      2) Sur toutes les tables la clé primaire est la premiere colonne ajoutée 
 *         et est appelée "id"
 *      3) les champs de la table principale sont notés sans préfixe
 *         les champs externes sont préfixés par le nom de la table
 *         les champs de jointure (fk) on utilise le nom de la table principale
 *      
 * @author Raymond SOUTO
 */
class Entity extends SQLEntity implements ItemInterface
{
    public 
        $debug;
    
    /**
     * Constructeur de l'objet
     *
     * @param unknown $db (objet PDO)
     * @param unknown $tablename
     */
    public function __construct($db,$tablename)
    {
        parent::__construct($db, $tablename);
        $this->debug = false;
    }
    
    /**
     * Supprime l'enregistrement spécifié par $keyvalue ou, à défaut, le courant
     * @param unknown $keyvalue
     * @return bool
     */
    public function Delete($keyvalue=null) : bool
    {
        if (($keyvalue==null) || ($keyvalue==0))
            $keyvalue = $this->$this->columns[fld_PrimaryKey]->value;
        if ($keyvalue==null) return false;
    
        $sql = 'DELETE FROM ' . $this->tablename
             . ' WHERE ' . $this->tablename . '.'
             . $this->fld_PrimaryKey
             . ' = "' . $keyvalue . '";';
        
        if ($this->debug) dump($sql, $this->tablename . ' : ');
        try {
            $requete = $this->db->prepare($sql);
            $requete->execute();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Enregistre (Insert ou Update) l'enregistrement courant
     * Insert si id==null
     * 
     * @param unknown $keyarray (tableau de colonnes)
     * @return bool
     */
    public function Save($keyarray=null) : bool
    {
        $id = $this->columns[$this->fld_PrimaryKey]->value;
        $newrec= (($id==null) || ($id==0));
        
        $sql = ($newrec ? 'INSERT INTO ' . $this->tablename
                . $this->SQLInsert($keyarray)
    
                : 'UPDATE '. $this->tablename
                . $this->SQLUpdate($keyarray)
                . ' WHERE '
                . $this->tablename . '.' . $this->fld_PrimaryKey
                . ' = "' . $id . '"') . ';';
        
        if ($this->debug) dump($sql, $this->tablename . ' : ');
        try 
        {
            $requete = $this->db->prepare($sql);
            $requete->execute();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Charge l'enregistrement spécifié par $id ou, à défaut, le courant (re-load)
     * @param unknown $keyvalue (valeur de l'index primaire)
     * @return bool
     */
    public function Load($keyvalue) : bool          // Get
    {
        if (($keyvalue==null) || ($keyvalue==0))
            $keyvalue = $this->columns[$this->fld_PrimaryKey]->value;
        if (($keyvalue==null) || ($keyvalue==0)) return false;
        
        $this->addClause('clause_id',$this->tablename . '.' . $this->fld_PrimaryKey, '=', $keyvalue);
        $sql = $this->SQLSelect();
        $this->removeClause('clause_id');
        
        if ($this->debug) dump($sql, $this->tablename . ' : ');
        try 
        {
            $requete = $this->db->prepare($sql);
            $requete->execute();
            $SQLResult = $requete->fetchAll(PDO::FETCH_ASSOC);
    
            if (count($SQLResult) != 1) return false;
               $this->Fill($SQLResult[0]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Renvoie une liste d'enregistrements à partir de $sql ou description entité (Contraintes et Clauses)
     * 
     * @param string $indexed les enreg. sont indexés par la première colonne
     * @param unknown $keyarray filtre sur les champs (si $sql='')
     * @param string $sql
     * @return unknown[] liste d'enregistrements
     */
    public function GetAll($indexed=false, $keyarray=null, $sql='')
    {
        if ($sql=='') $sql= $this->SQLSelect($keyarray) . ';';
    
        if ($indexed) $dbname_id = $this->columns[$this->fld_PrimaryKey]->alias;
    
        $res=[];
        
        if ($this->debug) dump($sql, $this->tablename . ' : ');
        try
        {
            $requete = $this->db->prepare($sql);
            $requete->execute();
            $SQLResult = $requete->fetchAll(PDO::FETCH_ASSOC);
            if (count($SQLResult) > 0) 
            {
                if (!$indexed) $res = $SQLResult;
                else foreach ($SQLResult as $key => &$row)
                         $res[$row[$dbname_id]] = $row;
            }    
            return $res;
    
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * efface le buffer
     *
     * @return bool (true)
     */
    public function Clear():bool
    {
        foreach ($this->columns as $name => &$column) 
            $column->value = null;
            return true;
    }
    
    /**
     * Remplit le buffer à partir d'un tableau (retour PDO, $_POST, ...)
     * @param unknown $array
     */
    public function Fill($array)
    {
        foreach ($this->columns as $dbname =>&$column) 
            $column->value = (isset($array[$column->alias]) 
                               ? $array[$column->alias] 
                               : null);
    }
    
}