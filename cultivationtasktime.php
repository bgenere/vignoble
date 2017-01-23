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
 * \file cultivationtasktime.php
 * \ingroup cultivation
 * \brief time spend by contacts for a cultivation project task
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
			
			if ($action == 'addtimespent' && $user->rights->projet->lire) {
				$action = addTimeSpent($object);
			}
			
			if ($action == 'updateline' && ! $_POST["cancel"] && $user->rights->projet->creer) {
				$action = updateTimeSpent($object);
			}
			
			if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->projet->creer) {
				$action = deleteTimeSpent($object);
			}
			
			/**
			 * View
			 */
			llxHeader("", $langs->trans("Task"));
			
			$form = new Form($db);
			$formother = new FormOther($db);
			$userstatic = new User($db);
			
			displayProjectHeaderCard($projectstatic, $form);
			
			print '<div class="fiche">'; // Task and Time Spent Tab
			
			$head = task_prepare_head($object);
			dol_fiche_head($head, 'cultivationtasktime', $langs->trans("Task"), 0, 'projecttask');
			
			displayTaskHeader($object, $projectstatic, $form);
			
			if ($action == 'deleteline') {
				// display confirmation dialog
				$lineid = GETPOST('lineid', 'int');
				print $form->formconfirm($_SERVER["PHP_SELF"] . "?id=" . $object->id . '&lineid=' . $lineid, $langs->trans("DeleteATimeSpent"), $langs->trans("ConfirmDeleteATimeSpent"), "confirm_delete", '', '', 1);
			}
			
			if ($user->rights->projet->lire) {
				displayAddTimeSpentForm($object, $form, $formother);
			}
			
			// List of time spent associated to task
			$sort = getsort();
			$filter = getTimeSpentfilter($object->id);
			$params = buildTimeSpentSearchParameters($filter);
			
			print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '">';
			print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
			print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
			print '<input type="hidden" name="sortfield" value="' . $sort['field'] . '">';
			print '<input type="hidden" name="sortorder" value="' . $sort['order'] . '">';
			if ($action == 'editline')
				print '<input type="hidden" name="action" value="updateline">';
			else
				print '<input type="hidden" name="action" value="list">';
			
			print '<div class="div-table-responsive">';
			
			print '<table class="liste" style="border-bottom-style: none;">';
			
			// Fields header
			print '<tr class="liste_titre">';
			print_liste_field_titre($langs->trans("Date"), $_SERVER['PHP_SELF'], 't.task_date,u.firstname,u.lastname', '', $params, '', $sort['field'], $sort['order']);
			print_liste_field_titre($langs->trans("Contributor"), $_SERVER['PHP_SELF'], 'u.firstname,u.lastname', '', $params, '', $sort['field'], $sort['order']);
			print_liste_field_titre($langs->trans("Note"), $_SERVER['PHP_SELF'], 't.note', '', $params, '', $sort['field'], $sort['order']);
			print_liste_field_titre($langs->trans("Time"), $_SERVER['PHP_SELF'], 't.task_duration', '', $params, 'align="right"', $sort['field'], $sort['order']);
			print '<td style="width:15%;" colspan="2">&nbsp</td>';
			print "</tr>";
			// Search fields header
			print '<tr class="liste_titre">';
			print '<td>' . $form->select_date((empty($filter['date']) ? - 1 : $filter['date']), 'search_date', 0, 0, 2, "search_date", 1, 0, 1) . '</td>';
			print '<td><input type="text" class="flat" name="search_user" value="' . $filter['user'] . '"></td>';
			print '<td><input type="text" class="flat" name="search_note" value="' . $filter['note'] . '"></td>';
			print '<td class=" right"></td>';
			print '<td ></td>';
			print '<td class=" right">';
			$searchpitco = $form->showFilterAndCheckAddButtons(0, 'checkforselect', 1);
			print $searchpitco;
			print '</td>';
			print '</tr>' . "\n";
			
			$totalarray = array();
			$tasks = getTaskTimeSpent($object, $sort, $filter["timespent"]);
			foreach ($tasks as $task_time) {
				$totalarray = displayTaskTimeSpentLine($task_time, $action, $object, $form, $userstatic, $totalarray);
			}
			// Show total line
			if (isset($totalarray['totaldurationfield'])) {
				displayTaskTimeSpentTotal($totalarray);
			}
			
			print '</tr>';
			
			print "</table>";
			print '</div>';
			print "</form>";
		}
		print '</div>'; // end Task and Time Spent Tab
	}
}
llxFooter();
$db->close();

/**
 * Add a time spent record for a task
 *
 * @param Task $object
 *        	the current task
 * @return string $action empty
 */
function addTimeSpent($object)
{
	Global $db, $conf, $user, $langs;
	
	$error = 0;
	if (empty($object->project->statut)) {
		setEventMessages($langs->trans("ProjectMustBeValidatedFirst"), null, 'errors');
		$error ++;
	}
	$multicontributors = GETPOST('multicontributors', 'array');
	if (empty($multicontributors)) {
		setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv("Contributor")), null, 'errors');
		$error ++;
	}
	// Check time spent is provided
	$timespent_durationhour = GETPOST('timespent_durationhour', 'int');
	$timespent_durationmin = GETPOST('timespent_durationmin', 'int');
	if (empty($timespent_durationhour) && empty($timespent_durationmin)) {
		setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv("Duration")), null, 'errors');
		$error ++;
	}
	
	if (! $error) {
		// get optional values
		$object->timespent_note = GETPOST("timespent_note", 'alpha');
		$object->progress = GETPOST('progress', 'int');
		$object->timespent_duration = GETPOST("timespent_durationhour") * 60 * 60; // We store duration in seconds
		$object->timespent_duration += GETPOST("timespent_durationmin") * 60; // We store duration in seconds
		if (GETPOST("timehour") != '' && GETPOST("timehour") >= 0) { // If hour was entered
			$object->timespent_date = dol_mktime(GETPOST("timehour"), GETPOST("timemin"), 0, GETPOST("timemonth"), GETPOST("timeday"), GETPOST("timeyear"));
			$object->timespent_withhour = 1;
		} else {
			$object->timespent_date = dol_mktime(12, 0, 0, GETPOST("timemonth"), GETPOST("timeday"), GETPOST("timeyear"));
		}
		// process contributors
		$currentcontributors =  array_merge($object->getIdContact('internal', 'TASKCONTRIBUTOR'),$object->getIdContact('internal', 'TASKEXECUTIVE'));
		$all = array_search(0, $multicontributors);
		if ($all === false) { // list of contributors in array
			foreach ($multicontributors as $contributorid) {
				// add contributor to contact if not already in
				if (array_search($contributorid, $currentcontributors) === false) {
					$result = $object->add_contact($contributorid, 'TASKCONTRIBUTOR', 'internal');
					if ($result >= 0) {
						setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
					} else {
						setEventMessages($langs->trans("ErrorThisContactIsAlreadyDefinedAsThisType"), null, 'errors');
					}
				}
				// add time spent
				$object->timespent_fk_user = $contributorid;
				$result = $object->addTimeSpent($user);
				if ($result >= 0) {
					setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
				} else {
					setEventMessages($langs->trans("ErrorSavingTimeSpent"), null, 'errors');
				}
			}
		} else { // all contributors selected
			if ($object->project->public)
				$contributorsofproject = get_dolusers(); // get all users
			else
				$contributorsofproject = $object->project->Liste_Contact(- 1, 'internal'); // Only users
			foreach ($contributorsofproject as $contributor) {
				if (array_search($contributor['id'], $currentcontributors) === false) {
					$result = $object->add_contact($contributor["id"], 'TASKCONTRIBUTOR', 'internal');
					if ($result >= 0) {
						setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
					} else {
						setEventMessages($langs->trans("ErrorThisContactIsAlreadyDefinedAsThisType"), null, 'errors');
					}
				}
				// add time spent
				$object->timespent_fk_user = $contributor["id"];
				$result = $object->addTimeSpent($user);
				if ($result >= 0) {
					setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
				} else {
					setEventMessages($langs->trans("ErrorSavingTimeSpent"), null, 'errors');
				}
			}
		}
	}
	
	return $action = '';
}

/**
 * update time spent of task using time spent form data
 *
 * @param Task $object
 *        	the current task
 * @return string $action empty
 */
function updateTimeSpent($object)
{
	Global $db, $conf, $user, $langs;
	
	$error = 0;
	
	if (empty(GETPOST("new_durationhour")) && empty(GETPOST("new_durationmin"))) {
		setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv("Duration")), null, 'errors');
		$error ++;
	}
	
	if (! $error) {
		$object->fetch($id, $ref);
		
		$object->timespent_id = GETPOST("lineid");
		$object->timespent_note = GETPOST("timespent_note_line");
		$object->timespent_old_duration = GETPOST("old_duration");
		$object->timespent_duration = GETPOST("new_durationhour") * 60 * 60; // We store duration in seconds
		$object->timespent_duration += GETPOST("new_durationmin") * 60; // We store duration in seconds
		if (GETPOST("timelinehour") != '' && GETPOST("timelinehour") >= 0) { // If hour was entered
			$object->timespent_date = dol_mktime(GETPOST("timelinehour"), GETPOST("timelinemin"), 0, GETPOST("timelinemonth"), GETPOST("timelineday"), GETPOST("timelineyear"));
			$object->timespent_withhour = 1;
		} else {
			$object->timespent_date = dol_mktime(12, 0, 0, GETPOST("timelinemonth"), GETPOST("timelineday"), GETPOST("timelineyear"));
		}
		$object->timespent_fk_user = GETPOST("userid_line");
		
		$result = $object->updateTimeSpent($user);
		if ($result >= 0) {
			setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
		} else {
			setEventMessages($langs->trans($object->error), null, 'errors');
			$error ++;
		}
	}
	return $action = '';
}

/**
 * Delete the time spent line selected
 *
 * @param Task $object
 *        	the current task
 * @return string $action empty
 */
function deleteTimeSpent($object)
{
	Global $db, $conf, $user, $langs;
	
	$object->fetchTimeSpent(GETPOST("lineid"));
	$result = $object->delTimeSpent($user);
	
	if ($result < 0) {
		$langs->load("errors");
		setEventMessages($langs->trans($object->error), null, 'errors');
	}
	return $action = '';
}

/**
 * Display the add time spent form to add one or more line of time spent.
 *
 * One line is created by contributor.
 *
 * @param Task $object
 *        	the current task
 *        	
 * @param Form $form        	
 *
 * @param FormOther $formother        	
 */
function displayAddTimeSpentForm($object, Form $form, $formother)
{
	Global $db, $conf, $user, $langs;
	
	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="addtimespent">';
	print '<input type="hidden" name="id" value="' . $object->id . '">';
	
	print '<table class="noborder nohover" width="100%">';
	
	print '<tr class="liste_titre">';
	print '<td>' . $langs->trans("Date") . ' (' . $langs->trans("Add") . ')</td>';
	print '<td style="min-width:20%">' . $langs->trans("By") . '</td>';
	print '<td style="min-width:20%">' . $langs->trans("Note") . '</td>';
	print '<td>' . $langs->trans("ProgressDeclared") . '</td>';
	print '<td  colspan="2">' . $langs->trans("NewTimeSpent") . '</td>';
	print "</tr>";
	
	print '<tr>';
	// Date when time was spent
	print '<td class="nowrap">';
	print $form->select_date('', 'time', 0, 0, 2, "timespent_date", 1, 0, 1);
	print '</td>';
	// Contributor selection
	print '<td>';
	$contributors = getProjectContributors($object, $object->project);
	print $form->multiselectarray('multicontributors', $contributors, GETPOST('multicontributors'), 0, 0, '', 0, '90%');
	print '</td>';
	// Note
	print '<td>';
	print '<textarea name="timespent_note" class="maxwidth100onsmartphone" rows="' . ROWS_1 . '">' . (GETPOST('timespent_note') ? GETPOST('timespent_note') : '') . '</textarea>';
	print '</td>';
	// Progress declared
	print '<td class="nowrap">';
	print $formother->select_percent(GETPOST('progress') ? GETPOST('progress') : $object->progress, 'progress');
	print '</td>';
	// Duration - Time spent
	print '<td class="nowrap" align="right">';
	print $form->select_duration('timespent_duration', (GETPOST('timespent_duration') ? GETPOST('timespent_duration') : ''), 0, 'text');
	print '</td>';
	// Add button
	print '<td align="center">';
	print '<input type="submit" class="button" value="' . $langs->trans("Add") . '">';
	print '</td>';
	
	print '</tr>';
	
	print '</table>';
	print '</form>';
}

/**
 * get the Task time spent lines making the proper SQL request.
 *
 * @param Task $object
 *        	the current task
 * @param array $sort
 *        	the sort fields and order
 * @param array $filter
 *        	the conditions to apply to get the lines
 * @return Object[] the time spent lines |NULL if empty
 */
function getTaskTimeSpent($object, $sort, $filter)
{
	Global $db, $conf, $user, $langs;
	
	$tasks = array();
	
	$sql = "SELECT";
	$sql .= " t.rowid,";
	$sql .= " t.fk_task,";
	$sql .= " t.task_date,";
	$sql .= " t.task_datehour,";
	$sql .= " t.task_date_withhour,";
	$sql .= " t.task_duration,";
	$sql .= " t.fk_user,";
	$sql .= " t.note,";
	$sql .= " pt.ref, pt.label,";
	$sql .= " u.lastname, u.firstname";
	$sql .= " FROM " . MAIN_DB_PREFIX . "projet_task_time as t, " . MAIN_DB_PREFIX . "projet_task as pt, " . MAIN_DB_PREFIX . "user as u";
	$sql .= " WHERE t.fk_user = u.rowid AND t.fk_task = pt.rowid";
	$sql .= " AND t.fk_task =" . $object->id;
	
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
 * Display a task time spent line.
 *
 * @param object $task_time
 *        	the line to print
 * @param string $action
 *        	when value is 'editline' the corresponding line is in edit mode
 * @param Task $object
 *        	the current task
 * @param Form $form        	
 * @param User $userstatic        	
 * @param array $totalarray        	
 * @return array $totalarray the total fields and value
 */
function displayTaskTimeSpentLine($task_time, $action, $object, Form $form, User $userstatic, $totalarray)
{
	Global $db, $conf, $user, $langs;
	
	$var = ! $var;
	$totalarray['nbfield'] = 0;
	
	print "<tr " . $bc[$var] . ">";
	
	// Date
	print '<td class="nowrap">';
	$date1 = $db->jdate($task_time->task_date);
	$date2 = $db->jdate($task_time->task_datehour);
	if ($action == 'editline' && GETPOST('lineid') == $task_time->rowid) {
		print $form->select_date(($task_time->task_date_withhour ? $date2 : $date1), 'timeline', $task_time->task_date_withhour, $task_time->task_date_withhour, 2, "timespent_date", 1, 0, 1);
	} else {
		print dol_print_date(($date2 ? $date2 : $date1), ($task_time->task_date_withhour ? 'dayhour' : 'day'));
	}
	print '</td>';
	$totalarray['nbfield'] ++;
	
	// User
	print '<td>';
	if ($action == 'editline' && GETPOST('lineid') == $task_time->rowid) {
		$contactsoftask = $object->getListContactId('internal');
		if (! in_array($task_time->fk_user, $contactsoftask)) {
			$contactsoftask[] = $task_time->fk_user;
		}
		if (count($contactsoftask) > 0) {
			print $form->select_dolusers($task_time->fk_user, 'userid_line', 0, '', 0, '', $contactsoftask);
		} else {
			print img_error($langs->trans('FirstAddRessourceToAllocateTime')) . $langs->trans('FirstAddRessourceToAllocateTime');
		}
	} else {
		$userstatic->id = $task_time->fk_user;
		$userstatic->lastname = $task_time->lastname;
		$userstatic->firstname = $task_time->firstname;
		print $userstatic->getNomUrl(1);
	}
	print '</td>';
	$totalarray['nbfield'] ++;
	
	// Note
	print '<td align="left">';
	if ($action == 'editline' && GETPOST('lineid') == $task_time->rowid) {
		print '<textarea name="timespent_note_line" width="95%" rows="' . ROWS_2 . '">' . $task_time->note . '</textarea>';
	} else {
		print dol_nl2br($task_time->note);
	}
	print '</td>';
	$totalarray['nbfield'] ++;
	
	// Time spent
	print '<td align="right">';
	if ($action == 'editline' && GETPOST('lineid') == $task_time->rowid) {
		print '<input type="hidden" name="old_duration" value="' . $task_time->task_duration . '">';
		print $form->select_duration('new_duration', $task_time->task_duration, 0, 'text');
	} else {
		print convertSecondToTime($task_time->task_duration, 'allhourmin');
	}
	print '</td>';
	$totalarray['nbfield'] ++;
	$totalarray['totaldurationfield'] = $totalarray['nbfield'];
	$totalarray['totalduration'] += $task_time->task_duration;
	
	// Action column
	print '<td class="right" colspan = "2">';
	if ($action == 'editline' && GETPOST('lineid') == $task_time->rowid) {
		print '<input type="hidden" name="lineid" value="' . GETPOST('lineid') . '">';
		print '<input type="submit" class="button" name="save" value="' . $langs->trans("Save") . '">';
		print '<br>';
		print '<input type="submit" class="button" name="cancel" value="' . $langs->trans('Cancel') . '">';
	} else 
		if ($user->rights->projet->creer) {
			print '&nbsp;';
			print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $task_time->fk_task . '&amp;action=editline&amp;lineid=' . $task_time->rowid . '">';
			print img_edit();
			print '</a>';
			
			print '&nbsp;';
			print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $task_time->fk_task . '&amp;action=deleteline&amp;lineid=' . $task_time->rowid . '">';
			print img_delete();
			print '</a>';
		}
	print '</td>';
	$totalarray['nbfield'] ++;
	$totalarray['nbfield'] ++;
	
	print "</tr>\n";
	
	return $totalarray;
}

/**
 * Display the time spent total line
 *
 * @param array $totalarray
 *        	containing the fields with total
 */
function displayTaskTimeSpentTotal($totalarray)
{
	Global $db, $conf, $user, $langs;
	
	print '<tr class="liste_total">';
	$i = 0;
	while ($i < $totalarray['nbfield']) {
		$i ++;
		if ($i == 1) {
			print '<td align="left">' . $langs->trans("Total") . '</td>';
		} elseif ($totalarray['totaldurationfield'] == $i)
			print '<td align="right">' . convertSecondToTime($totalarray['totalduration'], 'allhourmin') . '</td>';
		elseif ($totalarray['totalvaluefield'] == $i)
			print '<td align="right">' . price($totalarray['totalvalue']) . '</td>';
		else
			print '<td></td>';
	}
	print '</tr>';
}

/**
 * Get fields and order used for table sort.
 *
 * Use Ref Ascending by default.
 *
 * @return Array[] with keys : field, order.
 */
function getsort()
{
	$sortfield = GETPOST(sortfield, 'alpha');
	if (empty($sortfield)) {
		$sortfield = 't.task_date,u.firstname,u.lastname';
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
 * Get all data needed to filter the SQL requests on time spent task and produce the results
 *
 * @param int $id
 *        	the current task id
 * @return Array[] containing the following keys :
 *         id (of the task),
 *         date of the time spent,
 *         user i.e. the contributor,
 *         note,
 *         timespent (array of sql filter conditions for time spent on task),
 *        
 */
function getTimeSpentfilter($id)
{
	$timespentfilter = array();
	if (! empty($id))
		$timespentfilter[] = "t.fk_task = " . $id;
		
		// Purge search criteria
	if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter.x") || GETPOST("button_removefilter")) { // All test are required to be compatible with all browsers
		$search_date = '';
		$search_dateday = '';
		$search_datemonth = '';
		$search_dateyear = '';
		$search_user = '';
		$search_note = '';
	} else {
		
		$search_date = GETPOST("search_dateyear") . '-' . GETPOST("search_datemonth") . '-' . GETPOST("search_dateday");
		if ($search_date == '--') { // not in Form check URL
			$search_date = GETPOST("search_date");
		}
		if (! empty($search_date))
			$timespentfilter[] = "t.task_date =  '" . $search_date . "'";
		
		$search_user = GETPOST('search_user', 'alpha');
		if (! empty($search_user))
			$timespentfilter[] = " ( u.firstname LIKE '%" . $search_user . "%' OR u.lastname LIKE '%" . $search_user . "%')";
		
		$search_note = GETPOST('search_note', 'alpha');
		if (! empty($search_note))
			$timespentfilter[] = "t.note LIKE '%" . $search_note . "%'";
	}
	$filter = array(
		"id" => $id,
		"date" => $search_date,
		"user" => $search_user,
		"note" => $search_note,
		"timespent" => $timespentfilter
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
function buildTimeSpentSearchParameters($filter)
{
	$params = "";
	
	if (! empty($filter["id"]))
		$params .= '&amp;id=' . $filter["id"];
	if (! empty($filter["date"]))
		$params .= '&amp;search_date=' . urlencode($filter["date"]);
	if (! empty($filter["user"]))
		$params .= '&amp;search_user=' . urlencode($filter["user"]);
	if (! empty($filter["note"]))
		$params .= '&amp;search_note=' . urlencode($filter["note"]);
	
	return $params;
}

