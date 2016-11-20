<?php

namespace RayTools\Application;

use RayTools\Application\Application;
use RayTools\Entity\Entity;


abstract class WebApplication extends Application
{
    protected
        $menu,
        $linkCSS,
        $scriptJS;
    
    public
        $currentURL,
        $returnURL;
    
 /* Surcharges des méthodes du parent
    -------------------------------------------------------------------------- */
        
    public function __construct()
    {
        parent::__construct();
        
        $this->menu=[];
        $this->linkCSS = [];
        $this->scriptJS= [];
        
        $this->currentURL='';
        $this->returnURL='';
    }

    /**
     * Ré-active la session
     */
    public function OpenSession()
    {
        parent::OpenSession();
    
        /* Gestion des URLs
         --------------------------------------------------------------------------- */
        $CurURL = '';
        foreach($this->Get as $param => $value)
            $CurURL .= ($CurURL != '' ? '&' : '' )
            . $param . '=' . $value;
    
            $this->currentURL = get_class($this).'.php'
                    . ($CurURL !== '' ? '?'.$CurURL : '');
    
                    $this->returnURL = (isset($this->Get['ReturnURL']) 
                            ? $this->Get['ReturnURL'] 
                            : get_class($this).'.php' 
                            . (($this->Objet != '') && ($this->Action !='')
                                ? '.?Object=' . $this->Objet
                                : ''));
    }
    
    /**
     * Renvoie la chaine html pour continuer le document HTML complet
     * 
     * @param string $bodymethod (surcharge le router)
     * @param string $prefix 
     * 
     * {@inheritDoc}
     * @see Application::AllInTheBox()
     */
    final public function AllInTheBox($bodymethod='', $prefix='page')
    {
        return $this->beginHtmlDoc()
        . $this->Header()
        . (($bodymethod !='') && method_exists($this, $bodymethod)
                ? $this->{$bodymethod}()
                : parent::AllInTheBox($prefix))
        . $this->Footer()
        . $this->endHtmlDoc();
    }
    
    /* Nouvelles methodes 
     -------------------------------------------------------------------------- */
    
    /**
     * Ajoute un item de menu
     * @param unknown $id
     * @param unknown $text
     * @param unknown $target
     */
    final protected function addMenu($id,$text,$target)
    {
        $this->menu[] = ['id' => $id, 'text' => $text,'target' => $target];
    }

    /**
     * ajoute une ressource CSS
     * @param string $href
     * @param string $options
     */
    final protected function addLinkCSS($href, $options='')
    {
        $this->linkCSS[] = ['href'    => $href,
                            'options' => $options,
                           ];
    }
    
    /**
     * ajoute un script javascript
     * @param unknown $src
     * @param unknown $options
     */
    final function addScriptJS($src, $options='')
    {
        $this->scriptJS[] = ['src' => $src,
                             'options' => $options,
                            ];
    }
    
    /* Formatage du document HTML
     -------------------------------------------------------------------------- */
    
    /**
     * Début (head) du document HTML (dont CSS)
     * @return string
     */
    final private function beginHtmlDoc():string
    {
        $html= '<html>'
             . '<head>'
             . '<meta charset="UTF-8">'
             . '<title>'.$this->title.'</title>';
        
        foreach ($this->linkCSS as $key => $linkCSS)
            $html .= '<link rel="stylesheet"  type="text/css" ' 
                   . 'href ="' . $linkCSS['href'] . '" '
                   . ($linkCSS['options'] != '' ? $linkCSS['options'] : '' )
                   . ' />';
                           
        return $html 
             .'</head>'
             . '<body>';
    }
    
    /**
     * Fin du document HTML (dont script js)
     * @return string
     */
    final private function endHtmlDoc():string
    {
        $html = '</body>';
        
        foreach ( $this->scriptJS as $key => $scriptJS)
            $html .= '<script type="text/javascript" '
                   . 'src="' . $scriptJS['src'] .'" '
                   . ($scriptJS['options'] != '' ? $scriptJS['options'] : '')  
                   . ' ></script>';
        return $html 
             . '</html>';
    }

    /**
     * retourne l'entete de page web (bandeau, menu, ...)
     * @return string
     */
    abstract function Header():string;
    
    /**
     * retourne le pied de page web
     * @return string
     */
    abstract function Footer():string;

    /* Génération automatique d'un tableau de données (Table)
     -------------------------------------------------------------------------- */
    
    /**
     * Affiche une table sur la base du tableau de champs d'une entité
     *
     * @param string $baseURL chaine URL sur laquelle s'ajoutera 'id=xx&Action=yyy'
     * @param Entity $Entite
     * @param array $KeyArray  du type [$fld_id, $fld_nom, ...]
     * @param array $commands  du type ['texte'=>'', 'action'=>'']
     *
     * @return string
     */
    protected function Table($baseURL, Entity $entite, $keyarray=null, $commands=null):string
    {
        if (($baseURL ==null)||($baseURL ==''))
            $baseURL = get_class($this).'.php?Object='.$this->Objet;
        
        if (count($keyarray)===null)
                $keyarray = $entite->getColnames();
    
        if ($commands=== null)
            $commands = [['texte'=>'Modifier', 'action'=>'Edit'],
                        ['texte'=>'Supprimer','action'=>'Delete']];
    
        $html = '<table class="datalist">'
              . $this->Table_title($entite, $keyarray, count($commands)!=0);
    
        $entite->Clear();
        $SQLResult = $entite->GetAll(true, $keyarray);
        foreach ($SQLResult as $key => $item )
        {
            $entite->Fill($item);
            $html .= '<tr>'
                   . $this->Table_value($entite,$keyarray,count($commands)!=0)
                   . '<td>'
                   . $this->Table_actions($baseURL, $key,$commands)
                   . '</td></tr>';
        }
        return $html . '</table>';
    }
    
    private function Table_title(Entity $entite, $keyarray,$withcommands=false):string
    {
        $html = '<tr>';
        foreach ($keyarray as $index => $colname)
            $html .= '<th>'.$entite->columns[$colname]->label.'</th>';
    
        return $html.($withcommands? '<th></th>':'').'</tr>';
    }
    
    private function Table_value(Entity $entite, $keyarray):string
    {
        $html = '';
        foreach ($keyarray as $index => $colname)
            $html .= ($entite->columns[$colname]->type == $entite::type_INT
                       ? '<td class="text-right">' : '<td>')
                   . ($entite->columns[$colname]->value!=null
                       ? $entite->columns[$colname]->value :'')
                   . '</td>';
        return $html;
    }
    
    private function Table_actions($baseURL, $id,$commands)
    {
        $baseURL .= (strpos($baseURL,'?') === null ? '?' : '&')
        . 'id=' .$id .'&Action=';
    
        $html = '';
        foreach($commands as $key => $item)
            $html .= $this->tableCommand($baseURL.$item['action'],$item['texte']);
            return $html;
    }
    
    protected function tableCommand($action, $texte, $classcommand='btn-xs btn-primary')
    {
        return '<a href="'.$action.'" '
             . 'class="'.$classcommand.'">'
             . $texte.'</a> ';
    }

}
