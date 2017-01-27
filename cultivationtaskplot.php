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
 * \file cultivationtaskplot.php
 * \ingroup cultivation
 * \brief plots target for a cultivation project task
 */
@include './tpl/maindolibarr.inc.php';

@include './tpl/cultivationtask.inc.php';

$cultivationprojectid = setIsCultivationProject();

// Current Task id and/or Ref
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
// Page parameters
$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'alpha');

// Security check
if (! $user->rights->projet->lire)
	accessforbidden();

if (($id > 0 || ! empty($ref))) {
	$object = new Task($db);
	if ($object->fetch($id, $ref) >= 0) {
		
		$projectstatic = new Project($db);
		$result = $projectstatic->fetch($object->fk_project);
		$object->project = clone $projectstatic;
		if ($projectstatic->id == $cultivationprojectid) {
			/**
			 * Actions
			 */
			
			if ($action == 'addplot' && $user->rights->projet->lire) {
				$action = addPlotTask($object->id);
			}
			
			if ($action == 'updateline' && ! $cancel && $user->rights->projet->creer) {
				$action = updatePlotTask();
			}
			
			if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->projet->creer) {
				$action = deletePlotTask();
			}
			
			/**
			 * Display View
			 */
			llxHeader("", $langs->trans("Task"));
			
			$form = new Form($db);
			$formother = new FormOther($db);
			
			displayProjectHeaderCard($projectstatic, $form);
			
			print '<div class="fiche">'; // Task & Plots Tab
			
			$head = task_prepare_head($object);
			dol_fiche_head($head, 'cultivationtaskplot', $langs->trans("Plot"), 0, 'projecttask');
			
			displayTaskHeader($object, $projectstatic, $form);
			
			if ($action == 'deleteline') {
				// display confirmation dialog
				$lineid = GETPOST('lineid', 'int');
				$currplot = new Plotcultivationtask($db);
				if ($currplot->fetch($lineid)) {
					$plot = new plot($db);
					$plot->fetch($currplot->fk_plot);
					print $form->formconfirm($_SERVER["PHP_SELF"] . "?id=" . $object->id . '&lineid=' . $lineid, $langs->trans("DeleteLinktoPlot"), $langs->trans("ConfirmDeleteLinktoPlot") . ' ' . $plot->ref, "confirm_delete", '', '', 1);
				}
			}
			if ($user->rights->projet->creer) {
				displayAddPlotForm($object->id);
			}
			// List of plots associated to task
			$sort = getsort();
			$filter = getfilter($object->id);
			$params = buildSearchParameters($filter);
			
			print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '">';
			print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
			print '<input type="hidden" name="id" value="' . $object->id . '">';
			print '<input type="hidden" name="sortfield" value="' . $sort["field"] . '">';
			print '<input type="hidden" name="sortorder" value="' . $sort["order"] . '">';
			if ($action == 'editline')
				print '<input type="hidden" name="action" value="updateline">';
			else
				print '<input type="hidden" name="action" value="' . $action . '">';
			
			print '<div class="div-table-responsive">';
			
			print '<table class="liste" style="border-bottom-style: none;">';
			
			// Fields header
			print '<tr class="liste_titre">';
			print_liste_field_titre($langs->trans("Plot"), $_SERVER['PHP_SELF'], 'reference', '', $params, 'style="width:40%;"', $sort["field"], $sort["order"]);
			print_liste_field_titre($langs->trans("Note"), $_SERVER['PHP_SELF'], 'note', '', $params, 'style="width:40%;"', $sort["field"], $sort["order"]);
			print_liste_field_titre($langs->trans("ProgressDeclared"), $_SERVER['PHP_SELF'], 'coverage', '', $params, 'style="width:10%;"', $sort["field"], $sort["order"]);
			print '<td style="width:10%;">&nbsp</td>';
			print "</tr>";
			// Search Header
			print '<tr class="liste_titre">';
			print '<td ><input type="text" class="flat" name="search_reference" value="' . $filter["reference"] . '"> </td>';
			print '<td ><input type="text" class="flat" name="search_note" value="' . $filter["note"] . '"></td>';
			print '<td ><input type="text" class="flat" name="search_coverage" value="' . $filter["coverage"] . '"></td>';
			// Action column
			print '<td class=" right">';
			print $form->showFilterAndCheckAddButtons(0, 'checkforselect', 1);
			print '</td>';
			print '</tr>';
			
			$plottask = new Plotcultivationtask($db);
			if ($plottask->fetchAll($sort["order"], $sort["field"], 0, 0, $filter["plot"], 'AND')) {
				displayPlotTaskLines($action, $formother, $plottask);
			}
			print '</table>';
			print '</div>';
			print '</form>';
			print '<div>'; // end Task & Plots part
		}
	}
}

llxFooter();
$db->close();
// End

/**
 * Add plot(s) to the task using the add plots form data
 *
 * @param int $id
 *        	the Id of the current task.
 *        	
 * @return string $action empty
 */
function addPlotTask($id)
{
	Global $db, $conf, $user, $langs;
	
	$error = 0;
	$multiplots = GETPOST('multiplots', 'array');
	$note = GETPOST('addnote', 'alpha');
	$coverage = GETPOST('addcoverage', 'int');
	// var_dump($multiplots);var_dump($note);var_dump($coverage);
	if (empty($multiplots)) {
		setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv("Plots")), null, 'errors');
		$error ++;
	}
	
	if (! $error) {
		$object = new Task($db);
		$object->fetch($id); // $id and or $ref are in form
		$object->fetch_projet();
		
		$plot = new plot($db);
		$plotcultivation = new Plotcultivationtask($db);
		$all = array_search(0, $multiplots);
		// var_dump($all);
		if ($all === false) { // list of plot in array
			foreach ($multiplots as $plotid) {
				$plotcultivation->fk_plot = $plotid;
				$plotcultivation->fk_task = $object->id;
				$plotcultivation->note = $note;
				$plotcultivation->coverage = $coverage;
				$result = $plotcultivation->create($user);
			}
		} else { // all plots selected
			$result = $plot->fetchAll('ASC', 'ref');
			foreach ($plot->lines as $plotLine) {
				$plotcultivation->fk_plot = $plotLine->id;
				$plotcultivation->fk_task = $object->id;
				$plotcultivation->note = $note;
				$plotcultivation->coverage = $coverage;
				$result = $plotcultivation->create($user);
			}
			;
		}
	}
	if ($result >= 0) {
		setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
	} else {
		setEventMessages(null, $langs->trans($plotcultivation->errors), 'errors');
	}
	return $action = '';
}

/**
 * update plot line of task using plot line form data
 *
 * @return string $action empty
 */
function updatePlotTask()
{
	Global $db, $conf, $user, $langs;
	
	$error = 0;
	$plotcultivation = new Plotcultivationtask($db);
	$lineid = GETPOST('lineid', int);
	if ($plotcultivation->fetch($lineid)) {
		$plotcultivation->note = GETPOST('linenote', 'alpha');
		$plotcultivation->coverage = GETPOST('linecoverage', 'int');
		$result = $plotcultivation->update($user);
	} else
		$result = - 1;
	
	if ($result >= 0) {
		setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
	} else {
		setEventMessages(null, $langs->trans($plotcultivation->errors), 'errors');
	}
	return $action = '';
}

/**
 * delete plot line of task using plot line form data
 *
 * @return string $action empty
 */
function deletePlotTask()
{
	Global $db, $conf, $user, $langs;
	
	$plotcultivation = new Plotcultivationtask($db);
	$lineid = GETPOST('lineid', int);
	if ($plotcultivation->fetch($lineid)) {
		$result = $plotcultivation->delete($user);
	} else
		$result = - 1;
	
	if ($result < 0) {
		$langs->load("errors");
		setEventMessages(null, $langs->trans($plotcultivation->errors), 'errors');
		$error ++;
	}
	return $action = '';
}

/**
 * Display the add plot form to associate one or more plots to the task
 *
 * The form includes a multi selection list for plots with a note and a progress declaration.
 *
 * @param int $id
 *        	the current task id
 *        	
 */
function displayAddPlotForm($id)
{
	Global $db, $conf, $user, $langs;
	
	$form = new Form($db);
	$formother = new FormOther($db);
	
	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="addplot">';
	print '<input type="hidden" name="id" value="' . $id . '">';
	
	print '<table class="noborder" width="100%">';
	
	print '<tr class="liste_titre">';
	print '<td style="width:40%;">' . $langs->trans("Plots") . ' (' . $langs->trans("Add") . ')</td>';
	print '<td style="width:40%;">' . $langs->trans("Note") . '</td>';
	print '<td style="width:10%;">' . $langs->trans("ProgressDeclared") . '</td>';
	print '<td style="width:10%;">' . " &nbsp " . '</td>';
	print "</tr>";
	
	print '<tr>';
	// Plot selection
	print '<td>';
	$plot = new plot($db);
	if ($plot->fetchAll("ASC", "ref") > 0) {
		$plots = array(
			'0' => $langs->trans("All")
		);
		foreach ($plot->lines as $plotLine) {
			$key = $plotLine->id;
			$value = $plotLine->ref.' - '.$plotLine->label;
			$plots[$key] = $value;
		}
		print $form->multiselectarray('multiplots', $plots, '', 0, 0, '', 0, '90%');
	}
	print '</td>';
	// Note
	print '<td>';
	print '<textarea name="addnote" style="width:90%;" rows="' . ROWS_1 . '">' . (GETPOST('addnote', 'alpha') ? GETPOST('addnote', 'alpha') : '') . '</textarea>';
	print '</td>';
	// Coverage declared
	print '<td>';
	print $formother->select_percent(GETPOST('addcoverage', 'int') ? GETPOST('addcoverage') : $object->coverage, 'addcoverage');
	print '</td>';
	// Submit button
	print '<td style="text-align:right;">';
	print '<input type="submit" class="button" value="' . $langs->trans("Add") . '">';
	print '</td>';
	
	print '</tr>';
	print '</table></form>';
}

/**
 * Display the plot task lines in a table with the capability to edit or delete a line.
 *
 * Each line display plot ref and label, note and coverage.
 *
 * @param
 *        	action when value is 'editline' the corresponding line is in edit mode
 * @param
 *        	formother needed to use the select a percentage control
 * @param $plottask the
 *        	result of the SQL query on plot task
 */
function displayPlotTaskLines($action, $formother, $plottask)
{
	Global $db, $conf, $user, $langs;
	
	foreach ($plottask->lines as $line) {
		
		$var = ! $var;
		print "<tr " . $bc[$var] . ">";
		
		// Plot url
		$plot = new plot($db);
		$plot->fetch($line->fk_plot);
		print '<td >';
		print $plot->getNomUrl(1, 'plot') . " - " . $plot->label;
		print '</td>';
		// Note
		print '<td >';
		if ($action == 'editline' && GETPOST('lineid') == $line->id) {
			print '<textarea name="linenote" style="width:90%;" rows="' . ROWS_1 . '">' . $line->note . '</textarea>';
		} else {
			print dol_nl2br($line->note);
		}
		print '</td>';
		// Coverage
		print '<td>';
		if ($action == 'editline' && GETPOST('lineid') == $line->id) {
			print '<input type="hidden" name="old_coverage" value="' . $line->coverage . '">';
			print $formother->select_percent(GETPOST('linecoverage', 'int') ? GETPOST('linecoverage') : $line->coverage, 'linecoverage');
		} else {
			print $line->coverage . '%';
		}
		print '</td>';
		// Action column
		print '<td class="right" valign="middle" >';
		if ($action == 'editline' && GETPOST('lineid') == $line->id) {
			print '<input type="hidden" name="lineid" value="' . GETPOST('lineid') . '">';
			print '<input type="submit" class="button" name="save" value="' . $langs->trans("Save") . '">';
			print '<input type="submit" class="button" name="cancel" value="' . $langs->trans('Cancel') . '">';
		} else 
			if ($user->rights->projet->creer) {
				print '&nbsp;';
				print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $line->fk_task . '&amp;action=editline&amp;lineid=' . $line->id . '">';
				print img_edit();
				print '</a>';
				
				print '&nbsp;';
				print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $line->fk_task . '&amp;action=deleteline&amp;lineid=' . $line->id . '">';
				print img_delete();
				print '</a>';
			}
		print '</td>';
		
		print '</tr>';
	}
}

/**
 * Get fields and order used for the plot task table sort.
 *
 * Use Ref Ascending by default.
 *
 * @return Array[] with keys : field, order.
 */
function getsort()
{
	$sortfield = GETPOST(sortfield, 'alpha');
	if (empty($sortfield)) {
		$sortfield = 'reference';
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
 *        	the current task id
 * @return Array[] containing the following keys :
 *         id (of the task),
 *         reference (of the plot),
 *         note,
 *         coverage,
 *         plot (array of sql filter conditions for plot task),
 *        
 */
function getfilter($id)
{
	$plotfilter = array();
	if (! empty($id))
		$plotfilter[] = "t.fk_task = " . $id;
		
		// Purge search criteria
	if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter.x") || GETPOST("button_removefilter")) { // All test are required to be compatible with all browsers
		$search_note = '';
		$search_coverage = '';
		$search_reference = '';
	} else {
		$search_reference = GETPOST('search_reference', 'alpha');
		if (! empty($search_reference))
			$plotfilter[] = "plot.ref LIKE '%" . $search_reference . "%'";
		
		$search_note = GETPOST('search_note', 'alpha');
		if (! empty($search_note))
			$plotfilter[] = "t.note LIKE '%" . $search_note . "%'";
		
		$search_coverage = GETPOST('search_coverage', 'int');
		if (! ($search_coverage === "") || ($search_coverage > 0))
			$plotfilter[] = "t.coverage = " . $search_coverage;
	}
	$filter = array(
		"id" => $id,
		"reference" => $search_reference,
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
function buildSearchParameters($filter)
{
	$params = "";
	if (! empty($filter["id"]))
		$params .= '&amp;id=' . $filter["id"];
	if (! empty($filter["reference"]))
		$params .= '&amp;search_reference=' . urlencode($filter["reference"]);
	if (! empty($filter["note"]))
		$params .= '&amp;search_note=' . urlencode($filter["note"]);
	if (! empty($filter["coverage"]))
		$params .= '&amp;search_coverage=' . urlencode($filter["coverage"]);
	
	return $params;
}



