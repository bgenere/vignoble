<?php
/*
 * Copyright (C) 2001-2002 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003 Jean-Louis Bergamo <jlb@j1b.org>
 * Copyright (C) 2004-2013 Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2012 Regis Houssin <regis.houssin@capnetworks.com>
 * Copyright (C) 2012 Florian Henry <florian.henry@open-concept.pro>
 * Copyright (C) 2015 Jean-François Ferry <jfefe@aternatik.fr>
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
 * \file vignoble/admin/plot_extrafields.php
 * \ingroup admin
 * \brief Admin page to setup the extra fields for the plot object.
 */
@include '../tpl/maindolibarr.inc.php';

// Libraries
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
require_once '../lib/admin.html.lib.php';
require_once '../class/plot.class.php';

// Translations
$langs->load("admin");
$langs->load("errors");
$langs->load("other");
$langs->load("vignoble@vignoble");

// Only admin user
if (! $user->admin)
	accessforbidden();
	
$elementtype = 'plot';
/**
 * Initialize a new ExtraFields class
 */
$extrafields = new ExtraFields($db);
/**
 * Supported format for extra fields
 */
$tmptype2label = ExtraFields::$type2label;
$type2label = array(
	''
);
/**
 * Initialize translated type fields labels
 */
foreach ($tmptype2label as $key => $val)
	$type2label[$key] = $langs->trans($val);
	
/**
 * Actions
 */
$action = GETPOST('action', 'alpha');
/**
 * Action tag (create, edit, add, update, delete, createdefault)
 */
$attrname = GETPOST('attrname', 'alpha');
/**
 * Attribute name to edit
 */

/**
 * Process extrafields actions using template.
 * Requires :
 * - $action
 * - $attrname
 * - $extrafields
 * - $elementtype
 */
require DOL_DOCUMENT_ROOT . '/core/actions_extrafields.inc.php';

if ($action == 'createdefault') {
	createDefaultFields($extrafields, $elementtype);
}

/*
 * View
 */
beginForm('plotfields');

$textobject = $langs->transnoentitiesnoconv("Plot");
/**
 * < The Object name to which fields belong
 */

/**
 * Displays extrafields list with actions on attributes
 * This template, requires
 * $textobject
 * $extrafields
 * $elementtype
 */
require DOL_DOCUMENT_ROOT . '/core/tpl/admin_extrafields_view.tpl.php';

printButtonNewField($action);

/**
 * Display new attribute form below the list
 * when action is create.
 */
if ($action == 'create') {
	print "<br>";
	print load_fiche_titre($langs->trans('NewAttribute'));
	
	require DOL_DOCUMENT_ROOT . '/core/tpl/admin_extrafields_add.tpl.php';
}

/**
 * Display attribute form below the list
 * when action is edit and attrname is provided
 */
if ($action == 'edit' && ! empty($attrname)) {
	print "<br>";
	print load_fiche_titre($langs->trans("FieldEdition", $attrname));
	
	require DOL_DOCUMENT_ROOT . '/core/tpl/admin_extrafields_edit.tpl.php';
}

endForm();

/**
 * Print New Attribute button to create a new field
 *
 * @param
 *        	action the button is displayed only if action is not create or edit.
 */
function printButtonNewField($action)
{
	Global $langs;
	
	if ($action != 'create' && $action != 'edit') {
		print '<div class="tabsAction">';
		print "<a class=\"butAction\" href=\"" . $_SERVER["PHP_SELF"] . "?action=create\">" . $langs->trans("NewAttribute") . "</a>";
		print "<a class=\"butAction\" href=\"" . $_SERVER["PHP_SELF"] . "?action=createdefault\">" . $langs->trans("AddDefaultAttributes") . "</a>";
		print "</div>";
	}
}

/**
 *	Create the default extrafields for the plot object
 * @param array  $extrafields        	
 * @param string $elementtype      	
 */
function createDefaultFields($extrafields, $elementtype)
{
	Global $db, $langs;
	
	$result = $extrafields->addExtraField('areasize', 'Size (ha)', 'double', 1, '10,2', $elementtype, 0, 1, 0, 'a:1:{s:7:\"options\";a:1:{s:0:\"\";N;}}', 1, '', 0, 0);
	if ($result <= 0) {
		setEventMessages('areasize field not added', null, 'warnings');
	}
	$result = $extrafields->addExtraField('rootsnumber', 'Number of Roots', 'int', 2, '10', $elementtype, 0, 1, 0, 'a:1:{s:7:\"options\";a:1:{s:0:\"\";N;}}', 1, '', 0, 0);
	if ($result <= 0) {
		setEventMessages('rootsnumber field not added', null, 'warnings');
	}
	$result = $extrafields->addExtraField('spacing', 'Spacing (cm)', 'double', 3, '3,0', $elementtype, 0, 1, 0, 'a:1:{s:7:\"options\";a:1:{s:0:\"\";N;}}', 1, '', 0, 0);
	if ($result <= 0) {
		setEventMessages('spacing field not added', null, 'warnings');
	}
	$result = $extrafields->addExtraField('cultivationtype', 'Cultivation type', 'sellist', 4, '', $elementtype, 0, 1,'', 'a:1:{s:7:\"options\";a:1:{s:38:\"c_cultivationtype:label:code::active=1\";N;}}', 1, '', 0, 0);
	if ($result <= 0) {
		setEventMessages('cultivationtype field not added', null, 'warnings');
	}
	$result = $extrafields->addExtraField('varietal', 'Varietal', 'sellist', 5, '', $elementtype, 0, 1, '', 'a:1:{s:7:\"options\";a:1:{s:31:\"c_varietal:label:code::active=1\";N;}}', 1, '', 0, 0);
	if ($result <= 0) {
		setEventMessages('varietal field not added', null, 'warnings');
	}
	$result = $extrafields->addExtraField('rootstock', 'Root Stock', 'sellist', 6, '', $elementtype, 0, 1, '', 'a:1:{s:7:\"options\";a:1:{s:32:\"c_rootstock:label:code::active=1\";N;}}', 1, '', 0, 0);
	if ($result <= 0) {
		setEventMessages('rootstock field not added', null, 'warnings');
	}
	
	if ($result > 0) {
		setEventMessages('Default fields added, allready existing have been kept', null);
	}
}
