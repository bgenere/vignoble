<?php
/* Vignoble Module library
 * Copyright (C) 2016 Bruno Généré      <bgenere@webiseasy.org>
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
 *	\file		lib/vignoble.lib.php
 *	\ingroup	vignoble
 *	\brief		Module library for module vignoble
 *				Put some comments here
 */

/**
* Prepare tabs header for the admin page
* return $head
*/
function vignobleAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("vignoble@vignoble");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/vignoble/admin/admin_vignoble.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;
	$head[$h][0] = dol_buildpath("/vignoble/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@vignoble:/vignoble/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@vignoble:/vignoble/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'vignoble');

	return $head;
}
