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
		$object->fetch($id, $ref);
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
		$action = '';
	} else {
		setEventMessages(null, $langs->trans($plotcultivation->errors), 'errors');
		$error ++;
	}
}

if ($action == 'updateline' && ! $_POST["cancel"] && $user->rights->projet->creer) {
	/**
	 * Update an existing line
	 */
	$error = 0;
	$plotcultivation = new Plotcultivationtask($db);
	$lineid = GETPOST('lineid', int);
	if ($plotcultivation->fetch($lineid)) {
		$plotcultivation->note = GETPOST('note', 'alpha');
		$plotcultivation->coverage = GETPOST('coverage', 'int');
		$result = $plotcultivation->update($user);
	} else
		$result = - 1;
	
	if ($result >= 0) {
		setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
		$action = '';
	} else {
		setEventMessages(null, $langs->trans($plotcultivation->errors), 'errors');
		$error ++;
	}
}

if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->projet->creer) {
	/**
	 * Delete an existing line
	 */
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

if (($id > 0 || ! empty($ref))) {
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
			displayProjectHeaderCard($projectstatic, $form);
		}
	}
	
	/**
	 * - Display task summary card
	 */
	print '<div class="fiche">';
	$head = task_prepare_head($object);
	dol_fiche_head($head, 'cultivationtaskplot', $langs->trans("Plot"), 0, 'projecttask');
	
	displayTaskHeader($object, $projectstatic, $form);
	
	if ($action == 'deleteline') {
		$lineid = GETPOST('lineid', 'int');
		$currplot = new Plotcultivationtask($db);
		if ($currplot->fetch($lineid)) {
			$plot = new plot($db);
			$plot->fetch($currplot->fk_plot);
			print $form->formconfirm($_SERVER["PHP_SELF"] . "?id=" . $object->id . '&lineid=' . $lineid . ($withproject ? '&withproject=1' : ''), $langs->trans("DeleteLinktoPlot"), $langs->trans("ConfirmDeleteLinktoPlot") . ' ' . $plot->ref, "confirm_delete", '', '', 1);
		}
	}
	
	/**
	 * Display Form to add a plot
	 */
	if ($user->rights->projet->lire) {
				
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
				$plots[$key] = $value;
			}
			print $form->multiselectarray('multiplots', $plots, '', 1, 0, '', 0, '240');
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
		
		if ($plottask->fetchAll('', '', 0, 0, $plotfilter, 'AND')) {
			foreach ($plottask->lines as $currplot) {
				$var = ! $var;
				print "<tr " . $bc[$var] . ">";
				// Plot url
				if (! empty($arrayfields['t.plot']['checked'])) {
					$plot = new plot($db);
					$plot->fetch($currplot->fk_plot);
					print '<td class="nowrap">';
					print $plot->getNomUrl(1, 'plot');
					print '</td>';
				}
				// Note
				if (! empty($arrayfields['t.note']['checked'])) {
					print '<td align="left">';
					if (GETPOST('action') == 'editline' && GETPOST('lineid') == $currplot->id) {
						print '<textarea name="note" width="95%" rows="' . ROWS_1 . '">' . $currplot->note . '</textarea>';
					} else {
						print dol_nl2br($currplot->note);
					}
					print '</td>';
				}
				
				// Coverage
				if (! empty($arrayfields['t.coverage']['checked'])) {
					print '<td>';
					if (GETPOST('action') == 'editline' && GETPOST('lineid') == $currplot->id) {
						print '<input type="hidden" name="old_coverage" value="' . $currplot->coverage . '">';
						print $formother->select_percent(GETPOST('coverage', 'int') ? GETPOST('coverage') : $currplot->coverage, 'coverage');
					} else {
						print $currplot->coverage . '%';
					}
					print '</td>';
				}
				
				// Action column
				print '<td class="right" valign="middle" width="80">';
				if ($action == 'editline' && GETPOST('lineid') == $currplot->id) {
					print '<input type="hidden" name="lineid" value="' . GETPOST('lineid') . '">';
					print '<input type="submit" class="button" name="save" value="' . $langs->trans("Save") . '">';
					print '<br>';
					print '<input type="submit" class="button" name="cancel" value="' . $langs->trans('Cancel') . '">';
				} else 
					if ($user->rights->projet->creer) {
						print '&nbsp;';
						print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $currplot->fk_task . '&amp;action=editline&amp;lineid=' . $currplot->id . ($withproject ? '&amp;withproject=1' : '') . '">';
						print img_edit();
						print '</a>';
						
						print '&nbsp;';
						print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $currplot->fk_task . '&amp;action=deleteline&amp;lineid=' . $currplot->id . ($withproject ? '&amp;withproject=1' : '') . '">';
						print img_delete();
						print '</a>';
					}
				print '</td>';
			}
			print '</tr>';
			print "</table>";
		}
		print '</div>';
		print "</form>";
	}
}
print '/<div>';

llxFooter();
$db->close();
