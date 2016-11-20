<?php

/*
 * Application : Permet de gérer les Applications
 *      - Connexion via Authentification
 *      - Routage de pages en fonction de Objet et Action (methode HTTP-GET)
			- vers {prefix}_Main si Object n'est pas défini dans l'URL
			- vers {prefix}_{Object} si la méthode est définie avec le parametre Action
			- vers {prefix}_404  si la méthode {prefix}_{Object} n'existe pas
 */

namespace RayTools\Application;

use PDO;
use RayTools\Raytools;
use RayTools\Application\Config;
use Entities\Authentification;

abstract class Application 
{
    Protected
		$title,
		$Appname,
        $Objet,
        $Action;
    
    public
		$Post,
		$Get,
        $auth,
        $db;
        
    /**
     * Initialise $db, $title, AppName
     * @param string $hostname
     * @param string $username
     * @param string $dbname
     */
    public function __construct()
    {
        try
        {
            $config = new Config();
            $config->get('config.dev');
            $this->db = new PDO('mysql:host='.$config->hostname.';dbname='.$config->dbname,
                                $config->username,
                                $config->password,
                                array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_WARNING));
        } catch (PDOException $err) {
            echo 'Erreur ! '. $err->getMessage() . '<br/>';
            die();
        }
        $this->Appname = get_class($this);
        $this->title ='Application from Ray tools';
        $this->auth = new Authentification($this->db);    
    }
	
    /**
     * Attribut un titre à l'application
     * 
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
    
    /**
     * Active ou Ré-active la session et sécurise $_GET et $_Post
     */
    public function OpenSession()
    {
        session_start();
        
     /* Gestion des $Get 
        --------------------------------------------------------------------------- */
    	$this->Post = [];
        foreach($_POST as $key => $value)
            $this->Post[$key] = filter_input(INPUT_POST,$key,FILTER_SANITIZE_MAGIC_QUOTES);

        $this->Get = [];
        foreach($_GET as $key => $value)
            $this->Get[$key] = filter_input(INPUT_GET,$key,FILTER_SANITIZE_MAGIC_QUOTES);
		      
     /* Gestion du routage
        --------------------------------------------------------------------------- */
        $this->Objet =  (isset($this->Get['Object']) ? $this->Get['Object'] : '');
        $this->Action = (isset($this->Get['Action']) ? $this->Get['Action'] : '');
    }
    
    /**
     * retourne vrai si il y a une connexion et charge auth 
     * @return bool
     */
    public function Connect($id=0):bool
    {
        $res =$this->auth->Load($id);
        return $res;
    }
    
    /**
     * Encapsule le router de l'application
     */
    public function AllInTheBox($prefix='')
    {
        if ($prefix=='') $prefix='route';
    
        return $this->route($prefix, $this->Objet, $this->Action);
    }
 
    /* Routage de l'application
     -------------------------------------------------------------------------- */
    
    /**
     * Constitue la route 404 
     */
    protected function route_404() {}
    
    /**
     * Constitue la route par défaut :sans "?Object=" dans l'URL 
     */
    protected function route_Default() {}
    
    /**
     * Route vers {prefix}_{Object}
     *         ou {prefix}_404  si la méthode {prefix}_{Object} n'existe pas
     * @param string $prefixe
     * @param string $objet
     * @param string $action
     */
    final private function route($prefixe, $objet, $action)
    {
        return ($objet ==''
                ? (method_exists($this,$prefixe.'_Default')
                        ? $this->{$prefixe.'_Default'}()
                        : (method_exists($this,$prefixe.'_404')
                                ? $this->{$prefixe.'_404'}()
                                : null))
                                : (method_exists($this, $prefixe.'_'. $objet)
                                ? $this->{$prefixe.'_'. $objet}($action)
                                : (method_exists($this,$prefixe.'_404')
                                        ? $this->{$prefixe.'_404'}()
                                        : null)));
    }
    
}
