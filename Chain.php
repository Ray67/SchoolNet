<?php


require_once 'RayTools\Autoload.php';

use RayTools\Application\Application;
use Entities\Notification;

class Chain extends Application
{
    public
    $notif;

    function OpenSession()
    {   
        parent::OpenSession();
        
        /* initialisation */
        $this->notif = new Notification($this->db, $this->auth->ecoles, $this->auth->classes, $this->auth->membres);
        $this->notif->setFilter();
    }
    
    
    /* 
     * Retourne un div html specifique de notification pour Comment
     */ 
    private function divComment() : string
    {
        return '<div id="'.$this->notif->getValue(Notification::fld_ID).'" class="notif_chainage">'
             . $this->notif->getValue(Notification::fld_EMETEUR).' a écrit :<BR>'
             . $this->notif->getValue(Notification::fld_CONTENT)
	         . '</div>';    
    }
    
    /* 
     * Retourne un divhtml specifique de notification pour Inscrit
     */ 
    private function divInscrit() : string
    {
        return '<div id="'.$this->notif->getValue(Notification::fld_ID).'" class="notif_chainage popup">'
             . $this->notif->getValue(Notification::fld_EMETEUR).' a répondu<BR>'
             . $this->notif->getValue(Notification::fld_CONTENT)
             . '</div>';
    }
    
    /* 
     * Retourne un divhtml specifique de notification pour Inscrit
     */ 
    private function divChain() : string
    {
        return '<div id="'.$this->notif->getValue(Notification::fld_ID).'" class="notif_chainage popup">'
             . $this->notif->getValue(Notification::fld_EMETEUR).'<BR>'
             . $this->notif->getValue(Notification::fld_CONTENT)
             . '</div>';
    }
    
    /*
     * Route le formatage en fonction du type de div
     */
    public function div() : string
    {
        return (method_exists($this,'div'.$this->notif->getType()) // non trouvé
                ? $this->{'div'.$this->notif->getType()}()
                : $this->divChain());
    }

}
        
        
/*
 * Corps de l'application
 */
      

$myApp = new Chain();
session_start();
if ($myApp->Connect($_SESSION['id']))
{
    $myApp->OpenSession();
    if (($res = $myApp->notif->getChains($_GET['notif'])) != []) 
    {
        $html = '';
        foreach ($res as $key => &$value)
        { 
            $myApp->notif->Fill($value);
            $html .= $myApp->div();
        }
        die($html); exit;
    }
    else die('Erreur de notification ......................');
}
else die('Erreur de connexion ......................');
