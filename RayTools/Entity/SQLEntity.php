<?php

namespace RayTools\Entity;

use RayTools\Entity\Fields;

require_once 'RayTools/Raytools.php';

class SQLEntity extends Fields
{
    protected
        $db,
        $tablename;
    
    /**
     * Constructeur de l'objet
     *
     * @param unknown $db (objet PDO)
     * @param string  $tablename
     */
    public function __construct($db,$tablename)
    {
        parent::__construct();
            
        $this->db = $db;
        $this->tablename = $tablename;
    }
        
    /**
     * Retourne un segment de requete SQL correspondants aux champs
     * @param unknown $KeyArray liste de champs
     * @return string
     */
    public function SQLFields($KeyArray=null):string
    {
        $sql=' ';
        foreach ($this->columns as $colname =>&$column)
            $sql .= ($KeyArray == null || in_array($colname,$KeyArray)
                    ? (strpos($colname,'.')!==false ? '' : $this->tablename . '.')
                    . $colname
                    . ' as ' . $column->alias .' , '
                    : '');
            return substr($sql,0,-2);
    }

    /**
     *  Retourne un segment de requete SQL correspondants aux contraintes (WHERE)
     * @param string $tablename filtres portant sur la table jointe (Form DropDown)
     * @return string
     */
    public function SQLConstraints($tablename=''):string
    {
        $sql='';
        foreach ($this->columns as $dbname => &$column)
            if ($column->constraint !== null)
            {
                $ok = false;
                if (($tablename =='') && (strpos($dbname,'.')===false)) // contrainte sur table principale
                {
                    $newkey = ($this->tablename . '.' . $dbname) ;
                    $ok = true;
                }
                if (!($tablename ==''))
                {
                    if (isset($this->joins[$dbname]) && $this->joins[$dbname]->tablealias == $tablename) // jointure
                    {
                        $newkey = $tablename . '.' . $this->joins[$dbname]->colname;
                        $ok = true;
                    }
                    if (strpos($dbname,$tablename)!==false) // champ externe
                    {
                        $newkey = $dbname;
                        $ok = true;
                    }
                }
    
                if ($ok)
                {
                    $sql .= $newkey
                    . (is_array($column->constraint)
                            ? ' IN ("'. newImplode('","', $column->constraint) . '")'
                            : ' = "' . $column->constraint . '"')
                            . ' AND ' ;
                }
            }
        return ($sql !='' ? substr($sql,0,-4) : '');
    }
    
    /**
     *  Retourne un segment de requete SQL correspondants aux jointures et leur contrainte
     * @return string
     */
    public function SQLJoins():string
    {
        $sql='';
        foreach ($this->joins as $colname => &$join)
        {
            $constraint = $this->SQLConstraints($join->tablealias);
    
            $sql .= $join->type .' '
                  . $join->tablename
                  . ($join->tablealias != $join->tablename ? ' AS ' . $join->tablealias : '')
                  . ' ON '
                  . (strpos($colname,'.') ? '' : $this->tablename . '.') . $colname . '='
                  . $join->tablealias . '.' . $join->colname
                  . ($constraint != '' ? ' AND '.$constraint : '');
        }
        return $sql;
    }
    
    /**
     *  Retourne un segment de requete SQL correspondants aux clauses (WHERE)
     * @return string
     */
    public function SQLClauses():string
    {
        $sql='';
        foreach ($this->clauses as $key => $clause)
            $sql .= $clause
                  . ' AND ' ;
    
        return ($sql !=='' ? substr($sql,0,-4) : '');
    }
    
    /**
     * Retourne un segment de requete SQL correspondants à ORDER BY
     *
     * @return string
     */
    public function SQLOrder():string
    {
        if (count($this->orders)==0) return '';
    
        $sql=' ORDER BY ';
        foreach ($this->orders as $key =>$order)
            $sql .= $order['colname'] .' '. $order['direction'] .', ';
    
            return substr($sql,0,-2);
    }
    
    /**
     * Crée la requete SQL en appelant les segments SQL
     * @param unknown $KeyArray
     * @return string
     */
    public function SQLSelect($KeyArray=null):string
    {
        $constraint = $this->SQLConstraints();
        $clause = $this->SQLClauses();
    
        return 'SELECT '
                . $this->SQLFields($KeyArray)
                . ' FROM '
                . $this->tablename . ' '
                . $this->SQLJoins()
                . ($constraint!='' || $clause !='' ? ' WHERE ' : '')
                . $constraint
                . ($constraint!='' && $clause !='' ? ' AND ' : '')
                . $clause
                . $this->SQLOrder();
    }
    
    /**
     * Retourne a requete SQL INSERT correspondants au buffer
     * @return string
     */
    public function SQLInsert($KeyArray=null):string
    {
        If ($KeyArray==[]) $KeyArray = $this->getColnames();
    
        $listfld = ' (';
        $listval = ') VALUES (';
    
        foreach ($KeyArray as $key => $dbname)
            if (strpos($dbname,'.')===false)
            {
                $column = $this->columns[$dbname];
                $listfld .= $dbname .', ';
                $listval .= ($column->value===null ? 'NULL, ' : '"' . $column->value .'", ');
            }
        return substr($listfld,0,-2) . substr($listval,0,-2) . ')';
    }
    
    /**
     * Retourne la requete SQL UPDATE correspondant au buffer
     * @return string
     */
    public function SQLUpdate($KeyArray=null):string
    {
        If ($KeyArray==[]) $KeyArray = $this->getColnames();
    
        $sql=' SET ';
        foreach ($KeyArray as $key => $dbname)
            if (strpos($dbname,'.')===false)
            {
                $value = $this->columns[$dbname]->value;
                $sql .= $dbname . '='
                        . ($value===null ? 'NULL, ' : '"' . $value .'", ');
            }
        return substr($sql,0,-2);
    }
    
}
