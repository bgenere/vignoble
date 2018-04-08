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
 * \file plot_list.php
 * \ingroup plot
 * \brief Display the list of plots
 *
 * The list of plots is paginated and could be filtered/search/sorted on displayed fields.
 * User could select which fields to display in list.
 * Each item of the list is selectable and link to the object form
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
@include './tpl/maindolibarr.inc.php';

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
// Get search all option
$search_all = GETPOST('sall', 'alpha');
// Get CSS options
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
	$sortfield = "t.rowid"; // default sort field
if (! $sortorder)
	$sortorder = "ASC"; // default sort order
		                    
// Protection if external user
$socid = 0;
if ($user->socid > 0) { // défini pour utilisateur externe, Id du tiers société vide sinon
	$socid = $user->socid; // $socid est souvent dans l'url
		                       // accessforbidden();
}

// Initialize technical object to manage hooks. Note that conf->hooks_modules contains array
$hookmanager->initHooks(array(
	'plotlist'
));
/**
 *
 * @var ExtraFields $extrafields Instanciate the extrafields class
 */
$extrafields = new ExtraFields($db);
// fetch extra labels and add to search options
$extrafieldslabels = $extrafields->fetch_name_optionals_label('plot');
$search_array_extrafields = $extrafields->getOptionalsFromPost($extrafieldslabels, '', 'search_');

/**
 *
 * @var array $fieldstosearchall List of fields to search into when doing a "search in all"
 */
$fieldstosearchall = array(
	't.ref' => 'Ref',
	't.label' => 'Label',
	't.description' => 'Description'
);
//
/**
 *
 * @var array $arrayfields List of fields that could be displayed in the list
 */
$arrayfields = defineListFields($langs, $extrafields);

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
	$search_array_extrafields = array();
}
// TODO Check if action 'confirm_delete' make sense on a list without line tick box
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
$title = $langs->trans('PlotsList');

// addJQuery();

$sql = "SELECT";
$sql .= " t.rowid,";
$sql .= " t.entity,";
$sql .= " t.ref,";
$sql .= " t.label,";
$sql .= " t.description,";
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
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "plot_extrafields as ef on (t.rowid = ef.fk_object)";

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
if ($search_all)
	$sql .= natural_search(array_keys($fieldstosearchall), $search_all);
	
	// Add search from extra fields
foreach ($search_array_extrafields as $key => $val) {
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
		'select',
		'sellist'
	))) || ! empty($crit))) {
		$sql .= natural_search('ef.' . $tmpkey, $crit, $mode);
	}
}
// Add where from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters); // Note that $action and $currentPlot may have been modified by hook
$sql .= $hookmanager->resPrint;

$sql .= $db->order($sortfield, $sortorder);

// echo var_dump($sql);

// Count total nb of records
$nbtotalofrecords = 0;
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
	$result = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($result);
}

$sql .= $db->plimit($conf->liste_limit + 1, $offset);

dol_syslog($script_file, LOG_DEBUG);
/**
 *
 * @var $resql List result set to display on the page
 */
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
	foreach ($search_array_extrafields as $key => $val) {
		$tmpkey = preg_replace('/search_options_/', '', $key);
		if ($val != '')
			$param .= '&search_options_' . $tmpkey . '=' . urlencode($val);
		
		if ($optioncss != '')
			$param .= '&optioncss=' . $optioncss;
	}
	
	print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $params, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'object_plot@vignoble');
	// selection form
	print '<form method="GET" id="searchFormList" action="' . $_SERVER["PHP_SELF"] . '">';
	if ($optioncss != '')
		print '<input type="hidden" name="optioncss" value="' . $optioncss . '">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
	print '<input type="hidden" name="sortfield" value="' . $sortfield . '">';
	print '<input type="hidden" name="sortorder" value="' . $sortorder . '">';
	// message when global selection activated
	if ($search_all) {
		foreach ($fieldstosearchall as $key => $val)
			$fieldstosearchall[$key] = $langs->trans($val);
		print $langs->trans("FilterOnInto", $search_all) . join(', ', $fieldstosearchall);
	}
	// message when list not fully displayed
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
	
	// print Fields title
	print '<tr class="liste_titre">';
	
	if (! empty($arrayfields['t.entity']['checked']))
		print_liste_field_titre($arrayfields['t.entity']['label'], $_SERVER['PHP_SELF'], 't.entity', '', $param, '', $sortfield, $sortorder);
	if (! empty($arrayfields['t.ref']['checked']))
		print_liste_field_titre($arrayfields['t.ref']['label'], $_SERVER['PHP_SELF'], 't.ref', '', $param, '', $sortfield, $sortorder);
	if (! empty($arrayfields['t.label']['checked']))
		print_liste_field_titre($arrayfields['t.label']['label'], $_SERVER['PHP_SELF'], 't.label', '', $param, '', $sortfield, $sortorder);
	if (! empty($arrayfields['t.description']['checked']))
		print_liste_field_titre($arrayfields['t.description']['label'], $_SERVER['PHP_SELF'], 't.description', '', $param, '', $sortfield, $sortorder);
	if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) {
		foreach ($extrafields->attribute_label as $key => $val) {
			if (! empty($arrayfields["ef." . $key]['checked'])) {
				$align = $extrafields->getAlignFlag($key);
				print_liste_field_titre($extrafieldslabels[$key], $_SERVER["PHP_SELF"], "ef." . $key, "", $param, ($align ? 'align="' . $align . '"' : ''), $sortfield, $sortorder);
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
	
	// print Search Fields boxes in title
	print '<tr class="liste_titre">';
	
	if (! empty($arrayfields['t.entity']['checked']))
		print '<td class="liste_titre"><input type="text" class="flat" name="search_entity" value="' . $search_entity . '" size="10"></td>';
	if (! empty($arrayfields['t.ref']['checked']))
		print '<td class="liste_titre"><input type="text" class="flat" name="search_ref" value="' . $search_ref . '" size="10"></td>';
	if (! empty($arrayfields['t.label']['checked']))
		print '<td class="liste_titre"><input type="text" class="flat" name="search_label" value="' . $search_label . '" size="10"></td>';
	if (! empty($arrayfields['t.description']['checked']))
		print '<td class="liste_titre"><input type="text" class="flat" name="search_description" value="' . $search_description . '" size="10"></td>';
		
		// example of a combobox selection for search
		// if (! empty($arrayfields['t.fk_rootstock']['checked']))
		// print '<td class="liste_titre">' . $formvignoble->displayDicCombo('c_rootstock', 'rootstook', $search_fk_rootstock, 'search_fk_rootstock', true) . '</td>';
		
	// Search box for Extra fields in title
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
					'select',
					'sellist'
				))) { // print a search box
				      // get search value if any
					$tmpkey = preg_replace('/search_options_/', '', $key);
					$value = dol_escape_htmltag($search_array_extrafields['search_options_' . $tmpkey]);
					$searchclass = '';
					switch ($typeofextrafield) {
						case 'select':
							print $extrafields->showInputField($key, $value, null, '', 'search_', searchstring);
							break;
						case 'sellist':
							print $extrafields->showInputField($key, $value, null, '', 'search_', searchstring);
							break;
						case 'varchar':
							print '<input class="flat searchstring" size="4" type="text" name="search_options_' . $tmpkey . '" value="' . $value . '">';
							break;
						case 'int':
							print '<input class="flat searchnum" size="4" type="text" name="search_options_' . $tmpkey . '" value="' . $value . '">';
							break;
						case 'double':
							print '<input class="flat searchnum" size="4" type="text" name="search_options_' . $tmpkey . '" value="' . $value . '">';
							break;
					}
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
	/*
	 * Fetch the result set and print lines
	 */
	$i = 0;
	while ($i < $num) {
		$obj = $db->fetch_object($resql);
		if ($obj) {
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
						print $extrafields->showOutputField($key, $obj->$tmpkey, '','plot');
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
			// TODO check if example of action column exist
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
function defineListFields($langs, $extrafields)
{
	$arrayfields['t.ref'] = array(
		'label' => $langs->trans("Fieldref"),
		'checked' => 1
	);
	$arrayfields['t.label'] = array(
		'label' => $langs->trans("Fieldlabel"),
		'checked' => 1
	);
	$arrayfields['t.description'] = array(
		'label' => $langs->trans("Fielddescription"),
		'checked' => 0
	);
	// add extrafields attributes and labels
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
	$arrayfields['t.entity'] = array(
		'label' => $langs->trans("Entity"),
		'checked' => 1,
		'enabled' => (! empty($conf->multicompany->enabled) && empty($conf->multicompany->transverse_mode))
	);
	$arrayfields['t.datec'] = array(
		'label' => $langs->trans("DateCreation"),
		'checked' => 0,
		'position' => 500
	);
	$arrayfields['t.tms'] = array(
		'label' => $langs->trans("DateModificationShort"),
		'checked' => 0,
		'position' => 500
	);
	
	return $arrayfields;
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



