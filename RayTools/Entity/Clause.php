<?php

namespace RayTools\Entity;

require_once 'RayTools\Raytools.php';

class Clause
{
    const
       _EQUAL = '=',
        _DIFFERENT = '<>',
        _IN = 'IN',
        _ISNULL = 'IS NULL',
        _NOT = 'NOT',
        _AND = 'AND',
        _OR  = 'OR';

    private 
        $op1,
        $op2,
        $op3;
    
    private function convert($var) : string
    {
        return ''.(is_array($var) ? '("' . newImplode('","', $var) . '")'
                : $var );
    }
    
    public function __construct($op1, $op2=null, $op3=null)
    {
        $this->op1 = $this->convert($op1);
        $this->op2 = $this->convert($op2);
        $this->op3 = $this->convert($op3);
    }
    
    public function __toString()
    {
        return '(' 
             . $this->op1.' '
             . ($this->op2 !== null ? $this->op2.' ' : '')
             . ($this->op3 !== null ? $this->op3.' ' : '')
             . ')';
    }
    
}