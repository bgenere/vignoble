<?php
/*
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
 * \brief The plot card displays the main attributes of the plot.
 * Attributes are in view mode first.
 * The user could use the form to update, create or delete an object.
 * Top of the form is standardised to display the object identity and navigate the object list.
 */
@include './tpl/maindolibarr.inc.php';

@include './tpl/plot.inc.php';

// Current Plot id and/or Ref
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
// Page parameters
$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'alpha');
$backtopage = GETPOST('backtopage');

// Security check
if (! $user->rights->vignoble->plot->read)
	accessforbidden();
	
// Plot classes for C R U D operations
$object = new plot($db);
$extrafields = new ExtraFields($db);
$extrafieldslabels = $extrafields->fetch_name_optionals_label($object->table_element);

if (($id > 0 || ! empty($ref))) { // R U D operation
	
	if ($object->fetch($id, $ref) >= 0) {
		$id = $object->id;
		
		/**
		 * Actions
		 */
		
		// Cancel
		if ($action == 'update' && $cancel)
			$action = 'view';
			
			// Action to update record
		if ($action == 'update' && ! $cancel) {
			$error = 0;
			
			$object->id = GETPOST('id', 'int');
			$object->ref = GETPOST('ref', 'alpha');
			$object->label = GETPOST('label', 'alpha');
			$object->description = dol_htmlcleanlastbr(GETPOST('description'));
			//
			if (empty($object->ref)) {
				$error ++;
				setEventMessages($langs->transnoentitiesnoconv("ErrorFieldRequired", $langs->transnoentitiesnoconv("Ref")), null, 'errors');
			}
			// Fill array 'array_options' with data from add form
			$ret = $extrafields->setOptionalsFromPost($extrafieldslabels, $object);
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
		
		if ($action == 'builddoc') {
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
		
		/**
		 * Build View
		 */
		
		llxHeader('', $langs->trans('PlotCardTitle'), '');
		
		$form = new Form($db);
		$formvignoble = new FormVignoble($db);
		$formfile = new FormFile($db);
		$formactions = new FormActions($db);
		
		
		/**
		 * - edit view : card in edit mode for an existing object
		 */
		if (($id || $ref) && $action == 'edit') {
			print load_fiche_titre($langs->trans("EditPlot"), $object->label, 'object_plot@vignoble');
			
			print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
			print '<input type="hidden" name="action" value="update">';
			print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
			print '<input type="hidden" name="id" value="' . $object->id . '">';
			
			dol_fiche_head();
			print '<table class="border centpercent">' . "\n";
			
			print '<tr><td class="fieldrequired">' . $langs->trans("Fieldref") . '</td><td>';
			print '<input class="flat" type="text" name="ref" value="' . $object->ref . '">';
			print '</td></tr>';
			
			print '<tr><td>' . $langs->trans("Fieldlabel") . '</td><td>';
			print '<input class="flat" type="text" name="label" value="' . $object->label . '">';
			print '</td></tr>';
			
			print '<tr><td class="tdtop" width="25%">' . $langs->trans("Description") . '</td><td>';
			$doleditor = new DolEditor('description', $object->description, '', 160, 'dolibarr_notes', '', false, true, $conf->global->FCKEDITOR_ENABLE_SOCIETE, ROWS_4, '(50%');
			$doleditor->Create();
			print "</td></tr>";
			// Check if add
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
		
		/**
		 * - default view : card in view mode for an existing object when action is view, delete or none
		 */
		if (empty($action) || $action == 'view' || $action == 'delete') {
			/**
			 * - Display the card
			 */
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
			print '<tr><td class="tdtop" width="25%">' . $langs->trans("Fielddescription") . '</td><td>' . (dol_textishtml($object->description) ? $object->description : dol_nl2br($object->description, 1, true)) . '</td></tr>';
			if (! empty($extrafields->attribute_label)) {
				print $object->showOptionals($extrafields, 'view', $parameters);
			}
			print '</table>';
			
			dol_fiche_end();
			
			/**
			 * - Displays edit and delete buttons below the card
			 */
			print '<div class="tabsAction">' . "\n";
			$parameters = array();
			$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
			if ($reshook < 0)
				setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
			
			if (empty($reshook)) {
				if ($user->rights->vignoble->plot->create) {
					print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=edit">' . $langs->trans("Modify") . '</a></div>' . "\n";
				} else {
					print '<div class="inline-block divButAction"><a class="butActionRefused" href="#">' . $langs->trans('Modify') . '</a></div>' . "\n";
				}
				
				if ($user->rights->vignoble->plot->delete) {
					print '<div class="inline-block divButAction"><a class="butActionDelete" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=delete">' . $langs->trans('Delete') . '</a></div>' . "\n";
				} else {
					print '<div class="inline-block divButAction"><a class="butActionRefused" href="#">' . $langs->trans('Delete') . '</a></div>' . "\n";
				}
			}
			print '</div>' . "\n";
			
			/**
			 * - Display generic features
			 */
			print '<div class="fichecenter">'; // global frame
			print '<div class="fichehalfleft">'; // left column
			/**
			 * - Display generated documents
			 */
			// print 'Generated Documentss<br/>';
			// $ref = dol_sanitizeFileName($object->ref);
			// $file = $conf->vignoble->dir_output . '/' . $ref . '/' . $ref . '.pdf';
			// $relativepath = $ref . '/' . $ref . '.pdf';
			// $filedir = $conf->vignoble->dir_output . '/' . $ref;
			// $urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
			// $genallowed = $user->rights->vignoble->plot->create;
			// $delallowed = $user->rights->vignoble->plot->delete;
			// $somethingshown = $formfile->show_documents('vignoble', $ref, $filedir, $urlsource, $genallowed, $delallowed, $object->modelpdf, 1, 0, 0, 28, 0, '', '', '', $soc->default_lang);
			
			print '</div>'; // left column end
			print '<div class="fichehalfright">'; // right column
			/**
			 * - Display links to other objects (order or invoice)
			 */
			// print '<div class="ficheaddleft">';
			// print 'Linked Orders/Invoice<br/>';
			// $linktoelem = $form->showLinkToObjectBlock($object,array());
			// $somethingshown=$form->showLinkedObjectBlock($object,$linktoelem);
			// print '</div>';
			/**
			 * - Display links to events
			 */
			// print '<div class="ficheaddleft">';
			// print 'Linked Events<br/>';
			// include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
			// $formactions = new FormActions($db);
			// $somethingshown = $formactions->showactions($object, 'plot', $user->socid,1);
			// print '</div>';
			print '</div>'; // right column end
			print '</div>'; // fichecenter
		}
	}
} else { // Create operation
         // Security check
	if (! $user->rights->vignoble->plot->create)
		accessforbidden();
	
	if ($action == 'add' && ! $cancel) {
		$action = addPlot($object,$extrafields, $extrafieldslabels);
	} elseif ($cancel) {
		// TO DO Change destination
		$urltogo = $backtopage ? $backtopage : dol_buildpath('/vignoble/plot_list.php', 1);
		header("Location: " . $urltogo);
		exit();
	}
	
	llxHeader('', $langs->trans('PlotCardTitle'), '');
	
	print load_fiche_titre($langs->trans("NewPlot"), '', 'object_plot@vignoble');
	
	if ($action == 'create') {
		displayAddPlotForm($object, $extrafields);
	}
}
// End of page
llxFooter();
$db->close();

/**
 *
 * @param
 *        	backtopage
 * @param
 *        	object
 * @param
 *        	extrafields
 * @param
 *        	doleditor
 * @param
 *        	urltogo
 */
function addPlot(plot $object, ExtraFields $extrafields, $extrafieldslabels)
{
	Global $db, $conf, $user, $langs;
	
	$error = 0;
	
	$object->entity = $conf->entity;
	$object->ref = GETPOST('newref', 'alpha');
	if (empty($object->ref)) {
		$error ++;
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Ref")), null, 'errors');
	}
	$object->label = GETPOST('label', 'alpha');
	$object->description = dol_htmlcleanlastbr(GETPOST('description'));
	// Get extrafields values
	$ret = $extrafields->setOptionalsFromPost($extrafieldslabels, $object);
	if ($ret < 0)
		$error ++;
	
	$object->fk_user_author = $user->id;
	$object->fk_user_modif = $user->id;
	
	if (! $error) {
		$id = $object->create($user);
		if ($id > 0) {
			$urltogo = dol_buildpath('/vignoble/plot_card.php?id='.$id, 1);
			header("Location: " . $urltogo);
			exit();
		} else {
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}
	return $action = 'create';
}

/**
 * Display the form to enter a new plot
 * 
 * @param plot $object        	
 * @param ExtraFields $extrafields        	
 */
function displayAddPlotForm(plot $object, ExtraFields $extrafields)
{
	Global $db, $conf, $user, $langs;
	
	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="action" value="add">';
	
	dol_fiche_head();
	
	print '<table class="border centpercent">';
	// reference
	print '<tr><td class="fieldrequired">' . $langs->trans("Fieldref") . '</td><td>';
	print '<input class="flat" type="text" name="newref" value="' . GETPOST('newref') . '" >';
	print '</td></tr>';
	// label
	print '<tr><td>' . $langs->trans("Fieldlabel") . '</td><td>';
	print '<input class="flat" type="text" name="label" value="' . GETPOST('label') . '" >';
	print '</td></tr>';
	// description (with editor)
	print '<tr><td class="tdtop" width="25%">' . $langs->trans("Description") . '</td><td>';
	$doleditor = new DolEditor('description', GETPOST('description'), '', 160, 'dolibarr_notes', '', false, true, $conf->global->FCKEDITOR_ENABLE_SOCIETE, ROWS_4, '99%');
	$doleditor->Create();
	print "</td></tr>";
	// extrafields
	if (! empty($extrafields->attribute_label)) {
		print $object->showOptionals($extrafields, 'edit');
	}
	
	print '</table>';
	
	dol_fiche_end();
	
	// action buttons
	print '<div class="center">';
	print '<input type="submit" class="button" name="add" value="' . $langs->trans("Create") . '"> &nbsp;';
	print '<input type="submit" class="button" name="cancel" value="' . $langs->trans("Cancel") . '">';
	print '</div>';
	
	print '</form>';
}




