<?php


function newImplode($glue, $array):string
{
    function firstItem($array)
    {
        return (is_array($array)
                ? firstItem(current($array))
                : $array);
    }
    
    $str='';
    foreach ($array as $key => &$item)
        $str .= (is_array($item) ? firstItem($item) : $item)
              . $glue;
    return substr($str,0,-strlen($glue));
}


$montableau = [
        ['A-1', 'A-2', 'A3'],
        'B1',
        'C1',
        ['D1'],
        ['alpha'=>'E1','beta'=>'E2','Epsilone'=>'E3'],
        null,
        ['G1']
];

echo '**'.newImplode('","',$montableau).'**';
