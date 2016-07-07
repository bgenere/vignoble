<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) <year>  <name of author>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		admin/about.php
 * 	\ingroup	vignoble
 * 	\brief		This file is an example about page
 * 				Put some comments here
 */

// Load Dolibarr environment
if (false === (@include '../../main.inc.php')) {  // From htdocs directory
	require '../../../main.inc.php'; // From "custom" directory
}

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/vignoble.lib.php';

require __DIR__ . '/../vendor/autoload.php';

//require_once "../class/myclass.class.php";
// Translations
$langs->load("vignoble@vignoble");

// Access control
if (! $user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */

/*
 * View
 */
$page_name = "vignobleAbout";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
	. $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = vignobleAdminPrepareHead();
dol_fiche_head(
	$head,
	'about',
	$langs->trans("Module123001Name"),
	0,
	'vignoble@vignoble'
);

// About page goes here
echo $langs->trans("vignobleAboutPage");

echo '<br>';

$buffer = file_get_contents(dol_buildpath('/vignoble/README.md', 0));
echo Parsedown::instance()->text($buffer);

echo '<br>',
'<a href="' . dol_buildpath('/vignoble/COPYING', 1) . '">',
'<img src="' . dol_buildpath('/vignoble/img/gplv3.png', 1) . '"/>',
'</a>';


// Page end
dol_fiche_end();
llxFooter();
