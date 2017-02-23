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
 * \file cultivationtask.php
 * \ingroup cultivation
 * \brief Card page of a cultivation project task.
 */
@include './tpl/maindolibarr.inc.php';

@include './tpl/cultivationtask.inc.php';

$cultivationprojectid = setIsCultivationProject();

// Current Task id and/or Ref
$id = GETPOST('id', 'int');
$ref = GETPOST("ref", 'alpha', 1);
// Page parameters
$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'alpha');

// Security check
if (! $user->rights->projet->lire)
	accessforbidden();

/**
 * Actions on task : edit, update, delete , confirm_delete.
 *
 * Below are process required before display.
 */
if ($action == 'update' && ! $cancel && $user->rights->projet->creer) {
	$action = updateTask($id);
}

if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->projet->supprimer) {
	$object = new Task($db);
	if ($object->fetch($id) >= 0) {
		$action = deleteTask($object);
	}
}

/**
 * Display View
 */
llxHeader('', $langs->trans("Task"));

if ($id > 0 || ! empty($ref)) {
	
	$object = new Task($db);
	
	if ($object->fetch($id, $ref) > 0) {
		
		$extrafields = new ExtraFields($db);
		$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);
		$res = $object->fetch_optionals($object->id, $extralabels);
		
		$projectstatic = new Project($db);
		$result = $projectstatic->fetch($object->fk_project);
		if (! empty($projectstatic->socid))
			$projectstatic->fetch_thirdparty();
		
		$object->project = clone $projectstatic;
		
		displayProjectHeaderCard($projectstatic, $form);
		
		print '<div class="fiche">'; // Begin Task part
		
		$head = task_prepare_head($object);
		dol_fiche_head($head, 'cultivationtask', $langs->trans("Task"), 0, 'projecttask');
		
		$form = new Form($db);
		displayTaskHeader($object, $projectstatic, $form);
		
		if ($action == 'edit' && $user->rights->projet->creer) {
			displayTaskEditForm($object, $projectstatic, $form, $formother, $extrafields);
		} else {
			displayTaskCard($object, $extrafields);
			
			if ($action == 'delete') {
				print $form->formconfirm($_SERVER["PHP_SELF"] . "?id=" . $id, $langs->trans("DeleteATask"), $langs->trans("ConfirmDeleteATask"), "confirm_delete");
			} else {
				displayTaskButtons($object);
			}
		}
		print '</div>'; // End Task part
	}
}

llxFooter();
$db->close();

/**
 * Display task fields & extrafields on one column
 *
 * @param
 *        	object the task to display
 * @param
 *        	extrafields the task extra fields
 *        	
 */
function displayTaskCard($task, $extrafields)
{
	Global $db, $conf, $user, $langs;
	
	print '<div class="underbanner"></div>';
	print '<table class="border" width="100%">';
	// Date start
	print '<tr><td width="25%">' . $langs->trans("DateStart") . '</td><td colspan="3">';
	print dol_print_date($task->date_start, 'dayhour');
	print '</td></tr>';
	
	// Date end
	print '<tr><td>' . $langs->trans("DateEnd") . '</td><td colspan="3">';
	print dol_print_date($task->date_end, 'dayhour');
	if ($task->hasDelay())
		print img_warning("Late");
	print '</td></tr>';
	
	// Planned workload
	print '<tr><td>' . $langs->trans("PlannedWorkload") . '</td><td colspan="3">';
	if ($task->planned_workload != '') {
		print convertSecondToTime($task->planned_workload, 'allhourmin');
	}
	print '</td></tr>';
	
	// Progress declared
	print '<tr><td>' . $langs->trans("ProgressDeclared") . '</td><td colspan="3">';
	if ($task->progress != '') {
		print $task->progress . ' %';
	}
	print '</td></tr>';
	
	// Progress calculated
	print '<tr><td>' . $langs->trans("ProgressCalculated") . '</td><td colspan="3">';
	if ($task->planned_workload != '') {
		$tmparray = $task->getSummaryOfTimeSpent();
		if ($tmparray['total_duration'] > 0 && ! empty($task->planned_workload))
			print round($tmparray['total_duration'] / $task->planned_workload * 100, 2) . ' %';
		else
			print '0 %';
	} else
		print '';
	print '</td></tr>';
	
	// Description
	print '<td valign="top">' . $langs->trans("Description") . '</td><td colspan="3">';
	print nl2br($task->description);
	print '</td></tr>';
	
	// Extra fields
	if (! empty($extrafields->attribute_label)) {
		print $task->showOptionals($extrafields);
	}
	
	print '</table>';
	dol_fiche_end();
}

/**
 * Display the task edit form
 *
 * @param
 *        	task the task object to display in form
 * @param
 *        	project projectstatic
 * @param
 *        	form
 * @param
 *        	formother
 */
function displayTaskEditForm($task, $projectstatic, $extrafields)
{
	Global $db, $conf, $user, $langs;
	
	$form = new Form($db);
	$formother = new FormOther($db);
	
	print '<div class="underbanner"></div>';
	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="' . $task->id . '">';
	
	print '<table class="border" width="100%">';
	
	// Ref
	print '<tr><td class="titlefield fieldrequired" width = "25%">' . $langs->trans("Ref") . '</td>';
	print '<td><input size="12" name="taskref" value="' . $task->ref . '"></td></tr>';
	
	// Label
	print '<tr><td class="fieldrequired">' . $langs->trans("Label") . '</td>';
	print '<td><input size="30" name="label" value="' . $task->label . '"></td></tr>';
	
	// Task parent
	print '<tr style="display :none;"><td>' . $langs->trans("ChildOfTask") . '</td><td>';
	print $formother->selectProjectTasks($task->fk_task_parent, $projectstatic->id, 'task_parent', ($user->admin ? 0 : 1), 0, 0, 0, $task->id);
	print '</td></tr>';
	
	// Date start
	print '<tr><td>' . $langs->trans("DateStart") . '</td><td>';
	print $form->select_date($task->date_start, 'dateo', 1, 1, 0, '', 1, 1, 1);
	print '</td></tr>';
	
	// Date end
	print '<tr><td>' . $langs->trans("DateEnd") . '</td><td>';
	print $form->select_date($task->date_end ? $task->date_end : - 1, 'datee', 1, 1, 0, '', 1, 1, 1);
	print '</td></tr>';
	
	// Planned workload
	print '<tr><td>' . $langs->trans("PlannedWorkload") . '</td><td>';
	print $form->select_duration('planned_workload', $task->planned_workload, 0, 'text');
	print '</td></tr>';
	
	// Progress declared
	print '<tr><td>' . $langs->trans("ProgressDeclared") . '</td><td colspan="3">';
	print $formother->select_percent($task->progress, 'progress');
	print '</td></tr>';
	
	// Description
	print '<tr><td valign="top">' . $langs->trans("Description") . '</td>';
	print '<td>';
	print '<textarea name="description" wrap="soft" cols="80" rows="' . ROWS_3 . '">' . $task->description . '</textarea>';
	print '</td></tr>';
	
	// Extrafields
	if (! empty($extrafields->attribute_label)) {
		print $task->showOptionals($extrafields, 'edit');
	}
	
	print '</table>';
	
	dol_fiche_end();
	
	print '<div align="center">';
	print '<input type="submit" class="button" name="update" value="' . $langs->trans("Modify") . '"> &nbsp; ';
	print '<input type="submit" class="button" name="cancel" value="' . $langs->trans("Cancel") . '">';
	print '</div>';
	
	print '</form>';
}

/**
 * Delete task and go back to cultivation project page.
 *
 * If a delete error happens, clear $action.
 *
 * @param
 *        	task the task object ot delete
 *        	
 * @return empty action
 *        
 */
function deleteTask($task)
{
	Global $db, $conf, $user, $langs;
	
	if ($task->delete($user) > 0) {
		header('Location: cultivationtasks.php');
		exit();
	} else {
		setEventMessages('', $object->errors, 'errors');
		return $action = '';
	}
}

/**
 * Update Task using fields send by the task edit form.
 *
 * @param int $id
 *        	Task to update rowid
 * @return string $action empty when done or stay in edit mode when error
 */
function updateTask($id)
{
	Global $db, $conf, $user, $langs;
	
	$error = 0;
	
	// Check mandatory fields
	$taskref = GETPOST("taskref", 'alpha');
	if (empty($taskref)) {
		$error ++;
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Ref")), null, 'errors');
	}
	$label = GETPOST("label", 'alpha');
	if (empty($label)) {
		$error ++;
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Label")), null, 'errors');
	}
	
	if (! $error) {
		
		$task = new Task($db);
		$extrafields = new ExtraFields($db);
		$extralabels = $extrafields->fetch_name_optionals_label($task->table_element);
		
		$task->fetch($id);
		$task->oldcopy = clone $task; // needed for update
		
		$tmparray = explode('_', GETPOST('task_parent', 'alpha'));
		$task_parent = $tmparray[1];
		if (empty($task_parent))
			$task_parent = 0; // If task_parent is ''
		
		$task->ref = $taskref;
		$task->label = $label;
		$task->description = GETPOST('description', 'alpha');
		$task->fk_task_parent = $task_parent;
		$task->planned_workload = ((GETPOST('planned_workloadhour') != '' && GETPOST('planned_workloadmin') != '') ? GETPOST('planned_workloadhour') * 3600 + GETPOST('planned_workloadmin') * 60 : '');
		$task->date_start = dol_mktime($_POST['dateohour'], $_POST['dateomin'], 0, $_POST['dateomonth'], $_POST['dateoday'], $_POST['dateoyear']);
		$task->date_end = dol_mktime($_POST['dateehour'], $_POST['dateemin'], 0, $_POST['dateemonth'], $_POST['dateeday'], $_POST['dateeyear']);
		$task->progress = GETPOST('progress', 'int');
		
		// Fill array 'array_options' with data from add form
		$ret = $extrafields->setOptionalsFromPost($extralabels, $task);
		if ($ret < 0)
			$error ++;
		
		if (! $error) {
			$result = $task->update($user);
			if ($result < 0) {
				setEventMessages('', $task->errors, 'errors');
			}
		}
		return $action = '';
	} else {
		return $action = 'edit';
	}
}

/**
 * Display the modify and delete buttons
 * 
 * @param
 *        	task the task object
 */
function displayTaskButtons($task)
{
	Global $db, $conf, $user, $langs;
	
	print '<div class="tabsAction">';
	// Modify button
	if ($user->rights->projet->creer) {
		print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $task->id . '&amp;action=edit">' . $langs->trans('Modify') . '</a>';
	} else {
		print '<a class="butActionRefused" href="#" title="' . $langs->trans("NotAllowed") . '">' . $langs->trans('Modify') . '</a>';
	}
	// Delete button
	if ($user->rights->projet->supprimer && ! $task->hasChildren() && ( ! $task->hasTimeSpent() || $conf->global->MAIN_VERSION_LAST_UPGRADE < "5")) {
		print '<a class="butActionDelete" href="' . $_SERVER['PHP_SELF'] . '?id=' . $task->id . '&amp;action=delete">' . $langs->trans('Delete') . '</a>';
	} else {
		print '<a class="butActionRefused" href="#" title="' . $langs->trans("NotAllowed") . '">' . $langs->trans('Delete') . '</a>';
	}
	print '</div>';
}





