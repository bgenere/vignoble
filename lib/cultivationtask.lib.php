<?php

/*
 * Vignoble Module library for cultivation task management
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
 * \file lib/cultivationtask.lib.php
 * \ingroup cultivation
 * \brief
 *
 * Contains common functions for cultivation task management.
 */

/**
 * Initialize project Id with default cultivation project
 *
 * Also setup global key VIGNOBLE_ISCULTIVATIONPROJECT to true for tabs management
 *
 * @return $id Default cultivation project id, 0 if not found
 */
function setIsCultivationProject()
{
	Global $db, $conf, $user, $langs;
	
	if (! empty($conf->global->VIGNOBLE_CULTIVATIONPROJECT)) {
		$id = dolibarr_get_const($db, "VIGNOBLE_CULTIVATIONPROJECT", $conf->entity);
		$key = "VIGNOBLE_ISCULTIVATIONPROJECT";
		$conf->global->$key = true;
	}
	if ($id < 1) {
		$id = 0;
		setEventMessages('CultivationProjectNotDefined', null, 'warnings');
	}
	return $id;
}

/**
 * Display a project card summary.
 *
 * Shows header (ref and title) with key fields :
 * visibility, begin and end date, description, categories
 *
 * @param
 *        	object the project to display
 * @param
 *        	form the form object
 *        	
 */
function displayProjectHeaderCard($object, $form)
{
	Global $db, $conf, $user, $langs;
	
	// link to open full project
	$linkback = '<a href="' . DOL_URL_ROOT . '/projet/card.php?mainmenu=project&id=' . $object->id . '">' . $langs->trans("OpenFullProject") . '</a>';
	
	// Project title
	$projecttitle = '<div class="refidno">';
	$projecttitle .= $object->title;
	if ($object->thirdparty->id > 0) {
		$projecttitle .= '<br>' . $langs->trans('ThirdParty') . ' : ' . $object->thirdparty->getNomUrl(1, 'project');
	}
	$projecttitle .= '</div>';
	
	// print Project without navigation on list
	if ($conf->global->MAIN_VERSION_LAST_UPGRADE > "5") {
		dol_banner_tab($object, 'ref', $linkback, 0, 'ref', 'ref', $projecttitle, 0, '', '', '', '', 0, '', '', 0);
	} else {
		print '<table class="border" width="100%"><tr><td>';
		print $form->showrefnav($object, 'ref', $linkback, 0, 'ref', 'ref', $projecttitle, $param);
		print '</td></tr></table>';
	}
	// Projects attributes
	print '<div class="fichecenter">';
	
	// Left column
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border" width="100%">';
	
	// Visibility
	print '<tr><td class="titlefield">' . $langs->trans("Visibility") . '</td><td>';
	if ($object->public)
		print $langs->trans('SharedProject');
	else
		print $langs->trans('PrivateProject');
	print '</td></tr>';
	
	// Date start - end
	print '<tr><td>' . $langs->trans("DateStart") . ' - ' . $langs->trans("DateEnd") . '</td><td>';
	print dol_print_date($object->date_start, 'day');
	$end = dol_print_date($object->date_end, 'day');
	if ($end)
		print ' - ' . $end;
	print '</td></tr>';
	print '</table>';
	print '</div>';
	
	// Right column
	print '<div class="fichehalfright">';
	print '<div class="ficheaddleft">'; // add left space
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border" width="100%">';
	
	// Description
	print '<td class="titlefield tdtop">' . $langs->trans("Description") . '</td><td>';
	print nl2br($object->description);
	print '</td></tr>';
	
	// Categories
	if ($conf->categorie->enabled && $conf->global->MAIN_VERSION_LAST_UPGRADE > "5") {
		print '<tr><td valign="middle">' . $langs->trans("Categories") . '</td><td>';
		print $form->showCategories($object->id, 'project', 1);
		print "</td></tr>";
	}
	print '</table>';
	print '</div>';
	print '</div>';
	
	print '</div>';
	print '<div class="clearboth"></div>';
	
	dol_fiche_end();
}

/**
 * print the task ref and label with navigation links in task list.
 *
 * @param
 *        	object the task to display
 * @param
 *        	projectstatic the project linked to the task
 * @param
 *        	form the form object to display
 */
function displayTaskHeader($object, $projectstatic, $form)
{
	global $db, $conf, $langs, $user;
	
	$linkback = '<a href="cultivationtasks.php?withproject=1">' . $langs->trans("BackToList") . '</a>';
	// Task title
	$tasktitle = '<div class="refidno">';
	$tasktitle .= $object->label;
	$tasktitle .= '</div>';
	
	print '<table width="100%">';
	
	// Ref
	print '<tr><td>';
	print $form->showrefnav($object, 'ref', $linkback, 1, 'ref', 'ref', $tasktitle);
	print '</td></tr>';
	
	print "</table>";
}

/**
 * Show task lines with a particular parent
 *
 * @param string $inc
 *        	Line number (start to 0, then increased by recursive call)
 * @param string $parent
 *        	Id of parent project to show (0 to show all)
 * @param Task[] $lines
 *        	Array of lines
 * @param int $level
 *        	Level (start to 0, then increased/decrease by recursive call), or -1 to show all level in order of $lines without the recursive groupment feature.
 * @param string $var
 *        	Color
 * @param int $showproject
 *        	Show project columns
 * @param int $taskrole
 *        	Array of roles of user for each tasks
 * @param int $projectsListId
 *        	List of id of project allowed to user (string separated with comma)
 * @param int $addordertick
 *        	Add a tick to move task
 * @param int $projectidfortotallink
 *        	0 or Id of project to use on total line (link to see all time consumed for project)
 * @return void
 */
function showtasks(&$inc, $parent, &$lines, &$level, $var, $showproject, &$taskrole, $projectsListId = '', $addordertick = 0, $projectidfortotallink = 0)
{
	global $user, $bc, $langs;
	global $projectstatic, $taskstatic;
	
	$lastprojectid = 0;
	
	$projectsArrayId = explode(',', $projectsListId);
	
	$numlines = count($lines);
	
	// We declare counter as global because we want to edit them into recursive call
	global $total_projectlinesa_spent, $total_projectlinesa_planned, $total_projectlinesa_spent_if_planned;
	if ($level == 0) {
		$total_projectlinesa_spent = 0;
		$total_projectlinesa_planned = 0;
		$total_projectlinesa_spent_if_planned = 0;
	}
	
	for ($i = 0; $i < $numlines; $i ++) {
		if ($parent == 0 && $level >= 0)
			$level = 0; // if $level = -1, we dont' use sublevel recursion, we show all lines
				            
		// Process line
				            // print "i:".$i."-".$lines[$i]->fk_project.'<br>';
		
		if ($lines[$i]->fk_parent == $parent || $level < 0) // if $level = -1, we dont' use sublevel recursion, we show all lines
{
			// Show task line.
			$showline = 1;
			$showlineingray = 0;
			
			// If there is filters to use
			if (is_array($taskrole)) {
				// If task not legitimate to show, search if a legitimate task exists later in tree
				if (! isset($taskrole[$lines[$i]->id]) && $lines[$i]->id != $lines[$i]->fk_parent) {
					// So search if task has a subtask legitimate to show
					$foundtaskforuserdeeper = 0;
					searchTaskInChild($foundtaskforuserdeeper, $lines[$i]->id, $lines, $taskrole);
					// print '$foundtaskforuserpeeper='.$foundtaskforuserdeeper.'<br>';
					if ($foundtaskforuserdeeper > 0) {
						$showlineingray = 1; // We will show line but in gray
					} else {
						$showline = 0; // No reason to show line
					}
				}
			} else {
				// Caller did not ask to filter on tasks of a specific user (this probably means he want also tasks of all users, into public project
				// or into all other projects if user has permission to).
				if (empty($user->rights->projet->all->lire)) {
					// User is not allowed on this project and project is not public, so we hide line
					if (! in_array($lines[$i]->fk_project, $projectsArrayId)) {
						// Note that having a user assigned to a task into a project user has no permission on, should not be possible
						// because assignement on task can be done only on contact of project.
						// If assignement was done and after, was removed from contact of project, then we can hide the line.
						$showline = 0;
					}
				}
			}
			
			if ($showline) {
				// Break on a new project
				if ($parent == 0 && $lines[$i]->fk_project != $lastprojectid) {
					$var = ! $var;
					$lastprojectid = $lines[$i]->fk_project;
				}
				
				print '<tr ' . $bc[$var] . ' id="row-' . $lines[$i]->id . '">' . "\n";
				
				if ($showproject) {
					// Project ref
					print "<td>";
					// if ($showlineingray) print '<i>';
					$projectstatic->id = $lines[$i]->fk_project;
					$projectstatic->ref = $lines[$i]->projectref;
					$projectstatic->public = $lines[$i]->public;
					$projectstatic->title = $lines[$i]->projectlabel;
					if ($lines[$i]->public || in_array($lines[$i]->fk_project, $projectsArrayId) || ! empty($user->rights->projet->all->lire))
						print $projectstatic->getNomUrl(1);
					else
						print $projectstatic->getNomUrl(1, 'nolink');
						// if ($showlineingray) print '</i>';
					print "</td>";
					
					// Project status
					print '<td>';
					$projectstatic->statut = $lines[$i]->projectstatus;
					print $projectstatic->getLibStatut(2);
					print "</td>";
				}
				
				// Ref of task
				print '<td>';
				if ($showlineingray) {
					print '<i>' . img_object('', 'projecttask') . ' ' . $lines[$i]->ref . '</i>';
				} else {
					$taskstatic->id = $lines[$i]->id;
					$taskstatic->ref = $lines[$i]->ref;
					$taskstatic->label = ($taskrole[$lines[$i]->id] ? $langs->trans("YourRole") . ': ' . $taskrole[$lines[$i]->id] : '');
					print getTaskUrl($taskstatic, 1, 'withproject');
				}
				print '</td>';
				
				// Title of task
				print "<td>";
				if ($showlineingray)
					print '<i>';
					// else print '<a href="'.DOL_URL_ROOT.'/projet/tasks/task.php?id='.$lines[$i]->id.'&withproject=1">';
				for ($k = 0; $k < $level; $k ++) {
					print "&nbsp; &nbsp; &nbsp;";
				}
				print $lines[$i]->label;
				if ($showlineingray)
					print '</i>';
					// else print '</a>';
				print "</td>\n";
				
				// Date start
				print '<td align="center">';
				print dol_print_date($lines[$i]->date_start, 'dayhour');
				print '</td>';
				
				// Date end
				print '<td align="center">';
				$taskstatic->projectstatus = $lines[$i]->projectstatus;
				$taskstatic->progress = $lines[$i]->progress;
				$taskstatic->fk_statut = $lines[$i]->status;
				$taskstatic->datee = $lines[$i]->date_end;
				print dol_print_date($lines[$i]->date_end, 'dayhour');
				if ($taskstatic->hasDelay())
					print img_warning($langs->trans("Late"));
				print '</td>';
				
				$plannedworkloadoutputformat = 'allhourmin';
				$timespentoutputformat = 'allhourmin';
				if (! empty($conf->global->PROJECT_PLANNED_WORKLOAD_FORMAT))
					$plannedworkloadoutputformat = $conf->global->PROJECT_PLANNED_WORKLOAD_FORMAT;
				if (! empty($conf->global->PROJECT_TIMES_SPENT_FORMAT))
					$timespentoutputformat = $conf->global->PROJECT_TIME_SPENT_FORMAT;
					
					// Planned Workload (in working hours)
				print '<td align="right">';
				$fullhour = convertSecondToTime($lines[$i]->planned_workload, $plannedworkloadoutputformat);
				$workingdelay = convertSecondToTime($lines[$i]->planned_workload, 'all', 86400, 7); // TODO Replace 86400 and 7 to take account working hours per day and working day per weeks
				if ($lines[$i]->planned_workload != '') {
					print $fullhour;
					// TODO Add delay taking account of working hours per day and working day per week
					// if ($workingdelay != $fullhour) print '<br>('.$workingdelay.')';
				}
				// else print '--:--';
				print '</td>';
				
				// Time spent
				print '<td align="right">';
				if ($showlineingray)
					print '<i>';
				else
					print '<a href="' . DOL_URL_ROOT . '/projet/tasks/time.php?id=' . $lines[$i]->id . ($showproject ? '' : '&withproject=1') . '">';
				if ($lines[$i]->duration)
					print convertSecondToTime($lines[$i]->duration, $timespentoutputformat);
				else
					print '--:--';
				if ($showlineingray)
					print '</i>';
				else
					print '</a>';
				print '</td>';
				
				// Progress calculated (Note: ->duration is time spent)
				print '<td align="right">';
				if ($lines[$i]->planned_workload || $lines[$i]->duration) {
					if ($lines[$i]->planned_workload)
						print round(100 * $lines[$i]->duration / $lines[$i]->planned_workload, 2) . ' %';
					else
						print $langs->trans('WorkloadNotDefined');
				}
				print '</td>';
				
				// Progress declared
				print '<td align="right">';
				if ($lines[$i]->progress != '') {
					print $lines[$i]->progress . ' %';
				}
				print '</td>';
				
				// Tick to drag and drop
				if ($addordertick) {
					print '<td align="center" class="tdlineupdown hideonsmartphone">&nbsp;</td>';
				}
				
				print "</tr>\n";
				
				if (! $showlineingray)
					$inc ++;
				
				if ($level >= 0) // Call sublevels
{
					$level ++;
					if ($lines[$i]->id)
						showtasks($inc, $lines[$i]->id, $lines, $level, $var, $showproject, $taskrole, $projectsListId, $addordertick);
					$level --;
				}
				
				$total_projectlinesa_spent += $lines[$i]->duration;
				$total_projectlinesa_planned += $lines[$i]->planned_workload;
				if ($lines[$i]->planned_workload)
					$total_projectlinesa_spent_if_planned += $lines[$i]->duration;
			}
		} else {
			// $level--;
		}
	}
	
	if (($total_projectlinesa_planned > 0 || $total_projectlinesa_spent > 0) && $level <= 0) {
		print '<tr class="liste_total nodrag nodrop">';
		print '<td class="liste_total">' . $langs->trans("Total") . '</td>';
		if ($showproject)
			print '<td></td><td></td>';
		print '<td></td>';
		print '<td></td>';
		print '<td></td>';
		print '<td align="right" class="nowrap liste_total">';
		print convertSecondToTime($total_projectlinesa_planned, 'allhourmin');
		print '</td>';
		print '<td align="right" class="nowrap liste_total">';
		if ($projectidfortotallink > 0)
			print '<a href="' . DOL_URL_ROOT . '/projet/tasks/time.php?projectid=' . $projectidfortotallink . ($showproject ? '' : '&withproject=1') . '">';
		print convertSecondToTime($total_projectlinesa_spent, 'allhourmin');
		if ($projectidfortotallink > 0)
			print '</a>';
		print '</td>';
		print '<td align="right" class="nowrap liste_total">';
		if ($total_projectlinesa_planned)
			print round(100 * $total_projectlinesa_spent / $total_projectlinesa_planned, 2) . ' %';
		print '</td>';
		print '<td></td>';
		if ($addordertick)
			print '<td class="hideonsmartphone"></td>';
		print '</tr>';
	}
	
	return $inc;
}

/**
 * Return cultivation task clicable name (with picto eventually)
 *
 * @param object $task
 *        	the task to process
 * @param int $withpicto
 *        	0=No picto, 1=Include picto into link, 2=Only picto
 * @param string $option
 *        	'withproject' or ''
 * @param string $mode
 *        	Mode 'task', 'time', 'contact', 'note', document' define page to link to.
 * @param int $addlabel
 *        	0=Default, 1=Add label into string, >1=Add first chars into string
 * @param string $sep
 *        	Separator between ref and label if option addlabel is set
 * @param int $notooltip
 *        	1=Disable tooltip
 * @return string Chaine avec URL
 */
function getTaskUrl($task, $withpicto = 0, $option = '', $mode = 'cultivationtask', $addlabel = 0, $sep = ' - ', $notooltip = 0)
{
	global $conf, $langs, $user;
	
	if (! empty($conf->dol_no_mouse_hover))
		$notooltip = 1; // Force disable tooltips
	
	$result = '';
	$label = '<u>' . $langs->trans("ShowTask") . '</u>';
	if (! empty($task->ref))
		$label .= '<br><b>' . $langs->trans('Ref') . ':</b> ' . $task->ref;
	if (! empty($task->label))
		$label .= '<br><b>' . $langs->trans('LabelTask') . ':</b> ' . $task->label;
	if ($task->date_start || $task->date_end) {
		$label .= "<br>" . get_date_range($task->date_start, $task->date_end, '', $langs, 0);
	}
	
	$url = DOL_URL_ROOT . '/custom/vignoble/' . $mode . '.php?id=' . $task->id . ($option == 'withproject' ? '&withproject=1' : '');
	
	$linkclose = '';
	if (empty($notooltip)) {
		if (! empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
			$label = $langs->trans("ShowTask");
			$linkclose .= ' alt="' . dol_escape_htmltag($label, 1) . '"';
		}
		$linkclose .= ' title="' . dol_escape_htmltag($label, 1) . '"';
		$linkclose .= ' class="classfortooltip"';
	}
	
	$linkstart = '<a href="' . $url . '"';
	$linkstart .= $linkclose . '>';
	$linkend = '</a>';
	
	$picto = 'projecttask';
	
	if ($withpicto)
		$result .= ($linkstart . img_object(($notooltip ? '' : $label), $picto, ($notooltip ? '' : 'class="classfortooltip"'), 0, 0, $notooltip ? 0 : 1) . $linkend);
	if ($withpicto && $withpicto != 2)
		$result .= ' ';
	if ($withpicto != 2)
		$result .= $linkstart . $task->ref . $linkend . (($addlabel && $task->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');
	return $result;
}

/**
 * Create a new task for the project in database
 * Use the getpost variable from the new task form
 */
function createNewTask()
{
	global $db, $conf, $langs, $user;
}

/**
 * Return array list of users
 *
 * @param int $force_entity
 *        	0 or Id of environment to force
 * @param string $morefilter
 *        	Add more filters into sql request
 * @param int $noactive
 *        	Show only active users (this will also happened whatever is this option if USER_HIDE_INACTIVE_IN_COMBOBOX is on).
 * @return array list of users
 *        
 */
function get_dolusers($force_entity = 0, $morefilter = '', $noactive = 0)
{
	global $db, $conf, $user, $langs;
	
	// Build SQL request
	$sql = "SELECT DISTINCT u.rowid as id, u.lastname as nom, u.firstname, u.statut, u.login, u.admin, u.entity";
	if (! empty($conf->multicompany->enabled) && $conf->entity == 1 && $user->admin && ! $user->entity) {
		$sql .= ", e.label";
	}
	$sql .= " FROM " . MAIN_DB_PREFIX . "user as u";
	if (! empty($conf->multicompany->enabled) && $conf->entity == 1 && $user->admin && ! $user->entity) {
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "entity as e ON e.rowid=u.entity";
		if ($force_entity)
			$sql .= " WHERE u.entity IN (0," . $force_entity . ")";
		else
			$sql .= " WHERE u.entity IS NOT NULL";
	} else {
		if (! empty($conf->multicompany->transverse_mode)) {
			$sql .= ", " . MAIN_DB_PREFIX . "usergroup_user as ug";
			$sql .= " WHERE ug.fk_user = u.rowid";
			$sql .= " AND ug.entity = " . $conf->entity;
		} else {
			$sql .= " WHERE u.entity IN (0," . $conf->entity . ")";
		}
	}
	if (! empty($user->societe_id))
		$sql .= " AND u.fk_soc = " . $user->societe_id;
	if (! empty($conf->global->USER_HIDE_INACTIVE_IN_COMBOBOX) || $noactive)
		$sql .= " AND u.statut <> 0";
	if (! empty($morefilter))
		$sql .= " " . $morefilter;
	$sql .= " ORDER BY u.lastname ASC";
	
	dol_syslog(__METHOD__, LOG_DEBUG);
	$resql = $db->query($sql);
	if ($resql) {
		$num = $db->num_rows($resql);
		if ($num) {
			$userlist = $resql;
		} else {
			$userlist = "none";
		}
	} else {
		dol_print_error($this->db);
		$userlist = null;
	}
	return $userlist;
}

/**
 * Get list of users who could be allocated to project task
 *
 * @param Task $task
 *        	the current task
 * @param Project $projectstatic
 *        	the cultivation project
 * @param  $all
 *        	flag to remove the all option
 * @return array[] list of contributors for project
 */
function getProjectContributors($task, $projectstatic, $all = 1)
{
	Global $db, $conf, $user, $langs;
	
	if ($task->project->public)
		$contributorsofproject = get_dolusers(); // get all users
	else
		$contributorsofproject = $projectstatic->Liste_Contact(- 1, 'internal'); // Only users of project. // selection of users
	
	if ($all){
	$contributors = array(
		'0' => $langs->trans("All")
	);
	} else {
		$contributors = array();
		
	}
	
	foreach ($contributorsofproject as $contributor) {
		$key = $contributor["id"];
		$value = $contributor["firstname"] . ' ' . $contributor["lastname"];
		$contributors[$key] = $value;
	}
	return $contributors;
}
    