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
 * \file cultivationtaskcontact.php
 * \ingroup cultivation
 * \brief contacts for a cultivation project task
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

$object = new Task($db);
$projectstatic = new Project($db);

if ($id > 0 || ! empty($ref)) {
	if ($object->fetch($id, $ref) > 0) {
		
		$id = $object->id; // So when doing a search from ref, id is also set correctly.
		
		$result = $projectstatic->fetch($object->fk_project);
		$object->project = clone $projectstatic;
		if ($projectstatic->id == $cultivationprojectid) {
			
			/**
			 * Actions on task : add contact, swap contact status
			 */
			
			if ($action == 'addcontact' && $user->rights->projet->creer) {
				addTaskUser($object, $projectstatic);
			}
			
			if ($action == 'swapstatut' && $user->rights->projet->creer) {
				swapTaskUserStatus($object);
			}
			
			if ($action == 'deleteline' && $user->rights->projet->creer) {
				$action = deleteTaskUser($object);
			}
			
			/**
			 * Display View
			 */
			llxHeader('', $langs->trans("Task"));
			
			
			$form = new Form($db);
			$formcompany = new FormCompany($db);
			
			displayProjectHeaderCard($projectstatic, $form);
			
			print '<div class="fiche">'; // Task and Users Tab
			
			$head = task_prepare_head($object);
			dol_fiche_head($head, 'cultivationtaskcontact', $langs->trans("Task"), 0, 'projecttask');
			
			displayTaskHeader($object, $projectstatic, $form);
			
			/**
			 *
			 * @todo add delete line confirmation dialog
			 */
			
			if ($user->rights->projet->creer) {
				displayAddUserForm($object, $projectstatic);
			}
			
			// List of users associated to task
			
			print '<div class="div-table-responsive">';
			
			print '<table class="liste" style="border-bottom-style: none;">';
			
			// Fields header
			print '<tr class="liste_titre">';
			print '<td style="width:40%;">' . $langs->trans("ProjectContact") . '</td>';
			print '<td style="width:40%;">' . $langs->trans("ContactType") . '</td>';
			print '<td style="width:10%;text-align:center;">' . $langs->trans("Status") . '</td>';
			print '<td style="width:10%;text-align:center;" >&nbsp;</td>';
			print "</tr>\n";
			
			$taskusers = $object->liste_contact(- 1, 'internal');
			// TO DO check if sort could be done
			array_multisort($taskusers);
			if (! empty($taskusers))
				displayTaskUsers($taskusers, $object);
			
			print "</table>";
			print '</div>';
			print '</div>'; // end tasks & users part
		}
	}
}
llxFooter();
$db->close();

/**
 * Add a user links to the task based on users selected in the add user form
 * @param
 *        	object the task 
 * @param
 *        	projectstatic The cultivation project
 */
function addTaskUser($object, $projectstatic)
{
	Global $db, $conf, $user, $langs;
	
	$error = 0;
	
	$multicontributors = GETPOST('multicontributors', 'array');
	if (empty($multicontributors)) {
		setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv("Contributor")), null, 'errors');
		$error ++;
	}
	if (! $error) {
		$all = array_search(0, $multicontributors);
		if ($all === false) { // list of contributors in array
			foreach ($multicontributors as $contributorid) {
				$result = $object->add_contact($contributorid, GETPOST("type"), GETPOST("source"));
				if ($result >= 0) {
					setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
				} else {
					setEventMessages($langs->trans("ErrorThisContactIsAlreadyDefinedAsThisType"), null, 'errors');
				}
			}
		} else { // all contributors selected
			$contributorsofproject = $projectstatic->Liste_Contact(- 1, 'internal', 0); // Only users of project. // selection of users
			foreach ($contributorsofproject as $contributor) {
				$result = $object->add_contact($contributor["id"], GETPOST("type"), GETPOST("source"));
				if ($result >= 0) {
					setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
				} else {
					setEventMessages($langs->trans("ErrorThisContactIsAlreadyDefinedAsThisType"), null, 'errors');
				}
			}
		}
	}
}

/**
 * Swap task user status from active to unactive or the other way
 *
 * @param $task the
 *        	current task
 */
function swapTaskUserStatus($task)
{
	Global $db, $conf, $user, $langs;

	$result = $task->swapContactStatus(GETPOST('ligne'));

	if ($result < 0) {
		$langs->load("errors");
		setEventMessages(null, $langs->trans($task->errors), 'errors');
		$error ++;
	}
}

/**
 * Remove user from task
 *
 * @param $task the
 *        	task object
 * @return string $action empty
 */
function deleteTaskUser($task)
{
	Global $db, $conf, $user, $langs;
	
	//if ($task->fetch($id, $ref)) {
		$result = $task->delete_contact(GETPOST('lineid', int));
// 	} else
// 		$result = - 1;
	
	if ($result < 0) {
		$langs->load("errors");
		setEventMessages(null, $langs->trans($task->errors), 'errors');
		$error ++;
	}
	$action = '';
	return $action;
}

/**
 * Display the add user form to associate one or more user to the task
 *
 * @param $task the
 *        	current task object
 * @param $projectstatic the
 *        	current project
 */
function displayAddUserForm($task, $projectstatic)
{
	Global $db, $conf, $user, $langs;
	
	$form = new Form($db);
	$formcompany = new FormCompany($db);
	
	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id=' . $task->id . '">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="addcontact">';
	print '<input type="hidden" name="source" value="internal">';
	print '<input type="hidden" name="id" value="' . $task->id . '">';
	
	print '<table class="noborder" width="100%">';
	
	print '<tr class="liste_titre">';
	print '<td style="width:40%;">' . $langs->trans("ProjectContact") . ' (' . $langs->trans("Add") . ')</td>';
	print '<td style="width:40%;">' . $langs->trans("ContactType") . '</td>';
	print '<td style="width:20%;">' . "&nbsp;" . '</td>';
	print "</tr>";
	
	print "<tr>";
	// Contributor selection
	print '<td>';
	$contributors = getProjectContributors($task,$projectstatic);
	print $form->multiselectarray('multicontributors', $contributors, '', 1, 0, '', 0, '90%');
	print '</td>';
	// User role selection
	print '<td>';
	$formcompany->selectTypeContact($task, '', 'type', 'internal', 'rowid');
	print '</td>';
	// Add button
	print '<td align="right">';
	print '<input type="submit" class="button" value="' . $langs->trans("Add") . '">';
	print '</td>';
	
	print '</tr>';
	print '</table></form>';
}




/**
 * Get list of users who could be allocated to project task
 * 
 * @param Task $task the current task
 * @param Project $projectstatic the cultivation project
 * @return array[] list of contributors for project
 */
function getProjectContributors($task,$projectstatic)
{
	Global $db, $conf, $user, $langs;
	
	if ($task->project->public)
		$contributorsofproject = get_dolusers(); // get all users
	else
		$contributorsofproject = $projectstatic->Liste_Contact(- 1, 'internal'); // Only users of project. // selection of users
	$contributors = array(
		'0' => $langs->trans("All")
	);
	foreach ($contributorsofproject as $contributor) {
		$key = $contributor["id"];
		$value = $contributor["nom"];
		$contributors[$key] = $value;
	}
	return $contributors;
}


/**
 * Display the task user table with role and status
 * 
 * @param array $taskusers contains the list of users
 * @param Task $task the current task
 */
function displayTaskUsers($taskusers, $task)
{
	Global $db, $conf, $user, $langs;
	
	$var = true;
	$contactstatic = new Contact($db);
	$userstatic = new User($db);
	Foreach ($taskusers as $taskuser) {
		$var = ! $var;
		
		print '<tr ' . $bc[$var] . ' valign="top">';
		
		// User url and full name
		print '<td>';
		$userstatic->id = $taskuser['id'];
		$userstatic->lastname = $taskuser['lastname'];
		$userstatic->firstname = $taskuser['firstname'];
		print $userstatic->getNomUrl(1);
		print '</td>';
		// User Role
		print '<td>' . $taskuser['libelle'] . '</td>';
		// User Statut
		print '<td align="center">';
		// Swap button
		if ($task->statut >= 0)
			print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $task->id . '&action=swapstatut&ligne=' . $taskuser['rowid'] . '">';
		print $contactstatic->LibStatut($taskuser['status'], 3);
		if ($task->statut >= 0)
			print '</a>';
		print '</td>';
		// Delete button
		print '<td align="center" class="nowrap">';
		if ($user->rights->projet->creer) {
			print '&nbsp;';
			print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $task->id . '&action=deleteline&lineid=' . $taskuser['rowid'] . '">';
			print img_delete();
			print '</a>';
		}
		print '</td>';
		
		print "</tr>";
	}
}


