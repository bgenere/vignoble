<?php
/* Copyright (C) 2007-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2016 Bruno Généré      <bgenere@webiseasy.org>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *   	\file       vignoble/parcelle_card.php
 *		\ingroup    vignoble
 *		\brief      This file is an example of a php page
 *					Initialy built by build_class_from_table on 2016-07-13 11:12
 */

//if (! defined('NOREQUIREUSER'))  define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))    define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');
//if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK','1');			// Do not check anti CSRF attack test
//if (! defined('NOSTYLECHECK'))   define('NOSTYLECHECK','1');			// Do not check style html tag into posted data
//if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1');		// Do not check anti POST attack test
//if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');			// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');			// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
//if (! defined("NOLOGIN"))        define("NOLOGIN",'1');				// If this page is public (can be called outside logged session)

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';					// to work if your module directory is into dolibarr root htdocs directory
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';			// to work if your module directory is into a subdir of root htdocs directory
if (! $res && file_exists("../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../dolibarr/htdocs/main.inc.php';     // Used on dev env only
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../../dolibarr/htdocs/main.inc.php';   // Used on dev env only
if (! $res) die("Include of main fails");
// Change this following line to use the correct relative path from htdocs
include_once(DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php');
dol_include_once('/vignoble/class/parcelle.class.php');

// Load traductions files requiredby by page
$langs->load("vignoble@vignoble");
$langs->load("other");

// Get parameters
$id			= GETPOST('id','int');
$action		= GETPOST('action','alpha');
$backtopage = GETPOST('backtopage');
$myparam	= GETPOST('myparam','alpha');


$search_entity=GETPOST('search_entity','int');
$search_ref=GETPOST('search_ref','alpha');
$search_label=GETPOST('search_label','alpha');
$search_description=GETPOST('search_description','alpha');
$search_surface=GETPOST('search_surface','alpha');
$search_nbpieds=GETPOST('search_nbpieds','int');
$search_ecartement=GETPOST('search_ecartement','alpha');
$search_fk_assolement=GETPOST('search_fk_assolement','int');
$search_fk_cepage=GETPOST('search_fk_cepage','int');
$search_fk_porte_greffe=GETPOST('search_fk_porte_greffe','int');
$search_note_private=GETPOST('search_note_private','alpha');
$search_fk_user_author=GETPOST('search_fk_user_author','int');
$search_fk_user_modif=GETPOST('search_fk_user_modif','int');



// Protection if external user
if ($user->societe_id > 0)
{
	//accessforbidden();
}

if (empty($action) && empty($id) && empty($ref)) $action='list';

// Load object if id or ref is provided as parameter
$object=new Parcelle($db);
if (($id > 0 || ! empty($ref)) && $action != 'add')
{
	$result=$object->fetch($id,$ref);
	if ($result < 0) dol_print_error($db);
}

// Initialize technical object to manage hooks of modules. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array('parcelle'));
$extrafields = new ExtraFields($db);



/*******************************************************************
* ACTIONS
*
* Put here all code to do according to value of "action" parameter
********************************************************************/

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	// Action to add record
	if ($action == 'add')
	{
		if (GETPOST('cancel'))
		{
			$urltogo=$backtopage?$backtopage:dol_buildpath('/vignoble/list.php',1);
			header("Location: ".$urltogo);
			exit;
		}

		$error=0;

		/* object_prop_getpost_prop */
		
	$object->entity=GETPOST('entity','int');
	$object->ref=GETPOST('ref','alpha');
	$object->label=GETPOST('label','alpha');
	$object->description=GETPOST('description','alpha');
	$object->surface=GETPOST('surface','alpha');
	$object->nbpieds=GETPOST('nbpieds','int');
	$object->ecartement=GETPOST('ecartement','alpha');
	$object->fk_assolement=GETPOST('fk_assolement','int');
	$object->fk_cepage=GETPOST('fk_cepage','int');
	$object->fk_porte_greffe=GETPOST('fk_porte_greffe','int');
	$object->note_private=GETPOST('note_private','alpha');
	$object->fk_user_author=GETPOST('fk_user_author','int');
	$object->fk_user_modif=GETPOST('fk_user_modif','int');

		

		if (empty($object->ref))
		{
			$error++;
			setEventMessages($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("Ref")), null, 'errors');
		}

		if (! $error)
		{
			$result=$object->create($user);
			if ($result > 0)
			{
				// Creation OK
				$urltogo=$backtopage?$backtopage:dol_buildpath('/vignoble/list.php',1);
				header("Location: ".$urltogo);
				exit;
			}
			{
				// Creation KO
				if (! empty($object->errors)) setEventMessages(null, $object->errors, 'errors');
				else  setEventMessages($object->error, null, 'errors');
				$action='create';
			}
		}
		else
		{
			$action='create';
		}
	}

	// Cancel
	if ($action == 'update' && GETPOST('cancel')) $action='view';

	// Action to update record
	if ($action == 'update' && ! GETPOST('cancel'))
	{
		$error=0;

		
	$object->entity=GETPOST('entity','int');
	$object->ref=GETPOST('ref','alpha');
	$object->label=GETPOST('label','alpha');
	$object->description=GETPOST('description','alpha');
	$object->surface=GETPOST('surface','alpha');
	$object->nbpieds=GETPOST('nbpieds','int');
	$object->ecartement=GETPOST('ecartement','alpha');
	$object->fk_assolement=GETPOST('fk_assolement','int');
	$object->fk_cepage=GETPOST('fk_cepage','int');
	$object->fk_porte_greffe=GETPOST('fk_porte_greffe','int');
	$object->note_private=GETPOST('note_private','alpha');
	$object->fk_user_author=GETPOST('fk_user_author','int');
	$object->fk_user_modif=GETPOST('fk_user_modif','int');

		

		if (empty($object->ref))
		{
			$error++;
			setEventMessages($langs->transnoentitiesnoconv("ErrorFieldRequired",$langs->transnoentitiesnoconv("Ref")), null, 'errors');
		}

		if (! $error)
		{
			$result=$object->update($user);
			if ($result > 0)
			{
				$action='view';
			}
			else
			{
				// Creation KO
				if (! empty($object->errors)) setEventMessages(null, $object->errors, 'errors');
				else setEventMessages($object->error, null, 'errors');
				$action='edit';
			}
		}
		else
		{
			$action='edit';
		}
	}

	// Action to delete
	if ($action == 'confirm_delete')
	{
		$result=$object->delete($user);
		if ($result > 0)
		{
			// Delete OK
			setEventMessages("RecordDeleted", null, 'mesgs');
			header("Location: ".dol_buildpath('/vignoble/list.php',1));
			exit;
		}
		else
		{
			if (! empty($object->errors)) setEventMessages(null, $object->errors, 'errors');
			else setEventMessages($object->error, null, 'errors');
		}
	}
}




/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

llxHeader('',$langs->trans('ParcelleCardTitle'),'');

$form=new Form($db);


// Put here content of your page

// Example : Adding jquery code
print '<script type="text/javascript" language="javascript">
jQuery(document).ready(function() {
	function init_myfunc()
	{
		jQuery("#myid").removeAttr(\'disabled\');
		jQuery("#myid").attr(\'disabled\',\'disabled\');
	}
	init_myfunc();
	jQuery("#mybutton").click(function() {
		init_myfunc();
	});
});
</script>';


// Part to create
if ($action == 'create')
{
	print load_fiche_titre($langs->trans("ParcelleCardNew"));

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="action" value="add">';
	print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';

	dol_fiche_head();

	print '<table class="border centpercent">'."\n";
	// print '<tr><td class="fieldrequired">'.$langs->trans("Label").'</td><td><input class="flat" type="text" size="36" name="label" value="'.$label.'"></td></tr>';
	// 
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldentity").'</td><td><input class="flat" type="text" name="entity" value="'.GETPOST('entity').'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldref").'</td><td><input class="flat" type="text" name="ref" value="'.GETPOST('ref').'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldlabel").'</td><td><input class="flat" type="text" name="label" value="'.GETPOST('label').'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fielddescription").'</td><td><input class="flat" type="text" name="description" value="'.GETPOST('description').'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldsurface").'</td><td><input class="flat" type="text" name="surface" value="'.GETPOST('surface').'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldnbpieds").'</td><td><input class="flat" type="text" name="nbpieds" value="'.GETPOST('nbpieds').'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldecartement").'</td><td><input class="flat" type="text" name="ecartement" value="'.GETPOST('ecartement').'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldfk_assolement").'</td><td><input class="flat" type="text" name="fk_assolement" value="'.GETPOST('fk_assolement').'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldfk_cepage").'</td><td><input class="flat" type="text" name="fk_cepage" value="'.GETPOST('fk_cepage').'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldfk_porte_greffe").'</td><td><input class="flat" type="text" name="fk_porte_greffe" value="'.GETPOST('fk_porte_greffe').'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldnote_private").'</td><td><input class="flat" type="text" name="note_private" value="'.GETPOST('note_private').'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldfk_user_author").'</td><td><input class="flat" type="text" name="fk_user_author" value="'.GETPOST('fk_user_author').'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldfk_user_modif").'</td><td><input class="flat" type="text" name="fk_user_modif" value="'.GETPOST('fk_user_modif').'"></td></tr>';

	print '</table>'."\n";

	dol_fiche_end();

	print '<div class="center"><input type="submit" class="button" name="add" value="'.$langs->trans("Create").'"> &nbsp; <input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'"></div>';

	print '</form>';
}



// Part to edit record
if (($id || $ref) && $action == 'edit')
{
	print load_fiche_titre($langs->trans("MyModule"));
    
	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
	
	dol_fiche_head();

	print '<table class="border centpercent">'."\n";
	// print '<tr><td class="fieldrequired">'.$langs->trans("Label").'</td><td><input class="flat" type="text" size="36" name="label" value="'.$label.'"></td></tr>';
	// 
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldentity").'</td><td><input class="flat" type="text" name="entity" value="'.$object->entity.'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldref").'</td><td><input class="flat" type="text" name="ref" value="'.$object->ref.'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldlabel").'</td><td><input class="flat" type="text" name="label" value="'.$object->label.'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fielddescription").'</td><td><input class="flat" type="text" name="description" value="'.$object->description.'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldsurface").'</td><td><input class="flat" type="text" name="surface" value="'.$object->surface.'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldnbpieds").'</td><td><input class="flat" type="text" name="nbpieds" value="'.$object->nbpieds.'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldecartement").'</td><td><input class="flat" type="text" name="ecartement" value="'.$object->ecartement.'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldfk_assolement").'</td><td><input class="flat" type="text" name="fk_assolement" value="'.$object->fk_assolement.'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldfk_cepage").'</td><td><input class="flat" type="text" name="fk_cepage" value="'.$object->fk_cepage.'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldfk_porte_greffe").'</td><td><input class="flat" type="text" name="fk_porte_greffe" value="'.$object->fk_porte_greffe.'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldnote_private").'</td><td><input class="flat" type="text" name="note_private" value="'.$object->note_private.'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldfk_user_author").'</td><td><input class="flat" type="text" name="fk_user_author" value="'.$object->fk_user_author.'"></td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldfk_user_modif").'</td><td><input class="flat" type="text" name="fk_user_modif" value="'.$object->fk_user_modif.'"></td></tr>';

	print '</table>';
	
	dol_fiche_end();

	print '<div class="center"><input type="submit" class="button" name="save" value="'.$langs->trans("Save").'">';
	print ' &nbsp; <input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
	print '</div>';

	print '</form>';
}



// Part to show record
if ($id && (empty($action) || $action == 'view' || $action == 'delete'))
{
	print load_fiche_titre($langs->trans("MyModule"));
    
	dol_fiche_head();

	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteMyOjbect'), $langs->trans('ConfirmDeleteMyObject'), 'confirm_delete', '', 0, 1);
		print $formconfirm;
	}
	
	print '<table class="border centpercent">'."\n";
	// print '<tr><td class="fieldrequired">'.$langs->trans("Label").'</td><td><input class="flat" type="text" size="36" name="label" value="'.$label.'"></td></tr>';
	// 
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldentity").'</td><td>$object->entity</td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldref").'</td><td>$object->ref</td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldlabel").'</td><td>$object->label</td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fielddescription").'</td><td>$object->description</td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldsurface").'</td><td>$object->surface</td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldnbpieds").'</td><td>$object->nbpieds</td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldecartement").'</td><td>$object->ecartement</td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldfk_assolement").'</td><td>$object->fk_assolement</td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldfk_cepage").'</td><td>$object->fk_cepage</td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldfk_porte_greffe").'</td><td>$object->fk_porte_greffe</td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldnote_private").'</td><td>$object->note_private</td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldfk_user_author").'</td><td>$object->fk_user_author</td></tr>';
print '<tr><td class="fieldrequired">'.$langs->trans("Fieldfk_user_modif").'</td><td>$object->fk_user_modif</td></tr>';

	print '</table>';
	
	dol_fiche_end();


	// Buttons
	print '<div class="tabsAction">'."\n";
	$parameters=array();
	$reshook=$hookmanager->executeHooks('addMoreActionsButtons',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
	if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

	if (empty($reshook))
	{
		if ($user->rights->vignoble->write)
		{
			print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=edit">'.$langs->trans("Modify").'</a></div>'."\n";
		}

		if ($user->rights->vignoble->delete)
		{
			print '<div class="inline-block divButAction"><a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=delete">'.$langs->trans('Delete').'</a></div>'."\n";
		}
	}
	print '</div>'."\n";


	// Example 2 : Adding links to objects
	//$somethingshown=$form->showLinkedObjectBlock($object);
	//$linktoelem = $form->showLinkToObjectBlock($object);
	//if ($linktoelem) print '<br>'.$linktoelem;

}


// End of page
llxFooter();
$db->close();
