<?php

namespace lib;

use RayTools\Application\WebApplication;
use RayTools\Form\Form;
use RayTools\Entity\Entity;



/*
 * CommonApp : Permet de factoriser l'ensemble du code partagé entre
 *             chacune des applications (Schoolnet, Admin et Connect)
 *              - Charte graphique
 *              - Connexion
 */

abstract class CommonApp extends WebApplication
{
    
 /* Surcharges des méthodes du parent
    -------------------------------------------------------------------------- */
        
    /**
     * Constructeur définit la connexion à la BDD
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * opère l'authentification 
     */
    public function Connect($id=0):bool
    {
        $res = false;

        if (isset($_SESSION["id"]))
        {
            if (!($res = parent::Connect($_SESSION["id"]))) 
                header('location:Connect.php?err=1&ReturnURL='.get_class($this).'.php');
        } 
        elseif (isset($_POST["identifiant"]) && isset($_POST["password"]))
        {
            if (!($res = $this->auth->connect($_POST["identifiant"], $_POST["password"])))
                header('location:Connect.php?err=1&ReturnURL='.get_class($this).'.php');
        } 
        else  header('location:Connect.php?err=0&ReturnURL='.get_class($this).'.php');

        return $res;
    }

    /**
     *  Activation de la session et chargement des css et scripts
     */
    public function OpenSession()
    {
        parent::OpenSession();
        
     /* Chargement des CSS et  scripts JS
        --------------------------------------------------------------------------- */
        $this->addLinkCSS('bootstrap/css/bootstrap.css');
        $this->addLinkCSS('css/schoolnet.css'); 
        $this->addLinkCSS('css/schoolnet.css','media="screen"');
        $this->addLinkCSS('css/schoolnet768.css','media="screen and (max-width: 768px)"');
        $this->addLinkCSS('css/schoolnet1080.css','media="screen and (min-width: 768px)"');
        $this->addLinkCSS('cleditor/jquery.cleditor.css');

        $this->addScriptJS('https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js');
        $this->addScriptJS('bootstrap/js/bootstrap.js','DEFER="true"');
        $this->addScriptJS('cleditor/jquery.cleditor.min.js','DEFER="true"');
        $this->addScriptJS('js/schoolnet.js', 'DEFER="true"');
    }
    
    /* Formatage des pages web
     -------------------------------------------------------------------------- */
    
    /**
     * Header de la page
     * @return string Header de la page en HTML
     */
    public function Header():string
    {
        return '<div class="container col-md-12">'
             . '<div class="col-md-2"></div>'
             . '<div class="col-md-8 corps">'
             . '<header><div class="row">'
             . (count($this->menu)>0
                ? '<div class="menu gauche style768">'
                    . '<a href="" ONCLICK="javascript:$('."'#list_menu768'".').toggle(); return false;">menu</a>'
                    . '</div>'
                : '')
             . '<div  class="gauche style1080"><center>'
             . '<img src ="icones/schoolnet1.jpg" width=80px />'
             . '<br/>Réseau social privé'
             . '</center></div>'
             . $this->Div_Connexion()
             . $this->Nav()
             . '</header>'
             . ($this->Objet !='' 
             ? '<section>'
             . '<div class="col-xs-12 col-md-12 bloc_fonctions">'
             . '<h3>Page ' . $this->Objet . '</h3>'
             . '</div>'
             : '');
    }
    
    /**
     * Composant de Header (connexion)
     * @return string Bouton connexion en HTML
     */
    public function Div_Connexion():string
    {
        $ReturnURL= '&ReturnURL='.get_class($this).'.php';
        return '<div class="connexion droite"><a href="Connect.php?'.$ReturnURL.'">'
                . 'SchoolNet - <b>' . $this->auth->nom . '</b>'
                        .'</a></div>'
                                . '</div>';
    }
    
    /**
     * Composant de Header (menus)
     * @return string MENU de la page en HTML
     */
    public function Nav():string
    {
        function div_Item ($menuitem, $AddedClass):string
        {
            return '<div id="menu_'.$menuitem['id']
            . '" class="' . $AddedClass . ' icone_'.$menuitem['id'].' menuitem">'
                    . '<a href="'.$menuitem['target'].'">' . $menuitem['text'] . '</a>'
                            . '</div>';
        }
    
        $largeur = count($this->menu)>3 ? 'col-sm-3 col-md-3' : 'col-sm-4 col-md-4';
    
        $items='';
        foreach ($this->menu as $key => $menuitem)
        {
            $items  .= div_Item($menuitem, $largeur);
        }
    
        return (count($this->menu)==0
                ? ''
                : '<nav>'
                . '<div id="list_menu" class="row list_menuitems style1080">'
                . '<div class="col-sm-2  col-md-4"></div>'
                . '<div class="col-sm-10 col-md-8">' . $items .'</div>'
                . '</div>'
                . '<div class="row">'
                . '<div id="list_menu768" class="col-xs-12 list_menuitems style768 popup">'
                . $items
                . '</div>'
                . '</div>'
                . '</nav>');
    }
    
    /**
     * Footer de la page
     * @return string Footer de la page en HTML
     */
    public function Footer():string
    {
        return ($this->Objet !='' 
             ? '<br/><br/><a HREF="' . $this->returnURL
             . '">Page précédente ...</a>'
             . '</section>'
             : '')
             . '<footer class="row">'
             . '<div>Copyright 2016, Raymond SOUTO --- Projet PHP</div>'
             . '</footer>'
             .'</div>'
             . '<div class="col-md-2"></div>'
             . '</div>' ;
    }
    
    /* Préparation des pages route
     -------------------------------------------------------------------------- */
    
    abstract function page_default():string;
    
    final protected function page_404()
    {
        return 'page_404';
    }
    
    /**
     * Page automatique pour les actions : Delete, Edit, Save et (défaut d'action)
     *     Delete : appelle $Entite->Delete()
     *     Edit   : affiche un formulaire automatique
     *     Save   : appelle la méthode $Entité->Save()
     *     défaut   affiche un tableau automatique avec actions (Edit, Delete)
     * 
     * @param Entity $entity
     * @param tring $action
     * @param array $tableau tableau de champs à manipuler
     * 
     * @return string
     */
    protected function page_auto (Entity $entity, $tableau):string
    {
        $errmsg = 'Erreur';
        $this->currentURL = get_class($this). '.php'
                          . '?Object=' . $this->Objet;
        $this->returnURL  = get_class($this). '.php';
    
        $html = '';
        switch ($this->Action) 
        {
            case 'Delete':
                $this->ReturnURL  = $this->currentURL;
                if (isset($this->Get['id']))
                    $html = ($entity->Delete($this->Get['id'])
                            ? 'Suppression effectuée'
                            : $errmsg);
                break;
                
            case 'Edit':
                $this->returnURL  = $this->currentURL;
    
                $resLoad = (isset($this->Get['id']) 
                            ? $entity->Load($this->Get['id']) 
                            : $entity->Clear());
                
                If ($resLoad)
                {
                    $form = new Form($this->Objet);
                    $form->addFromEntity($entity);
                    
                    $html = ($form->isReloadedAndOk($this->Post) 
                                ? ($form->fillEntity($entity)->Save() 
                                    ? 'Enregistrement effectué' 
                                    : $errmsg )
                                : $form->loadFromEntity($entity)->show());
                }    
                else $html = $errmsg ;
                break;
    
            default:
                $html = $this->tableCommand($this->currentURL.'&Action=Edit','Ajouter au tableau','btn btn-primary')
                      . '<br/><br/>'
                      . $this->Table(null, $entity, $tableau)
                      . '<br/><br/>';
                break;
        }
        return $html;
    }
    
}
