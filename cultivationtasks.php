<?php
/* Copyright (C) 2005      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2016 Bruno Généré <bgenere@webiseasy.org>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file       cultivationtasks.php
 *	\ingroup    cultivation
 *	\brief      List all tasks of the default cultivation project
 */

@include './tpl/maindolibarr.inc.php';

dol_include_once('/projet/class/project.class.php');
dol_include_once('/projet/class/task.class.php');
dol_include_once('/core/lib/project.lib.php');
dol_include_once('/core/lib/date.lib.php');
dol_include_once('/core/lib/admin.lib.php');
dol_include_once('/core/class/html.formother.class.php');
dol_include_once('/core/class/extrafields.class.php');

$langs->load("users");
$langs->load("projects");
$langs->load("vignoble@vignoble");

/**
 * Initialize project Id with default cultivation project
 * and setup boolean for tab management
 */
if (! empty($conf->global->VIGNOBLE_CULTIVATIONPROJECT)) {
	$id = dolibarr_get_const($db, "VIGNOBLE_CULTIVATIONPROJECT", $conf->entity);
	$key= "VIGNOBLE_ISCULTIVATIONPROJECT";
	$conf->global->$key=true;
}
if ($id<1){
	$id = 0;
	setEventMessages('CultivationProjectNotDefined',null,'warnings');
}

/**
 * Get page variables
 * 
 */
$action = GETPOST('action', 'alpha');
$ref = GETPOST('ref', 'alpha');
$taskref = GETPOST('taskref', 'alpha');
$backtopage=GETPOST('backtopage','alpha');
$cancel=GETPOST('cancel');
$mode = GETPOST('mode', 'alpha');

$mine = ($mode == 'mine' ? 1 : 0);
//if (! $user->rights->projet->all->lire) $mine=1;	// Special for projects

$object = new Project($db);
$taskstatic = new Task($db);
$extrafields_project = new ExtraFields($db);
$extrafields_task = new ExtraFields($db);

include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php';  // Must be include, not include_once

//var_dump($object);

if ($id > 0 || ! empty($ref))
{
	$extralabels_projet=$extrafields_project->fetch_name_optionals_label($object->table_element);
}
$extralabels_task=$extrafields_task->fetch_name_optionals_label($taskstatic->table_element);

// Security check
$socid=0;
if ($user->societe_id > 0) $socid = $user->societe_id;
$result = restrictedArea($user, 'projet', $id,'projet&project');

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array('projecttaskcard','globalcard'));

$progress=GETPOST('progress', 'int');
$label=GETPOST('label', 'alpha');
$description=GETPOST('description');
$planned_workload=GETPOST('planned_workloadhour')*3600+GETPOST('planned_workloadmin')*60;

$userAccess=0;

/**
 * Process actions
 */

/**
 * - Create new task 
 */
if ($action == 'createtask' && $user->rights->projet->creer)
{
	$error=0;

	$date_start = dol_mktime($_POST['dateohour'],$_POST['dateomin'],0,$_POST['dateomonth'],$_POST['dateoday'],$_POST['dateoyear'],'user');
	$date_end = dol_mktime($_POST['dateehour'],$_POST['dateemin'],0,$_POST['dateemonth'],$_POST['dateeday'],$_POST['dateeyear'],'user');

	if (! $cancel)
	{
		if (empty($taskref))
		{
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Ref")), null, 'errors');
			$action='create';
			$error++;
		}
	    if (empty($label))
		{
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Label")), null, 'errors');
			$action='create';
			$error++;
		}
		else if (empty($_POST['task_parent']))
		{
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("ChildOfTask")), null, 'errors');
			$action='create';
			$error++;
		}

		if (! $error)
		{
			$tmparray=explode('_',$_POST['task_parent']);
			$projectid=$tmparray[0];
			if (empty($projectid)) $projectid = $id; // If projectid is ''
			$task_parent=$tmparray[1];
			if (empty($task_parent)) $task_parent = 0;	// If task_parent is ''

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
			$ret = $extrafields_task->setOptionalsFromPost($extralabels_task,$task);

			$taskid = $task->create($user);

			if ($taskid > 0)
			{
				$result = $task->add_contact($_POST["userid"], 'TASKEXECUTIVE', 'internal');
			}
			else
			{
			    setEventMessages($task->error,$task->errors,'errors');
			}
		}

		if (! $error)
		{
			if (! empty($backtopage))
			{
				header("Location: ".$backtopage);
				exit;
			}
			else if (empty($projectid))
			{
				header("Location: ".DOL_URL_ROOT.'/projet/tasks/list.php'.(empty($mode)?'':'?mode='.$mode));
				exit;
			}
			$id = $projectid;
		}
	}
	else
	{
		if (! empty($backtopage))
		{
			header("Location: ".$backtopage);
			exit;
		}
		else if (empty($id))
		{
			// We go back on task list
			header("Location: ".DOL_URL_ROOT.'/projet/tasks/list.php'.(empty($mode)?'':'?mode='.$mode));
			exit;
		}
	}
}


/**
 *  Display View
 */
$form=new Form($db);
$formother=new FormOther($db);
$taskstatic = new Task($db);
$userstatic=new User($db);

// page header (title and help url) 
$title=$langs->trans("Cultivation").' - '.$langs->trans("Tasks").' - '.$object->ref.' '.$object->title;
if (! empty($conf->global->MAIN_HTML_TITLE) && preg_match('/projectnameonly/',$conf->global->MAIN_HTML_TITLE) && $object->name) $title=$object->ref.' '.$object->name.' - '.$langs->trans("Tasks");
$help_url="EN:Module_Projects|FR:Module_Projets|ES:M&oacute;dulo_Proyectos";
llxHeader("",$title,$help_url);

if ($id > 0 || ! empty($ref))
{
	/**
	 * Print Project Card
	 */
	// get current project
	$object->fetch($id, $ref);
	$object->fetch_thirdparty();
	$res=$object->fetch_optionals($object->id,$extralabels_projet);
	
	// verify role of users
	//$userAccess = $object->restrictedProjectArea($user,'read');
	$userWrite  = $object->restrictedProjectArea($user,'write');
	//$userDelete = $object->restrictedProjectArea($user,'delete');
	//print "userAccess=".$userAccess." userWrite=".$userWrite." userDelete=".$userDelete;

	// initialize tab to tasks if not defined
	$tab=GETPOST('tab')?GETPOST('tab'):'cultivationtasks';
	$head=project_prepare_head($object);
	//var_dump($head);
	dol_fiche_head($head, $tab, $langs->trans("Project"),0,($object->public?'projectpub':'project'));

	// display only my task ?? already defined 
	$param=($mode=='mine'?'&mode=mine':'');

    // prepare project card
    // 
    $linkback = '<a href="'.DOL_URL_ROOT.'/projet/card.php?mainmenu=project&id='.$id.'">'.$langs->trans("OpenFullProject").'</a>';
    
    $morehtmlref='<div class="refidno">';
    // Title
    $morehtmlref.=$object->title;
    // Thirdparty
    if ($object->thirdparty->id > 0) 
    {
        $morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . $object->thirdparty->getNomUrl(1, 'project');
    }
    $morehtmlref.='</div>';
    
    // Define a complementary filter for search of next/prev ref.
    if (! $user->rights->projet->all->lire)
    {
        $objectsListId = $object->getProjectsAuthorizedForUser($user,0,0);
        $object->next_prev_filter=" rowid in (".(count($objectsListId)?join(',',array_keys($objectsListId)):'0').")";
    }
    // banner without navigation on list
    dol_banner_tab($object, 'ref', $linkback, 0, 'ref', 'ref', $morehtmlref);

    print '<div class="fichecenter">';
    print '<div class="fichehalfleft">';
    print '<div class="underbanner clearboth"></div>';

    print '<table class="border" width="100%">';

    // Visibility
    print '<tr><td class="titlefield">'.$langs->trans("Visibility").'</td><td>';
    if ($object->public) print $langs->trans('SharedProject');
    else print $langs->trans('PrivateProject');
    print '</td></tr>';

	// Date start - end
    print '<tr><td>'.$langs->trans("DateStart").' - '.$langs->trans("DateEnd").'</td><td>';
    print dol_print_date($object->date_start,'day');
    $end=dol_print_date($object->date_end,'day');
    if ($end) print ' - '.$end;
    print '</td></tr>';
    
    // Budget
    print '<tr><td>'.$langs->trans("Budget").'</td><td>';
    if (strcmp($object->budget_amount, '')) print price($object->budget_amount,'',$langs,1,0,0,$conf->currency);
    print '</td></tr>';

    // Other attributes
    $cols = 2;
    include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';
    
    print '</table>';
    
    print '</div>';
    print '<div class="fichehalfright">';
    print '<div class="ficheaddleft">';
    print '<div class="underbanner clearboth"></div>';
    
    print '<table class="border" width="100%">';
    
    // Description
    print '<td class="titlefield tdtop">'.$langs->trans("Description").'</td><td>';
    print nl2br($object->description);
    print '</td></tr>';

    // Categories
    if($conf->categorie->enabled) {
        print '<tr><td valign="middle">'.$langs->trans("Categories").'</td><td>';
        print $form->showCategories($object->id,'project',1);
        print "</td></tr>";
    }
    
    print '</table>';
    
    print '</div>';
    print '</div>';
    print '</div>';
    
    print '<div class="clearboth"></div>';


	dol_fiche_end();
}


if ($action == 'create' && $user->rights->projet->creer && (empty($object->thirdparty->id) || $userWrite > 0))
{
	/**
	 * Display empty Task card
	 */
	if ($id > 0 || ! empty($ref)) print '<br>';

	print load_fiche_titre($langs->trans("NewTask"), '', 'title_project');

	print '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="createtask">';
	print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	if (! empty($object->id)) print '<input type="hidden" name="id" value="'.$object->id.'">';
	if (! empty($mode)) print '<input type="hidden" name="mode" value="'.$mode.'">';
	
	dol_fiche_head('');

	print '<table class="border" width="100%">';

	$defaultref='';
	$obj = empty($conf->global->PROJECT_TASK_ADDON)?'mod_task_simple':$conf->global->PROJECT_TASK_ADDON;
	if (! empty($conf->global->PROJECT_TASK_ADDON) && is_readable(DOL_DOCUMENT_ROOT ."/core/modules/project/task/".$conf->global->PROJECT_TASK_ADDON.".php"))
	{
		require_once DOL_DOCUMENT_ROOT ."/core/modules/project/task/".$conf->global->PROJECT_TASK_ADDON.'.php';
		$modTask = new $obj;
		$defaultref = $modTask->getNextValue($object->thirdparty,null);
	}

	if (is_numeric($defaultref) && $defaultref <= 0) $defaultref='';

	// Ref
	print '<tr><td class="titlefieldcreate"><span class="fieldrequired">'.$langs->trans("Ref").'</span></td><td>';
	print ($_POST["ref"]?$_POST["ref"]:$defaultref);
	print '<input type="hidden" name="taskref" value="'.($_POST["ref"]?$_POST["ref"]:$defaultref).'">';
	print '</td></tr>';
	// task label
	print '<tr><td class="fieldrequired">'.$langs->trans("Label").'</td><td>';
	print '<input type="text" name="label" class="flat minwidth300" value="'.$label.'">';
	print '</td></tr>';

	// List of projects - not needed we are on the cultivation project
	print '<tr style="display: none;"><td>'.$langs->trans("ChildOfTask").'</td><td>';
	print $formother->selectProjectTasks(GETPOST('task_parent'),$projectid?$projectid:$object->id, 'task_parent', 0, 0, 1, 1);
	print '</td></tr>';
	// User responsible by default the current user Id
	print '<tr><td>'.$langs->trans("AffectedTo").'</td><td>';
	$contactsofproject=(! empty($object->id)?$object->getListContactId('internal'):'');
	if (count($contactsofproject))
	{
		print $form->select_dolusers($user->id, 'userid', 0, '', 0, '', $contactsofproject, '', 0, 0, '', 0, '', 'maxwidth300');
	}
	else
	{
		print $langs->trans("NoUserAssignedToTheProject");
	}
	print '</td></tr>';

	// Date start
	print '<tr><td>'.$langs->trans("DateStart").'</td><td>';
	print $form->select_date(($date_start?$date_start:''),'dateo',1,1,0,'',1,1,1);
	print '</td></tr>';

	// Date end
	print '<tr><td>'.$langs->trans("DateEnd").'</td><td>';
	print $form->select_date(($date_end?$date_end:-1),'datee',1,1,0,'',1,1,1);
	print '</td></tr>';

	// planned workload
	print '<tr><td>'.$langs->trans("PlannedWorkload").'</td><td>';
	print $form->select_duration('planned_workload', $planned_workload?$planned_workload : $object->planned_workload,0,'text');
	print '</td></tr>';

	// Progress
	print '<tr><td>'.$langs->trans("ProgressDeclared").'</td><td colspan="3">';
	print $formother->select_percent($progress,'progress');
	print '</td></tr>';

	// Description
	print '<tr><td valign="top">'.$langs->trans("Description").'</td>';
	print '<td>';
	print '<textarea name="description" wrap="soft" cols="80" rows="'.ROWS_3.'">'.$description.'</textarea>';
	print '</td></tr>';

	// Other options
	$parameters=array();
	$reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action); // Note that $action and $object may have been modified by hook
	if (empty($reshook) && ! empty($extrafields_task->attribute_label))
	{
		print $object->showOptionals($extrafields_task,'edit');
	}

	print '</table>';

	dol_fiche_end();

	print '<div align="center">';
	print '<input type="submit" class="button" name="add" value="'.$langs->trans("Add").'">';
	print ' &nbsp; &nbsp; ';
	print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
	print '</div>';

	print '</form>';

}
else if ($id > 0 || ! empty($ref))
{
	/** 
	 * Display cultivation tasks
	 */

	// add new task button
	print '<div class="tabsAction">';
	if ($user->rights->projet->all->creer || $user->rights->projet->creer)
	{
		if ($object->public || $userWrite > 0)
		{
			print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=create'.$param.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$object->id).'">'.$langs->trans('AddTask').'</a>';
		}
		else
		{
			print '<a class="butActionRefused" href="#" title="'.$langs->trans("NotOwnerOfProject").'">'.$langs->trans('AddTask').'</a>';
		}
	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.$langs->trans("NotEnoughPermissions").'">'.$langs->trans('AddTask').'</a>';
	}
	print '</div>';

	// Task list
	$title=$langs->trans("ListOfTasks");
	// TODO change link when page ready
	$linktotasks='<a href="'.DOL_URL_ROOT.'/projet/tasks/time.php?projectid='.$object->id.'&withproject=1">'.$langs->trans("GoToListOfTimeConsumed").'</a>';
	
	print load_fiche_titre($title,$linktotasks,'title_generic.png');
	
	// Get list of tasks in tasksarray and taskarrayfiltered
	// We need all tasks (even not limited to a user because a task to user can have a parent that is not affected to him).
	$tasksarray=$taskstatic->getTasksArray(0, 0, $object->id, $socid, 0);
	// We load also tasks limited to a particular user
	$tasksrole=($mode=='mine' ? $taskstatic->getUserRolesForProjectsOrTasks(0,$user,$object->id,0) : '');
	//var_dump($tasksarray);
	//var_dump($tasksrole);

	if (! empty($conf->use_javascript_ajax))
	{
		include DOL_DOCUMENT_ROOT.'/core/tpl/ajaxrow.tpl.php';
	}
	// Print task list
	print '<table id="tablelines" class="noborder" width="100%">';
	print '<tr class="liste_titre nodrag nodrop">';
	print '<td width="100">'.$langs->trans("RefTask").'</td>';
	print '<td>'.$langs->trans("LabelTask").'</td>';
	print '<td align="center">'.$langs->trans("DateStart").'</td>';
	print '<td align="center">'.$langs->trans("DateEnd").'</td>';
	print '<td align="right">'.$langs->trans("PlannedWorkload").'</td>';
	print '<td align="right">'.$langs->trans("TimeSpent").'</td>';
	print '<td align="right">'.$langs->trans("ProgressCalculated").'</td>';
	print '<td align="right">'.$langs->trans("ProgressDeclared").'</td>';
	print '<td>&nbsp;</td>';
	print "</tr>\n";

	if (count($tasksarray) > 0)
	{
    	// Link to switch in "my task" / "all task"
    	print '<tr class="liste_titre nodrag nodrop"><td colspan="9">';
    	if ($mode == 'mine')
    	{
    	    print '<a href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">'.$langs->trans("DoNotShowMyTasksOnly").'</a>';
    	}
    	else
    	{
    	    print '<a href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&mode=mine">'.$langs->trans("ShowMyTasksOnly").'</a>';
    	}
    	print '</td></tr>';
	
	    // Show all lines in taskarray (recursive function to go down on tree)
		$j=0; $level=0;
		$nboftaskshown=showtasks($j, 0, $tasksarray, $level, true, 0, $tasksrole, $object->id, 1, $object->id);
	}
	else
	{
		print '<tr '.$bc[false].'><td colspan="9" class="opacitymedium">'.$langs->trans("NoTasks").'</td></tr>';
	}
	print "</table>";


	// Test if database is clean. If not we clean it.
	//print 'mode='.$_REQUEST["mode"].' $nboftaskshown='.$nboftaskshown.' count($tasksarray)='.count($tasksarray).' count($tasksrole)='.count($tasksrole).'<br>';
	if (! empty($user->rights->projet->all->lire))	// We make test to clean only if user has permission to see all (test may report false positive otherwise)
	{
		if ($mode=='mine')
		{
			if ($nboftaskshown < count($tasksrole))
			{
				include_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
				cleanCorruptedTree($db, 'projet_task', 'fk_task_parent');
			}
		}
		else
		{
			if ($nboftaskshown < count($tasksarray))
			{
				include_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
				cleanCorruptedTree($db, 'projet_task', 'fk_task_parent');
			}
		}
	}
}
else {
	// project not defined
	print '<a href="'.DOL_URL_ROOT.'/custom/vignoble/admin/module_settings.php">'.$langs->trans("VignobleSetup").'</a>';
}


llxFooter();

$db->close();

/**
 * Show task lines with a particular parent
 *
 * @param	string	   	$inc				Line number (start to 0, then increased by recursive call)
 * @param   string		$parent				Id of parent project to show (0 to show all)
 * @param   Task[]		$lines				Array of lines
 * @param   int			$level				Level (start to 0, then increased/decrease by recursive call), or -1 to show all level in order of $lines without the recursive groupment feature.
 * @param 	string		$var				Color
 * @param 	int			$showproject		Show project columns
 * @param	int			$taskrole			Array of roles of user for each tasks
 * @param	int			$projectsListId		List of id of project allowed to user (string separated with comma)
 * @param	int			$addordertick		Add a tick to move task
 * @param   int         $projectidfortotallink     0 or Id of project to use on total line (link to see all time consumed for project)
 * @return	void
 */
function showtasks(&$inc, $parent, &$lines, &$level, $var, $showproject, &$taskrole, $projectsListId='', $addordertick=0, $projectidfortotallink=0)
{
	global $user, $bc, $langs;
	global $projectstatic, $taskstatic;

	$lastprojectid=0;

	$projectsArrayId=explode(',',$projectsListId);

	$numlines=count($lines);

	// We declare counter as global because we want to edit them into recursive call
	global $total_projectlinesa_spent,$total_projectlinesa_planned,$total_projectlinesa_spent_if_planned;
	if ($level == 0)
	{
		$total_projectlinesa_spent=0;
		$total_projectlinesa_planned=0;
		$total_projectlinesa_spent_if_planned=0;
	}

	for ($i = 0 ; $i < $numlines ; $i++)
	{
		if ($parent == 0 && $level >= 0) $level = 0;              // if $level = -1, we dont' use sublevel recursion, we show all lines

		// Process line
		// print "i:".$i."-".$lines[$i]->fk_project.'<br>';

		if ($lines[$i]->fk_parent == $parent || $level < 0)       // if $level = -1, we dont' use sublevel recursion, we show all lines
		{
			// Show task line.
			$showline=1;
			$showlineingray=0;

			// If there is filters to use
			if (is_array($taskrole))
			{
				// If task not legitimate to show, search if a legitimate task exists later in tree
				if (! isset($taskrole[$lines[$i]->id]) && $lines[$i]->id != $lines[$i]->fk_parent)
				{
					// So search if task has a subtask legitimate to show
					$foundtaskforuserdeeper=0;
					searchTaskInChild($foundtaskforuserdeeper,$lines[$i]->id,$lines,$taskrole);
					//print '$foundtaskforuserpeeper='.$foundtaskforuserdeeper.'<br>';
					if ($foundtaskforuserdeeper > 0)
					{
						$showlineingray=1;		// We will show line but in gray
					}
					else
					{
						$showline=0;			// No reason to show line
					}
				}
			}
			else
			{
				// Caller did not ask to filter on tasks of a specific user (this probably means he want also tasks of all users, into public project
				// or into all other projects if user has permission to).
				if (empty($user->rights->projet->all->lire))
				{
					// User is not allowed on this project and project is not public, so we hide line
					if (! in_array($lines[$i]->fk_project, $projectsArrayId))
					{
						// Note that having a user assigned to a task into a project user has no permission on, should not be possible
						// because assignement on task can be done only on contact of project.
						// If assignement was done and after, was removed from contact of project, then we can hide the line.
						$showline=0;
					}
				}
			}

			if ($showline)
			{
				// Break on a new project
				if ($parent == 0 && $lines[$i]->fk_project != $lastprojectid)
				{
					$var = !$var;
					$lastprojectid=$lines[$i]->fk_project;
				}

				print '<tr '.$bc[$var].' id="row-'.$lines[$i]->id.'">'."\n";

				if ($showproject)
				{
					// Project ref
					print "<td>";
					//if ($showlineingray) print '<i>';
					$projectstatic->id=$lines[$i]->fk_project;
					$projectstatic->ref=$lines[$i]->projectref;
					$projectstatic->public=$lines[$i]->public;
					$projectstatic->title=$lines[$i]->projectlabel;
					if ($lines[$i]->public || in_array($lines[$i]->fk_project,$projectsArrayId) || ! empty($user->rights->projet->all->lire)) print $projectstatic->getNomUrl(1);
					else print $projectstatic->getNomUrl(1,'nolink');
					//if ($showlineingray) print '</i>';
					print "</td>";

					// Project status
					print '<td>';
					$projectstatic->statut=$lines[$i]->projectstatus;
					print $projectstatic->getLibStatut(2);
					print "</td>";
				}

				// Ref of task
				print '<td>';
				if ($showlineingray)
				{
					print '<i>'.img_object('','projecttask').' '.$lines[$i]->ref.'</i>';
				}
				else
				{
					$taskstatic->id=$lines[$i]->id;
					$taskstatic->ref=$lines[$i]->ref;
					$taskstatic->label=($taskrole[$lines[$i]->id]?$langs->trans("YourRole").': '.$taskrole[$lines[$i]->id]:'');
					print $taskstatic->getNomUrl(1,'withproject');
				}
				print '</td>';

				// Title of task
				print "<td>";
				if ($showlineingray) print '<i>';
				//else print '<a href="'.DOL_URL_ROOT.'/projet/tasks/task.php?id='.$lines[$i]->id.'&withproject=1">';
				for ($k = 0 ; $k < $level ; $k++)
				{
					print "&nbsp; &nbsp; &nbsp;";
				}
				print $lines[$i]->label;
				if ($showlineingray) print '</i>';
				//else print '</a>';
				print "</td>\n";

				// Date start
				print '<td align="center">';
				print dol_print_date($lines[$i]->date_start,'dayhour');
				print '</td>';

				// Date end
				print '<td align="center">';
				$taskstatic->projectstatus = $lines[$i]->projectstatus;
	            $taskstatic->progress = $lines[$i]->progress;
	            $taskstatic->fk_statut = $lines[$i]->status;
	            $taskstatic->datee = $lines[$i]->date_end;
	            print dol_print_date($lines[$i]->date_end,'dayhour');
	            if ($taskstatic->hasDelay()) print img_warning($langs->trans("Late"));
				print '</td>';

				$plannedworkloadoutputformat='allhourmin';
				$timespentoutputformat='allhourmin';
				if (! empty($conf->global->PROJECT_PLANNED_WORKLOAD_FORMAT)) $plannedworkloadoutputformat=$conf->global->PROJECT_PLANNED_WORKLOAD_FORMAT;
				if (! empty($conf->global->PROJECT_TIMES_SPENT_FORMAT)) $timespentoutputformat=$conf->global->PROJECT_TIME_SPENT_FORMAT;

				// Planned Workload (in working hours)
				print '<td align="right">';
				$fullhour=convertSecondToTime($lines[$i]->planned_workload,$plannedworkloadoutputformat);
				$workingdelay=convertSecondToTime($lines[$i]->planned_workload,'all',86400,7);	// TODO Replace 86400 and 7 to take account working hours per day and working day per weeks
				if ($lines[$i]->planned_workload != '')
				{
					print $fullhour;
					// TODO Add delay taking account of working hours per day and working day per week
					//if ($workingdelay != $fullhour) print '<br>('.$workingdelay.')';
				}
				//else print '--:--';
				print '</td>';

				// Time spent
				print '<td align="right">';
				if ($showlineingray) print '<i>';
				else print '<a href="'.DOL_URL_ROOT.'/projet/tasks/time.php?id='.$lines[$i]->id.($showproject?'':'&withproject=1').'">';
				if ($lines[$i]->duration) print convertSecondToTime($lines[$i]->duration,$timespentoutputformat);
				else print '--:--';
				if ($showlineingray) print '</i>';
				else print '</a>';
				print '</td>';

				// Progress calculated (Note: ->duration is time spent)
				print '<td align="right">';
				if ($lines[$i]->planned_workload || $lines[$i]->duration)
				{
					if ($lines[$i]->planned_workload) print round(100 * $lines[$i]->duration / $lines[$i]->planned_workload,2).' %';
					else print $langs->trans('WorkloadNotDefined');
				}
				print '</td>';

				// Progress declared
				print '<td align="right">';
				if ($lines[$i]->progress != '')
				{
					print $lines[$i]->progress.' %';
				}
				print '</td>';

				// Tick to drag and drop
				if ($addordertick)
				{
					print '<td align="center" class="tdlineupdown hideonsmartphone">&nbsp;</td>';
				}

				print "</tr>\n";

				if (! $showlineingray) $inc++;

				if ($level >= 0)    // Call sublevels
				{
    				$level++;
    				if ($lines[$i]->id) showtasks($inc, $lines[$i]->id, $lines, $level, $var, $showproject, $taskrole, $projectsListId, $addordertick);
    				$level--;
				}
				
				$total_projectlinesa_spent += $lines[$i]->duration;
				$total_projectlinesa_planned += $lines[$i]->planned_workload;
				if ($lines[$i]->planned_workload) $total_projectlinesa_spent_if_planned += $lines[$i]->duration;
			}
		}
		else
		{
			//$level--;
		}
	}

	if (($total_projectlinesa_planned > 0 || $total_projectlinesa_spent > 0) && $level <= 0)
	{
		print '<tr class="liste_total nodrag nodrop">';
		print '<td class="liste_total">'.$langs->trans("Total").'</td>';
		if ($showproject) print '<td></td><td></td>';
		print '<td></td>';
		print '<td></td>';
		print '<td></td>';
		print '<td align="right" class="nowrap liste_total">';
		print convertSecondToTime($total_projectlinesa_planned, 'allhourmin');
		print '</td>';
		print '<td align="right" class="nowrap liste_total">';
		if ($projectidfortotallink > 0) print '<a href="'.DOL_URL_ROOT.'/projet/tasks/time.php?projectid='.$projectidfortotallink.($showproject?'':'&withproject=1').'">';
		print convertSecondToTime($total_projectlinesa_spent, 'allhourmin');
		if ($projectidfortotallink > 0) print '</a>';
		print '</td>';
		print '<td align="right" class="nowrap liste_total">';
		if ($total_projectlinesa_planned) print round(100 * $total_projectlinesa_spent / $total_projectlinesa_planned,2).' %';
		print '</td>';
		print '<td></td>';
		if ($addordertick) print '<td class="hideonsmartphone"></td>';
		print '</tr>';
	}

	return $inc;
}

//TODO replace $this by $task and add parameter
/**
     *	Return clicable name (with picto eventually)
     *
     *	@param	int		$withpicto		0=No picto, 1=Include picto into link, 2=Only picto
     *	@param	string	$option			'withproject' or ''
     *  @param	string	$mode			Mode 'task', 'time', 'contact', 'note', document' define page to link to.
     * 	@param	int		$addlabel		0=Default, 1=Add label into string, >1=Add first chars into string
     *  @param	string	$sep			Separator between ref and label if option addlabel is set
     *  @param	int   	$notooltip		1=Disable tooltip
     *	@return	string					Chaine avec URL
     */
    function getTaskUrl($withpicto=0,$option='',$mode='cultivationtask', $addlabel=0, $sep=' - ', $notooltip=0)
    {
        global $conf, $langs, $user;

        if (! empty($conf->dol_no_mouse_hover)) $notooltip=1;   // Force disable tooltips
        
        $result='';
        $label = '<u>' . $langs->trans("ShowTask") . '</u>';
        if (! empty($this->ref))
            $label .= '<br><b>' . $langs->trans('Ref') . ':</b> ' . $this->ref;
        if (! empty($this->label))
            $label .= '<br><b>' . $langs->trans('LabelTask') . ':</b> ' . $this->label;
        if ($this->date_start || $this->date_end)
        {
        	$label .= "<br>".get_date_range($this->date_start,$this->date_end,'',$langs,0);
        }
        
        $url = DOL_URL_ROOT.'/custom/vignoble/'.$mode.'.php?id='.$this->id.($option=='withproject'?'&withproject=1':'');

        $linkclose = '';
        if (empty($notooltip))
        {
            if (! empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER))
            {
                $label=$langs->trans("ShowTask");
                $linkclose.=' alt="'.dol_escape_htmltag($label, 1).'"';
            }
            $linkclose.= ' title="'.dol_escape_htmltag($label, 1).'"';
            $linkclose.=' class="classfortooltip"';
        }
        
        $linkstart = '<a href="'.$url.'"';
        $linkstart.=$linkclose.'>';
        $linkend='</a>';
        
        $picto='projecttask';

        if ($withpicto) $result.=($linkstart.img_object(($notooltip?'':$label), $picto, ($notooltip?'':'class="classfortooltip"'), 0, 0, $notooltip?0:1).$linkend);
        if ($withpicto && $withpicto != 2) $result.=' ';
        if ($withpicto != 2) $result.=$linkstart.$this->ref.$linkend . (($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');
        return $result;
    }
