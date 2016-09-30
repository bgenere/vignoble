<?php
/*
 * This page is for module administration
 * Copyright (C) 2016 Bruno Généré <webiseasy.org>
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
 * \file admin/vignoble.php
 * \ingroup vignoble
 * \brief Setup tab for module
 * The setup is currently empty
 */

// Load Dolibarr environment
if (false === (@include '../../main.inc.php')) { // From htdocs directory
	require '../../../main.inc.php'; // From "custom" directory
}

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/vignoble.lib.php';

// Translations
$langs->load("vignoble@vignoble");

// Access control admin user only
if (! $user->admin) {
	accessforbidden();
}

// Get Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */
echo $action;

printView($langs, $user);

/**
 * generate and print the view
 */
function printView($langs, $user)
{
	// page name and header
	$page_name = "vignobleSetup";
	llxHeader('', $langs->trans($page_name));
	
	// page title (printed) and link to module list
	// @TODO Why configuration icons on top is a folder ?
	$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';
	print load_fiche_titre($langs->trans($page_name), $linkback);
	
	// Tabs configuration (in library)
	$head = vignobleAdminPrepareHead();
	// Select settings tab in tabs
	dol_fiche_head($head, 'settings', $langs->trans("Module123001Name"), 0, "vignoble@vignoble");
	echo $langs->trans("No Content Yet");
	dol_fiche_end();
	
	llxFooter();
}

