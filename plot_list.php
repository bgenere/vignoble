<?php
/*
 * Copyright (C) 2007-2015 Laurent Destailleur <eldy@users.sourceforge.net>
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
 * \file vignoble/plot_list.php
 * \ingroup vignoble
 * \brief Display the list of plots
 * List is displayed
 */

// if (! defined('NOREQUIREUSER')) define('NOREQUIREUSER','1');
// if (! defined('NOREQUIREDB')) define('NOREQUIREDB','1');
// if (! defined('NOREQUIRESOC')) define('NOREQUIRESOC','1');
// if (! defined('NOREQUIRETRAN')) define('NOREQUIRETRAN','1');
// if (! defined('NOCSRFCHECK')) define('NOCSRFCHECK','1'); // Do not check anti CSRF attack test
// if (! defined('NOSTYLECHECK')) define('NOSTYLECHECK','1'); // Do not check style html tag into posted data
// if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1'); // Do not check anti POST attack test
// if (! defined('NOREQUIREMENU')) define('NOREQUIREMENU','1'); // If there is no need to load and show top and left menu
// if (! defined('NOREQUIREHTML')) define('NOREQUIREHTML','1'); // If we don't need to load the html.form.class.php
// if (! defined('NOREQUIREAJAX')) define('NOREQUIREAJAX','1');
// if (! defined("NOLOGIN")) define("NOLOGIN",'1'); // If this page is public (can be called outside logged session)

// Change this following line to use the correct relative path (../, ../../, etc)
$res = 0;
if (! $res && file_exists("../main.inc.php"))
	$res = @include '../main.inc.php'; // to work if your module directory is into dolibarr root htdocs directory
if (! $res && file_exists("../../main.inc.php"))
	$res = @include '../../main.inc.php'; // to work if your module directory is into a subdir of root htdocs directory

if (! $res)
	die("Include of main fails");
	// Change this following line to use the correct relative path from htdocs
	// include_once(DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php');
dol_include_once('/vignoble/class/plot.class.php');
dol_include_once('/vignoble/class/html.form.vignoble.class.php');

// Load traductions files requiredby by page
$langs->load("other");
$langs->load("vignoble@vignoble");

// Get parameters
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage');
$myparam = GETPOST('myparam', 'alpha');

// Get search field values
$search_ref = GETPOST('search_ref', 'alpha');
$search_label = GETPOST('search_label', 'alpha');
$search_description = GETPOST('search_description', 'alpha');
$search_areasize = GETPOST('search_areasize', 'alpha');
$search_rootsnumber = GETPOST('search_rootsnumber', 'int');
$search_spacing = GETPOST('search_spacing', 'alpha');
$search_fk_cultivationtype = GETPOST('search_fk_cultivationtype', 'int');
$search_fk_varietal = GETPOST('search_fk_varietal', 'int');
$search_fk_rootstock = GETPOST('search_fk_rootstock', 'int');
$search_entity = GETPOST('search_entity', 'int');
// CSS options
$optioncss = GETPOST('optioncss', 'alpha');

// Load variable for pagination
$limit = GETPOST("limit") ? GETPOST("limit", "int") : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'alpha');
$sortorder = GETPOST('sortorder', 'alpha');
$page = GETPOST('page', 'int');
if ($page == - 1) {
	$page = 0;
}
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortfield)
	$sortfield = "t.rowid"; // default search field
if (! $sortorder)
	$sortorder = "ASC";
	
	// Protection if external user
$socid = 0;
if ($user->societe_id > 0) {
	$socid = $user->societe_id;
	// accessforbidden();
}
// @TODO check why NULL ($user loading ??)
// var_dump($user->societe);

// Initialize technical object to manage hooks. Note that conf->hooks_modules contains array
$hookmanager->initHooks(array(
	'plotlist'
));



$currentPlot = getRequestedObject($db, $id, $ref, $action, $result);

$arrayfields = defineListFields($langs);

// Extra fields
// Get extrafields for the object
$extrafields = new ExtraFields($db);

// fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label('vignoble');
$search_array_options = $extrafields->getOptionalsFromPost($extralabels, '', 'search_');
if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) {
	foreach ($extrafields->attribute_label as $key => $val) {
		$arrayfields["ef." . $key] = array(
			'label' => $extrafields->attribute_label[$key],
			'checked' => $extrafields->attribute_list[$key],
			'position' => $extrafields->attribute_pos[$key],
			'enabled' => $extrafields->attribute_perms[$key]
		);
	}
}

/**
 * *****************************************************************
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 * ******************************************************************
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $currentPlot, $action); // Note that $action and $currentPlot may have been modified by some hooks
if ($reshook < 0)
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

include DOL_DOCUMENT_ROOT . '/core/actions_changeselectedfields.inc.php';

if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter.x") || GETPOST("button_removefilter")) 
// All test are required to be compatible with all browsers
{
	// empty selection boxes
	$search_ref = '';
	$search_label = '';
	$search_description = '';
	$search_areasize = '';
	$search_rootsnumber = '';
	$search_spacing = '';
	$search_fk_cultivationtype = '';
	$search_fk_varietal = '';
	$search_fk_rootstock = '';
	$search_entity = '';
	$search_array_options = array();
}

if (empty($reshook)) {
	// Action to delete
	if ($action == 'confirm_delete') {
		$result = $currentPlot->delete($user);
		if ($result > 0) {
			// Delete OK
			setEventMessages("RecordDeleted", null, 'mesgs');
			header("Location: " . dol_buildpath('/vignoble/list.php', 1));
			exit();
		} else {
			if (! empty($currentPlot->errors))
				setEventMessages(null, $currentPlot->errors, 'errors');
			else
				setEventMessages($currentPlot->error, null, 'errors');
		}
	}
}

/**
 * *************************************************
 * VIEW
 *
 * Put here all code to build page
 * **************************************************
 */

llxHeader('', $langs->trans('PlotListTitle'), '');

$form = new Form($db);
$formvignoble = new FormVignoble($db);
$plot = new plot($db);

// Put here content of your page
$title = $langs->trans('Plots List');

addJQuery();

$sql = "SELECT";
$sql .= " t.rowid,";
$sql .= " t.entity,";
$sql .= " t.ref,";
$sql .= " t.label,";
$sql .= " t.description,";
$sql .= " t.areasize,";
$sql .= " t.rootsnumber,";
$sql .= " t.spacing,";
$sql .= " t.fk_cultivationtype,";
$sql .= " t.fk_varietal,";
$sql .= " t.fk_rootstock,";
$sql .= " t.tms as date_update,";
$sql .= " t.datec as date_creation";

// Add fields for extrafields
foreach ($extrafields->attribute_list as $key => $val)
	$sql .= ",ef." . $key . ' as options_' . $key;

// Add fields from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListSelect', $parameters); // Note that $action and $currentPlot may have been modified by hook
$sql .= $hookmanager->resPrint;

$sql .= " FROM " . MAIN_DB_PREFIX . "plot as t";
if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "plot_extrafields as ef on (u.rowid = ef.fk_object)";

$sql .= " WHERE 1 = 1";
// $sql.= " WHERE u.entity IN (".getEntity('mytable',1).")";

if ($search_entity)
	$sql .= natural_search("entity", $search_entity);
if ($search_ref)
	$sql .= natural_search("ref", $search_ref);
if ($search_label)
	$sql .= natural_search("label", $search_label);
if ($search_description)
	$sql .= natural_search("description", $search_description);
if ($search_areasize)
	$sql .= natural_search("areasize", $search_areasize);
if ($search_rootsnumber)
	$sql .= natural_search("rootsnumber", $search_rootsnumber);
if ($search_spacing)
	$sql .= natural_search("spacing", $search_spacing);
if ($search_fk_cultivationtype)
	$sql .= natural_search("fk_cultivationtype", $search_fk_cultivationtype);
if ($search_fk_varietal > 0)
	$sql .= natural_search("fk_varietal", $search_fk_varietal);
if ($search_fk_rootstock)
	$sql .= natural_search("fk_rootstock", $search_fk_rootstock);

if ($sall)
	$sql .= natural_search(array_keys($fieldstosearchall), $sall);

// Add where from extra fields
foreach ($search_array_options as $key => $val) {
	$crit = $val;
	$tmpkey = preg_replace('/search_options_/', '', $key);
	$typ = $extrafields->attribute_type[$tmpkey];
	$mode = 0;
	if (in_array($typ, array(
		'int',
		'double'
	)))
		$mode = 1; // Search on a numeric
	if ($val && (($crit != '' && ! in_array($typ, array(
		'select'
	))) || ! empty($crit))) {
		$sql .= natural_search('ef.' . $tmpkey, $crit, $mode);
	}
}
// Add where from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters); // Note that $action and $currentPlot may have been modified by hook
$sql .= $hookmanager->resPrint;

$sql .= $db->order($sortfield, $sortorder);

// Count total nb of records
$nbtotalofrecords = 0;
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
	$result = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($result);
}

$sql .= $db->plimit($conf->liste_limit + 1, $offset);

dol_syslog($script_file, LOG_DEBUG);
$resql = $db->query($sql);

if ($resql) {
	$num = $db->num_rows($resql);
	
	$params = '';
	
	if ($search_entity != '')
		$params .= '&amp;search_entity=' . urlencode($search_entity);
	if ($search_ref != '')
		$params .= '&amp;search_ref=' . urlencode($search_ref);
	if ($search_label != '')
		$params .= '&amp;search_label=' . urlencode($search_label);
	if ($search_description != '')
		$params .= '&amp;search_description=' . urlencode($search_description);
	if ($search_areasize != '')
		$params .= '&amp;search_areasize=' . urlencode($search_areasize);
	if ($search_rootsnumber != '')
		$params .= '&amp;search_rootsnumber=' . urlencode($search_rootsnumber);
	if ($search_spacing != '')
		$params .= '&amp;search_spacing=' . urlencode($search_spacing);
	if ($search_fk_cultivationtype != '')
		$params .= '&amp;search_fk_cultivationtype=' . urlencode($search_fk_cultivationtype);
	if ($search_fk_varietal != '')
		$params .= '&amp;search_fk_varietal=' . urlencode($search_fk_varietal);
	if ($search_fk_rootstock != '')
		$params .= '&amp;search_fk_rootstock=' . urlencode($search_fk_rootstock);
		
	if ($optioncss != '')
		$param .= '&optioncss=' . $optioncss;
		// Add $param from extra fields
	foreach ($search_array_options as $key => $val) {
		$crit = $val;
		$tmpkey = preg_replace('/search_options_/', '', $key);
		if ($val != '')
			$param .= '&search_options_' . $tmpkey . '=' . urlencode($val);
	}
	
	print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $params, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'object_plot@vignoble');
	
	print '<form method="GET" id="searchFormList" action="' . $_SERVER["PHP_SELF"] . '">';
	if ($optioncss != '')
		print '<input type="hidden" name="optioncss" value="' . $optioncss . '">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
	print '<input type="hidden" name="sortfield" value="' . $sortfield . '">';
	print '<input type="hidden" name="sortorder" value="' . $sortorder . '">';
	
	if ($sall) {
		foreach ($fieldstosearchall as $key => $val)
			$fieldstosearchall[$key] = $langs->trans($val);
		print $langs->trans("FilterOnInto", $all) . join(', ', $fieldstosearchall);
	}
	
	if (! empty($moreforfilter)) {
		print '<div class="liste_titre liste_titre_bydiv centpercent">';
		print $moreforfilter;
		$parameters = array();
		$reshook = $hookmanager->executeHooks('printFieldPreListTitle', $parameters); // Note that $action and $currentPlot may have been modified by hook
		print $hookmanager->resPrint;
		print '</div>';
	}
	
	$varpage = empty($contextpage) ? $_SERVER["PHP_SELF"] : $contextpage;
	$selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage); // This also change content of $arrayfields
	
	print '<table class="liste ' . ($moreforfilter ? "listwithfilterbefore" : "") . '">';
	
	// Fields title
	print '<tr class="liste_titre">';
	
	if (! empty($arrayfields['t.entity']['checked']))
		print_liste_field_titre($arrayfields['t.entity']['label'], $_SERVER['PHP_SELF'], 't.entity', '', $param, '', $sortfield, $sortorder);
	if (! empty($arrayfields['t.ref']['checked']))
		print_liste_field_titre($arrayfields['t.ref']['label'], $_SERVER['PHP_SELF'], 't.ref', '', $param, '', $sortfield, $sortorder);
	if (! empty($arrayfields['t.label']['checked']))
		print_liste_field_titre($arrayfields['t.label']['label'], $_SERVER['PHP_SELF'], 't.label', '', $param, '', $sortfield, $sortorder);
	if (! empty($arrayfields['t.description']['checked']))
		print_liste_field_titre($arrayfields['t.description']['label'], $_SERVER['PHP_SELF'], 't.description', '', $param, '', $sortfield, $sortorder);
	if (! empty($arrayfields['t.areasize']['checked']))
		print_liste_field_titre($arrayfields['t.areasize']['label'], $_SERVER['PHP_SELF'], 't.areasize', '', $param, '', $sortfield, $sortorder);
	if (! empty($arrayfields['t.rootsnumber']['checked']))
		print_liste_field_titre($arrayfields['t.rootsnumber']['label'], $_SERVER['PHP_SELF'], 't.rootsnumber', '', $param, '', $sortfield, $sortorder);
	if (! empty($arrayfields['t.spacing']['checked']))
		print_liste_field_titre($arrayfields['t.spacing']['label'], $_SERVER['PHP_SELF'], 't.spacing', '', $param, '', $sortfield, $sortorder);
	if (! empty($arrayfields['t.fk_cultivationtype']['checked']))
		print_liste_field_titre($arrayfields['t.fk_cultivationtype']['label'], $_SERVER['PHP_SELF'], 't.fk_cultivationtype', '', $param, '', $sortfield, $sortorder);
	if (! empty($arrayfields['t.fk_varietal']['checked']))
		print_liste_field_titre($arrayfields['t.fk_varietal']['label'], $_SERVER['PHP_SELF'], 't.fk_varietal', '', $param, '', $sortfield, $sortorder);
	if (! empty($arrayfields['t.fk_rootstock']['checked']))
		print_liste_field_titre($arrayfields['t.fk_rootstock']['label'], $_SERVER['PHP_SELF'], 't.fk_rootstock', '', $param, '', $sortfield, $sortorder);
			
		// Extra fields
	if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) {
		foreach ($extrafields->attribute_label as $key => $val) {
			if (! empty($arrayfields["ef." . $key]['checked'])) {
				$align = $extrafields->getAlignFlag($key);
				print_liste_field_titre($extralabels[$key], $_SERVER["PHP_SELF"], "ef." . $key, "", $param, ($align ? 'align="' . $align . '"' : ''), $sortfield, $sortorder);
			}
		}
	}
	// Hook fields
	$parameters = array(
		'arrayfields' => $arrayfields
	);
	$reshook = $hookmanager->executeHooks('printFieldListTitle', $parameters); // Note that $action and $currentPlot may have been modified by hook
	print $hookmanager->resPrint;
	if (! empty($arrayfields['t.datec']['checked']))
		print_liste_field_titre($langs->trans("DateCreationShort"), $_SERVER["PHP_SELF"], "t.datec", "", $param, 'align="center" class="nowrap"', $sortfield, $sortorder);
	if (! empty($arrayfields['t.tms']['checked']))
		print_liste_field_titre($langs->trans("DateModificationShort"), $_SERVER["PHP_SELF"], "t.tms", "", $param, 'align="center" class="nowrap"', $sortfield, $sortorder);
		// if (! empty($arrayfields['t.status']['checked'])) print_liste_field_titre($langs->trans("Status"),$_SERVER["PHP_SELF"],"t.status","",$param,'align="center"',$sortfield,$sortorder);
	print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"], "", '', '', 'align="right"', $sortfield, $sortorder, 'maxwidthsearch ');
	print '</tr>' . "\n";
	
	// Search Fields in title 
	print '<tr class="liste_titre">';
	
	if (! empty($arrayfields['t.entity']['checked']))
		print '<td class="liste_titre"><input type="text" class="flat" name="search_entity" value="' . $search_entity . '" size="10"></td>';
	if (! empty($arrayfields['t.ref']['checked']))
		print '<td class="liste_titre"><input type="text" class="flat" name="search_ref" value="' . $search_ref . '" size="10"></td>';
	if (! empty($arrayfields['t.label']['checked']))
		print '<td class="liste_titre"><input type="text" class="flat" name="search_label" value="' . $search_label . '" size="10"></td>';
	if (! empty($arrayfields['t.description']['checked']))
		print '<td class="liste_titre"><input type="text" class="flat" name="search_description" value="' . $search_description . '" size="10"></td>';
	if (! empty($arrayfields['t.areasize']['checked']))
		print '<td class="liste_titre"><input type="text" class="flat" name="search_areasize" value="' . $search_areasize . '" size="10"></td>';
	if (! empty($arrayfields['t.rootsnumber']['checked']))
		print '<td class="liste_titre"><input type="text" class="flat" name="search_rootsnumber" value="' . $search_rootsnumber . '" size="10"></td>';
	if (! empty($arrayfields['t.spacing']['checked']))
		print '<td class="liste_titre"><input type="text" class="flat" name="search_spacing" value="' . $search_spacing . '" size="10"></td>';
	if (! empty($arrayfields['t.fk_cultivationtype']['checked']))
		print '<td class="liste_titre">'. $formvignoble->displayDicCombo('c_cultivationtype', 'cultivationtype',$search_fk_cultivationtype,'search_fk_cultivationtype',true).'</td>';
	if (! empty($arrayfields['t.fk_varietal']['checked']))
		print '<td class="liste_titre">'. $formvignoble->displayDicCombo('c_varietal', 'varietal',$search_fk_varietal, 'search_fk_varietal', true) . '</td>';
	if (! empty($arrayfields['t.fk_rootstock']['checked']))
		print '<td class="liste_titre">'.$formvignoble->displayDicCombo('c_rootstock', 'rootstook', $search_fk_rootstock, 'search_fk_rootstock',true).'</td>';
	if (! empty($arrayfields['t.note_private']['checked']))
		print '<td class="liste_titre"><input type="text" class="flat" name="search_note_private" value="' . $search_note_private . '" size="10"></td>';
	if (! empty($arrayfields['t.note_public']['checked']))
		print '<td class="liste_titre"><input type="text" class="flat" name="search_note_public" value="' . $search_note_public . '" size="10"></td>';
	if (! empty($arrayfields['t.fk_user_author']['checked']))
		print '<td class="liste_titre"><input type="text" class="flat" name="search_fk_user_author" value="' . $search_fk_user_author . '" size="10"></td>';
	if (! empty($arrayfields['t.fk_user_modif']['checked']))
		print '<td class="liste_titre"><input type="text" class="flat" name="search_fk_user_modif" value="' . $search_fk_user_modif . '" size="10"></td>';
		
		// Search on Extra fields in title
	if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) {
		foreach ($extrafields->attribute_label as $key => $val) {
			if (! empty($arrayfields["ef." . $key]['checked'])) {
				$align = $extrafields->getAlignFlag($key);
				$typeofextrafield = $extrafields->attribute_type[$key];
				print '<td class="liste_titre' . ($align ? ' ' . $align : '') . '">';
				if (in_array($typeofextrafield, array(
					'varchar',
					'int',
					'double',
					'select'
				))) {
					$crit = $val;
					$tmpkey = preg_replace('/search_options_/', '', $key);
					$searchclass = '';
					if (in_array($typeofextrafield, array(
						'varchar',
						'select'
					)))
						$searchclass = 'searchstring';
					if (in_array($typeofextrafield, array(
						'int',
						'double'
					)))
						$searchclass = 'searchnum';
					print '<input class="flat' . ($searchclass ? ' ' . $searchclass : '') . '" size="4" type="text" name="search_options_' . $tmpkey . '" value="' . dol_escape_htmltag($search_array_options['search_options_' . $tmpkey]) . '">';
				}
				print '</td>';
			}
		}
	}
	// Fields from hook
	$parameters = array(
		'arrayfields' => $arrayfields
	);
	$reshook = $hookmanager->executeHooks('printFieldListOption', $parameters); // Note that $action and $currentPlot may have been modified by hook
	print $hookmanager->resPrint;
	if (! empty($arrayfields['t.datec']['checked'])) {
		// Date creation
		print '<td class="liste_titre">';
		print '</td>';
	}
	if (! empty($arrayfields['t.tms']['checked'])) {
		// Date modification
		print '<td class="liste_titre">';
		print '</td>';
	}
	/*
	 * if (! empty($arrayfields['u.statut']['checked']))
	 * {
	 * // Status
	 * print '<td class="liste_titre" align="center">';
	 * print $form->selectarray('search_statut', array('-1'=>'','0'=>$langs->trans('Disabled'),'1'=>$langs->trans('Enabled')),$search_statut);
	 * print '</td>';
	 * }
	 */
	// Action column in list title
	print '<td class="liste_titre" align="right">';
	print '<input type="image" class="liste_titre" name="button_search" src="' . img_picto($langs->trans("Search"), 'search.png', '', '', 1) . '" value="' . dol_escape_htmltag($langs->trans("Search")) . '" title="' . dol_escape_htmltag($langs->trans("Search")) . '">';
	print '<input type="image" class="liste_titre" name="button_removefilter" src="' . img_picto($langs->trans("Search"), 'searchclear.png', '', '', 1) . '" value="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '" title="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '">';
	print '</td>';
	print '</tr>' . "\n";
	
	$i = 0;
	while ($i < $num) {
		$obj = $db->fetch_object($resql);
		if ($obj) {
			// You can use here results
			print '<tr>';
			$plot->fetch($obj->rowid);
			if (! empty($arrayfields['t.entity']['checked']))
				print '<td>' . $obj->entity . '</td>';
			if (! empty($arrayfields['t.ref']['checked']))
				print '<td>' . $plot->getNomUrl(1) . '</td>';
			if (! empty($arrayfields['t.label']['checked']))
				print '<td>' . $obj->label . '</td>';
			if (! empty($arrayfields['t.description']['checked']))
				print '<td>' . $obj->description . '</td>';
			if (! empty($arrayfields['t.areasize']['checked']))
				print '<td>' . $obj->areasize . '</td>';
			if (! empty($arrayfields['t.rootsnumber']['checked']))
				print '<td>' . $obj->rootsnumber . '</td>';
			if (! empty($arrayfields['t.spacing']['checked']))
				print '<td>' . $obj->spacing . '</td>';
			if (! empty($arrayfields['t.fk_cultivationtype']['checked']))
				print '<td>' . dol_getIdFromCode($db,$obj->fk_cultivationtype,'c_cultivationtype', 'rowid', 'label') . '</td>';
			if (! empty($arrayfields['t.fk_varietal']['checked']))
				print '<td>' . dol_getIdFromCode($db, $obj->fk_varietal, 'c_varietal', 'rowid', 'label') . '</td>';
			if (! empty($arrayfields['t.fk_rootstock']['checked']))
				print '<td>' . dol_getIdFromCode($db, $obj->fk_rootstock,'c_rootstock', 'rowid', 'label') . '</td>';
				
				// Extra fields
			if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) {
				foreach ($extrafields->attribute_label as $key => $val) {
					if (! empty($arrayfields["ef." . $key]['checked'])) {
						print '<td';
						$align = $extrafields->getAlignFlag($key);
						if ($align)
							print ' align="' . $align . '"';
						print '>';
						$tmpkey = 'options_' . $key;
						print $extrafields->showOutputField($key, $obj->$tmpkey, '', 1);
						print '</td>';
					}
				}
			}
			// Fields from hook
			$parameters = array(
				'arrayfields' => $arrayfields,
				'obj' => $obj
			);
			$reshook = $hookmanager->executeHooks('printFieldListValue', $parameters); // Note that $action and $currentPlot may have been modified by hook
			print $hookmanager->resPrint;
			// Date creation
			if (! empty($arrayfields['t.datec']['checked'])) {
				print '<td align="center">';
				print dol_print_date($db->jdate($obj->date_creation), 'dayhour');
				print '</td>';
			}
			// Date modification
			if (! empty($arrayfields['t.tms']['checked'])) {
				print '<td align="center">';
				print dol_print_date($db->jdate($obj->date_update), 'dayhour');
				print '</td>';
			}
			// Status
			/*
			 * if (! empty($arrayfields['u.statut']['checked']))
			 * {
			 * $userstatic->statut=$obj->statut;
			 * print '<td align="center">'.$userstatic->getLibStatut(3).'</td>';
			 * }
			 */
			// Action column
			print '<td></td>';
			print '</tr>';
		}
		$i ++;
	}
	
	$db->free($resql);
	
	$parameters = array(
		'sql' => $sql
	);
	$reshook = $hookmanager->executeHooks('printFieldListFooter', $parameters); // Note that $action and $currentPlot may have been modified by hook
	print $hookmanager->resPrint;
	
	print "</table>\n";
	print "</form>\n";
	
	$db->free($result);
} else {
	$error ++;
	dol_print_error($db);
}

// End of page
llxFooter();
$db->close();

/**
 * Return array of fields that are part of the list
 * each field is identified by table field name
 * field properties are stored in a sub array with
 * label => display the user field name
 * checked => [1 field displayed in list | 0 hidden]
 * enabled => [ NULL or 1 field is usable| 0 field not usable]
 * position => int to order the column
 */
function defineListFields($langs)
{
	$arrayfields = array(
		
		't.ref' => array(
			'label' => $langs->trans("Fieldref"),
			'checked' => 1
		),
		't.label' => array(
			'label' => $langs->trans("Fieldlabel"),
			'checked' => 1
		),
		't.description' => array(
			'label' => $langs->trans("Fielddescription"),
			'checked' => 0
		),
		't.areasize' => array(
			'label' => $langs->trans("Fieldareasize"),
			'checked' => 1
		),
		't.rootsnumber' => array(
			'label' => $langs->trans("Fieldrootsnumber"),
			'checked' => 1
		),
		't.spacing' => array(
			'label' => $langs->trans("Fieldspacing"),
			'checked' => 1
		),
		't.fk_cultivationtype' => array(
			'label' => $langs->trans("Fieldfk_cultivationtype"),
			'checked' => 1
		),
		't.fk_varietal' => array(
			'label' => $langs->trans("Fieldfk_varietal"),
			'checked' => 1
		),
		't.fk_rootstock' => array(
			'label' => $langs->trans("Fieldfk_rootstock"),
			'checked' => 1
		),
		't.entity' => array(
			'label' => $langs->trans("Entity"),
			'checked' => 1,
			'enabled' => (! empty($conf->multicompany->enabled) && empty($conf->multicompany->transverse_mode))
		),
		't.datec' => array(
			'label' => $langs->trans("DateCreation"),
			'checked' => 0,
			'position' => 500
		),
		't.tms' => array(
			'label' => $langs->trans("DateModificationShort"),
			'checked' => 0,
			'position' => 500
		)
	);
	return $arrayfields;
}

/**
 * Load object if id or ref is provided as parameter
 *
 * @param
 *        	db the database context
 * @param
 *        	id the object id provided in URL
 * @param
 *        	action the action provided in URL
 * @param
 *        	result the object
 */
function getRequestedObject($db, $id, $ref,$action, $result)
{
	// Load object if id or ref is provided as parameter
	$object = new plot($db);
	if (($id > 0 || ! empty($ref)) && $action != 'add') {
		$result = $object->fetch($id, $ref);
		if ($result < 0)
			dol_print_error($db);
	}
	return $object;
}

/**
 * Example : Adding jquery code
 */

function addJQuery()
{
	print '<script type="text/javascript" language="javascript">
	jQuery(document).ready(function() {
		function init_myfunc()
		{
			jQuery("#myid").removeAttr(\'disabled\');
			jQuery("#myid").attr(\'disabled\',\'disabled\');
		}
		init_myfunc();
		jQuery("#mybutton").click(function() {
			init_myfunc();
		});
	});
	</script>';
}



