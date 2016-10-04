<?php
/*
 * This page is displayed to provide module information
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
 * \file admin/about.php
 * \ingroup vignoble
 * \brief Displays module information in a Setup Tab
 * Tab content :
 * - README.md file for the module
 * - COPYING file for the module
 * - GPLV3 licence link
 */

// Load Dolibarr environment
if (false === (@include '../../main.inc.php')) { // From htdocs directory
	require '../../../main.inc.php'; // From "custom" directory
}

global $langs, $user;

// Dolibar and module libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/vignoble.lib.php';
// md file parser
require __DIR__ . '/../vendor/autoload.php';
// Translations

$langs->load("admin");
$langs->load("errors");
$langs->load("other");
$langs->load("vignoble@vignoble");

// Access control admin user only
if (! $user->admin) {
	accessforbidden();
}

// Parameters if any
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */

/*
 * View
 */
// page name and header
$page_name = "vignobleSetup";
llxHeader('', $langs->trans($page_name));

// page title (printed) and link to module list
// @TODO Why configuration icons on top is a folder ?
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';
print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Tabs configuration (in library)
$head = vignobleAdminPrepareHead();
// Select about tab in tabs
dol_fiche_head($head, 'about', $langs->trans("Module123100Name"), 0, 'vignoble@vignoble');

// About Tab start here
// get readme file and print
//echo '<br>',var_dump($user),'<br>';
// @TODO issue with user language not properly set-up
switch ($user->lang){
	case 'fr-FR':
		$buffer = file_get_contents(dol_buildpath('/vignoble/README-fr.md', 0));
		break;
	default:
		$buffer = file_get_contents(dol_buildpath('/vignoble/README.md', 0));
}
echo Parsedown::instance()->text($buffer);
// link to GPLV3 licence
echo '<br>', '<a href="' . dol_buildpath('/vignoble/COPYING', 1) . '">', '<img src="' . dol_buildpath('/vignoble/img/gplv3.png', 1) . '"/>', '</a>';

// Page end
dol_fiche_end();
llxFooter();
