<?php

namespace RayTools\Entity;

use RayTools\Item\Item;
use RayTools\Entity\Clause;
use RayTools\Entity\Join;

abstract class Fields
{
    protected
        $fld_PrimaryKey,
        
        $joins,
        $clauses,
        $aliases,   /* Table d'indexation par alias */
        $orders;
    
    public
        $columns;

    /**
     * Constructeur de l'objet
     */    
    public function __construct()
    {
        $this->fld_PrimaryKey = null;
        
        $this->fields  = [];
        $this->joins   = [];
        $this->clauses = [];
        $this->aliases = [];
        $this->orders  = [];
    }
   
    /**
     * Ajoute une colonne à la description de l'entité
     *
     * @param string $dbname
     * @param string $alias alias de champs
     * @param string $type
     * @param string $size
     * @param string $null  valeur null acceptée
     * @param string $label label apparaissant dans Table et Form
     * @return \RayTools\Entity\Entity
     */
    public function addCol($dbname, $alias, $type,  $size, $null_value=true, $label='')
    {
        if ($this->fld_PrimaryKey===null) $this->fld_PrimaryKey = $dbname;
    
        $this->columns[$dbname] = new Item($dbname, $alias, $type, $size, $null_value, $label, null);
        
        $this->aliases[$alias] = $dbname;
        
        return $this;
    }

    /**
     * retourne un tableau des noms de colonnes
     * @return unknown[]
     */
    public function getColnames()
    {
        $res = [];
        foreach($this->columns as $colname => $col) $res[] = $colname;
        return $res;
    }
    
    /**
     * retourne le nom de la colonne en table
     * @param unknown $idx (int, alias ou colname)
     * @return NULL|unknown
     */
    private function _getDbname($idx)
    {
        if (isset($this->columns[$idx] )) return $idx;
        elseif (isset($this->aliases[$idx])) return $this->aliases[$idx];
        elseif (is_int($idx))
        {
            if ($idx <0 || $idx >= count($this->columns)-1) return null;
    
            $j=0;
            foreach ($this->columns as $dbname => &$value)
            {
                if ($j===$idx) return $dbname;
                $j++;
            }
        }
        else return null;
    }
    
    /**
     * retourne la valeur d'une colonne
     * @param unknown $idx (int, alias ou dbname)
     * @return NULL|mixed
     */
    public function getValue($idx)
    {
        return (($dbname = $this->_getDbname($idx))!=null
                ?  $this->columns[$dbname]->value
                : null);
    }
    
    /**
     * 
     * @param unknown $idx (int, alias ou dbname)
     * @param unknown $value
     */
    public function setValue($idx,  $value) 
    {
        if (($dbname = $this->_getDbname($idx))!=null)
            $this->columns[$dbname]->value = $value;
    }
    
    /**
     * retourne l'alias d'une colonne
     * @param unknown $idx (int, alias ou dbname)
     * @return string
     */
    public function getAlias($idx):string
    { 
        return (($dbname = $this->_getDbname($idx))!=null
                ?  $this->columns[$dbname]->alias
                : null);
    }
        
    /**
     * retourne la colonne spécifiée
     * @param unknown $idx (int, alias ou colname)
     * @return Item or NULL
     */
    public function getCol($idx)
    {
        return (($dbname = $this->_getDbname($idx))!=null
                ?  $this->columns[$dbname]
                : null);
    }
    
    /**
     * Ajoute une jointure à une colonne 
     * @param string $dbname
     * @param string $jointable
     * @param string $fldlabel
     * @param string $type
     * @param string $joindbname
     * @param unknown $tablealias
     * @return \RayTools\Entity\Entity
     */
    public function addJoin($dbname, $jointable, $fldlabel, $type='INNER JOIN', $joindbname='`id`',$tablealias=null)
    {
        $this->joins[$dbname] = new Join($jointable, $fldlabel, $type, $joindbname,  $tablealias=null);
        return $this;
    }
    
    /**
     * Ajoute une contrainte à un champs (string ou array)
     * @param string $dbname
     * @param unknown $value
     * @return \RayTools\Entity\Entity
     */
    public function addConstraint($dbname, &$value)
    {
        $this->columns[$dbname]->constraint = $value;
        return $this;
    }
    
    /**
     * Retire une contrainte
     * @param string $dbname
     * @return \RayTools\Entity\Entity
     */
    public function removeConstraint($dbname)
    {
        $this->columns[$dbname]->constraint = null;
        return $this;
    }
    
    /**
     * Retourne True si il existe des contraintes sur la table principale (defaut) ou jointe
     *
     * @return bool
     */
    public function existConstraints($tablename=''):bool
    {
        foreach ($this->columns as $colname => &$column)
            if ($column->constraint !== null) 
                return ( ($tablename=='') ||
                     (isset($this->joins[$colname]) && ($this->joins[$colname]->tablename==$tablename)));
    
        return false;
    }
    
    /**
     * instancie une clause
     * les valeurs string doivent être passées entre double-cotes
     * 
     * @param unknown $op1 (Clause, Array, valeurs scalaires)
     * @param unknown $op2 (Clause, Array, valeurs scalaires)
     * @param unknown $op3 (Clause, Array, valeurs scalaires)
     * @return Clause
     */
    public function Clause($op1, $op2=null, $op3=null):Clause
    {
        return new Clause($op1, $op2, $op3);
    }
    
    /**
     * instancie une clause et la nomme
     * les valeurs string doivent être passées entre double-cotes
     * 
     * @param unknown $name Nom de la clause
     * @param unknown $op1 (Clause, Array, valeurs scalaires)
     * @param unknown $op2 (Clause, Array, valeurs scalaires)
     * @param unknown $op3 (Clause, Array, valeurs scalaires)
     * @return \RayTools\Entity\Entity
     */
    public function addClause($name, $op1, $op2=null, $op3=null)
    {
        $this->clauses[$name] = $this->Clause($op1, $op2, $op3);
        return $this;
    }
    
    /**
     * retire une clause nommée
     * @param unknown $name
     * @return \RayTools\Entity\Entity
     */
    public function removeClause($name)
    {
        unset($this->clauses[$name]);
        return $this;
    }
    
    /**
     * ajoute un ordre à la requete SQL
     * @param string $name
     * @param string $direction
     * @return \RayTools\Entity\Entity
     */
    public function addOrder($name,$direction)
    {
        $this->orders[] = ['colname' => $name, 'direction' => $direction];
        return $this;
    }

    /**
     * retire tous les ordres
     * @return \RayTools\Entity\Entity
     */
    public function removeOrders()
    {
        $this->orders=[];
        return $this;
    }

}
