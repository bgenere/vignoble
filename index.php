<?php
/*
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
 * \file index.php
 * \brief Main page - Displays the Vignoble dashboard
 *
 * \ingroup dashboard
 */
@include './tpl/maindolibarr.inc.php';

/* get dashboard boxes */
dol_include_once('/vignoble/core/boxes/plotslastchanged.php');

/* get language files */
$langs->load("vignoble@vignoble");

displayView();

/* close database */
$db->close();

/**
 * Displays the view
 */
function displayView()
{
	global $db, $conf, $langs, $user;
	
	llxHeader('', $langs->trans('VineYardArea'));
	print load_fiche_titre($langs->trans("VineYardArea"), '', 'object_vignoble@vignoble');
	
	$plotslastchanged = new plotslastchanged($db);
	$plotslastchanged->loadBox(10);
	// $box->showBox($box->info_box_head, $box->info_box_contents);
	/*
	 * Show boxes
	 */
	print '<div class="fichecenter"><div class="fichethirdleft">';
	$plotslastchanged->showBox($plotslastchanged->info_box_head, $plotslastchanged->info_box_contents);
	$plotslastchanged->showBox($plotslastchanged->info_box_head, $plotslastchanged->info_box_contents);
	print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';
	$plotslastchanged->showBox($plotslastchanged->info_box_head, $plotslastchanged->info_box_contents);
	$plotslastchanged->showBox($plotslastchanged->info_box_head, $plotslastchanged->info_box_contents);
	print '</div></div>';
	
	llxFooter();
}
