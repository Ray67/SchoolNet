<?php

include('RayTools\Autoload.php');

use lib\CommonApp;

class Connect extends CommonApp
{
    private 
        $messages,
        $msg;
    
    function __construct()
    {
        parent::__construct();
        
        $this->messages['0'] = 'Merci de vous identifier' ;
        $this->messages['1'] = "l'identification a échouée, veuillez recommencer.";
        $this->messages['2'] = "Vous n'avez pas les droits nécessaires, veuillez vous reconnecter.";
    }
    
    /** 
     * Surcharge la connexion automatique (et plus)
     * {@inheritDoc}
     * @see Application::Connect()
     */
    function Connect($id=0) : bool
    {        
        unset($_SESSION["id"]);
        
        $this->msg = isset($this->Get['err']) && isset($this->messages[$this->Get['err']])
                   ? $this->Get['err']
                   : '0' ;
        return true;
    }
    
    public function OpenSession()
    {
        parent::OpenSession();
        $this->setTitle('SchoolNet  - Connexion');
    }
    
    /**
     * surcharge l'affichage de la connexion
     * 
     * {@inheritDoc}
     * @see CommonApp::Div_Connexion()
     */
    public function Div_Connexion():string
    {
        return '';
    }
    
    /**
     * routage de l'application
     * 
     * {@inheritDoc}
     * @see Application::Body()
     */
    public function page_default():string
    {
        return '<center><p class="erreur">' . $this->messages[$this->msg] . '</p>'
             . '<form method="post" action="' 
             . (isset($this->Get['ReturnURL']) ? $this->Get['ReturnURL'] : 'Schoolnet.php').'">'
			 . 'Identificateur :<br/>'
        	 . '<input id="identifiant" name="identifiant" type="textbox"><br/>'
			 . '<br/>'
             . 'Mot de passe :<br/>'
			 . '<input id="password" name="password" type="password"><br/>'
			 . '<br/>'
			 . '<br/>'
			 . '<center><input type="submit" value="se connecter"></center>'
			 . '</form></center>';
    }
    
}

/*
 * Corps de l'application
 */

$myApp = new Connect();
$myApp->OpenSession();
if ($myApp->Connect()) 
    echo $myApp->AllInTheBox();
