<?php

include "lib/SQLTools.php";
include "Entities/Authentification.php";
include "lib/Application.php";
include "archives/Notif.php";

class Chain extends Application
{
    public
    $notif;

    function OpenSession()
    {   
        session_start();
        $this->auth = new Authentification($this->db);
        
        if   (!isset($_SESSION['id']) 
           or !isset($_GET['notif']))
        {
            die(); exit;
        }
        
        /* Verfication de la notification père */
        $this->notif = new Notif($this->db, $this->auth);
        if   (!$this->auth->load($_SESSION["id"]) 
           or ! $this->notif->isValid($_GET['notif']))
        {
            die(); exit;
        }
    }
    
}
        
        
/*
 * Corps de l'application
 */
      

$myApp = new Chain();
$myApp->OpenSession();

die($myApp->notif->Get_Chained($_GET['notif']));

