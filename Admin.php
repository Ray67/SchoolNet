<?php


require_once 'RayTools\Autoload.php';

use lib\CommonApp;
use Entities\Classe;
use Entities\Membre;
use Entities\Connexion;

class Admin extends CommonApp
{    
    function OpenSession()
    {
        parent::OpenSession();
         
		$this->setTitle('Schoolnet 2.0 - Admin');
		
     /* Gestion des menus
        --------------------------------------------------------------------------- */  
        $this->AddMenu('administrer droite','Consulter','Schoolnet.php');
    }
    
    public function page_Classe($action):string
    {
        $classe = new Classe($this->db);
        $classe->addConstraint(Classe::fld_ECOLEID,$this->auth->ecoles);
        
        $tableau = [Classe::fld_ID, Classe::fld_NOM];
         
        return $this->page_auto($classe, $tableau);
    }
    
    public function page_Instituteur($action):string
    {
        $Profil_Instit[]=['3'];
        
        $instit = new Membre($this->db);
        var_dump($this->auth->ecoles);
        $instit->addConstraint(Membre::fld_ECOLE,$this->auth->ecoles);
        $instit->addConstraint(Membre::fld_PROFIL,$Profil_Instit);
        
        $tableau = [Membre::fld_ID, 
                    Membre::fld_NOM,
                    Membre::fld_CLASSE ];
        
        return $this->Page_auto($instit, $tableau);
    }
    
    public function page_Eleve($action):string
    {
        $eleve = new Membre($this->db);
        
        if ($this->auth->profil=='2') 
            $eleve->addConstraint(Membre::fld_ECOLE,$this->auth->ecoles);  
        if ($this->auth->profil=='3')
            $eleve->addConstraint(Membre::fld_CLASSE,$this->auth->classes); 
        
        $profil_Eleve[]=['4'];
        $eleve->addConstraint(Membre::fld_PROFIL,$profil_Eleve);
        $eleve->addOrder(Membre::fld_NOMCLASSE,'ASC');
        $eleve->addOrder(Membre::fld_NOM,'ASC');
        
        $tableau = ($action == ''
                 ? [Membre::fld_NOMCLASSE,Membre::fld_ID, Membre::fld_NOM]
                 : null ); 
        $html = $this->Page_auto($eleve, $tableau);
                
        return $html;
    }
    
    public function page_Connexion($action):string
    {
        $connexion = new Connexion($this->db);
        
        $Profil_Eleve[]=['4','Elève'];
        $connexion->addConstraint(Connexion::fld_PROFILID,$Profil_Eleve);
        
        $tableau = [Connexion::fld_ID, 
                    Connexion::fld_NOM,
                    Connexion::fld_PROFILID,
                    Connexion::fld_DTECNX ];
         
        return $this->Page_auto($connexion, $tableau);
    }
        
    function page_Default():string
    {
        function Div_fonction($object,$titre): string
        {
            return '<div class="fonction_' . $object . '">'
                 . '<a HREF="Admin.php?Object=' . $object . '">'
                 . '<h4>' . $titre 
                 . '</h4></a></div>';
        }
        function Div_Directeur():string
        {
            return '<div class="col-xs-12 col-md-12 bloc_fonctions">' 
	    	     . '<h3>Administrer votre établissement</h3>'
	    	     . '<ul>'
	    	     . '<li>'. Div_fonction('Classe','Créer des classes') . '</li>'
	    	     . '<li>'. Div_fonction('Instituteur','Créer des instituteurs') . '</li>'
	    	     . '</ul></div>';
        }
        
        function Div_Instituteur():string
        {
            return '<div class="col-xs-12 col-md-12 bloc_fonctions">' 
	    	     . '<h3>Administrer les élèves de votre classe</h3>'
	    	     . '<ul>'
	    	     . '<li>'. Div_fonction('Eleve','Inscrire des éleves') . '</li>'
	    	     . '<li>'. Div_fonction('Connexion','Créer une connexion') . '</li>'
	    	     . '</ul></div>';
        }
        
        return (($this->auth->profil<=2) ? Div_Directeur()  : '')
             . (($this->auth->profil<=3) ? Div_Instituteur(): '')
             . (($this->auth->profil==4) ? $this->Page_404() : '');
    }
    
}
    
/*
 * Corps de l'application
 */
session_start();
$myApp = new Admin();

if ($myApp->Connect())
{
    $myApp->OpenSession();
    echo ($myApp->AllInTheBox());
}   