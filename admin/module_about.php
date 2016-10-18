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
 * \file admin/module_about.php
 * \ingroup admin
 * \brief Displays module information in a Setup Tab
 * Tab content :
 * - README.md file for the module
 * - GPLV3 icon linking to COPYING file (licence text)
 */
@include '../tpl/maindolibarr.inc.php';

// Dolibar and module libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/admin.html.lib.php';
// md file parser
require __DIR__ . '/../vendor/autoload.php';
// Translations
$langs->load("admin");
$langs->load("errors");
$langs->load("other");
$langs->load("vignoble@vignoble");

/*
 * View
 */
beginForm('about');

printREADME($langs);
printGPLV3();

endForm();

/**
 * Parse and display root README.md file.
 * If user language is french, displays README-FR.md instead.
 *
 * @param $langs Dolibarr
 *        	global array
 */
function printREADME($langs)
{
	switch ($langs->getDefaultLang()) {
		case 'fr_FR':
			$buffer = file_get_contents(dol_buildpath('/vignoble/README-FR.md', 0));
			break;
		default:
			$buffer = file_get_contents(dol_buildpath('/vignoble/README.md', 0));
	}
	echo Parsedown::instance()->text($buffer);
}

/**
 * Print GPLV3 icon and link to copying file
 */
function printGPLV3()
{
// link to GPLV3 licence
echo '<br>'; 
echo '<a href="' . dol_buildpath('/vignoble/COPYING', 1) . '" target="blank">';
echo '<img src="' . dol_buildpath('/vignoble/img/gplv3.png', 1) . '"/>', '</a>';
}


