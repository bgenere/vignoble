<?php
/* <one line to give the program's name and a brief idea of what it does.>
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
// Change this following line to use the correct relative path (../, ../../, etc)
$res = 0;
if (! $res && file_exists("../main.inc.php"))
	$res = @include '../main.inc.php'; // to work if your module directory is into dolibarr root htdocs directory
if (! $res && file_exists("../../main.inc.php"))
	$res = @include '../../main.inc.php'; // to work if your module directory is into a subdir of root htdocs directory
if (! $res)
	die("Include of main fails");

	dol_include_once('/vignoble/core/boxes/plotsummarybox.php');
	
// $langs->load("orders");
$langs->load("vignoble@vignoble");
/*
 * View
 */
llxHeader('', $langs->trans('Dashboard'));

print('<h1> Dashboard </h1>');
$box=new plotsummarybox($db);
$box->loadBox(10);
$box->showBox($box->info_box_head, $box->info_box_contents);

llxFooter();
$db->close();