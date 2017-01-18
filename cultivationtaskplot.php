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

$search_note = GETPOST('search_note', 'alpha');
$search_coverage = GETPOST('search_coverage', 'int');
$search_value = GETPOST('search_value', 'int');

// Security check
if (! $user->rights->projet->lire)
	accessforbidden();

$limit = GETPOST("limit") ? GETPOST("limit", "int") : $conf->liste_limit;
$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');
$page = GETPOST("page", 'int');
if ($page == - 1) {
	$page = 0;
}
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortfield)
	$sortfield = 't.fk_task,t.fk_plot,t.rowid';
if (! $sortorder)
	$sortorder = 'DESC';

$object = new Task($db);
$projectstatic = new Project($db);

/**
 * Actions
 */

$parameters = array(
	'socid' => $socid,
	'projectid' => $cultivationprojectid
);

include DOL_DOCUMENT_ROOT . '/core/actions_changeselectedfields.inc.php';

// Purge search criteria
if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter.x") || GETPOST("button_removefilter")) // All test are required to be compatible with all browsers
{
	$search_note = '';
	$search_coverage = '';
	$search_value = '';
	$toselect = '';
	$search_array_options = array();
	$action = '';
}

if ($action == 'addplot' && $user->rights->projet->lire) {
	$action = addPlotTask($id);
}

if ($action == 'updateline' && ! $_POST["cancel"] && $user->rights->projet->creer) {
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

if (($id > 0 || ! empty($ref))) {
	if ($object->fetch($id, $ref) >= 0) {
		
		$result = $projectstatic->fetch($object->fk_project);
		$object->project = clone $projectstatic;
		if ($projectstatic->id == $cultivationprojectid) {
			
			displayProjectHeaderCard($projectstatic, $form);
			
			print '<div class="fiche">';
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
				displayAddPlotForm($id);
			}
			
			// Definition of fields for Plot list
			$arrayfields = array();
			$arrayfields['t.plot'] = array(
				'label' => $langs->trans("Plot"),
				'checked' => 1
			);
			$arrayfields['t.note'] = array(
				'label' => $langs->trans("Note"),
				'checked' => 1
			);
			$arrayfields['t.coverage'] = array(
				'label' => $langs->trans("ProgressDeclared"),
				'checked' => 1
			);
			
			/*
			 * List of plots and coverage
			 */
			
			$arrayofselected = is_array($toselect) ? $toselect : array();
			
			$params = '';
			if (! empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"])
				$param .= '&contextpage=' . $contextpage;
			if ($limit > 0 && $limit != $conf->liste_limit)
				$param .= '&limit=' . $limit;
			if ($search_plot != '')
				$params .= '&amp;search_plot=' . urlencode($search_plot);
			if ($search_note != '')
				$params .= '&amp;search_note=' . urlencode($search_note);
			if ($search_coverage != '')
				$params .= '&amp;search_coverage=' . urlencode($search_coverage);
			if ($optioncss != '')
				$param .= '&optioncss=' . $optioncss;			
			if ($id)
				$params .= '&amp;id=' . $id;
			
			print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '">';
			
			if ($optioncss != '')
				print '<input type="hidden" name="optioncss" value="' . $optioncss . '">';
			print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
			print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
			if ($action == 'editline')
				print '<input type="hidden" name="action" value="updateline">';
			else
				print '<input type="hidden" name="action" value="' . $action . '">';
			print '<input type="hidden" name="sortfield" value="' . $sortfield . '">';
			print '<input type="hidden" name="sortorder" value="' . $sortorder . '">';
			print '<input type="hidden" name="id" value="' . $id . '">';
			
			$moreforfilter = '';
			
			if (! empty($moreforfilter)) {
				print '<div class="liste_titre liste_titre_bydiv centpercent">';
				print $moreforfilter;
				print '</div>';
			}
			
			$varpage = empty($contextpage) ? $_SERVER["PHP_SELF"] : $contextpage;
			$selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage); // This also change content of $arrayfields
			
			print '<div class="div-table-responsive">';
			
			print '<table class="tagtable liste' . ($moreforfilter ? " listwithfilterbefore" : "") . '">' . "\n";
			
			print '<tr class="liste_titre">';
			if (! empty($arrayfields['t.plot']['checked']))
				print_liste_field_titre($arrayfields['t.plot']['label'], $_SERVER['PHP_SELF'], '', '', $params, 'style="width:40%;"', $sortfield, $sortorder);
			if (! empty($arrayfields['t.note']['checked']))
				print_liste_field_titre($arrayfields['t.note']['label'], $_SERVER['PHP_SELF'], 't.note', '', $params, 'style="width:40%;"', $sortfield, $sortorder);
			if (! empty($arrayfields['t.coverage']['checked']))
				print_liste_field_titre($arrayfields['t.coverage']['label'], $_SERVER['PHP_SELF'], 't.coverage','', $params, 'style="width:10%;"', $sortfield, $sortorder);
			
			print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"], "", '', '', 'style="width:10%;text-align:right;"', $sortfield, $sortorder, 'maxwidthsearch ');
			print "</tr>\n";
			
			// Fields title search
			print '<tr class="liste_titre">';
			// LIST_OF_TD_TITLE_SEARCH
			print '<td class="liste_titre"> </td>';
			if (! empty($arrayfields['t.note']['checked']))
				print '<td class="liste_titre"><input type="text" class="flat" name="search_note" value="' . $search_note . '"></td>';
			if (! empty($arrayfields['t.coverage']['checked']))
				print '<td class="liste_titre"><input type="text" class="flat" name="search_coverage" value="' . $search_coverage . '"></td>';
				
				// Action column
			print '<td class="liste_titre" align="right">';
			$searchpicto = $form->showFilterAndCheckAddButtons($massactionbutton ? 1 : 0, 'checkforselect', 1);
			print $searchpicto;
			print '</td>';
			print '</tr>' . "\n";
			
			$plottask = new Plotcultivationtask($db);
			
			$plotfilter = array();
			$plotfilter[] = "t.fk_task = '" . $object->id . "'";
			if (! empty($search_note))
				$plotfilter[] = "t.note LIKE %" . $search_note . "%";
			if (! empty($search_coverage))
				$plotfilter[] = "t.coverage =" . $search_coverage;
			
			if ($plottask->fetchAll($sortorder, $sortfield, 0, 0, $plotfilter, 'AND')) {
				foreach ($plottask->lines as $currplot) {
					displayPlotTaskLine($action, $formother, $currplot, $arrayfields);
				}
				print '</tr>';
				print '</table>';
			}
			print '</div>';
			print '</form>';
		}
		print '<div>';
	}
}

llxFooter();
$db->close();

/**
 * Add plot(s) to the task using the add plots form
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
	$action = '';
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
	$action = '';
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
	$action = '';
	return $action;
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
	print "</tr>\n";
	
	print '<tr">';
	// Plot selection
	print '<td>';
	$plot = new plot($db);
	if ($plot->fetchAll("ASC", "ref") > 0) {
		$plots = array(
			'0' => $langs->trans("All")
		);
		foreach ($plot->lines as $plotLine) {
			$key = $plotLine->id;
			$value = $plotLine->ref;
			$plots[$key] = $value;
		}
		print $form->multiselectarray('multiplots', $plots, '', 1, 0, '', 0, '90%');
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
 *
 * @param
 *        	action
 * @param
 *        	withproject
 * @param
 *        	formother
 * @param
 *        	plot
 * @param
 *        	arrayfields
 */
function displayPlotTaskLine($action, $formother, $line, $arrayfields)
{
	Global $db, $conf, $user, $langs;
	
	$var = ! $var;
	print "<tr " . $bc[$var] . ">";
	// Plot url
	if (! empty($arrayfields['t.plot']['checked'])) {
		$plot = new plot($db);
		$plot->fetch($line->fk_plot);
		print '<td class="nowrap">';
		print $plot->getNomUrl(1, 'plot');
		print '</td>';
	}
	// Note
	if (! empty($arrayfields['t.note']['checked'])) {
		print '<td align="left">';
		if (GETPOST('action') == 'editline' && GETPOST('lineid') == $line->id) {
			print '<textarea name="linenote" style="width:90%;" rows="' . ROWS_1 . '">' . $line->note . '</textarea>';
		} else {
			print dol_nl2br($line->note);
		}
		print '</td>';
	}
	// Coverage
	if (! empty($arrayfields['t.coverage']['checked'])) {
		print '<td>';
		if (GETPOST('action') == 'editline' && GETPOST('lineid') == $line->id) {
			print '<input type="hidden" name="old_coverage" value="' . $line->coverage . '">';
			print $formother->select_percent(GETPOST('linecoverage', 'int') ? GETPOST('linecoverage') : $line->coverage, 'linecoverage');
		} else {
			print $line->coverage . '%';
		}
		print '</td>';
	}
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
}




