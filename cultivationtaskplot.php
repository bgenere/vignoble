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

$search_plot = GETPOST('search_plot', 'alpha');
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
	 * Add a plot link
	 */
	$error = 0;
	$multiplots = GETPOST('multiplots', 'array');
	$note = GETPOST('note', 'alpha');
	$coverage = GETPOST('coverage', 'int');
	// var_dump($multiplots);var_dump($note);var_dump($coverage);
	if (empty($multiplots)) {
		setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv("Plots")), null, 'errors');
		$error ++;
	}
	
	if (! $error) {
		$plot = new plot($db);
		$plotcultivation = new Plotcultivationtask($db);
		$all = array_search(0, $multiplots);
		var_dump($all);
		if ($all === false) { // list of plot in array
			foreach ($multiplots as $plotid) {
				var_dump($plotid);
			}
		} else { // all plots selected
			$result = $plot->fetchAll('ASC', 'ref');
			foreach ($plot->lines as $plotLine){
				var_dump($plotLine);
			};
			
		}
	}
	if ($result >= 0) {
		setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
	} else {
		setEventMessages(null, $langs->trans($object->errors), 'errors');
		$error ++;
	}
} else {
	$action = '';
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

/**
 * Display View
 */

llxHeader("", $langs->trans("Task"));

$form = new Form($db);
$formother = new FormOther($db);
$userstatic = new User($db);

if (($id > 0 || ! empty($ref)) || $projectidforalltimes > 0) {
	/**
	 * - Display project summary card
	 */
	if ($object->fetch($id, $ref) >= 0) {
		$result = $projectstatic->fetch($object->fk_project);
		$object->project = clone $projectstatic;
	}
	
	if ($projectstatic->id > 0) {
		if ($withproject) {
			// initialize project tab to cultivationtasks
			$tab = 'cultivationtasks';
			displayProjectCard($projectstatic->id, $mode, $projectstatic, $form, $tab);
		}
	}
	
	/**
	 * - Display task summary card
	 */
	$head = task_prepare_head($object);
	dol_fiche_head($head, 'cultivationtaskplot', $langs->trans("Plot"), 0, 'projecttask');
	
	displayTaskCard($object, $projectstatic, $form);
	
	if ($action == 'deleteline') {
		print $form->formconfirm($_SERVER["PHP_SELF"] . "?id=" . $object->id . '&lineid=' . GETPOST("lineid", "int") . ($withproject ? '&withproject=1' : ''), $langs->trans("DeleteAPlot"), $langs->trans("ConfirmDeleteAPlot"), "confirm_delete", '', '', 1);
	}
	
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
		print '<td>' . "&nbsp" . '</td>';
		print "</tr>\n";
		
		print '<tr ' . $bc[false] . '>';
		
		// Plot selection
		$plot = new plot($db);
		print '<td class="maxwidthonsmartphone">';
		print img_object('', 'plot14@vignoble', 'class="hideonsmartphone"');
		
		if ($plot->fetchAll("ASC", "ref") > 0) {
			$plots = array(
				'0' => $langs->trans("All")
			);
			foreach ($plot->lines as $plotLine) {
				$key = $plotLine->id;
				$value = $plotLine->ref;
				$plots = array_merge($plots, array(
					$key => $value
				));
			}
			print $form->multiselectarray('multiplots', $plots, $plots, 0, 0, '', 0, '240');
		}
		print '</td>';
		
		// Note
		print '<td>';
		print '<textarea name="note" class="maxwidth100onsmartphone" rows="' . ROWS_1 . '">' . (GETPOST('note', 'alpha') ? GETPOST('note', 'alpha') : '') . '</textarea>';
		print '</td>';
		
		// Coverage declared
		print '<td class="nowrap">';
		print $formother->select_percent(GETPOST('coverage', 'int') ? GETPOST('coverage') : $object->coverage, 'coverage');
		print '</td>';
		
		print '<td align="center">';
		print '<input type="submit" class="button" value="' . $langs->trans("Add") . '">';
		print '</td></tr>';
		
		print '</table></form>';
		
		print '<br>';
	} // end form
	
	if ($projectstatic->id > 0) {
		if ($action == 'deleteline') {
			print $form->formconfirm($_SERVER["PHP_SELF"] . "?id=" . $object->id . '&lineid=' . GETPOST('lineid', 'int') . ($withproject ? '&withproject=1' : ''), $langs->trans("DeleteAPlot"), $langs->trans("ConfirmDeleteAPlot"), "confirm_delete", '', '', 1);
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
		if ($projectid)
			$params .= '&amp;projectid=' . $projectid;
		if ($withproject)
			$params .= '&amp;withproject=' . $withproject;
		
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
			print_liste_field_titre($arrayfields['t.plot']['label'], $_SERVER['PHP_SELF'], 't.plot,t.rowid', '', $params, '', $sortfield, $sortorder);
		if (! empty($arrayfields['t.note']['checked']))
			print_liste_field_titre($arrayfields['t.note']['label'], $_SERVER['PHP_SELF'], 't.note', '', $params, '', $sortfield, $sortorder);
		if (! empty($arrayfields['t.coverage']['checked']))
			print_liste_field_titre($arrayfields['t.coverage']['label'], $_SERVER['PHP_SELF'], 't.coverage', '', $params, '', $sortfield, $sortorder);
		
		print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"], "", '', '', 'align="right"', $sortfield, $sortorder, 'maxwidthsearch ');
		print "</tr>\n";
		
		// Fields title search
		print '<tr class="liste_titre">';
		// LIST_OF_TD_TITLE_SEARCH
		if (! empty($arrayfields['t.plot']['checked']))
			print '<td class="liste_titre"><input type="text" class="flat" name="search_plot" value="' . $search_plot . '"></td>';
		if (! empty($arrayfields['t.note']['checked']))
			print '<td class="liste_titre"><input type="text" class="flat" name="search_note" value="' . $search_note . '"></td>';
		if (! empty($arrayfields['t.coverage']['checked']))
			print '<td class="liste_titre"><input type="text" class="flat" name="search_coverage" value="' . $search_coverage . '"></td>';
			
			// Action column
		print '<td class="liste_titre" align="right">';
		$searchpitco = $form->showFilterAndCheckAddButtons($massactionbutton ? 1 : 0, 'checkforselect', 1);
		print $searchpitco;
		print '</td>';
		print '</tr>' . "\n";
		
		$plottask = new Plotcultivationtask($db);
		
		$plotfilter = array();
		$plotfilter[] = '';
		
		if ($plottask->fetchAll('', '', 0, 0, $plotfilter)) {
			
			$i = 0;
			$total = 0;
			$totalvalue = 0;
			$totalarray = array();
			foreach ($plottask->lines as $currplot) {
				$var = ! $var;
				print "<tr " . $bc[$var] . ">";
				
				// Plot
				if (! empty($arrayfields['t.plot']['checked'])) {
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
						$plottask->id = $task_time->fk_task;
						$plottask->ref = $task_time->ref;
						$plottask->label = $task_time->label;
						print $plottask->getNomUrl(1, 'withproject', 'time');
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
		}
		print '</div>';
		print "</form>";
	}
}

llxFooter();
$db->close();
