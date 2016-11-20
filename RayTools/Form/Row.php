<?php
namespace RayTools\Form;

use RayTools\Item\Item;
use RayTools\Item\ItemInterface;


class Row extends Item implements ItemInterface
{

    const
        ERROR_NONE = 0,
        ERROR_NULLVALUE = 1,
        ERROR_NOTINTYPE =2,
    
        ERROR_MSG = [self::ERROR_NULLVALUE => 'La valeur doit être saisie :',
            self::ERROR_NOTINTYPE => 'Le format de la valeur n\est pas respecté :',
        ];
    
    public
        $error;
    
    public function __construct($name, $alias, $type, $size, $null_value=true, $label='', $value=null, $enum=null)
    {
        parent::__construct($name, $alias, $type, $size, $null_value, ($label!='' ? $label : $name) , $value);
        $this->constraint = $enum;
        $this->error = self::ERROR_NONE;
    }
   
    /**
     * Génére le html d'un input de type text
     * @return string
     */
    private function _textedit():string
    {
        return '<input '
             . 'id="' . $this->alias . '" '
             . 'name="' . $this->alias . '" '
             . 'maxlength="' . $this->size . '" '
             . ($this->null_value ? '' : 'required="required" ')
             . 'type='
             . ($this->type == self::type_PASSWORD
                 ? '"password" size="'.min([$this->size,40]).'" '
                 : ($this->type == self::type_DATE ? '"date" '
                 : ($this->type == self::type_INT  ? '"number" '
                 : 'text" size="'.min([$this->size,40]).'" ')))
             . 'value="'
//              . ($constraint!=null && $constraint!=''
//                 ? $constraint . '" DISABLED '
//                 : ($this->rows[$alias][self::prop_value] != null ? $this->rows[$alias][self::prop_value] . '" ' : '" ' ));
             . ($this->value != null ? $this->value  : '' )
             . '" >';
    }
        
    /**
     * Génére le html d'un input de type textarea
     * @return string
     */
    private function _textarea():string
    {
        return '<textarea '
             . 'id="' . $this->alias . '" '
             . 'name="' . $this->alias. '" '
             . 'maxlength="' . $this->size . '" '
             . ($this->null_value ? '' : 'required="required" ')
             . 'col="100">'
//           attention bug DISABLED à l'extérieur de >
//           . ($constraint!=null && $constraint!=''
//               ? $constraint . '" DISABLED '
//               : ($value != null ? $value . '" ' : '" ' ))
             . ($this->value != null ? $this->value :'' )
            .'</textarea>';
    }
        
    /**
     * Génére le html d'un input de type select
     *
     * @param unknown $alias
     * @return string
     */
    private function _dropdown():string
    {
        if ($this->value === null
                && $this->constraint !== null
                && count($this->constraint)==1)
        {
            $value = firstItem($this->constraint);
            $disable = true;
        } else
        {
            $value = $this->value;
            $disable = false;
        }
        
        $html = '<select '
              . 'id="' . $this->alias . '" '
              . 'name="' . $this->alias. '" '
              . ($this->null_value
                  ? '><option value='
                  . ($this->type== self::type_INT ? '"0"' : 'null')
                  . '></option>'
                  : 'required="required" >');
        foreach($this->constraint as $key => $item)
        {
            $id = current($item);
            $name = next($item); // retourne false si vide
            $html .= '<option value="' . $id .'" '
                   . ($value != null && $value==$id
                       ? 'selected '
                       : '')
                   . '>'
                   . ($name !='' ? $name : $id)
                   .'</option>';
        }
        return $html .'</select>';
    }

    public function show($staticmode, $classlabel, $classdata)
    {
        return '<span class="'.$classlabel
             . ($this->error != self::ERROR_NONE ? ' form_labelerror':'')
             . '">'
             . '<label for"'.$this->alias.'">' . $this->label
             . (!$this->null_value ? '*' : '')
             . '</label>'
             . '</span>'
             . '<span class="'.$classdata.'">'
             . ($staticmode
                 ? $this->value
                 : ($this->constraint !==null
                     ? $this->_dropdown()
                     : ($this->type==self::type_TEXT
                         ? $this->_textarea()
                         : $this->_textedit())))
             . ($this->error != self::ERROR_NONE
                 ? self::ERROR_MSG[$this->error]
                 :'')
             . '</span>';
    }
    
}
