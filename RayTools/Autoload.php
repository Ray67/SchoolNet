<?php


class Autoloader
{
    static function loader($classname)
    {
        $fileName = str_replace('\\','/',$classname) . '.php';
        if (file_exists($fileName))
        {
            require_once ($fileName);
            if (class_exists($classname)) return true;
        }
        return false;
    }
}
spl_autoload_register('Autoloader::loader',false);


