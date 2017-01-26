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
$tab = GETPOST('tab', 'alpha');

// Security check
if (! $user->rights->vignoble->plot->read)
	accessforbidden();
	
	// Plot classes for C R U D operations
$object = new plot($db);
$extrafields = new ExtraFields($db);
// get $extrafields->attribute_label populated
$extrafields->fetch_name_optionals_label($object->table_element);

if (($id > 0 || ! empty($ref))) { // R U D operation
	if ($object->fetch($id, $ref) >= 0) {
		// card actions
		if ($tab == 'card') {
			if ($action == 'update' && ! $cancel) {
				$action = updatePlot($object, $extrafields);
			} elseif ($cancel)
				$action = 'view';
			
			if ($action == 'confirm_delete') {
				deletePlot($object);
			}
			
			if ($action == 'builddoc') {
				buildPlotDocument($object);
			}
			
			if ($action == 'remove_file') {
				removePlotFile($object);
			}
		}
		// notes actions
		if ($tab == 'notes') {
			$permissionnote = $user->rights->vignoble->plot->create;
			include DOL_DOCUMENT_ROOT . '/core/actions_setnotes.inc.php';
		}
		// View
		llxHeader('', $langs->trans('PlotCardTitle'), '');
		
		if ($action == 'edit') {
			displayPlotEditForm($object, $extrafields);
		} else {
			displayPlotTab($action, $object, $extrafields);
		}
		llxFooter();
	}
} else { // We assume create operation
         // Security check
	if (! $user->rights->vignoble->plot->create)
		accessforbidden();
		// actions
	if ($action == 'add' && ! $cancel) {
		$action = addPlot($object, $extrafields);
	} elseif ($cancel) {
		header("Location: " . dol_buildpath('/vignoble/plot_list.php', 1));
		exit();
	}
	// view
	llxHeader('', $langs->trans('PlotCardTitle'), '');
	
	print load_fiche_titre($langs->trans("NewPlot"), '', 'object_plot@vignoble');
	
	if ($action == 'create') {
		displayAddPlotForm($object, $extrafields);
	}
	llxFooter();
}
$db->close();

/**
 * Add plot entered in Add Plot Form to the database
 *
 * @param plot $object        	
 * @param ExtraFields $extrafields        	
 * @return string
 */
function addPlot(plot $object, ExtraFields $extrafields)
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
	$ret = $extrafields->setOptionalsFromPost($extrafields->attribute_label, $object);
	if ($ret < 0)
		$error ++;
	
	$object->fk_user_author = $user->id;
	$object->fk_user_modif = $user->id;
	
	if (! $error) {
		$id = $object->create($user);
		if ($id > 0) {
			header("Location: " . dol_buildpath('/vignoble/plot_card.php?tab=card&id=' . $id, 1));
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
	
	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?tab=card">';
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

/**
 * Update Plot information in database using the Plot Edit Form data
 *
 * @param plot $object        	
 * @param ExtraFields $extrafields        	
 * @return string $action 'view' when update is done | 'edit' if fields are not properly entered
 */
function updatePlot(plot $object, ExtraFields $extrafields)
{
	Global $db, $conf, $user, $langs;
	
	$error = 0;
	// Get attributes values
	$object->id = GETPOST('id', 'int');
	$object->ref = GETPOST('ref', 'alpha');
	$object->label = GETPOST('label', 'alpha');
	$object->description = dol_htmlcleanlastbr(GETPOST('description'));
	// Check values
	if (empty($object->ref)) {
		$error ++;
		setEventMessages($langs->transnoentitiesnoconv("ErrorFieldRequired", $langs->transnoentitiesnoconv("Ref")), null, 'errors');
	}
	// Get and check extrafields
	$ret = $extrafields->setOptionalsFromPost($extrafields->attribute_label, $object);
	if ($ret < 0)
		$error ++;
	
	if (! $error) {
		$result = $object->update($user);
		if ($result > 0) {
			return $action = 'view';
		} else {
			if (! empty($object->errors))
				setEventMessages(null, $object->errors, 'errors');
			else
				setEventMessages($object->error, null, 'errors');
			return $action = 'edit';
		}
	} else {
		return $action = 'edit';
	}
}

/**
 * Delete selected plot from database
 *
 * ! No control is made on plot use !
 *
 * @param plot $object        	
 */
function deletePlot(plot $object)
{
	Global $db, $conf, $user, $langs;
	
	$result = $object->delete($user);
	if ($result > 0) {
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

/**
 *
 * @param
 *        	object
 */
function buildPlotDocument(plot $object)
{
	Global $db, $conf, $user, $langs;
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

/**
 * Remove file associated to current object
 *
 * File id is provided in Get Post
 *
 * @param plot $object        	
 */
function removePlotFile(plot $object)
{
	Global $db, $conf, $user, $langs;
	
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
	}
}

/**
 * display the Plot Edit Form
 *
 * @param plot $object        	
 * @param ExtraFields $extrafields        	
 */
function displayPlotEditForm(plot $object, ExtraFields $extrafields)
{
	Global $db, $conf, $user, $langs;
	
	print load_fiche_titre($langs->trans("EditPlot"), $object->label, 'object_plot@vignoble');
	
	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?tab=card&id=' . $object->id . '">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="' . $object->id . '">';
	
	dol_fiche_head();
	print '<table class="border centpercent">';
	// ref
	print '<tr><td class="fieldrequired">' . $langs->trans("Fieldref") . '</td><td>';
	print '<input class="flat" type="text" name="ref" value="' . $object->ref . '">';
	print '</td></tr>';
	// label
	print '<tr><td>' . $langs->trans("Fieldlabel") . '</td><td>';
	print '<input class="flat" type="text" name="label" value="' . $object->label . '">';
	print '</td></tr>';
	// description
	print '<tr><td class="tdtop" width="25%">' . $langs->trans("Description") . '</td><td>';
	$doleditor = new DolEditor('description', $object->description, '', 160, 'dolibarr_notes', '', false, true, $conf->global->FCKEDITOR_ENABLE_SOCIETE, ROWS_4, '(50%');
	$doleditor->Create();
	print "</td></tr>";
	// extrafields
	if (! empty($extrafields->attribute_label)) {
		print $object->showOptionals($extrafields, 'edit');
	}
	
	print '</table>';
	dol_fiche_end();
	
	print '<div class="center"><input type="submit" class="button" name="save" value="' . $langs->trans("Save") . '">';
	print ' &nbsp; <input type="submit" class="button" name="cancel" value="' . $langs->trans("Cancel") . '">';
	print '</div>';
	
	print '</form>';
}

/**
 *
 * @param
 *        	action
 * @param
 *        	object
 * @param
 *        	form
 * @param
 *        	formvignoble
 * @param
 *        	formconfirm
 */
function displayPlotTab($action, plot $object, ExtraFields $extrafields)
{
	Global $db, $conf, $user, $langs;
	
	$tab = GETPOST('tab', 'alpha');
	
	$form = new Form($db);
	$formvignoble = new FormVignoble($db);
	
	$head = getTabsHeader($langs, $object);
	dol_fiche_head($head, $tab, $langs->trans("Plot"), 0, 'plot@vignoble');
	// object header
	$formvignoble->printObjectRef($form, $langs, $object);
	
	switch ($tab) {
		case 'tasks':
			displayPlotTasks($object, $form);
			break;
		case 'notes':
			$permission = $user->rights->vignoble->plot->create;
			$permissionnote = $user->rights->vignoble->plot->create;
			$cssclass = "titlefield";
			$moreparam = "&tab=notes";
			include DOL_DOCUMENT_ROOT . '/core/tpl/notes.tpl.php';
			break;
		case 'info':
			$object->info($object->id);
			dol_print_object_info($object, 1);
			break;
		default:
			displayPlotCard($action, $object, $extrafields, $form);
			break;
	}
	dol_fiche_end();
}

/**
 *
 * @param unknown $action        	
 * @param plot $object        	
 * @param ExtraFields $extrafields        	
 * @param Form $form        	
 */
function displayPlotCard($action, plot $object, ExtraFields $extrafields, Form $form)
{
	Global $db, $conf, $user, $langs;
	
	print '<table class="border centpercent">';
	if ($action == 'delete') {
		print $form->formconfirm($_SERVER["PHP_SELF"] . '?tab=card&id=' . $object->id, $langs->trans('DeletePlot'), $langs->trans('ConfirmDeletePlot') . ' ' . $object->ref, 'confirm_delete', '', 0, 1);
	}
	// description
	print '<tr>';
	print '<td class="tdtop" width="25%">' . $langs->trans("Fielddescription") . '</td>';
	print '<td>' . (dol_textishtml($object->description) ? $object->description : dol_nl2br($object->description, 1, true)) . '</td>';
	print '</tr>';
	// Extrafields
	if (! empty($extrafields->attribute_label)) {
		print $object->showOptionals($extrafields, 'view');
	}
	print '</table>';
	// edit and delete buttons below the card
	print '<div class="tabsAction">';
	$parameters = array();
	
	if ($user->rights->vignoble->plot->create) {
		print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?tab=card&id=' . $object->id . '&amp;action=edit">' . $langs->trans("Modify") . '</a></div>' . "\n";
	} else {
		print '<div class="inline-block divButAction"><a class="butActionRefused" href="#">' . $langs->trans('Modify') . '</a></div>' . "\n";
	}
	if ($user->rights->vignoble->plot->delete) {
		print '<div class="inline-block divButAction"><a class="butActionDelete" href="' . $_SERVER["PHP_SELF"] . '?tab=card&id=' . $object->id . '&amp;action=delete">' . $langs->trans('Delete') . '</a></div>' . "\n";
	} else {
		print '<div class="inline-block divButAction"><a class="butActionRefused" href="#">' . $langs->trans('Delete') . '</a></div>' . "\n";
	}
	print '</div>';
}

function displayPlotTasks(plot $plot, Form $form)
{
	Global $db, $conf, $user, $langs;
	
	// List of tasks associated to plot
	$sort = getTasksort();
	$filter = getTaskfilter($plot->id);
	$params = buildTaskSearchParameters($filter);
	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?tab=tasks&id=' . $plot->id . '">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="id" value="' . $plot->id . '">';
	print '<input type="hidden" name="sortfield" value="' . $sort["field"] . '">';
	print '<input type="hidden" name="sortorder" value="' . $sort["order"] . '">';
	print '<input type="hidden" name="action" value="' . $action . '">';
	
	print '<div class="div-table-responsive">';
	print '<table class="liste" style="border-bottom-style: none;">';
	
	// Fields header
	print '<tr class="liste_titre">';
	print_liste_field_titre($langs->trans("Task"), $_SERVER['PHP_SELF'], 'taskref', '', $params, '', $sort["field"], $sort["order"]);
	print_liste_field_titre($langs->trans("Start"), $_SERVER['PHP_SELF'], 'taskopen', '', $params, '', $sort["field"], $sort["order"]);
	print_liste_field_titre($langs->trans("End"), $_SERVER['PHP_SELF'], 'taskend', '', $params, '', $sort["field"], $sort["order"]);
	print_liste_field_titre($langs->trans("Note"), $_SERVER['PHP_SELF'], 'note', '', $params, 'style="width:20%;"', $sort["field"], $sort["order"]);
	print_liste_field_titre($langs->trans("ProgressDeclared"), $_SERVER['PHP_SELF'], 'coverage', '', $params, '', $sort["field"], $sort["order"]);
	print '<td class=" right"> ';
	print '</td>';
	print "</tr>";
	// Search Header
	print '<tr class="liste_titre">';
	print '<td ><input type="text" class="flat" name="search_tasklabel" value="' . $filter["tasklabel"] . '"> </td>';
	print '<td >' . $form->select_date((empty($filter['begin']) ? - 1 : $filter['begin']), 'search_begin', 0, 0, 2, "search_begin", 1, 0, 1) . '</td>';
	print '<td >' . $form->select_date((empty($filter['end']) ? - 1 : $filter['end']), 'search_end', 0, 0, 2, "search_end", 1, 0, 1) . '</td>';
	print '<td ><input type="text" class="flat" name="search_note" value="' . $filter["note"] . '"></td>';
	print '<td ><input type="text" class="flat" name="search_coverage" value="' . $filter["coverage"] . '"></td>';
	// // Action column
	print '<td class=" right">';
	print $form->showFilterAndCheckAddButtons(0, 'checkforselect', 1);
	print '</td>';
	print '</tr>';

	$plottask = new Plotcultivationtask($db);
	if ($plottask->fetchAll($sort["order"], $sort["field"], 0, 0, $filter["plot"], 'AND')) {
		displayPlotTaskLines($plottask);
	}
	print '</table>';
	print '</div>';
	print '</form>';


}

/**
 * Get fields and order used for the plot task table sort.
 *
 * Use Ref Ascending by default.
 *
 * @return Array[] with keys : field, order.
 */
function getTasksort()
{
	$sortfield = GETPOST(sortfield, 'alpha');
	if (empty($sortfield)) {
		$sortfield = 'taskref';
	}
	$sortorder = GETPOST(sortorder, 'alpha');
	if (empty($sortorder)) {
		$sortorder = 'ASC';
	}
	return $sort = array(
		"field" => $sortfield,
		"order" => $sortorder
	);
}

/**
 * Get all data needed to filter the SQL requests on plot task and produce the results
 *
 * @param int $id
 *        	the current plot id
 * @return Array[] containing the following keys :
 *         id (of the task),
 *         reference (of the task),
 *         note,
 *         coverage,
 *         plot (array of sql filter conditions for plot task),
 *        
 */
function getTaskfilter($id)
{
	$plotfilter = array();
	if (! empty($id))
		$plotfilter[] = "t.fk_plot = " . $id;
		
		// Purge search criteria
	if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter.x") || GETPOST("button_removefilter")) { // All test are required to be compatible with all browsers
		$search_note = '';
		$search_coverage = '';
		$search_tasklabel = '';
		$search_begin = '';
		$search_beginday = '';
		$search_beginmonth = '';
		$search_beginyear = '';
		$search_end = '';
		$search_endday = '';
		$search_endmonth = '';
		$search_endyear = '';
	} else {
		$search_tasklabel = GETPOST('search_tasklabel', 'alpha');
		if (! empty($search_tasklabel))
			$plotfilter[] = "task.label LIKE '%" . $search_tasklabel . "%'";
		
		$search_note = GETPOST('search_note', 'alpha');
		if (! empty($search_note))
			$plotfilter[] = "t.note LIKE '%" . $search_note . "%'";
		
		$search_coverage = GETPOST('search_coverage', 'int');
		if (! ($search_coverage === "") || ($search_coverage > 0))
			$plotfilter[] = "t.coverage = " . $search_coverage;
		
		$search_begin = GETPOST("search_beginyear") . '-' . GETPOST("search_beginmonth") . '-' . GETPOST("search_beginday");
		if ($search_begin == '--') { // not in Form check URL
			$search_begin = GETPOST("search_begin");
		}
		if (! empty($search_begin))
			$plotfilter[] = "DATE(task.dateo) >= '" . $search_begin . "'";
		
		$search_end = GETPOST("search_endyear") . '-' . GETPOST("search_endmonth") . '-' . GETPOST("search_endday");
		if ($search_end == '--') { // not in Form check URL
			$search_end = GETPOST("search_end");
		}
		if (! empty($search_end))
			$plotfilter[] = "DATE(task.datee) <= '" . $search_end."'";
	}
	$filter = array(
		"id" => $id,
		"tasklabel" => $search_tasklabel,
		"begin" => $search_begin,
		"end" => $search_end,
		"note" => $search_note,
		"coverage" => $search_coverage,
		"plot" => $plotfilter
	);
	return $filter;
}

/**
 * Build the parameters string to be added to URL to keep the filter conditions.
 *
 * (used for list sort)
 *
 * @param Array $filter
 *        	the filter conditions including $id
 * @return string to be added to URL
 */
function buildTaskSearchParameters($filter)
{
	$params = "";
	if (! empty($filter["id"]))
		$params .= '&amp;tab=tasks&amp;id=' . $filter["id"];
	if (! empty($filter["reference"]))
		$params .= '&amp;search_reference=' . urlencode($filter["reference"]);
	if (! empty($filter["note"]))
		$params .= '&amp;search_note=' . urlencode($filter["note"]);
	if (! empty($filter["coverage"]))
		$params .= '&amp;search_coverage=' . urlencode($filter["coverage"]);
	
	return $params;
}

/**
 * Display the plot task lines in a table in read only mode.
 *
 * Each line display Task ref and label, note and coverage.
 *
 * @param $plottask the
 *        	result of the SQL query on plot task
 */
function displayPlotTaskLines($plottask)
{
	Global $db, $conf, $user, $langs;
	
	foreach ($plottask->lines as $line) {
		
		$var = ! $var;
		print "<tr " . $bc[$var] . ">";
		// Task
		$task = new Task($db);
		$task->fetch($line->fk_task);
		print '<td >';
		print $task->getNomUrl(1, 'projet_task', 'task', 1, ' - ');
		print '</td>';
		// Start date
		print '<td >';
		print dol_print_date($task->date_start);
		print '</td>';
		// End date
		print '<td >';
		print dol_print_date($task->date_end);
		print '</td>';
		// Note
		print '<td >';
		print dol_nl2br($line->note);
		print '</td>';
		// Coverage
		print '<td>';
		print $line->coverage . '%';
		print '</td>';
		
		print '</tr>';
	}
}

/**
 * Set up 3 Tabs : Card, Notes, Info
 */
function getTabsHeader($langs, $object)
{
	$head = array();
	$h = 0;
	$head[$h][0] = 'plot_card.php?tab=card&id=' . $object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$h ++;
	$head[$h][0] = 'plot_card.php?tab=tasks&id=' . $object->id;
	$head[$h][1] = $langs->trans("Tasks");
	$head[$h][2] = 'tasks';
	$h ++;
	$head[$h][0] = 'plot_card.php?tab=notes&id=' . $object->id;
	$head[$h][1] = $langs->trans("Notes");
	$head[$h][2] = 'notes';
	$h ++;
	$head[$h][0] = 'plot_card.php?tab=info&id=' . $object->id;
	$head[$h][1] = $langs->trans("Info");
	$head[$h][2] = 'info';
	return $head;
}
// $formfile = new FormFile($db);
	// $formactions = new FormActions($db);
	// /**
	// * - Display generic features
	// */
	// print '<div class="fichecenter">'; // global frame
	// print '<div class="fichehalfleft">'; // left column
	// /**
	// * - Display generated documents
	// */
	// // print 'Generated Documentss<br/>';
	// // $ref = dol_sanitizeFileName($object->ref);
	// // $file = $conf->vignoble->dir_output . '/' . $ref . '/' . $ref . '.pdf';
	// // $relativepath = $ref . '/' . $ref . '.pdf';
	// // $filedir = $conf->vignoble->dir_output . '/' . $ref;
	// // $urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
	// // $genallowed = $user->rights->vignoble->plot->create;
	// // $delallowed = $user->rights->vignoble->plot->delete;
	// // $somethingshown = $formfile->show_documents('vignoble', $ref, $filedir, $urlsource, $genallowed, $delallowed, $object->modelpdf, 1, 0, 0, 28, 0, '', '', '', $soc->default_lang);
	
	// print '</div>'; // left column end
	// print '<div class="fichehalfright">'; // right column
	// /**
	// * - Display links to other objects (order or invoice)
	// */
	// // print '<div class="ficheaddleft">';
	// // print 'Linked Orders/Invoice<br/>';
	// // $linktoelem = $form->showLinkToObjectBlock($object,array());
	// // $somethingshown=$form->showLinkedObjectBlock($object,$linktoelem);
	// // print '</div>';
	// /**
	// * - Display links to events
	// */
	// // print '<div class="ficheaddleft">';
	// // print 'Linked Events<br/>';
	// // include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
	// // $formactions = new FormActions($db);
	// // $somethingshown = $formactions->showactions($object, 'plot', $user->socid,1);
	// // print '</div>';
	// print '</div>'; // right column end
	// print '</div>'; // fichecenter



