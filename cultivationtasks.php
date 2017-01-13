<?php
/*
 * Copyright (C) 2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin <regis.houssin@capnetworks.com>
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
 * \file cultivationtasks.php
 * \ingroup cultivation
 * \brief List all tasks of the default cultivation project. 
 * Called by the cultivation option in the module menu.
 * Also allow to create a new task using the menu link.
 */

// Include main Dolibarr library and global variables.
@include './tpl/maindolibarr.inc.php';

@include './tpl/cultivationtask.inc.php';

$id = setIsCultivationProject();

/**
 * Get page variables
 */
$action = GETPOST('action', 'alpha');
$ref = GETPOST('ref', 'alpha');
$taskref = GETPOST('taskref', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');
$cancel = GETPOST('cancel');
$mode = GETPOST('mode', 'alpha');

$mine = ($mode == 'mine' ? 1 : 0);

/**
 * Instanciate
 * - object as project class
 * - taskstatic as task class
 *
 * add extrafields for both.
 */
$object = new Project($db);
$taskstatic = new Task($db);
$extrafields_task = new ExtraFields($db);
/**
 *  Fetch $object using $id or $ref
 *  $action should not be create or createtask
 *  $cancel should be empty
 */
if ($cancel){ // reset to display if createtask canceled
	$action = null;
	$cancel = null;
}
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php'; // Must be include, not include_once
                                                                 
/**
 * Get extrafields
 */
$extralabels_task = $extrafields_task->fetch_name_optionals_label($taskstatic->table_element);

// Security check
$socid = 0;
if ($user->societe_id > 0)
	$socid = $user->societe_id;
$result = restrictedArea($user, 'projet', $id, 'projet&project');

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array(
	'projecttaskcard',
	'globalcard'
));

$progress = GETPOST('progress', 'int');
$label = GETPOST('label', 'alpha');
$description = GETPOST('description');
$planned_workload = GETPOST('planned_workloadhour') * 3600 + GETPOST('planned_workloadmin') * 60;

/**
 * Process actions : createtask
 * else do nothing
 */

if ($action == 'createtask' && $user->rights->projet->creer) {
	/**
	 * - Create new task
	 */
	$error = 0;
	
	$date_start = dol_mktime($_POST['dateohour'], $_POST['dateomin'], 0, $_POST['dateomonth'], $_POST['dateoday'], $_POST['dateoyear'], 'user');
	$date_end = dol_mktime($_POST['dateehour'], $_POST['dateemin'], 0, $_POST['dateemonth'], $_POST['dateeday'], $_POST['dateeyear'], 'user');
	
	if (! $cancel) {
		if (empty($taskref)) {
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Ref")), null, 'errors');
			$action = 'create';
			$error ++;
		}
		if (empty($label)) {
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Label")), null, 'errors');
			$action = 'create';
			$error ++;
		} else 
			if (empty($_POST['task_parent'])) {
				setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("ChildOfTask")), null, 'errors');
				$action = 'create';
				$error ++;
			}
		
		if (! $error) {
			$tmparray = explode('_', $_POST['task_parent']);
			$projectid = $tmparray[0];
			if (empty($projectid))
				$projectid = $id; // If projectid is ''
			$task_parent = $tmparray[1];
			if (empty($task_parent))
				$task_parent = 0; // If task_parent is ''
			
			$task = new Task($db);
			
			$task->fk_project = $projectid;
			$task->ref = $taskref;
			$task->label = $label;
			$task->description = $description;
			$task->planned_workload = $planned_workload;
			$task->fk_task_parent = $task_parent;
			$task->date_c = dol_now();
			$task->date_start = $date_start;
			$task->date_end = $date_end;
			$task->progress = $progress;
			
			// Fill array 'array_options' with data from add form
			$ret = $extrafields_task->setOptionalsFromPost($extralabels_task, $task);
			
			$taskid = $task->create($user);
			
			if ($taskid > 0) {
				// add current user id as the responsible for the task
				$result = $task->add_contact($_POST["userid"], 'TASKEXECUTIVE', 'internal');
				$backtopage = 'cultivationtask.php?id=' . $taskid . '&withproject=1';
			} else {
				setEventMessages($task->error, $task->errors, 'errors');
			}
		}
		
		if (! $error) {
			if (! empty($backtopage)) {
				header("Location: " . $backtopage);
				exit();
			} else 
				if (empty($projectid)) {
					header("Location: " . DOL_URL_ROOT . '/projet/tasks/list.php' . (empty($mode) ? '' : '?mode=' . $mode));
					exit();
				}
			$id = $projectid;
		}
	} else {
		if (! empty($backtopage)) {
			header("Location: " . $backtopage);
			exit();
		} else 
			if (empty($id)) {
				// We go back on task list
				header("Location: " . DOL_URL_ROOT . '/projet/tasks/list.php' . (empty($mode) ? '' : '?mode=' . $mode));
				exit();
			}
	}
}

/**
 * Display View
 */
$form = new Form($db);
$formother = new FormOther($db);

// page header (title and help url)
$title = $langs->trans("Cultivation") . ' - ' . $langs->trans("Tasks") . ' - ' . $object->ref . ' ' . $object->title;
if (! empty($conf->global->MAIN_HTML_TITLE) && preg_match('/projectnameonly/', $conf->global->MAIN_HTML_TITLE) && $object->name)
	$title = $object->ref . ' ' . $object->name . ' - ' . $langs->trans("Tasks");
$help_url = "EN:Module_Projects|FR:Module_Projets|ES:M&oacute;dulo_Proyectos";
llxHeader("", $title, $help_url);

if ($id > 0 || ! empty($ref)) {
	/**
	 * Print Project Card
	 */
	$tab = GETPOST('tab') ? GETPOST('tab') : 'cultivationtasks';
	displayProjectCard($id, $mode, $object, $form, $tab);
}
print '<div class="fiche">';
if ($action == 'create' && $user->rights->projet->creer && (empty($object->thirdparty->id) || $userWrite > 0)) {
	/**
	 * Display empty Task card
	 */
	if ($id > 0 || ! empty($ref))
		print '<br>';
	
	print load_fiche_titre($langs->trans("NewTask"), '', 'title_project');
	
	print '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="createtask">';
	print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
	if (! empty($object->id))
		print '<input type="hidden" name="id" value="' . $object->id . '">';
	if (! empty($mode))
		print '<input type="hidden" name="mode" value="' . $mode . '">';
	
	dol_fiche_head('');
	
	print '<table class="border" width="100%">';
	
	$defaultref = '';
	$obj = empty($conf->global->PROJECT_TASK_ADDON) ? 'mod_task_simple' : $conf->global->PROJECT_TASK_ADDON;
	if (! empty($conf->global->PROJECT_TASK_ADDON) && is_readable(DOL_DOCUMENT_ROOT . "/core/modules/project/task/" . $conf->global->PROJECT_TASK_ADDON . ".php")) {
		require_once DOL_DOCUMENT_ROOT . "/core/modules/project/task/" . $conf->global->PROJECT_TASK_ADDON . '.php';
		$modTask = new $obj();
		$defaultref = $modTask->getNextValue($object->thirdparty, null);
	}
	
	if (is_numeric($defaultref) && $defaultref <= 0)
		$defaultref = '';
		
		// Ref
	print '<tr><td class="titlefieldcreate"><span class="fieldrequired">' . $langs->trans("Ref") . '</span></td><td>';
	print($_POST["ref"] ? $_POST["ref"] : $defaultref);
	print '<input type="hidden" name="taskref" value="' . ($_POST["ref"] ? $_POST["ref"] : $defaultref) . '">';
	print '</td></tr>';
	// task label
	print '<tr><td class="fieldrequired">' . $langs->trans("Label") . '</td><td>';
	print '<input type="text" name="label" class="flat minwidth300" value="' . $label . '">';
	print '</td></tr>';
	
	// List of projects - not needed we are on the cultivation project
	print '<tr style="display: none;"><td>' . $langs->trans("ChildOfTask") . '</td><td>';
	print $formother->selectProjectTasks(GETPOST('task_parent'), $projectid ? $projectid : $object->id, 'task_parent', 0, 0, 1, 1);
	print '</td></tr>';
	// User responsible by default the current user Id
	print '<tr><td>' . $langs->trans("AffectedTo") . '</td><td>';
	$contactsofproject = (! empty($object->id) ? $object->getListContactId('internal') : '');
	if (count($contactsofproject)) {
		print $form->select_dolusers($user->id, 'userid', 0, '', 0, '', $contactsofproject, '', 0, 0, '', 0, '', 'maxwidth300');
	} else {
		print $langs->trans("NoUserAssignedToTheProject");
	}
	print '</td></tr>';
	
	// Date start
	print '<tr><td>' . $langs->trans("DateStart") . '</td><td>';
	print $form->select_date(($date_start ? $date_start : ''), 'dateo', 1, 1, 0, '', 1, 1, 1);
	print '</td></tr>';
	
	// Date end
	print '<tr><td>' . $langs->trans("DateEnd") . '</td><td>';
	print $form->select_date(($date_end ? $date_end : - 1), 'datee', 1, 1, 0, '', 1, 1, 1);
	print '</td></tr>';
	
	// planned workload
	print '<tr><td>' . $langs->trans("PlannedWorkload") . '</td><td>';
	print $form->select_duration('planned_workload', $planned_workload ? $planned_workload : $object->planned_workload, 0, 'text');
	print '</td></tr>';
	
	// Progress
	print '<tr><td>' . $langs->trans("ProgressDeclared") . '</td><td colspan="3">';
	print $formother->select_percent($progress, 'progress');
	print '</td></tr>';
	
	// Description
	print '<tr><td valign="top">' . $langs->trans("Description") . '</td>';
	print '<td>';
	print '<textarea name="description" wrap="soft" cols="80" rows="' . ROWS_3 . '">' . $description . '</textarea>';
	print '</td></tr>';
	
	// Other options
	$parameters = array();
	$reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook) && ! empty($extrafields_task->attribute_label)) {
		print $object->showOptionals($extrafields_task, 'edit');
	}
	
	print '</table>';
	
	dol_fiche_end();
	
	print '<div align="center">';
	print '<input type="submit" class="button" name="add" value="' . $langs->trans("Add") . '">';
	print ' &nbsp; &nbsp; ';
	print '<input type="submit" class="button" name="cancel" value="' . $langs->trans("Cancel") . '">';
	print '</div>';
	
	print '</form>';
} else 
	if ($id > 0 || ! empty($ref)) {
		/**
		 * Display cultivation tasks
		 */
		
		// add new task button
		// print '<div class="tabsAction">';
		// if ($user->rights->projet->all->creer || $user->rights->projet->creer) {
		// if ($object->public || $userWrite > 0) {
		// print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=create' . $param . '&backtopage=' . urlencode($_SERVER['PHP_SELF'] . '?id=' . $object->id) . '">' . $langs->trans('AddTask') . '</a>';
		// } else {
		// print '<a class="butActionRefused" href="#" title="' . $langs->trans("NotOwnerOfProject") . '">' . $langs->trans('AddTask') . '</a>';
		// }
		// } else {
		// print '<a class="butActionRefused" href="#" title="' . $langs->trans("NotEnoughPermissions") . '">' . $langs->trans('AddTask') . '</a>';
		// }
		// print '</div>';
		
		// Task list
		
		$title = $langs->trans("ListOfTasks");
		// TODO change link when page ready
		// $linktotasks = '<a href="' . DOL_URL_ROOT . '/projet/tasks/time.php?projectid=' . $object->id . '&withproject=1">' . $langs->trans("GoToListOfTimeConsumed") . '</a>';
		$linkstotasks = '';
		print load_fiche_titre($title, $linktotasks, 'title_generic.png');
		
		// Get list of tasks in tasksarray and taskarrayfiltered
		// We need all tasks (even not limited to a user because a task to user can have a parent that is not affected to him).
		$tasksarray = $taskstatic->getTasksArray(0, 0, $object->id, $socid, 0);
		// We load also tasks limited to a particular user
		$tasksrole = ($mode == 'mine' ? $taskstatic->getUserRolesForProjectsOrTasks(0, $user, $object->id, 0) : '');
		// var_dump($tasksarray);
		// var_dump($tasksrole);
		
		if (! empty($conf->use_javascript_ajax)) {
			include DOL_DOCUMENT_ROOT . '/core/tpl/ajaxrow.tpl.php';
		}
		// Print task list
		print '<table id="tablelines" class="noborder" width="100%">';
		print '<tr class="liste_titre nodrag nodrop">';
		print '<td width="100">' . $langs->trans("RefTask") . '</td>';
		print '<td>' . $langs->trans("LabelTask") . '</td>';
		print '<td align="center">' . $langs->trans("DateStart") . '</td>';
		print '<td align="center">' . $langs->trans("DateEnd") . '</td>';
		print '<td align="right">' . $langs->trans("PlannedWorkload") . '</td>';
		print '<td align="right">' . $langs->trans("TimeSpent") . '</td>';
		print '<td align="right">' . $langs->trans("ProgressCalculated") . '</td>';
		print '<td align="right">' . $langs->trans("ProgressDeclared") . '</td>';
		print '<td>&nbsp;</td>';
		print "</tr>\n";
		
		if (count($tasksarray) > 0) {
			// Link to switch in "my task" / "all task"
			print '<tr class="liste_titre nodrag nodrop"><td colspan="9">';
			if ($mode == 'mine') {
				print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '">' . $langs->trans("DoNotShowMyTasksOnly") . '</a>';
			} else {
				print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&mode=mine">' . $langs->trans("ShowMyTasksOnly") . '</a>';
			}
			print '</td></tr>';
			
			// Show all lines in taskarray (recursive function to go down on tree)
			$j = 0;
			$level = 0;
			$nboftaskshown = showtasks($j, 0, $tasksarray, $level, true, 0, $tasksrole, $object->id, 1, $object->id);
		} else {
			print '<tr ' . $bc[false] . '><td colspan="9" class="opacitymedium">' . $langs->trans("NoTasks") . '</td></tr>';
		}
		print "</table>";
		
		// Test if database is clean. If not we clean it.
		// print 'mode='.$_REQUEST["mode"].' $nboftaskshown='.$nboftaskshown.' count($tasksarray)='.count($tasksarray).' count($tasksrole)='.count($tasksrole).'<br>';
		if (! empty($user->rights->projet->all->lire)) { // We make test to clean only if user has permission to see all (test may report false positive otherwise)
			
			if ($mode == 'mine') {
				if ($nboftaskshown < count($tasksrole)) {
					include_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
					cleanCorruptedTree($db, 'projet_task', 'fk_task_parent');
				}
			} else {
				if ($nboftaskshown < count($tasksarray)) {
					include_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
					cleanCorruptedTree($db, 'projet_task', 'fk_task_parent');
				}
			}
		}
	} else {
		// project not defined
		print '<a href="' . DOL_URL_ROOT . '/custom/vignoble/admin/module_settings.php">' . $langs->trans("VignobleSetup") . '</a>';
	}
print '</div>';
llxFooter();

$db->close();
/**
 * END
 */