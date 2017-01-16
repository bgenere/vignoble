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
 * \file cultivationtaskcontact.php
 * \ingroup cultivation
 * \brief contacts for a cultivation project task
 */
@include './tpl/maindolibarr.inc.php';

@include './tpl/cultivationtask.inc.php';

$cultivationprojectid = setIsCultivationProject();

$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$withproject = GETPOST('withproject', 'int');
$project_ref = GETPOST('project_ref', 'alpha');

// Security check
$socid = 0;
if ($user->societe_id > 0)
	$socid = $user->societe_id;
	// $result = restrictedArea($user, 'projet', $id, 'projet_task');
if (! $user->rights->projet->lire)
	accessforbidden();

$object = new Task($db);
$projectstatic = new Project($db);

/**
 * Actions on task : add contact, swap contact status
 */

if ($action == 'addcontact' && $user->rights->projet->creer) {
	/**
	 * - Add a new contact to the cultivation task
	 */
	$result = $object->fetch($id, $ref);
	
	if ($result > 0 && $id > 0) {
		$idfortaskuser = (GETPOST("contactid") != 0) ? GETPOST("contactid") : GETPOST("userid"); // GETPOST('contactid') may val -1 to mean empty or -2 to means "everybody"
		if ($idfortaskuser == - 2) { // everybody selected
			$result = $projectstatic->fetch($object->fk_project);
			if ($result <= 0) {
				dol_print_error($db, $projectstatic->error, $projectstatic->errors);
			} else {
				$contactsofproject = $projectstatic->getListContactId('internal');
				$contactsofproject = array_merge($contactsofproject, $projectstatic->getListContactId('external'));
				foreach ($contactsofproject as $key => $val) {
					$result = $object->add_contact($val, GETPOST("type"), GETPOST("source"));
				}
			}
		} elseif ($idfortaskuser !== - 1) { // not empty
			$result = $object->add_contact($idfortaskuser, GETPOST("type"), GETPOST("source"));
		}
	}
	
	if ($result >= 0) {
		$selectedCompany = GETPOST("newcompany") ? GETPOST("newcompany") : $projectstatic->societe->id;
		header("Location: " . $_SERVER["PHP_SELF"] . "?id=" . $object->id . ($withproject ? '&withproject=1' : '') . ($selectedCompany ? '&newcompany=' . $selectedCompany : ''));
		exit();
	} else {
		if ($object->error == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
			$langs->load("errors");
			setEventMessages($langs->trans("ErrorThisContactIsAlreadyDefinedAsThisType"), null, 'errors');
		} else {
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}
}

if ($action == 'swapstatut' && $user->rights->projet->creer) {
	/**
	 * - Swap the contact status active/inactive
	 */
	if ($object->fetch($id, $ref)) {
		$result = $object->swapContactStatus(GETPOST('ligne'));
	} else {
		dol_print_error($db);
	}
}

if ($action == 'deleteline' && $user->rights->projet->creer) {
	/**
	 * - Remove contact from task
	 */
	$object->fetch($id, $ref);
	$result = $object->delete_contact($_GET["lineid"]);
	
	if ($result >= 0) {
		header("Location: " . $_SERVER["PHP_SELF"] . "?id=" . $object->id . ($withproject ? '&withproject=1' : ''));
		exit();
	} else {
		dol_print_error($db);
	}
}

// Retreive First Task ID of Project if withprojet is on to allow project prev next to work
if (! empty($project_ref) && ! empty($withproject)) {
	if ($projectstatic->fetch(0, $project_ref) > 0) {
		$tasksarray = $object->getTasksArray(0, 0, $projectstatic->id, $socid, 0);
		if (count($tasksarray) > 0) {
			$id = $tasksarray[0]->id;
		} else {
			header("Location: " . DOL_URL_ROOT . '/projet/tasks.php?id=' . $projectstatic->id . ($withproject ? '&withproject=1' : '') . (empty($mode) ? '' : '&mode=' . $mode));
			exit();
		}
	}
}

/**
 * View
 */

llxHeader('', $langs->trans("Task"));

$form = new Form($db);
$formcompany = new FormCompany($db);
$contactstatic = new Contact($db);
$userstatic = new User($db);

if ($id > 0 || ! empty($ref)) {
	if ($object->fetch($id, $ref) > 0) {
		$id = $object->id; // So when doing a search from ref, id is also set correctly.
		
		$result = $projectstatic->fetch($object->fk_project);
		if (! empty($projectstatic->socid))
			$projectstatic->fetch_thirdparty();
		
		$object->project = clone $projectstatic;
		
		$userWrite = $projectstatic->restrictedProjectArea($user, 'write');
		
		if (! empty($withproject)) {
			/**
			 * Display project card
			 */
			$tab = 'cultivationtasks';
			displayProjectHeaderCard($projectstatic, $form);
		}
		
		/**
		 * Display task summary card
		 */
		print '<div class="fiche">';
		$head = task_prepare_head($object);
		dol_fiche_head($head, 'cultivationtaskcontact', $langs->trans("Task"), 0, 'projecttask');
		
		displayTaskHeader($object, $projectstatic, $form);
		
		/**
		 * Display contact Part
		 */
		print '<table class="noborder" width="100%">';
		
		if ($user->rights->projet->creer) {
			/**
			 * - header to add a contact
			 */
			print '<tr class="liste_titre">';
			print '<td>' . $langs->trans("Source") . '</td>';
			print '<td>' . $langs->trans("ThirdParty") . '</td>';
			print '<td>' . $langs->trans("ProjectContact") . '</td>';
			print '<td>' . $langs->trans("ContactType") . '</td>';
			print '<td colspan="3">&nbsp;</td>';
			print "</tr>\n";
			
			$var = false; // manage line color swap.
			/**
			 * - form to add a user contact (internal)
			 */
			print '<form action="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '" method="POST">';
			print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
			print '<input type="hidden" name="action" value="addcontact">';
			print '<input type="hidden" name="source" value="internal">';
			print '<input type="hidden" name="id" value="' . $id . '">';
			if ($withproject)
				print '<input type="hidden" name="withproject" value="' . $withproject . '">';
				// start line to add internal contact
			print "<tr " . $bc[$var] . ">";
			
			print '<td class="nowrap">';
			print img_object('', 'user') . ' ' . $langs->trans("Users");
			print '</td>';
			
			print '<td colspan="1">';
			print $conf->global->MAIN_INFO_SOCIETE_NOM;
			print '</td>';
			
			print '<td colspan="1">';
			// init filter for selection of users
			if ($object->project->public)
				$contactsofproject = ''; // No project contact filter
			else
				$contactsofproject = $projectstatic->getListContactId('internal'); // Only users of project.
					                                                                   // selection of users
			print $form->select_dolusers((GETPOST('contactid') ? GETPOST('contactid') : $user->id), 'contactid', 0, '', 0, '', $contactsofproject, 0, 0, 0, '', 1, $langs->trans("ResourceNotAssignedToProject"));
			print '</td>';
			
			print '<td>';
			$formcompany->selectTypeContact($object, '', 'type', 'internal', 'rowid');
			print '</td>';
			
			print '<td align="right" colspan="3" ><input type="submit" class="button" value="' . $langs->trans("Add") . '"></td>';
			
			print '</tr>';
			print '</form>';
			
			
		}
		/**
		 * Display list of linked contacts
		 */
		print '<tr class="liste_titre">';
		print '<td>' . $langs->trans("Source") . '</td>';
		print '<td>' . $langs->trans("ThirdParty") . '</td>';
		print '<td>' . $langs->trans("ProjectContact") . '</td>';
		print '<td>' . $langs->trans("ContactType") . '</td>';
		print '<td align="center">' . $langs->trans("Status") . '</td>';
		print '<td colspan="2">&nbsp;</td>';
		print "</tr>\n";
		
		$companystatic = new Societe($db);
		$var = true;
		
		foreach (array(
			'internal',
			'external'
		) as $source) {
			$tab = $object->liste_contact(- 1, $source);
			$num = count($tab);
			// process line result
			$i = 0;
			while ($i < $num) {
				$var = ! $var;
				
				print '<tr ' . $bc[$var] . ' valign="top">';
				
				// Source
				print '<td align="left">';
				if ($tab[$i]['source'] == 'internal')
					print $langs->trans("User");
				if ($tab[$i]['source'] == 'external')
					print $langs->trans("ThirdPartyContact");
				print '</td>';
				
				// Third party link or Company name
				print '<td align="left">';
				if ($tab[$i]['socid'] > 0) {
					$companystatic->fetch($tab[$i]['socid']);
					print $companystatic->getNomUrl(1);
				} elseif ($tab[$i]['socid'] < 0) {
					print $conf->global->MAIN_INFO_SOCIETE_NOM;
				} elseif (! $tab[$i]['socid']) {
					print '&nbsp;';
				}
				print '</td>';
				
				// User or Contact url and full name
				print '<td>';
				if ($tab[$i]['source'] == 'internal') {
					$userstatic->id = $tab[$i]['id'];
					$userstatic->lastname = $tab[$i]['lastname'];
					$userstatic->firstname = $tab[$i]['firstname'];
					print $userstatic->getNomUrl(1);
				}
				if ($tab[$i]['source'] == 'external') {
					$contactstatic->id = $tab[$i]['id'];
					$contactstatic->lastname = $tab[$i]['lastname'];
					$contactstatic->firstname = $tab[$i]['firstname'];
					print $contactstatic->getNomUrl(1);
				}
				print '</td>';
				
				// Person Role 
				print '<td>' . $tab[$i]['libelle'] . '</td>';
				
				// Person Statut
				print '<td align="center">';
				// Swap button 
				if ($object->statut >= 0)
					print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=swapstatut&ligne=' . $tab[$i]['rowid'] . ($withproject ? '&withproject=1' : '') . '">';
				print $contactstatic->LibStatut($tab[$i]['status'], 3);
				if ($object->statut >= 0)
					print '</a>';
				print '</td>';
				
				// Delete button
				print '<td align="center" class="nowrap">';
				if ($user->rights->projet->creer) {
					print '&nbsp;';
					print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=deleteline&lineid=' . $tab[$i]['rowid'] . ($withproject ? '&withproject=1' : '') . '">';
					print img_delete();
					print '</a>';
				}
				print '</td>';
				
				print "</tr>\n";
				
				$i ++;
			}
		}
		print "</table>";
	} else {
		print "ErrorRecordNotFound";
	}
}
print '</div>';


llxFooter();

$db->close();
/**
 * END
 */


