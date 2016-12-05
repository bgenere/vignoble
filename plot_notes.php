<?php
/*
 * Copyright (C) 2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2007 Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin <regis.houssin@capnetworks.com>
 * Copyright (C) 2013 Florian Henry <florian.henry@open-concept.pro>
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
 * \file plot_notes.php
 * \ingroup plot
 * \brief The plot note form
 * 
 * The form displays the notes (public and private) of the plot.
 * Notes are in view mode first. 
 * The user could use the form to update, create or delete a note.
 * Top of the form is standardised to display the object identity and navigate the object list.
 * 
 */

@include './tpl/maindolibarr.inc.php';

dol_include_once('/vignoble/class/plot.class.php');
dol_include_once('/vignoble/class/html.form.vignoble.class.php');

$langs->load("vignoble@vignoble");

$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'alpha');

// Security check
// $socid=0;
// if ($user->societe_id) $socid=$user->societe_id;
// result=restrictedArea($user,'commande',$id,'');
// the $object name is mandatory for the includes !!!
// any change there break the code !
$object = new plot($db);
$result = $object->loadObject($id, $ref, true);
//echo 'after object load id: ';var_dump($id);echo ' $ref: ';var_dump($ref);echo ' object: ';var_dump($object);echo'<br />';
	
/**
 * Define tab permission
 * - permission : display the button
 * - permissionnote : allow for changing value
 */
$permission = $user->rights->vignoble->plot->create;
$permissionnote = $user->rights->vignoble->plot->create;

/*
 * Actions
 */

include DOL_DOCUMENT_ROOT . '/core/actions_setnotes.inc.php'; 

/*
 * View
 */

llxHeader('', $langs->trans('PlotCardTitle'));

$form = new Form($db);
$formvignoble = new FormVignoble($db);

if ($id > 0 || ! empty($ref)) {
	
	$head = $formvignoble->getTabsHeader($langs, $object);
	dol_fiche_head($head, 'notes', $langs->trans("Plot"), 0, 'plot@vignoble');
	
	$formvignoble->printObjectRef($form, $langs, $object);
	
	print '<br>';
	
	$cssclass = "titlefield";
	include DOL_DOCUMENT_ROOT . '/core/tpl/notes.tpl.php';
	
	print '</div>';
}

llxFooter();
$db->close();



