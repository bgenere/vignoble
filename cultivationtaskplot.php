<?php
/*
 * Copyright (C) 2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2006-2016 Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2010-2012 Regis Houssin <regis.houssin@capnetworks.com>
 * Copyright (C) 2011 Juanjo Menent <jmenent@2byte.es>
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

$id = GETPOST('id', 'int');
$projectid = GETPOST('projectid', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$withproject = GETPOST('withproject', 'int');
$project_ref = GETPOST('project_ref', 'alpha');

$search_note = GETPOST('search_note', 'alpha');
$search_coverage = GETPOST('search_coverage', 'int');
$search_value = GETPOST('search_value', 'int');

// Security check
$socid = 0;
if ($user->societe_id > 0)
	$socid = $user->societe_id;
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
	'projectid' => $projectid
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
	/**
	 * Add a plot
	 */
	$error = 0;
	
// 	if (empty(GETPOST("userid"))) {
// 		$langs->load("errors");
// 		setEventMessages($langs->trans('ErrorUserNotAssignedToTask'), null, 'errors');
// 		$error ++;
// 	} else {
// 		$idfortaskuser = GETPOST("userid"); // val -2 means "everybody"
// 	}
// 	// Check time spent is provided
// 	$timespent_durationhour = GETPOST('timespent_durationhour', 'int');
// 	$timespent_durationmin = GETPOST('timespent_durationmin', 'int');
// 	if (empty($timespent_durationhour) && empty($timespent_durationmin)) {
// 		setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv("Duration")), null, 'errors');
// 		$error ++;
// 	}
	
	if (! $error) {
		$object->fetch($id, $ref);
		$object->fetch_projet();
		
		if (empty($object->projet->statut)) {
			setEventMessages($langs->trans("ProjectMustBeValidatedFirst"), null, 'errors');
			$error ++;
		} else {
			$object->timespent_note = $_POST["timespent_note"];
			$object->progress = GETPOST('progress', 'int');
			$object->timespent_duration = $_POST["timespent_durationhour"] * 60 * 60; // We store duration in seconds
			$object->timespent_duration += $_POST["timespent_durationmin"] * 60; // We store duration in seconds
			if (GETPOST("timehour") != '' && GETPOST("timehour") >= 0) { // If hour was entered
				$object->timespent_date = dol_mktime(GETPOST("timehour"), GETPOST("timemin"), 0, GETPOST("timemonth"), GETPOST("timeday"), GETPOST("timeyear"));
				$object->timespent_withhour = 1;
			} else {
				$object->timespent_date = dol_mktime(12, 0, 0, GETPOST("timemonth"), GETPOST("timeday"), GETPOST("timeyear"));
			}
			
			if ($idfortaskuser == - 2) { // everybody selected
				$plotsoftask = $object->liste_contact(- 1, 'internal', 1);
				foreach ($plotsoftask as $userid) {
					$object->timespent_fk_user = $userid;
					$result = $object->addTimeSpent($user);
				}
			} elseif ($idfortaskuser !== - 1) { // not empty
				$object->timespent_fk_user = $idfortaskuser;
				$result = $object->addTimeSpent($user);
			}
			if ($result >= 0) {
				setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
			} else {
				setEventMessages($langs->trans($object->error), null, 'errors');
				$error ++;
			}
		}
	} else {
		$action = '';
	}
}

if ($action == 'updateline' && ! $_POST["cancel"] && $user->rights->projet->creer) {
	/**
	 * Update an existing line
	 */
	$error = 0;
	
	if (empty($_POST["new_durationhour"]) && empty($_POST["new_durationmin"])) {
		setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv("Duration")), null, 'errors');
		$error ++;
	}
	
	if (! $error) {
		$object->fetch($id, $ref);
		
		$object->timespent_id = $_POST["lineid"];
		$object->timespent_note = $_POST["timespent_note_line"];
		$object->timespent_old_duration = $_POST["old_duration"];
		$object->timespent_duration = $_POST["new_durationhour"] * 60 * 60; // We store duration in seconds
		$object->timespent_duration += $_POST["new_durationmin"] * 60; // We store duration in seconds
		if (GETPOST("timelinehour") != '' && GETPOST("timelinehour") >= 0) // If hour was entered
{
			$object->timespent_date = dol_mktime(GETPOST("timelinehour"), GETPOST("timelinemin"), 0, GETPOST("timelinemonth"), GETPOST("timelineday"), GETPOST("timelineyear"));
			$object->timespent_withhour = 1;
		} else {
			$object->timespent_date = dol_mktime(12, 0, 0, GETPOST("timelinemonth"), GETPOST("timelineday"), GETPOST("timelineyear"));
		}
		$object->timespent_fk_user = $_POST["userid_line"];
		
		$result = $object->updateTimeSpent($user);
		if ($result >= 0) {
			setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
		} else {
			setEventMessages($langs->trans($object->error), null, 'errors');
			$error ++;
		}
	} else {
		$action = '';
	}
}

if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->projet->creer) {
	/**
	 * Delete an existing line
	 */
	$object->fetchTimeSpent($_GET['lineid']);
	$result = $object->delTimeSpent($user);
	
	if ($result < 0) {
		$langs->load("errors");
		setEventMessages($langs->trans($object->error), null, 'errors');
		$error ++;
		$action = '';
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

// To show all time lines for project
// $projectidforalltimes = 0;
// if (GETPOST('projectid')) {
// 	$projectidforalltimes = GETPOST('projectid', 'int');
// }

/**
 * Display View
 */

llxHeader("", $langs->trans("Task"));

$form = new Form($db);
$formother = new FormOther($db);
$userstatic = new User($db);

if (($id > 0 || ! empty($ref)) || $projectidforalltimes > 0) {
	/*
	 * Fiche projet en mode visu
	 */
	if ($projectidforalltimes) {
		$result = $projectstatic->fetch($projectidforalltimes);
		if (! empty($projectstatic->socid))
			$projectstatic->fetch_thirdparty();
		$res = $projectstatic->fetch_optionals($object->id, $extralabels_projet);
	} elseif ($object->fetch($id, $ref) >= 0) {
		$result = $projectstatic->fetch($object->fk_project);
		if (! empty($projectstatic->socid))
			$projectstatic->fetch_thirdparty();
		$res = $projectstatic->fetch_optionals($object->id, $extralabels_projet);
		
		$object->project = clone $projectstatic;
	}
	
	if ($projectstatic->id > 0) {
		if ($withproject) {
			// initialize project tab to cultivationtasks
			$tab = 'cultivationtasks';
			displayProjectCard($projectstatic->id, $mode, $projectstatic, $form, $tab);
		}
	}
	
	if (empty($projectidforalltimes)) {
		/**
		 * Display task summary card
		 */
		$head = task_prepare_head($object);
		dol_fiche_head($head, 'cultivationtaskplot', $langs->trans("Plot"), 0, 'projecttask');
		
		if ($action == 'deleteline') {
			print $form->formconfirm($_SERVER["PHP_SELF"] . "?id=" . $object->id . '&lineid=' . $_GET["lineid"] . ($withproject ? '&withproject=1' : ''), $langs->trans("DeleteATimeSpent"), $langs->trans("ConfirmDeleteATimeSpent"), "confirm_delete", '', '', 1);
		}
		
		displayTaskCard($object, $projectstatic, $form);
		
		dol_fiche_end();
		
		/**
		 * Display Form to add a plot
		 */
		if ($user->rights->projet->lire) {
			print '<br>';
			
			print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '">';
			print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
			print '<input type="hidden" name="action" value="addplot">';
			print '<input type="hidden" name="id" value="' . $object->id . '">';
			print '<input type="hidden" name="withproject" value="' . $withproject . '">';
			
			print '<table class="noborder nohover" width="100%">';
			
			print '<tr class="liste_titre">';
			print '<td>' . $langs->trans("Plots") . '</td>';
			print '<td>' . $langs->trans("Note") . '</td>';
			print '<td>' . $langs->trans("ProgressDeclared") . '</td>';
			print '<td>'."&nbsp".'</td>';
			print "</tr>\n";
			
			print '<tr ' . $bc[false] . '>';
				
			// Plot selection
			$plot = new plot($db);
			print '<td class="maxwidthonsmartphone">';
			print img_object('', 'plot14@vignoble', 'class="hideonsmartphone"');
			
			if ($plot->fetchAll("ASC","ref") > 0) {
				$plots = array('-2' => $langs->trans("All") );
				foreach ($plot->lines as $plotLine){
					$key = $plotLine->id;
					$value = $plotLine->ref;
					$plots = array_merge($plots,array($key => $value));
				}
				print $form->multiselectarray('multi_plots', $plots,$plots,0,0,'',0,'240') ;
			} 
			print '</td>';
			
			// Note
			print '<td>';
			print '<textarea name="timespent_note" class="maxwidth100onsmartphone" rows="' . ROWS_1 . '">' . ($_POST['timespent_note'] ? $_POST['timespent_note'] : '') . '</textarea>';
			print '</td>';
			
			// Progress declared
			print '<td class="nowrap">';
			print $formother->select_percent(GETPOST('progress') ? GETPOST('progress') : $object->progress, 'progress');
			print '</td>';
					
			print '<td align="center">';
			print '<input type="submit" class="button" value="' . $langs->trans("Add") . '">';
			print '</td></tr>';
			
			print '</table></form>';
			
			print '<br>';
		} // end form
	}
	
	if ($projectstatic->id > 0) {
		if ($action == 'deleteline') {
			print $form->formconfirm($_SERVER["PHP_SELF"] . "?id=" . $object->id . '&lineid=' . $_GET["lineid"] . ($withproject ? '&withproject=1' : ''), $langs->trans("DeleteATimeSpent"), $langs->trans("ConfirmDeleteATimeSpent"), "confirm_delete", '', '', 1);
		}
		
			
		// Definition of fields for list
		$arrayfields = array();
		$arrayfields['t.task_date'] = array(
			'label' => $langs->trans("Date"),
			'checked' => 1
		);
		if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes)) {
			$arrayfields['t.task_ref'] = array(
				'label' => $langs->trans("RefTask"),
				'checked' => 1
			);
			$arrayfields['t.task_label'] = array(
				'label' => $langs->trans("LabelTask"),
				'checked' => 1
			);
		}
		$arrayfields['author'] = array(
			'label' => $langs->trans("By"),
			'checked' => 1
		);
		$arrayfields['t.note'] = array(
			'label' => $langs->trans("Note"),
			'checked' => 1
		);
		$arrayfields['t.task_duration'] = array(
			'label' => $langs->trans("Duration"),
			'checked' => 1
		);
		$arrayfields['value'] = array(
			'label' => $langs->trans("Value"),
			'checked' => 1,
			'enabled' => $conf->salaries->enabled
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
		if ($search_note != '')
			$params .= '&amp;search_note=' . urlencode($search_note);
		if ($search_coverage != '')
			$params .= '&amp;search_field2=' . urlencode($search_coverage);
		if ($optioncss != '')
			$param .= '&optioncss=' . $optioncss;
			// Add $param from extra fields
			/*
		 * foreach ($search_array_options as $key => $val)
		 * {
		 * $crit=$val;
		 * $tmpkey=preg_replace('/search_options_/','',$key);
		 * if ($val != '') $param.='&search_options_'.$tmpkey.'='.urlencode($val);
		 * }
		 */
		if ($id)
			$params .= '&amp;id=' . $id;
		if ($projectid)
			$params .= '&amp;projectid=' . $projectid;
		if ($withproject)
			$params .= '&amp;withproject=' . $withproject;
		
		$arrayofmassactions = array();
		// 'presend'=>$langs->trans("SendByMail"),
		// 'builddoc'=>$langs->trans("PDFMerge"),
		
		// if ($user->rights->projet->creer) $arrayofmassactions['delete']=$langs->trans("Delete");
		if ($massaction == 'presend')
			$arrayofmassactions = array();
		$massactionbutton = $form->selectMassAction('', $arrayofmassactions);
		
		print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '">';
		if ($optioncss != '')
			print '<input type="hidden" name="optioncss" value="' . $optioncss . '">';
		print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
		print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
		if ($action == 'editline')
			print '<input type="hidden" name="action" value="updateline">';
		else
			print '<input type="hidden" name="action" value="list">';
		print '<input type="hidden" name="sortfield" value="' . $sortfield . '">';
		print '<input type="hidden" name="sortorder" value="' . $sortorder . '">';
		
		print '<input type="hidden" name="id" value="' . $id . '">';
		print '<input type="hidden" name="projectid" value="' . $projectidforalltimes . '">';
		print '<input type="hidden" name="withproject" value="' . $withproject . '">';
		
		$moreforfilter = '';
		
		$parameters = array();
		$reshook = $hookmanager->executeHooks('printFieldPreListTitle', $parameters); // Note that $action and $object may have been modified by hook
		if (empty($reshook))
			$moreforfilter .= $hookmanager->resPrint;
		else
			$moreforfilter = $hookmanager->resPrint;
		
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
		if (! empty($arrayfields['t.task_date']['checked']))
			print_liste_field_titre($arrayfields['t.task_date']['label'], $_SERVER['PHP_SELF'], 't.task_date,t.task_datehour,t.rowid', '', $params, '', $sortfield, $sortorder);
		if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes)) // Not a dedicated task
{
			if (! empty($arrayfields['t.task_ref']['checked']))
				print_liste_field_titre($arrayfields['t.task_ref']['label'], $_SERVER['PHP_SELF'], 'pt.ref', '', $params, '', $sortfield, $sortorder);
			if (! empty($arrayfields['t.task_label']['checked']))
				print_liste_field_titre($arrayfields['t.task_label']['label'], $_SERVER['PHP_SELF'], 'pt.label', '', $params, '', $sortfield, $sortorder);
		}
		if (! empty($arrayfields['author']['checked']))
			print_liste_field_titre($arrayfields['author']['label'], $_SERVER['PHP_SELF'], '', '', $params, '', $sortfield, $sortorder);
		if (! empty($arrayfields['t.note']['checked']))
			print_liste_field_titre($arrayfields['t.note']['label'], $_SERVER['PHP_SELF'], 't.note', '', $params, '', $sortfield, $sortorder);
		if (! empty($arrayfields['t.task_duration']['checked']))
			print_liste_field_titre($arrayfields['t.task_duration']['label'], $_SERVER['PHP_SELF'], 't.task_duration', '', $params, 'align="right"', $sortfield, $sortorder);
		if (! empty($arrayfields['value']['checked']))
			print_liste_field_titre($arrayfields['value']['label'], $_SERVER['PHP_SELF'], '', '', $params, 'align="right"', $sortfield, $sortorder);
			
			
		print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"], "", '', '', 'align="right"', $sortfield, $sortorder, 'maxwidthsearch ');
		print "</tr>\n";
		
		// Fields title search
		print '<tr class="liste_titre">';
		// LIST_OF_TD_TITLE_SEARCH
		if (! empty($arrayfields['t.task_date']['checked']))
			print '<td class="liste_titre"></td>';
		if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes)) // Not a dedicated task
{
			if (! empty($arrayfields['t.task_ref']['checked']))
				print '<td class="liste_titre"></td>';
			if (! empty($arrayfields['t.task_label']['checked']))
				print '<td class="liste_titre"></td>';
		}
		if (! empty($arrayfields['author']['checked']))
			print '<td class="liste_titre"></td>';
		if (! empty($arrayfields['t.note']['checked']))
			print '<td class="liste_titre"><input type="text" class="flat" name="search_note" value="' . $search_note . '"></td>';
		if (! empty($arrayfields['t.task_duration']['checked']))
			print '<td class="liste_titre right"></td>';
		if (! empty($arrayfields['value']['checked']))
			print '<td class="liste_titre"></td>';
			
			
		// Action column
		print '<td class="liste_titre" align="right">';
		$searchpitco = $form->showFilterAndCheckAddButtons($massactionbutton ? 1 : 0, 'checkforselect', 1);
		print $searchpitco;
		print '</td>';
		print '</tr>' . "\n";
		
		$tasktmp = new Task($db);
		
		$i = 0;
		$total = 0;
		$totalvalue = 0;
		$totalarray = array();
		foreach ($tasks as $task_time) {
			$var = ! $var;
			print "<tr " . $bc[$var] . ">";
			
			$date1 = $db->jdate($task_time->task_date);
			$date2 = $db->jdate($task_time->task_datehour);
			
			// Date
			if (! empty($arrayfields['t.task_date']['checked'])) {
				print '<td class="nowrap">';
				if ($_GET['action'] == 'editline' && $_GET['lineid'] == $task_time->rowid) {
					print $form->select_date(($date2 ? $date2 : $date1), 'timeline', 1, 1, 2, "timespent_date", 1, 0, 1);
				} else {
					print dol_print_date(($date2 ? $date2 : $date1), ($task_time->task_date_withhour ? 'dayhour' : 'day'));
				}
				print '</td>';
				if (! $i)
					$totalarray['nbfield'] ++;
			}
			
			// Task ref
			if (! empty($arrayfields['t.task_ref']['checked'])) {
				if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes)) // Not a dedicated task
{
					print '<td class="nowrap">';
					$tasktmp->id = $task_time->fk_task;
					$tasktmp->ref = $task_time->ref;
					$tasktmp->label = $task_time->label;
					print $tasktmp->getNomUrl(1, 'withproject', 'time');
					print '</td>';
					if (! $i)
						$totalarray['nbfield'] ++;
				}
			}
			
			// Task label
			if (! empty($arrayfields['t.task_label']['checked'])) {
				if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes)) // Not a dedicated task
{
					print '<td class="nowrap">';
					print $task_time->label;
					print '</td>';
					if (! $i)
						$totalarray['nbfield'] ++;
				}
			}
			
			// User
			if (! empty($arrayfields['author']['checked'])) {
				print '<td>';
				if ($_GET['action'] == 'editline' && $_GET['lineid'] == $task_time->rowid) {
					if (empty($object->id))
						$object->fetch($id);
					$plotsoftask = $object->getListContactId('internal');
					if (! in_array($task_time->fk_user, $plotsoftask)) {
						$plotsoftask[] = $task_time->fk_user;
					}
					if (count($plotsoftask) > 0) {
						print img_object('', 'user', 'class="hideonsmartphone"');
						print $form->select_dolusers($task_time->fk_user, 'userid_line', 0, '', 0, '', $plotsoftask);
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
				if (! $i)
					$totalarray['nbfield'] ++;
			}
			
			// Note
			if (! empty($arrayfields['t.note']['checked'])) {
				print '<td align="left">';
				if ($_GET['action'] == 'editline' && $_GET['lineid'] == $task_time->rowid) {
					print '<textarea name="timespent_note_line" width="95%" rows="' . ROWS_2 . '">' . $task_time->note . '</textarea>';
				} else {
					print dol_nl2br($task_time->note);
				}
				print '</td>';
				if (! $i)
					$totalarray['nbfield'] ++;
			}
			
			// Time spent
			if (! empty($arrayfields['t.task_duration']['checked'])) {
				print '<td align="right">';
				if ($_GET['action'] == 'editline' && $_GET['lineid'] == $task_time->rowid) {
					print '<input type="hidden" name="old_duration" value="' . $task_time->task_duration . '">';
					print $form->select_duration('new_duration', $task_time->task_duration, 0, 'text');
				} else {
					print convertSecondToTime($task_time->task_duration, 'allhourmin');
				}
				print '</td>';
				if (! $i)
					$totalarray['nbfield'] ++;
				if (! $i)
					$totalarray['totaldurationfield'] = $totalarray['nbfield'];
				$totalarray['totalduration'] += $task_time->task_duration;
			}
			
			// Value spent
			if (! empty($arrayfields['value']['checked'])) {
				print '<td align="right">';
				$value = price2num($task_time->thm * $task_time->task_duration / 3600);
				print price($value, 1, $langs, 1, - 1, - 1, $conf->currency);
				print '</td>';
				if (! $i)
					$totalarray['nbfield'] ++;
				if (! $i)
					$totalarray['totalvaluefield'] = $totalarray['nbfield'];
				$totalarray['totalvalue'] += $value;
			}
			
					
			// Action column
			print '<td class="right" valign="middle" width="80">';
			if ($action == 'editline' && $_GET['lineid'] == $task_time->rowid) {
				print '<input type="hidden" name="lineid" value="' . $_GET['lineid'] . '">';
				print '<input type="submit" class="button" name="save" value="' . $langs->trans("Save") . '">';
				print '<br>';
				print '<input type="submit" class="button" name="cancel" value="' . $langs->trans('Cancel') . '">';
			} else 
				if ($user->rights->projet->creer) {
					print '&nbsp;';
					print '<a href="' . $_SERVER["PHP_SELF"] . '?' . ($projectidforalltimes ? 'projectid=' . $projectidforalltimes . '&amp;' : '') . 'id=' . $task_time->fk_task . '&amp;action=editline&amp;lineid=' . $task_time->rowid . ($withproject ? '&amp;withproject=1' : '') . '">';
					print img_edit();
					print '</a>';
					
					print '&nbsp;';
					print '<a href="' . $_SERVER["PHP_SELF"] . '?' . ($projectidforalltimes ? 'projectid=' . $projectidforalltimes . '&amp;' : '') . 'id=' . $task_time->fk_task . '&amp;action=deleteline&amp;lineid=' . $task_time->rowid . ($withproject ? '&amp;withproject=1' : '') . '">';
					print img_delete();
					print '</a>';
				}
			print '</td>';
			if (! $i)
				$totalarray['nbfield'] ++;
			
			print "</tr>\n";
			
			$i ++;
		}
		
		// Show total line
		if (isset($totalarray['totaldurationfield']) || isset($totalarray['totalvaluefield'])) {
			print '<tr class="liste_total">';
			$i = 0;
			while ($i < $totalarray['nbfield']) {
				$i ++;
				if ($i == 1) {
					if ($num < $limit)
						print '<td align="left">' . $langs->trans("Total") . '</td>';
					else
						print '<td align="left">' . $langs->trans("Totalforthispage") . '</td>';
				} elseif ($totalarray['totaldurationfield'] == $i)
					print '<td align="right">' . convertSecondToTime($totalarray['totalduration'], 'allhourmin') . '</td>';
				elseif ($totalarray['totalvaluefield'] == $i)
					print '<td align="right">' . price($totalarray['totalvalue']) . '</td>';
				else
					print '<td></td>';
			}
			print '</tr>';
		}
		
		print '</tr>';
		
		print "</table>";
		print '</div>';
		print "</form>";
	}
}

llxFooter();
$db->close();