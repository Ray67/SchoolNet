<?php

require_once 'RayTools\Autoload.php';

use lib\CommonApp;
use RayTools\Form\Form;

use Entities\Contact;
use Entities\Notification;
use Entities\Connexion;


class Schoolnet extends CommonApp
{
    private 
        $notif,
        $filter;
    
    public function OpenSession()
    {
        parent::OpenSession();
       
 		$this->setTitle('Schoolnet 2.0');
 
 /* Gestion des menus
        --------------------------------------------------------------------------- */  
        $this->AddMenu('profil','Profil','Schoolnet.php?Object=Profil&Action=Edit');
        $this->AddMenu('notifier','Message','Schoolnet.php?Object=Message&Action=New');
        $this->AddMenu('contacter','Contact','Schoolnet.php?Object=Contact');
        if  ($this->auth->profil<4)
            $this->AddMenu('administrer','Admin','Admin.php');
        
     /* Gestion des filtres
        --------------------------------------------------------------------------- */  
        $this->filter = ['filter_perimetre'   =>(isset($this->Post['filter_perimetre'])
                                                ? $this->Post['filter_perimetre'] : 0), 
                         'filter_type'        =>(isset($this->Post['filter_type'])
                                                ? $this->Post['filter_type'] : 0), 
                         'filter_destinataire'=> (isset($this->Post['filter_destinataire'])
                                                ? $this->Post['filter_destinataire'] : 0)];
        
     /* Gestion des notifications
        --------------------------------------------------------------------------- */   
        $this->notif  = new Notification($this->db, 
                                         $this->auth->ecoles, 
                                         $this->auth->classes, 
                                         $this->auth->membres);
    }
    
    /**
     * Affiche le formulaire de filtre sur les notifs
     * @return string
     */
    function div_Filter()
    {
        $perimetre=[];
        $perimetre[]=['id'=>'1', 'lib'=>'Public'];
        $perimetre[]=['id'=>'2', 'lib'=>'Privé'];
        
        $filter = (new Form('filter','','','filter','filter','btn-xs btn-primary','Filtrer'))
                ->addRow('perimetre', '', Form::type_INT, 12, true, 0, $perimetre)
                ->addRow('type','',Form::type_INT, 12, true, 0,$this->notif->typesprimaires)
                ->addRow('destinataire','Pour : ',Form::type_INT, 12, true, 0, $this->auth->membres);
                
        $filter->isReloadedAndOk($this->Post); // pour recharger les filtres dans le formulaire avant l'affichage
        
        return '<div class="filtre">'
             . $filter->show()
             . '</div>';
    }

    /**
     * affiche une notification
     * @return string
     */
    function div_Notif()
    {
        $id           = $this->notif->getValue(Notification::fld_ID);
        $type         = $this->notif->getValue(Notification::fld_TYPEID);
        $titre        = $this->notif->getValue(Notification::fld_TITRE);
        $contenu      = $this->notif->getValue(Notification::fld_CONTENT);
        $expediteur   = $this->notif->getValue(Notification::fld_EMETEUR);
        $emeteur_id   = $this->notif->getValue(Notification::fld_EMETEUR);
        $datecreation = $this->notif->getValue(Notification::fld_CREATION);
        
        $action       = $this->notif->types[$type]['Action'];
        $objets       = $this->notif->types[$type]['Objets'];
        
        return '<article id="' . $id . '">'
                . '<div class="col-md-12 notification '. $type . '">'
                  . '<div id="titre" class="icone_'. $type .' titre">'
                    . '<h1>'.$titre .'</h1>'
                    . 'par <font class="expediteur">' . $expediteur
                    . '</font>, le ' . substr($datecreation,0,10)
                  . '</div><div id="contenu" class="'. $type .' contenu">'
                    . $contenu
                  . '</div>'
                  . '<div id="pied" class="row pied">'
	          
                  /* bouton Modifier ses propres notifications */
                    . (isset($this->user->membres[$emeteur_id]) 
                        ? ' <button onclick="">Modifier</button> '
                        . ' <button onclick="">Supprimer</button> '
                        :'')
                 /* Actions particulières sur les notifs */
                    . ' <button onclick="javascript:return false;">' . $action . '</button> '
                    . '<a href="javascript:{toggle_Chains('.$id.'); }"> '
                            
                    . 'Voir / Cacher les ' . $objets.'</a>'
	          
                 /* enfin le div pour voir les chainages */
                  .	'</div><div id="chain'.$id.'" class="popup">AAA</div>'
                . '</div></article>';
    }
    
    //SPEC rajouter un tableau avec votre identité (vos identités)
    function page_Profil($action):string
    {
        $errmsg = 'Erreur';
        $this->CurrentURL = get_class($this). '.php'
                          . '?Object=' . $this->Objet;
        $this->ReturnURL  = get_class($this). '.php';
        
        $entity = new Connexion($this->db);
        $entity->addConstraint(Connexion::fld_PROFILID, $this->auth->profil);
        
        $html = '';                 
        switch ($action) {
            case 'Edit':
                $entity->Load($_SESSION['id']);
                
                $form = new Form($this->Objet);
                $form->addFromEntity($entity);
                    
                $html = ($form->isReloadedAndOk($this->Post) 
                            ? ($form->fillEntity($entity)->Save() 
                                ? 'Enregistrement effectué' 
                                : $errmsg )
                            : $form->loadFromEntity($entity)->show());
                    $html .='<br/>';
            break;
        }
        return $html;
    }
    
    function page_Message($action):string
    {
        $errmsg = 'Erreur';
        $this->CurrentURL = get_class($this). '.php'
                . '?Object=' . $this->Objet;
        $this->ReturnURL  = get_class($this). '.php';
        
        $html = '';
        switch ($action) {
            case 'New' :
                /* affichage form avec Expéditeur et Type Notif
                   si les deux champs sont saisis alors Acion = Edit */
                $html = 'En construction ...<br/>';
                break;
            case 'Delete' :
                if (isset($this->Get['id']) && 
                    $this->notif->Load($this->Get['id']) &&
                    $this->notif->Delete())
                {
                    $html = 'Suppression effectuée.<br/>' ;
                }
                else $html = $errmsg .'<br/>';
                break;
                
            case 'Edit' :
                $this->ReturnURL  = $this->CurrentURL;

                $resLoad = true;
                if (isset($this->Get['id'])) 
                     { $resLoad = $this->notif->Load($this->Get['id']); }
                else { $this->notif->Clear(); };

                $form = new Form($this->Objet);
                $form->addFromEntity($this->notif);
                $html = ($form->isReloadedAndOk($this->Post) 
                            ? ($form->fillEntity($this->notif)->Save() 
                                ? 'Enregistrement effectué' 
                                : $errmsg )
                            : $form->loadFromEntity($this->notif)->show());
                $html .='<br/>';
            break;
        }
        return $html;
    }
    
    function page_Contact($action):string
    {
        $errmsg = 'Erreur';
        $this->CurrentURL = get_class($this). '.php'
                . '?Object=' . $this->Objet;
        $this->ReturnURL  = get_class($this). '.php';
        
        $entity = new Contact($this->db);
        
        $html = '';
        switch ($action) {
            case 'Delete':
                $this->ReturnURL  = $this->CurrentURL;
                if (isset($this->Get['id']))
                    $html = ($entity->Delete($this->Get['id']) 
                             ? 'Suppression effectuée' 
                             : $errmsg);
            break;
            case 'Ajouter': // Valide une invitation de contact (insert )
                if (isset($this->Get['`membre_id`']) && isset($this->Get['`ami`']))
                    $html = ($entity->Ajouter($this->Get['`membre_id`'],$this->Get['`ami`']) 
                             ? 'Validation du contact effectué.<br/>'
                               . 'Vous pouvez communiquer avecc votre nouvel ami, dès à présent.' 
                             : $errmsg);
            break;  
            case 'Save': // Ajouter un ami en lui en validant
                $this->ReturnURL  = $this->CurrentURL;
                $html = 'Demande envoyée.';
            break;
            default:
                $url = get_class($this);
                
                // $entity->add_Constraint(Contact::fld_ID, $this->auth->membres);
                $tableau = [Contact::fld_AMIID,Contact::fld_AMINOM];
                
                $html = '<A HREF="'.$this->CurrentURL.'&Action=Edit">Ajouter ...</A>'
                      . '<br/><br/>'
                      . $this->Table($this->CurrentURL, $entity, $tableau);
                break;
        }
        return $html;
    }
    
    function page_Default():string
    {
        $html =  '<section>' . $this->div_Filter();
        
        $this->notif->setFilter($this->filter['filter_perimetre'],
                                $this->filter['filter_type'],
                                $this->filter['filter_destinataire']);
        
        foreach ($this->notif->getPrims() as $id => $notif)
        {
             $this->notif->Fill($notif);
             $html .= $this->div_Notif();
        }                
        $this->notif->debug = false;
        return $html . '</section>';
    }
    
    
}

/*
 * Corps de l'application
 */
session_start();
$myApp = new Schoolnet();
if ($myApp->Connect())
{
    $myApp->OpenSession();
    echo $myApp->AllInTheBox();
}