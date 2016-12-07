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

/* get dashboard boxes classes */
dol_include_once('/vignoble/core/boxes/plotslastchanged.php');
dol_include_once('/vignoble/core/boxes/vignoblebox.php');

/* get language files */
$langs->load("vignoble@vignoble");
$langs->load("other");

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
	/**
	 * - Get boxes
	 * 
	 */
	$plotslastchanged = new plotslastchanged($db);
	$plotslastchanged->loadBox(10);
	
	$vignoblebox= new vignoblebox($db);
	$vignoblebox->loadBox(5);
	
	/**
	 * - Display boxes
	 */
	print '<div class="fichecenter">'; // frame
	print '<div class="fichethirdleft">'; // left column
	
	print '<div class="ficheaddleft">';
	$plotslastchanged->showBox($plotslastchanged->info_box_head, $plotslastchanged->info_box_contents);
	print '</div>';
	
		
	print '</div>'; // left column end
	
	print '<div class="fichetwothirdright">'; // right column
	
	print '<div class="ficheaddleft">';
	$vignoblebox->showBox($vignoblebox->info_box_head, $vignoblebox->info_box_contents);
	print '</div>';
	
	print '</div>'; // right column end
	print '</div>'; // frame end
	
	llxFooter();
}
