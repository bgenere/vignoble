<?php
/* Copyright (C) 2016 Bruno Généré      <bgenere@webiseasy.org>
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
 * \file index.php
 * \brief Main page - module dashboard
 *  
 *  Displays the Vignoble dashboard
 *  
 *  \ingroup dashboard
 */

@include './tpl/maindolibarr.inc.php';

/*  get dashboard boxes */
dol_include_once('/vignoble/core/boxes/plotsummarybox.php');
	
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
	global $db,$conf,$langs,$user;
	
	llxHeader('', $langs->trans('Dashboard'));
	
	print('<h1> Dashboard </h1>');
	$box=new plotsummarybox($db);
	$box->loadBox(10);
	$box->showBox($box->info_box_head, $box->info_box_contents);
	
	llxFooter();
}
