<?php

namespace RayTools\Item;

class Item
{
    public 
        $name,    // en as t-on encore besoin ???
        $alias, 
        $type,
        $size,
        $null_value,
        $label,
        $value,
        $constraint;
    
    public function __construct($name, $alias, $type, $size, $null_value=true, $label='', $value=null)
    {
        $this->name = $name;
        $this->alias = $alias;
        $this->type = $type;
        $this->size = $size;
        $this->null_value = $null_value;
        $this->value = $value;
        $this->label = ($label=='' ? $alias : $label);
        $this->constraint = null;
    }
    
    public function __toString(): string
    {
        return (is_array($this->value) ? '("' . implode('","', $this->value)
             : (is_string($this->value) ? '"' . $this->value . '"'
             : $this->value ));
    }
    
}