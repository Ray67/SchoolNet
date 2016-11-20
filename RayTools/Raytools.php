<?php


 /* Fonctions sur les tableaux
    -------------------------------------------------------------------------- */

    /**
     * return first array element by recursive way
     * @param unknown $array
     * @return unknown
     */
    function firstItem($array)
    {
        return (is_array($array)
             ? firstItem(current($array))
             : $array);
    }
    
    /**
     * Join first elements of each array rows to a string separated by glue
     * @param string $glue
     * @param unknown $array
     * @return string
     */
    function newImplode($glue, $array):string
    {
        $str='';
        foreach ($array as $key => &$item)
            $str .= (is_array($item) ? firstItem($item) : $item)
                  . $glue;
        return substr($str,0,-strlen($glue));
    }

    function in_array_field($needle, $needle_field, $haystack, $strict = false): bool
    {
        if ($strict) 
        {
            foreach ($haystack as $item)
                if (isset($item[$needle_field]) && $item[$needle_field] === $needle)
                    return true;
        }
        else 
        {
            foreach ($haystack as $item)
                if (isset($item[$needle_field]) && $item[$needle_field] == $needle)
                    return true;
        }
        return false;
    }

 /**
  * extrait un sous tableau (option : avec valeur distincte et indexation des éléments)
  * @param array  $needle : la première valeur donne l'unicité si distinct
  * @param array  $haystack data array
  * @param bool $unique (premier champ unique)
  * @param bool $indexed (indexé sur le premier champ)
  * @return array
  */
    function sub_array($needle, $haystack, $unique=false, $indexed=false):array
    {
        if ((count($haystack)==0) || (count($needle)==0)) return [];    
        $firstneedle = $needle[0];
        $unique = ($indexed ? true : $unique);
    
        $idx = [];
        $target=[];
        foreach ($haystack as $key =>$row)
        {
            $keyUnique = ($unique ? $row[$firstneedle] : $key);
            if (!isset($idx[$keyUnique]))
            {
                $idx[$keyUnique]=$keyUnique;
                $keyTarget = ($indexed ? $keyUnique : $key);
                foreach ($needle as $i => $col)
                    if (isset($row[$col])) 
                        $target[$keyTarget][$col] = $row[$col];
            }
        }
        return $target;
    }


 /* Miscellanious (fourre-tout)
    ------------------------------------------------------------------------------- */
    
    /**
     * 
     * @param unknown $var
     * @param string $title
     * @return boolean true
     */
    function dump($var, $title='')
    {
        echo '<div class="cadre">'.$title.' <pre>';
        var_dump($var);
        echo '</pre></div>';
        return true;
    }

