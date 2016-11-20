/**
 * Bibliotheque de gestion des éléments d'affichage
 */

/* 
 * affiche un formulaire de saisie
 * par appel à PHP
 */
function EditNotif($id,$chain)
{   
    $('#EditNotif div').show();
    $('#EditNotif div').html
    return false;
}

/*
 * Affiche ou cache la div des chainages
 * par appel à PHP
 */
function toggle_Chains($id)
{
	$selecteur = "#chain"+$id;
	$($selecteur).empty(); 
  	$($selecteur).load('http://localhost/Schoolnet/Chain.php?notif='+$id);
	$($selecteur).toggle();		

}

/* initialisation de l'application
   ------------------------------- */

$(document).ready(function() {
	$(".popup").hide(); 
	$("#input").cleditor();
});

