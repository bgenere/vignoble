<?php
/*
 * Copyright (C) 2007-2015 Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2016 Bruno Généré <bgenere@webiseasy.org>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file plot_card.php
 * \ingroup plot
 * \brief The plot main form.
 *
 * The form displays the main attributes of the plot.
 * Attributes are in view mode first.
 * The user could use the form to update, create or delete an object.
 * Top of the form is standardised to display the object identity and navigate the object list.
 */

// if (! defined('NOREQUIREUSER')) define('NOREQUIREUSER','1');
// if (! defined('NOREQUIREDB')) define('NOREQUIREDB','1');
// if (! defined('NOREQUIRESOC')) define('NOREQUIRESOC','1');
// if (! defined('NOREQUIRETRAN')) define('NOREQUIRETRAN','1');
// if (! defined('NOCSRFCHECK')) define('NOCSRFCHECK','1'); // Do not check anti CSRF attack test
// if (! defined('NOSTYLECHECK')) define('NOSTYLECHECK','1'); // Do not check style html tag into posted data
// if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1'); // Do not check anti POST attack test
// if (! defined('NOREQUIREMENU')) define('NOREQUIREMENU','1'); // If there is no need to load and show top and left menu
// if (! defined('NOREQUIREHTML')) define('NOREQUIREHTML','1'); // If we don't need to load the html.form.class.php
// if (! defined('NOREQUIREAJAX')) define('NOREQUIREAJAX','1');
// if (! defined("NOLOGIN")) define("NOLOGIN",'1'); // If this page is public (can be called outside logged session)
@include './tpl/maindolibarr.inc.php';

// Change this following line to use the correct relative path from htdocs
// include_once(DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php');
dol_include_once('/vignoble/class/plot.class.php');
dol_include_once('/vignoble/class/html.form.vignoble.class.php');

include_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';

include_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';

// Load traductions files requiredby by page
$langs->load("vignoble@vignoble");
// Dolibarr language file @TODO document usage of file other
$langs->load("other");

// Get parameters
$id = GETPOST('id', 'int'); // object row id
$ref = GETPOST('ref', 'alpha'); // object unique reference
$action = GETPOST('action', 'alpha'); // action to do
$ref = GETPOST('ref', 'alpha');
if ($ref == '') {
	$ref = NULL;
} // NEEDED else your record will never be populated when ref is empty !!!
  // echo 'URL param ';var_dump($id);var_dump($ref);var_dump($action);echo '<br />';
$backtopage = GETPOST('backtopage'); // page to redirect when process is done
                                     // add your own parameters like this
                                     // $myparam = GETPOST('myparam','alpha');
                                     
// Prevent direct access through URL
if ($user->societe_id > 0 || $user->rights->vignoble->level1->level2 == 0) {
	// accessforbidden();
}

if (empty($action) && empty($id) && empty($ref))
	$action = 'view';
	
	// Load object if id or ref is provided as parameter
	// echo 'Before object fetch id ';var_dump($id);echo '$ref ';var_dump($ref);echo '$action ';var_dump($action);echo '<br />';
$object = new plot($db);
$extrafields = new ExtraFields($db);
// fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);

// TODO create a function that populate the object when objet id or ref is provided and object is not loaded
if (($id > 0 || ! empty($ref)) && $action != 'add') { // $action should not be there
	$result = $object->fetch($id, $ref);
	// echo 'After object fetch ';echo '$id ';var_dump($id);echo '$ref ';var_dump($ref);echo '$object ';var_dump($object);echo '<br />';
	$id = $object->id; // NEEDED else view is not displayed when only ref is provided
	if ($result < 0)
		dol_print_error($db);
}

// Initialize technical object to manage hooks of modules. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array(
	'plot'
));

/**
 * *****************************************************************
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 * ******************************************************************
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0)
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook)) {
	// Action to add record
	if ($action == 'add') {
		if (GETPOST('cancel')) {
			$urltogo = $backtopage ? $backtopage : dol_buildpath('/vignoble/plot_list.php', 1);
			header("Location: " . $urltogo);
			exit();
		}
		
		$error = 0;
		
		/* object_prop_getpost_prop */
		
		$object->entity = $conf->entity;
		$object->ref = GETPOST('ref', 'alpha');
		$object->label = GETPOST('label', 'alpha');
		$object->description = GETPOST('description', 'alpha');
		$object->areasize = GETPOST('areasize', 'alpha');
		$object->rootsnumber = GETPOST('rootsnumber', 'int');
		$object->spacing = GETPOST('spacing', 'alpha');
		$object->fk_cultivationtype = GETPOST('fk_cultivationtype', 'int');
		$object->fk_varietal = GETPOST('fk_varietal', 'int');
		$object->fk_rootstock = GETPOST('fk_rootstock', 'int');
		$object->note_private = GETPOST('note_private', 'alpha');
		$object->fk_user_author = $user->id;
		$object->fk_user_modif = $user->id;
		
		if (empty($object->ref)) {
			$error ++;
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Ref")), null, 'errors');
		}
		
		// Get extrafields values
		$ret = $extrafields->setOptionalsFromPost($extralabels, $object);
		if ($ret < 0)
			$error ++;
		
		if (! $error) {
			$result = $object->create($user);
			if ($result > 0) {
				// Creation OK
				$urltogo = $backtopage ? $backtopage : dol_buildpath('/vignoble/plot_list.php', 1);
				header("Location: " . $urltogo);
				exit();
			}
			{
				// Creation KO
				setEventMessages($object->error, $object->errors, 'errors');
				$action = 'create';
			}
		} else {
			$action = 'create';
		}
	}
	
	// Cancel
	if ($action == 'update' && GETPOST('cancel'))
		$action = 'view';
		
		// Action to update record
	if ($action == 'update' && ! GETPOST('cancel')) {
		$error = 0;
		
		$object->id = GETPOST('id', 'int');
		$object->ref = GETPOST('ref', 'alpha');
		$object->label = GETPOST('label', 'alpha');
		$object->description = GETPOST('description', 'alpha');
		$object->areasize = GETPOST('areasize', 'alpha');
		$object->rootsnumber = GETPOST('rootsnumber', 'int');
		$object->spacing = GETPOST('spacing', 'alpha');
		$object->fk_cultivationtype = GETPOST('fk_cultivationtype', 'int');
		$object->fk_varietal = GETPOST('fk_varietal', 'int');
		$object->fk_rootstock = GETPOST('fk_rootstock', 'int');
		
		if (empty($object->ref)) {
			$error ++;
			setEventMessages($langs->transnoentitiesnoconv("ErrorFieldRequired", $langs->transnoentitiesnoconv("Ref")), null, 'errors');
		}
		// Fill array 'array_options' with data from add form
		$ret = $extrafields->setOptionalsFromPost($extralabels, $object);
		if ($ret < 0)
			$error ++;
		
		if (! $error) {
			$result = $object->update($user);
			if ($result > 0) {
				$action = 'view';
			} else {
				// Creation KO
				if (! empty($object->errors))
					setEventMessages(null, $object->errors, 'errors');
				else
					setEventMessages($object->error, null, 'errors');
				$action = 'edit';
			}
		} else {
			$action = 'edit';
		}
	}
	
	// Action to delete
	if ($action == 'confirm_delete') {
		$result = $object->delete($user);
		if ($result > 0) {
			// Delete OK
			setEventMessages("RecordDeleted", null, 'mesgs');
			header("Location: " . dol_buildpath('/vignoble/plot_list.php', 1));
			exit();
		} else {
			if (! empty($object->errors))
				setEventMessages(null, $object->errors, 'errors');
			else
				setEventMessages($object->error, null, 'errors');
		}
	}
	
	if ($action == 'builddoc') // In get or post
{
		// Save last template used to generate document
		if (GETPOST('model'))
			$object->setDocModel($user, GETPOST('model', 'alpha'));
			
			// Define output language
		$outputlangs = $langs;
		$newlang = '';
		if ($conf->global->MAIN_MULTILANGS && empty($newlang) && ! empty($_REQUEST['lang_id']))
			$newlang = $_REQUEST['lang_id'];
		if (! empty($newlang)) {
			$outputlangs = new Translate("", $conf);
			$outputlangs->setDefaultLang($newlang);
		}
		$result = $object->generateDocument($object->modelpdf, $outputlangs, $hidedetails, $hidedesc, $hideref);
		if ($result <= 0) {
			setEventMessages($object->error, $object->errors, 'errors');
			$action = '';
		}
		$action = '';
	}
	
	// Remove file in doc form
	if ($action == 'remove_file') {
		if ($object->id > 0) {
			require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
			
			$langs->load("other");
			$upload_dir = $conf->vignoble->dir_output;
			$file = $upload_dir . '/' . GETPOST('file');
			$ret = dol_delete_file($file, 0, 0, 0, $object);
			if ($ret)
				setEventMessages($langs->trans("FileWasRemoved", GETPOST('file')), null, 'mesgs');
			else
				setEventMessages($langs->trans("ErrorFailToDeleteFile", GETPOST('file')), null, 'errors');
			$action = '';
		}
	}
}

/**
 * *************************************************
 * VIEW
 *
 * Put here all code to build page
 * **************************************************
 */

llxHeader('', $langs->trans('PlotCardTitle'), '');

$form = new Form($db);
$formvignoble = new FormVignoble($db);
$formfile = new FormFile($db);
$formactions = new FormActions($db);

// Put here content of your page

// Part to create
if ($action == 'create') {
	print load_fiche_titre($langs->trans("New Plot"), '', 'object_plot@vignoble');
	
	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="action" value="add">';
	print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
	
	dol_fiche_head();
	print '<table class="border centpercent">' . "\n";
	// print '<tr><td class="fieldrequired">'.$langs->trans("Label").'</td><td><input class="flat" type="text" size="36" name="label" value="'.$label.'"></td></tr>';
	//
	print '<tr><td class="fieldrequired">' . $langs->trans("Fieldref") . '</td><td><input class="flat" type="text" name="ref" value="' . GETPOST('ref') . '"></td></tr>';
	print '<tr><td class="fieldrequired">' . $langs->trans("Fieldlabel") . '</td><td><input class="flat" type="text" name="label" value="' . GETPOST('label') . '"></td></tr>';
	print '<tr><td class="fieldrequired">' . $langs->trans("Fielddescription") . '</td><td><input class="flat" type="text" name="description" value="' . GETPOST('description') . '"></td></tr>';
	print '<tr><td class="fieldrequired">' . $langs->trans("Fieldareasize") . '</td><td><input class="flat" type="text" name="areasize" value="' . GETPOST('areasize') . '"></td></tr>';
	print '<tr><td class="fieldrequired">' . $langs->trans("Fieldrootsnumber") . '</td><td><input class="flat" type="text" name="rootsnumber" value="' . GETPOST('rootsnumber') . '"></td></tr>';
	print '<tr><td class="fieldrequired">' . $langs->trans("Fieldspacing") . '</td><td><input class="flat" type="text" name="spacing" value="' . GETPOST('spacing') . '"></td></tr>';
	print '<tr><td class="fieldrequired">' . $langs->trans("Fieldfk_cultivationtype") . '</td><td>' . $formvignoble->displayDicCombo('c_cultivationtype', 'Cultivation Type', GETPOST('fk_cultivationtype'), 'fk_cultivationtype') . '</td></tr>';
	print '<tr><td class="fieldrequired">' . $langs->trans("Fieldfk_varietal") . '</td><td>' . $formvignoble->displayDicCombo('c_varietal', 'Varietal', GETPOST('fk_varietal'), 'fk_varietal') . '</td></tr>';
	print '<tr><td class="fieldrequired">' . $langs->trans("Fieldfk_rootstock") . '</td><td>' . $formvignoble->displayDicCombo('c_rootstock', 'Rootstock', GETPOST('fk_rootstock'), 'fk_rootstock') . '</td></tr>';
	
	if (! empty($extrafields->attribute_label)) {
		print $object->showOptionals($extrafields, 'edit', $parameters);
	}
	
	print '</table>' . "\n";
	
	dol_fiche_end();
	
	print '<div class="center"><input type="submit" class="button" name="add" value="' . $langs->trans("Create") . '"> &nbsp; <input type="submit" class="button" name="cancel" value="' . $langs->trans("Cancel") . '"></div>';
	
	print '</form>';
}

// Part to edit record @TODO remove test on data
if (($id || $ref) && $action == 'edit') {
	print load_fiche_titre($langs->trans("Edit Plot"), $object->label, 'object_plot@vignoble');
	
	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
	print '<input type="hidden" name="id" value="' . $object->id . '">';
	
	dol_fiche_head();
	print '<table class="border centpercent">' . "\n";
	// print '<tr><td class="fieldrequired">'.$langs->trans("Label").'</td><td><input class="flat" type="text" size="36" name="label" value="'.$label.'"></td></tr>';
	//
	print '<tr><td class="fieldrequired">' . $langs->trans("Fieldref") . '</td><td><input class="flat" type="text" name="ref" value="' . $object->ref . '"></td></tr>';
	print '<tr><td class="fieldrequired">' . $langs->trans("Fieldlabel") . '</td><td><input class="flat" type="text" name="label" value="' . $object->label . '"></td></tr>';
	print '<tr><td class="fieldrequired">' . $langs->trans("Fielddescription") . '</td><td><input class="flat" type="text" name="description" value="' . $object->description . '"></td></tr>';
	print '<tr><td class="fieldrequired">' . $langs->trans("Fieldareasize") . '</td><td><input class="flat" type="text" name="areasize" value="' . $object->areasize . '"></td></tr>';
	print '<tr><td class="fieldrequired">' . $langs->trans("Fieldrootsnumber") . '</td><td><input class="flat" type="text" name="rootsnumber" value="' . $object->rootsnumber . '"></td></tr>';
	print '<tr><td class="fieldrequired">' . $langs->trans("Fieldspacing") . '</td><td><input class="flat" type="text" name="spacing" value="' . $object->spacing . '"></td></tr>';
	print '<tr><td class="fieldrequired">' . $langs->trans("Fieldfk_cultivationtype") . '</td><td>' . $formvignoble->displayDicCombo('c_cultivationtype', 'Cultivation Type', $object->fk_cultivationtype, 'fk_cultivationtype') . '</td></tr>';
	print '<tr><td class="fieldrequired">' . $langs->trans("Fieldfk_varietal") . '</td><td>' . $formvignoble->displayDicCombo('c_varietal', 'Varietal', $object->fk_varietal, 'fk_varietal') . '</td></tr>';
	print '<tr><td class="fieldrequired">' . $langs->trans("Fieldfk_rootstock") . '</td><td>' . $formvignoble->displayDicCombo('c_rootstock', 'Rootstock', $object->fk_rootstock, 'fk_rootstock') . '</td></tr>';
	
	if (! empty($extrafields->attribute_label)) {
		print $object->showOptionals($extrafields, 'edit', $parameters);
	}
	print '</table>';
	
	dol_fiche_end();
	
	print '<div class="center"><input type="submit" class="button" name="save" value="' . $langs->trans("Save") . '">';
	print ' &nbsp; <input type="submit" class="button" name="cancel" value="' . $langs->trans("Cancel") . '">';
	print '</div>';
	
	print '</form>';
}

// echo 'Before object view ';var_dump($id);var_dump($ref);var_dump($action);echo '<br />';
// show object card when action is view, delete or none
if (empty($action) || $action == 'view' || $action == 'delete') {
	
	$head = $formvignoble->getTabsHeader($langs, $object);
	dol_fiche_head($head, 'card', $langs->trans("Plot"), 0, 'plot@vignoble');
	
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteMyOjbect'), $langs->trans('ConfirmDeleteMyObject'), 'confirm_delete', '', 0, 1);
		print $formconfirm;
	}
	
	print '<table class="border centpercent">' . "\n";
	
	$linkback = '<a href="' . dol_buildpath('/vignoble/plot_list.php', 1) . '">' . $langs->trans("BackToList") . '</a>';
	
	// Ref
	print '<tr><td class="titlefield">' . $langs->trans("Ref") . '</td><td>';
	print $form->showrefnav($object, 'ref', $linkback, 1, 'ref', 'ref');
	print '</td></tr>';
	
	print '<tr><td>' . $langs->trans("Fieldlabel") . '</td><td>' . $object->label . '</td></tr>';
	print '<tr><td>' . $langs->trans("Fielddescription") . '</td><td>' . $object->description . '</td></tr>';
	print '<tr><td>' . $langs->trans("Fieldareasize") . '</td><td>' . $object->areasize . '</td></tr>';
	print '<tr><td>' . $langs->trans("Fieldrootsnumber") . '</td><td>' . $object->rootsnumber . '</td></tr>';
	print '<tr><td>' . $langs->trans("Fieldspacing") . '</td><td>' . $object->spacing . '</td></tr>';
	print '<tr><td>' . $langs->trans("Fieldfk_cultivationtype") . '</td><td>' . dol_getIdFromCode($db, $object->fk_cultivationtype, 'c_cultivationtype', 'rowid', 'label') . '</td></tr>';
	print '<tr><td>' . $langs->trans("Fieldfk_varietal") . '</td><td>' . dol_getIdFromCode($db, $object->fk_varietal, 'c_varietal', 'rowid', 'label') . '</td></tr>';
	print '<tr><td>' . $langs->trans("Fieldfk_rootstock") . '</td><td>' . dol_getIdFromCode($db, $object->fk_rootstock, 'c_rootstock', 'rowid', 'label') . '</td></tr>';
	
	if (! empty($extrafields->attribute_label)) {
		print $object->showOptionals($extrafields, 'view', $parameters);
	}
	print '</table>';
	
	dol_fiche_end();
	
	// Buttons
	print '<div class="tabsAction">' . "\n";
	$parameters = array();
	$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if ($reshook < 0)
		setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
	
	if (empty($reshook)) {
		// if ($user->rights->vignoble->level1->level2) {
		print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=edit">' . $langs->trans("Modify") . '</a></div>' . "\n";
		// }
		
		if ($user->rights->vignoble->plot->delete) {
			print '<div class="inline-block divButAction"><a class="butActionDelete" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=delete">' . $langs->trans('Delete') . '</a></div>' . "\n";
		} else {
			print '<div class="inline-block divButAction"><a class="butActionRefused" href="#">' . $langs->trans('Delete') . '</a></div>' . "\n";
		}
	}
	print '</div>' . "\n";
	
	// Example 2 : Adding links to objects
	// $somethingshown=$form->showLinkedObjectBlock($object);
	// $linktoelem = $form->showLinkToObjectBlock($object);
	// if ($linktoelem) print '<br>'.$linktoelem;
	
	print '<div class="fichecenter"><div class="fichehalfleft">';
	
	// Documents
	$ref = dol_sanitizeFileName($object->ref);
	$file = $conf->vignoble->dir_output . '/' . $ref . '/' . $ref . '.pdf';
	$relativepath = $ref . '/' . $ref . '.pdf';
	$filedir = $conf->vignoble->dir_output . '/' . $ref;
	$urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
	$genallowed = 1;
	// $user->rights->commande->creer;
	$delallowed = 1;
	// $user->rights->commande->supprimer;
	$somethingshown = $formfile->show_documents('vignoble', $ref, $filedir, $urlsource, $genallowed, $delallowed, $object->modelpdf, 1, 0, 0, 28, 0, '', '', '', $soc->default_lang);
	
	// Show links to link elements
	// $linktoelem = $form->showLinkToObjectBlock($object, null, array('plot'));
	// $somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);
	
	print '</div><div class="fichehalfright"><div class="ficheaddleft">';
	
	// List of actions on element
	include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
	$formactions = new FormActions($db);
	// $somethingshown = $formactions->showactions($object, 'plot', 0);
	
	print '</div></div></div>';
}

// End of page
llxFooter();
$db->close();


