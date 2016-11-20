<?php

namespace RayTools\Form;

use RayTools\Entity\Entity;
use RayTools\Item\ItemInterface;


class Form implements ItemInterface
{

    private 
        $id,
        $targetURL;
    
    public 
        $classrow,
        $classlabel,
        $classdata,
        $classbutton,
        $labelbutton,
        $rows;
    
    /**
     * Constructor
     * @param string $id (permet de faire coexister plusieurs form sur la meme page)
     * @param string $targetURL (adresse de renvoi du submit)
     * @param string $classrow   (col-xs-12 par defaut)
     * @param string $classlabel  (col-xs-4 par defaut)
     * @param string $classdata   (col-xs-8 par defaut)
     * @param string $classbutton (btn-default par defaut)
     */   
    public function __construct($id='singleform', $targetURL='',  
                                $classrow='col-xs-12', $classlabel='col-xs-4', $classdata='col-xs-8', 
                                $classbutton='btn btn-primary', $labelbutton='Enregistrer')
    {
        $this->id         = $id;
        $this->targetURL  = $targetURL;
        $this->classrow   = $classrow;
        $this->classlabel = $classlabel;
        $this->classdata  = $classdata;
        $this->classbutton= $classbutton;
        $this->labelbutton= $labelbutton;
        
        $this->rows = [];
    }
    
    /**
     * permet de donner/retrouver l'alias d'un élément a partir de son nom 
     * 
     * @param string $name
     * @param string $formid (si provient d'une autre form)
     * @return string
     */
    private function _giveAlias($name, $formid=''):string
    {
        $formid=($formid !='' ? $formid : $this->id );
        return $formid.'_'.$name;
    }
    
    /**
     * Ajoute une ligne de formulaire
     * @param string $name
     * @param string $alias
     * @param string $label
     * @param string $type
     * @param int $size
     * @param bool $null_value
     * @param unknown $value
     * @param array $enum
     */
    public function addRow($name, $label, $type, $size, $null_value, $value=null, $enum=null)
    {
        $this->rows[$name] = new Row($name, $this->_giveAlias($name), $type, $size, $null_value, $label, $value, $enum);
        return $this;
    }
    
    /**
     * Ajoute les définitions de Row à partir des colonnes d'entité
     * @param SQLTools $entity
     * @param unknown $keyarray (tableau de noms de colonne, si null toutes les colonnes)
     * @return Form
     */
    public function addFromEntity(Entity $entity, $keyarray=null)
    {
        if ($keyarray ===null) $keyarray = $entity->getColnames();
    
        foreach ($keyarray as $key => $dbname)
            if (strpos($dbname,'.',0) == null)
            {
                $col = $entity->getCol($dbname);
                $this->addRow($col->alias,
                        $col->label,
                        $col->type,
                        $col->size,
                        $col->null_value,
                        $col->value,
                        $col->constraint);
            }
        return $this;
    }
    
    /**
     * remplit le buffer Entity avec les valeurs de rows
     *
     * @param SQLTools $entity
     * @param unknown $keyarray (tableau de colonnes)
     * @return Entity
     */
    public function fillEntity(Entity $entity, $keyarray=null)
    {
        if ($keyarray ===null) $keyarray = $entity->getColnames();
    
        foreach ($keyarray as $key => $dbname)
            $entity->columns[$dbname]->value = (isset($this->rows[$entity->columns[$dbname]->alias])
                                                    ? $this->rows[$entity->columns[$dbname]->alias]->value
                                                    : null);
        return $entity;
    }
    
    /**
     * remplit les valeurs des row de la form avec un tableau
     *
     * @param unknown $array (tableau de valeurs)
     * @param string $formid (si provient d'une autre form)
     * @return Form
     */
    public function fill($array, $formid='')
    {
        foreach ($this->rows as $name => &$row)
            $row->value = (isset($array[$row->alias])
                    ? $array[$row->alias]
                    : null);
        return $this;
    }
    
    /**
     * vérifie si retour de la form, charge les valeurs (method POST) et les vérifie
     *
     * @return bool
     */
    public function isReloadedAndOk($post):bool
    {
        if (isset($post[$this->_giveAlias('posted')]))
        {
            $res = true;
    
            foreach ($this->rows as $name => &$row) // utilisation de row par reference
            {
                $givenAlias = $row->alias;
    
                $row->value = (isset($post[$givenAlias])
                            ? $post[$givenAlias]
                            : null);
                
                // null value ?
                $row->error =  (
                        ((!$row->null_value)
                                && (($row->value===null) || ($row->value=='')))
                        ? Row::ERROR_NULLVALUE
                        : Row::ERROR_NONE);
    
                // REGEX
//                 if ($row[self::propfrm_error] == self::ERROR_NONE)
//                     $row[self::propfrm_error] = (isset($row[self::propfrm_regex])
//                             && !preg_match($row[self::propfrm_regex], $row[self::prop_value])
//                             ? self::ERROR_NOTINTYPE
//                             : self::ERROR_NONE);
//                     $res = $res && ($row[self::propfrm_error] != self::ERROR_NONE);
            }
            return $res;
        }
        return false;
    }
    
    /**
     * Charge les listes Dropdown
     *
     * @param SQLTools $entity
     */
    public function loadFromEntity(Entity $entity)
    {
        foreach ($this->rows as $name => &$row)  // utilisation par reference
        {
            $col = $entity->getCol($name);
            
            if (isset($entity->joins[$col->name]))
            {
                $tablename = $entity->joins[$col->name]->tablename;
           
                 if (($tablename!= '')
                    && (($array = $entity->getValuesforFK($name,$tablename)) != []))
                $row->constraint = $array;
            }
        }
        return $this;
    }
    
    /**
     * Génère le html de la form
     *
     * @param string $staticmode (pour l'affichage des valeur et non leur saisie)
     * @return string
     */
    public function show($staticmode=false, $beginform=true, $endform=true):string
    {
        $html = ($beginform
              ?'<form method="post" class="form" '
              . 'action ="' . $this->targetURL . '">'
              . '<input type="hidden" name="'.$this->_giveAlias('posted').'" value="true">'
              : '');
    
        foreach ($this->rows as $alias => &$row) // utilisation par reference (rapidité)
        {
            $html .= '<span class="'.$this->classrow.'">'
                   . $row->show($staticmode,$this->classlabel, $this->classdata) 
                   . '</span>';
        }
    
        $html .= ($endform
                     ? '<span class="'.$this->classrow.'">'
                     . '<span class="'.$this->classlabel.'"></span>'
                     . '<span class="'.$this->classdata.'">'
                     . '<input type="submit" value="'. $this->labelbutton.'" class="'.$this->classbutton.'">' // a modifier
                     . '</span>'
                     . '</span></form>'
                     : '');
    
        return $html;
    }


}
