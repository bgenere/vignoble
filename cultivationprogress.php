<?php
/*
 * Copyright (C) 2016 Bruno Généré <bgenere@webiseasy.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
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
 * \file cultivationprogress.php
 * \brief Displays cultivation progress between 2 dates
 *
 * \ingroup dashboard
 */
@include './tpl/maindolibarr.inc.php';

@include './tpl/cultivationtask.inc.php';

/* get library */
dol_include_once('/vignoble/lib/ordersandshipments.lib.php'); // delete

/* get language files */

$langs->load("other");

/* check permissions */
if (! $user->rights->projet->lire)
	accessforbidden();
	
	// load cultivation project
$cultivationprojectid = setIsCultivationProject();
$cultivationproject = new Project($db);
$cultivationproject->fetch($cultivationprojectid);
$cultivationproject->getLinesArray($user);

$sort = getsort();

$filter = getfilter();

$timespent = getTaskTimeSpent($cultivationproject, $sort, $filter["timespent"]);

$plotprogress = getPlotProgress($cultivationproject, $sort, $filter["plotprogress"]);

displayView($cultivationproject, $timespent, $plotprogress, $sort, $filter);

/* close database */
$db->close();

/* Function List */

/**
 * Displays the page view
 *
 * A filter form to select date begin, date end, products.
 * Then Orders and Shipment summary by products on 2 columns.
 *
 * @param array[] $orders
 *        	the orders summary result set
 * @param array[] $shipments
 *        	the shipments summary result set
 * @param array[] $sort
 *        	sort field and order
 * @param array[] $filter
 *        	the filter parameters
 *        	
 */
function displayView(Project $cultivationproject, $timespent, $plotprogress, $sort, $filter)
{
	global $db, $conf, $langs, $user;
	
	$pagetitle = $langs->trans('Progress') . ' - ' . $cultivationproject->title;
	
	llxHeader('', $pagetitle);
	print load_fiche_titre($pagetitle, '', 'object_vignoble@vignoble');
	
	displaySearchForm($cultivationproject, $filter, $sort);
	
	$urlparam = buildSearchParameters($filter);
	
	print '<div class="fichecenter">'; // frame
	
	print '<div class="fichehalfleft">'; // left column
	
	displayTable('TimeSpent', $timespent, $sort, $urlparam);
	
	print '</div>'; // left column end
	
	print '<div class="fichehalfright">'; // right column
	
	print '<div class="ficheaddleft">'; // add white space on left
	displayTable('Plots', $plotprogress, $sort, $urlparam);
	print '</div>'; //
	
	print '</div>'; // right column end
	print '</div>'; // frame end
	
	llxFooter();
}

/**
 * Display the filter Form :
 *
 * Date begin, date end, products list.
 *
 * @param array[] $filter
 *        	the filter parameters
 * @param array[] $sort
 *        	sort field and order
 */
function displaySearchForm(Project $cultivationproject, $filter, $sort)
{
	global $db, $conf, $langs, $user;
	
	$form = new Form($db);
	print '<div class="fichecenter">';
	
	print '<form method="post" action="' . DOL_URL_ROOT . '/custom/vignoble/cultivationprogress.php">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="sortfield" value="' . $sort['field'] . '">';
	print '<input type="hidden" name="sortorder" value="' . $sort['order'] . '">';
	print '<table class="noborder nohover centpercent">';
	// Form header
	print '<tr class="liste_titre"><td colspan="3">' . $langs->trans("Search") . '</td></tr>';
	// Date Begin and Button
	print '<tr>';
	print '<td class="nowrap"><label for="datebegin">' . $langs->trans("DateStart") . '</label></td>';
	print '<td>' . $form->select_date($filter['datebegin'], 'datebegin', 0, 0, 0, "datebegin", 1, 1, 1) . '</td>';
	print '<td rowspan="3"><input type="submit" value="' . $langs->trans("Search") . '" class="button"></td>';
	print '</tr>';
	// Date End
	print '<tr>';
	print '<td class="nowrap"><label for="dateend">' . $langs->trans("DateEnd") . '</label></td>';
	print '<td>' . $form->select_date($filter['dateend'], 'dateend', 0, 0, 0, "dateend", 1, 1, 1) . '</td>';
	print '</tr>';
	// Tasks
	$tasks = Array();
	foreach ($cultivationproject->lines as $task) {
		$tasks[$task->ref] = $task->label;
		array_multisort($tasks);
	}
	print '<tr>';
	print '<td class="nowrap"><label for="tasks">' . $langs->trans("Tasks") . '</label></td>';
	print '<td>' . $form->multiselectarray('multitasks', $tasks, $filter['tasks'], 0, 0, '', 0, '90%') . '</td>';
	print '</tr>';
	print '</table>';
	print '</form>';
	print '<br>';
	
	print '</div>'; // fiche
}



/**
 * Display the resut table :
 *
 * Based on $fields populated
 *
 * @param string $tablename
 *        	name of the object
 * @param array[] $table
 *        	result set from SQL query
 * @param array[] $sort
 *        	sort field and order
 * @param string $urlparam
 *        	filter parameters as a URL part for field sort
 */
function displayTable($tablename, $table, $sort, $urlparam)
{
	global $db, $conf, $langs, $user;
	
	$fields = array(
		'date' => array(
			'align' => 'left',
			'label' => 'Date',
			'sort' => 'date,task'
		),
		'task' => array(
			'align' => 'left',
			'label' => 'Task',
			'sort' => 'task,date'
		)
	);
	switch ($tablename) {
		case 'TimeSpent':
			$extrafields = array(
				'contributor' => array(
					'align' => 'left',
					'label' => 'Contributor'
				),
				'note' => array(
					'align' => 'left',
					'label' => 'Note'
				),
				'timespent' => array(
					'align' => 'right',
					'display' => 'convertSecondToTime',
					'label' => 'TimeSpent',
					'total' => 0
				)
			);
			break;
		case 'Plots':
			$extrafields = array(
				'plot' => array(
					'align' => 'left',
					'label' => 'Plot'
				),
				'progress' => array(
					'align' => 'right',
					'label' => 'Progress',
					'total' => 0
				),
				'duration' => array(
					'align' => 'right',
					'display' => 'convertSecondToTime',
					'label' => 'Duration',
					'total' => 0
				)
			);
			break;
	}
	
	$fields = array_merge($fields, $extrafields);
	
	print load_fiche_titre($langs->trans($tablename), '', '');
	print '<table class="liste" >';
	// Fields title
	print '<tr class="liste_titre">';
	foreach ($fields as $field => $fieldvalue) {
		if ($fieldvalue['sort'] !== null) {
			print print_liste_field_titre($langs->trans($fieldvalue['label']), $_SERVER['PHP_SELF'], $fieldvalue['sort'], '', $urlparam, 'align="' . $fieldvalue['align'] . '"', $sort["field"], $sort["order"]);
		} else
			print print_liste_field_titre($langs->trans($fieldvalue['label']), '', '', '', '', 'align="' . $fieldvalue['align'] . '"');
	}
	print '</tr>';
	// Table lines
	foreach ($table as $line) {
		print '<tr>';
		foreach ($fields as $field => $fieldvalue) {
			print '<td align="' . $fieldvalue['align'] . '">';
			if (! empty($fieldvalue['display'])) {
				print $fieldvalue['display']($line->$field);
			} else {
				print $line->$field;
			}
			print '</td>';
			if ($fieldvalue['total'] !== null) {
				$fields[$field]['total'] += ($line->$field);
			}
		}
		print '</tr>';
	}
	// Total
	print '<tr>';
	foreach ($fields as $field => $fieldvalue) {
		print '<td align="' . $fieldvalue['align'] . '">';
		if (! empty($fieldvalue['total'])) {
			if (! empty($fieldvalue['display'])) {
				print $fieldvalue['display']($fieldvalue['total']);
			} else
				print $fieldvalue['total'];
		} else {
			print '--';
		}
		print '</td>';
	}
	print '</tr>';
	print '</table>';
}

/**
 * Get field and order used for the tables sort.
 *
 * Use Ref Ascending by default.
 *
 * @return Array[] with keys : field, order.
 */
function getsort()
{
	$sortfield = GETPOST(sortfield, 'alpha');
	if (empty($sortfield)) {
		$sortfield = 'date,task';
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
 * Get all data needed to filter the SQL requests and produce the results
 *
 * @return Array[] containing the following keys :
 *         datebegin,
 *         dateend,
 *         products (array of selected products),
 *         orders (array of sql filter conditions for orders),
 *         shipments (array of sql filter conditions for shipments),
 */
function getfilter()
{
	$datebegin = GETPOST("datebeginyear") . '-' . GETPOST("datebeginmonth") . '-' . GETPOST("datebeginday");
	if ($datebegin == '--') { // not in Form check URL
		$datebegin = GETPOST("datebegin");
	}
	if (empty($datebegin)) {
		$datebegin = date("Y-m-d", strtotime("now - 1 month"));
	}
	
	$dateend = GETPOST("dateendyear") . '-' . GETPOST("dateendmonth") . '-' . GETPOST("dateendday");
	if ($dateend == '--') { // not in Form check URL
		$dateend = GETPOST("dateend");
	}
	if (empty($dateend)) {
		$dateend = date("Y-m-d");
	}
	
	$tasks = GETPOST('multitasks', 'array');
	
	if (empty($tasks)) { // not in Form check URL
		$tasks = GETPOST('tasks', 'alpha');
		if (empty($tasks)) {
			$selectedtasks = "pt.ref IS NOT NULL";
		} else {
			$selectedtasks = " pt.ref IN " . $tasks;
			$tasks = explode("','", trim($tasks, "('')"));
		}
	} else {
		$selectedtasks = " pt.ref IN ('" . implode("','", $tasks) . "')";
	}
	
	$filter = array(
		"datebegin" => $datebegin,
		"dateend" => $dateend,
		"tasks" => $tasks,
		"timespent" => array(
			" t.task_date >= '" . $datebegin . "' ",
			" t.task_date <= '" . $dateend . "' ",
			$selectedtasks
		),
		"plotprogress" => array(
			" t.dateprogress >= '" . $datebegin . "' ",
			" t.dateprogress <= '" . $dateend . "' ",
			$selectedtasks
		)
	);
	
	return $filter;
}

/**
 * Build the parameters string to be added to URL to keep the filter conditions.
 *
 * (used for list sort)
 *
 * @param Array $filter
 *        	the filter conditions
 * @return string to be added to URL
 */
function buildSearchParameters($filter)
{
	$param = "";
	if (! empty($filter['datebegin']))
		$param .= "&amp;datebegin=" . urlencode($filter['datebegin']);
	if (! empty($filter['dateend']))
		$param .= "&amp;dateend=" . urlencode($filter['dateend']);
	if (! empty($filter['tasks']))
		$param .= "&amp;tasks=" . urlencode("('" . implode("','", $filter['tasks']) . "')");
	return $param;
}

/**
 * get the Task time spent lines making the proper SQL request.
 *
 * @param Project $project
 *        	the current project
 * @param array $sort
 *        	the sort fields and order
 * @param array $filter
 *        	the conditions to apply to get the lines
 * @return Object[] the time spent lines |NULL if empty
 */
function getTaskTimeSpent(Project $project, $sort, $filter)
{
	Global $db, $conf, $user, $langs;
	
	$tasks = array();
	
	$sql = "SELECT";
	$sql .= " t.rowid,";
	$sql .= " t.fk_task,";
	$sql .= " t.task_date as date,";
	$sql .= " t.task_datehour,";
	$sql .= " t.task_date_withhour,";
	$sql .= " t.task_duration as timespent,";
	$sql .= " t.fk_user,";
	$sql .= " t.note as note,";
	$sql .= " pt.ref, pt.label as task,";
	$sql .= " trim(concat(u.firstname,' ',u.lastname)) as contributor";
	$sql .= " FROM " . MAIN_DB_PREFIX . "projet_task_time as t, " . MAIN_DB_PREFIX . "projet_task as pt, " . MAIN_DB_PREFIX . "user as u";
	$sql .= " WHERE t.fk_user = u.rowid AND t.fk_task = pt.rowid";
	$sql .= " AND pt.fk_projet =" . $project->id;
	
	if (count($filter) > 0) {
		// add clauses to WHERE
		$sql .= ' AND ' . implode(' AND ', $filter);
	}
	
	if (! empty($sort)) {
		// add ORDER BY
		$sql .= $db->order($sort['field'], $sort['order']);
	}
	
	$resql = $db->query($sql);
	if ($resql) {
		$num = $db->num_rows($resql);
		$totalnboflines = $num;
		
		$i = 0;
		while ($i < $num) {
			$row = $db->fetch_object($resql);
			$tasks[$i] = $row;
			$i ++;
		}
		$db->free($resql);
		return $tasks;
	} else {
		dol_print_error($db);
		return null;
	}
}

/**
 * get the plot progress lines making the proper SQL request.
 *
 * @param Project $project
 *        	the current project
 * @param array $sort
 *        	the sort fields and order
 * @param array $filter
 *        	the conditions to apply to get the lines
 * @return Object[] the plot progress lines |NULL if empty
 */
function getPlotProgress(Project $project, $sort, $filter)
{
	Global $db, $conf, $user, $langs;
	
	$tasks = array();
	
	$sql = "SELECT";
	$sql .= " t.rowid,";
	$sql .= " t.fk_task,";
	$sql .= " t.dateprogress as date,";
	$sql .= " t.progress as progress,";
	$sql .= " t.duration as duration,";
	$sql .= " t.fk_plot,";
	$sql .= " pt.ref, pt.label as task,";
	$sql .= " pl.label as plot";
	$sql .= " FROM " . MAIN_DB_PREFIX . "plot_taskprogress as t, " . MAIN_DB_PREFIX . "projet_task as pt, " . MAIN_DB_PREFIX . "plot as pl";
	$sql .= " WHERE t.fk_plot = pl.rowid AND t.fk_task = pt.rowid";
	$sql .= " AND pt.fk_projet =" . $project->id;
	
	if (count($filter) > 0) {
		// add clauses to WHERE
		$sql .= ' AND ' . implode(' AND ', $filter);
	}
	
	if (! empty($sort)) {
		// add ORDER BY
		$sql .= $db->order($sort['field'], $sort['order']);
	}
	
	$resql = $db->query($sql);
	if ($resql) {
		$num = $db->num_rows($resql);
		$totalnboflines = $num;
		
		$i = 0;
		while ($i < $num) {
			$row = $db->fetch_object($resql);
			$tasks[$i] = $row;
			$i ++;
		}
		$db->free($resql);
		return $tasks;
	} else {
		dol_print_error($db);
		return null;
	}
}
