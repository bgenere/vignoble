<?php
/*
 * Copyright (C) 2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2006-2015 Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2010-2012 Regis Houssin <regis.houssin@capnetworks.com>
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
 * 
 */
@include './tpl/maindolibarr.inc.php';

@include './tpl/cultivationtask.inc.php';

$cultivationprojectid=setIsCultivationProject();

$id = GETPOST('id', 'int');
$ref = GETPOST("ref", 'alpha', 1);
$taskref = GETPOST("taskref", 'alpha');
$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$withproject = GETPOST('withproject', 'int');
$project_ref = GETPOST('project_ref', 'alpha');
$planned_workload = ((GETPOST('planned_workloadhour') != '' && GETPOST('planned_workloadmin') != '') ? GETPOST('planned_workloadhour') * 3600 + GETPOST('planned_workloadmin') * 60 : '');

// Security check
$socid = 0;
if ($user->societe_id > 0)
	$socid = $user->societe_id;
if (! $user->rights->projet->lire)
	accessforbidden();
	
	// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array(
	'projecttaskcard',
	'globalcard'
));

$object = new Task($db);
$extrafields = new ExtraFields($db);
$projectstatic = new Project($db);

// fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);

/**
 * Actions  on task : update , delete
 */
if ($action == 'update' && ! $_POST["cancel"] && $user->rights->projet->creer) {
	/**
	 * - Update task
	 */
	$error = 0;
	
	if (empty($taskref)) {
		$error ++;
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Ref")), null, 'errors');
	}
	if (empty($_POST["label"])) {
		$error ++;
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Label")), null, 'errors');
	}
	if (! $error) {
		$object->fetch($id, $ref);
		$object->oldcopy = clone $object;
		
		$tmparray = explode('_', $_POST['task_parent']);
		$task_parent = $tmparray[1];
		if (empty($task_parent))
			$task_parent = 0; // If task_parent is ''
		
		$object->ref = $taskref ? $taskref : GETPOST("ref", 'alpha', 2);
		$object->label = $_POST["label"];
		$object->description = $_POST['description'];
		$object->fk_task_parent = $task_parent;
		$object->planned_workload = $planned_workload;
		$object->date_start = dol_mktime($_POST['dateohour'], $_POST['dateomin'], 0, $_POST['dateomonth'], $_POST['dateoday'], $_POST['dateoyear']);
		$object->date_end = dol_mktime($_POST['dateehour'], $_POST['dateemin'], 0, $_POST['dateemonth'], $_POST['dateeday'], $_POST['dateeyear']);
		$object->progress = $_POST['progress'];
		
		// Fill array 'array_options' with data from add form
		$ret = $extrafields->setOptionalsFromPost($extralabels, $object);
		if ($ret < 0)
			$error ++;
		
		if (! $error) {
			$result = $object->update($user);
			if ($result < 0) {
				setEventMessages($object->error, $object->errors, 'errors');
			}
		}
	} else {
		$action = 'edit';
	}
}

if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->projet->supprimer) {
	/**
	 * - Delete task
	 */
	if ($object->fetch($id, $ref) >= 0) {
		$result = $projectstatic->fetch($object->fk_project);
		$projectstatic->fetch_thirdparty();
		
		if ($object->delete($user) > 0) {
			header('Location: cultivationtasks.php?id=' . $projectstatic->id . ($withproject ? '&withproject=1' : ''));
			exit();
		} else {
			setEventMessages($object->error, $object->errors, 'errors');
			$action = '';
		}
	}
}

// Retrieve First Task ID of Project if withprojet is on to allow project prev next to work
if (! empty($project_ref) && ! empty($withproject)) {
	if ($projectstatic->fetch('', $project_ref) > 0) {
		$tasksarray = $object->getTasksArray(0, 0, $projectstatic->id, $socid, 0);
		if (count($tasksarray) > 0) {
			$id = $tasksarray[0]->id;
		} else {
			header("Location: " . DOL_URL_ROOT . '/projet/tasks.php?id=' . $projectstatic->id . (empty($mode) ? '' : '&mode=' . $mode));
		}
	}
}

/**
 * Display View
 */

llxHeader('', $langs->trans("Task"));

$form = new Form($db);
$formother = new FormOther($db);

if ($id > 0 || ! empty($ref)) {
	if ($object->fetch($id, $ref) > 0) {
		$res = $object->fetch_optionals($object->id, $extralabels);
		
		$result = $projectstatic->fetch($object->fk_project);
		if (! empty($projectstatic->socid))
			$projectstatic->fetch_thirdparty();
		
		$object->project = clone $projectstatic;
		
		$userWrite = $projectstatic->restrictedProjectArea($user, 'write');
		
		if (! empty($withproject)) {
			// initialize tab to cultivationtasks
			$tab = 'cultivationtasks';	
			displayProjectCard($projectstatic->id, $mode, $projectstatic, $form, $tab);

		}
		
		/**
		 *  Display task card
		 */
		print '<div class="fiche">';
		$head = task_prepare_head($object);
		//var_dump($head);
		if ($action == 'edit' && $user->rights->projet->creer) {
			/**
			 * - edit card
			 */
			print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
			print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
			print '<input type="hidden" name="action" value="update">';
			print '<input type="hidden" name="withproject" value="' . $withproject . '">';
			print '<input type="hidden" name="id" value="' . $object->id . '">';
			
			dol_fiche_head($head, 'task_task', $langs->trans("Task"), 0, 'projecttask');
			
			print '<table class="border" width="100%">';
			
			// Ref
			print '<tr><td class="titlefield fieldrequired">' . $langs->trans("Ref") . '</td>';
			print '<td><input size="12" name="taskref" value="' . $object->ref . '"></td></tr>';
			
			// Label
			print '<tr><td class="fieldrequired">' . $langs->trans("Label") . '</td>';
			print '<td><input size="30" name="label" value="' . $object->label . '"></td></tr>';
			
			// Project and third party not displayed within project
			if (empty($withproject)) {
				print '<tr><td>' . $langs->trans("Project") . '</td><td colspan="3">';
				print $projectstatic->getNomUrl(1);
				print '</td></tr>';
				
				// Third party
				print '<td>' . $langs->trans("ThirdParty") . '</td><td colspan="3">';
				if ($projectstatic->societe->id)
					print $projectstatic->societe->getNomUrl(1);
				else
					print '&nbsp;';
				print '</td></tr>';
			}
			
			// Task parent
			print '<tr style="display :none;"><td>' . $langs->trans("ChildOfTask") . '</td><td>';
			print $formother->selectProjectTasks($object->fk_task_parent, $projectstatic->id, 'task_parent', ($user->admin ? 0 : 1), 0, 0, 0, $object->id);
			print '</td></tr>';
			
			// Date start
			print '<tr><td>' . $langs->trans("DateStart") . '</td><td>';
			print $form->select_date($object->date_start, 'dateo', 1, 1, 0, '', 1, 1, 1);
			print '</td></tr>';
			
			// Date end
			print '<tr><td>' . $langs->trans("DateEnd") . '</td><td>';
			print $form->select_date($object->date_end ? $object->date_end : - 1, 'datee', 1, 1, 0, '', 1, 1, 1);
			print '</td></tr>';
			
			// Planned workload
			print '<tr><td>' . $langs->trans("PlannedWorkload") . '</td><td>';
			print $form->select_duration('planned_workload', $object->planned_workload, 0, 'text');
			print '</td></tr>';
			
			// Progress declared
			print '<tr><td>' . $langs->trans("ProgressDeclared") . '</td><td colspan="3">';
			print $formother->select_percent($object->progress, 'progress');
			print '</td></tr>';
			
			// Description
			print '<tr><td valign="top">' . $langs->trans("Description") . '</td>';
			print '<td>';
			print '<textarea name="description" wrap="soft" cols="80" rows="' . ROWS_3 . '">' . $object->description . '</textarea>';
			print '</td></tr>';
			
			// Other options
			$parameters = array();
			$reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
			if (empty($reshook) && ! empty($extrafields->attribute_label)) {
				print $object->showOptionals($extrafields, 'edit');
			}
			
			print '</table>';
			
			dol_fiche_end();
			
			print '<div align="center">';
			print '<input type="submit" class="button" name="update" value="' . $langs->trans("Modify") . '"> &nbsp; ';
			print '<input type="submit" class="button" name="cancel" value="' . $langs->trans("Cancel") . '">';
			print '</div>';
			
			print '</form>';
		} else {
			/**
			 * display task
			 */
			$param = ($withproject ? '&withproject=1' : '');
			$linkback = $withproject ? '<a href="cultivationtasks.php?id=' . $projectstatic->id . '">' . $langs->trans("BackToList") . '</a>' : '';
			
			dol_fiche_head($head, 'cultivationtask', $langs->trans("Task"), 0, 'projecttask');
			
			if ($action == 'delete') {
				print $form->formconfirm($_SERVER["PHP_SELF"] . "?id=" . $_GET["id"] . '&withproject=' . $withproject, $langs->trans("DeleteATask"), $langs->trans("ConfirmDeleteATask"), "confirm_delete");
			}
			
			print '<table class="border" width="100%">';
			
			// Ref
			print '<tr><td class="titlefield">';
			print $langs->trans("Ref");
			print '</td><td colspan="3">';
			if (! GETPOST('withproject') || empty($projectstatic->id)) {
				$projectsListId = $projectstatic->getProjectsAuthorizedForUser($user, 0, 1);
				$object->next_prev_filter = " fk_projet in (" . $projectsListId . ")";
			} else
				$object->next_prev_filter = " fk_projet = " . $projectstatic->id;
			print $form->showrefnav($object, 'ref', $linkback, 1, 'ref', 'ref', '', $param);
			print '</td>';
			print '</tr>';
			
			// Label
			print '<tr><td>' . $langs->trans("Label") . '</td><td colspan="3">' . $object->label . '</td></tr>';
					
			// Date start
			print '<tr><td>' . $langs->trans("DateStart") . '</td><td colspan="3">';
			print dol_print_date($object->date_start, 'dayhour');
			print '</td></tr>';
			
			// Date end
			print '<tr><td>' . $langs->trans("DateEnd") . '</td><td colspan="3">';
			print dol_print_date($object->date_end, 'dayhour');
			if ($object->hasDelay())
				print img_warning("Late");
			print '</td></tr>';
			
			// Planned workload
			print '<tr><td>' . $langs->trans("PlannedWorkload") . '</td><td colspan="3">';
			if ($object->planned_workload != '') {
				print convertSecondToTime($object->planned_workload, 'allhourmin');
			}
			print '</td></tr>';
			
			// Progress declared
			print '<tr><td>' . $langs->trans("ProgressDeclared") . '</td><td colspan="3">';
			if ($object->progress != '') {
				print $object->progress . ' %';
			}
			print '</td></tr>';
			
			// Progress calculated
			print '<tr><td>' . $langs->trans("ProgressCalculated") . '</td><td colspan="3">';
			if ($object->planned_workload != '') {
				$tmparray = $object->getSummaryOfTimeSpent();
				if ($tmparray['total_duration'] > 0 && ! empty($object->planned_workload))
					print round($tmparray['total_duration'] / $object->planned_workload * 100, 2) . ' %';
				else
					print '0 %';
			} else
				print '';
			print '</td></tr>';
			
			// Description
			print '<td valign="top">' . $langs->trans("Description") . '</td><td colspan="3">';
			print nl2br($object->description);
			print '</td></tr>';
			
			// Other options
			$parameters = array();
			$reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
			if (empty($reshook) && ! empty($extrafields->attribute_label)) {
				print $object->showOptionals($extrafields);
			}
			
			print '</table>';
			
			dol_fiche_end();
		}
		
		if ($action != 'edit') {
			/**
			 * - display Actions button Edit and Delete
			 */
			
			print '<div class="tabsAction">';
			
			$parameters = array();
			$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been
			                                                                                               // modified by hook
			if (empty($reshook)) {
				// Modify
				if ($user->rights->projet->creer) {
					print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&amp;action=edit&amp;withproject=' . $withproject . '">' . $langs->trans('Modify') . '</a>';
				} else {
					print '<a class="butActionRefused" href="#" title="' . $langs->trans("NotAllowed") . '">' . $langs->trans('Modify') . '</a>';
				}
				
				// Delete
				if ($user->rights->projet->supprimer && ! $object->hasChildren()) {
					print '<a class="butActionDelete" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&amp;action=delete&amp;withproject=' . $withproject . '">' . $langs->trans('Delete') . '</a>';
				} else {
					print '<a class="butActionRefused" href="#" title="' . $langs->trans("NotAllowed") . '">' . $langs->trans('Delete') . '</a>';
				}
				
				print '</div>';
			}
		}
		print '</div>';
	}
}

llxFooter();
$db->close();
