<?php

namespace RayTools\Entity;


class Join
{
    public 
    $type,
    $tablename,
    $tablealias,
    $colname,
    $label;
    
    public function __construct($tablename, $label=null, $type='INNER JOIN', $colname='id',  $tablealias=null)
    {
        $this->type = $type;
        $this->tablename = $tablename;
        $this->tablealias = ($tablealias===null || $tablealias==='' ? $tablename : $tablealias);
        $this->colname = $colname;
        $this->label = $label;
    }
    
    public function SQL($dbname=''):string
    {
        return $this->type .' '
             . $this->tablename
             . ($this->tablealias != $this->tablename ? ' AS ' . $this->tablealias : '')
             . ' ON '
             . $this->tablealias . '.' . $this->colname
             . '=' 
             . $dbname ;
    }
}