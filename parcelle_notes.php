<?php
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2007 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2013      Florian Henry		  	<florian.henry@open-concept.pro>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       htdocs/commande/note.php
 *  \ingroup    commande
 *  \brief      Fiche de notes sur une commande
 */

// Change this following line to use the correct relative path (../, ../../, etc)
$res = 0;
if (! $res && file_exists("../main.inc.php"))
	$res = @include '../main.inc.php'; // to work if your module directory is into dolibarr root htdocs directory
if (! $res && file_exists("../../main.inc.php"))
	$res = @include '../../main.inc.php'; // to work if your module directory is into a subdir of root htdocs directory
if (! $res && file_exists("../../../dolibarr/htdocs/main.inc.php"))
	$res = @include '../../../dolibarr/htdocs/main.inc.php'; // Used on dev env only
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php"))
	$res = @include '../../../../dolibarr/htdocs/main.inc.php'; // Used on dev env only
if (! $res)
	die("Include of main fails");
//require_once DOL_DOCUMENT_ROOT.'/core/lib/order.lib.php';
//require_once DOL_DOCUMENT_ROOT .'/commande/class/commande.class.php';
dol_include_once('/vignoble/class/parcelle.class.php');


//$langs->load("companies");
//$langs->load("bills");
$langs->load("vignoble@vignoble");

$id = GETPOST('id','int');
$ref=GETPOST('ref','alpha');
//$socid=GETPOST('socid','int');
$action=GETPOST('action','alpha');

// Security check
//$socid=0;
//if ($user->societe_id) $socid=$user->societe_id;
//result=restrictedArea($user,'commande',$id,'');


$object = new Parcelle($db);
if (! $object->fetch($id) > 0)
{
	dol_print_error($db);
}

//$permissionnote=$user->rights->vignoble->creer;	// Used by the include of actions_setnotes.inc.php
$permission=true;
$permissionnote=true;


/*
 * Actions
 */

include DOL_DOCUMENT_ROOT.'/core/actions_setnotes.inc.php';	// Must be include, not includ_once


/*
 * View
 */

llxHeader('',$langs->trans('Order'),'EN:Customers_Orders|FR:Commandes_Clients|ES:Pedidos de clientes');

$form = new Form($db);

if ($id > 0 || ! empty($ref))
{
	//$soc = new Societe($db);
	//$soc->fetch($object->socid);

	//$head = commande_prepare_head($object);
	$head = array();
	$h = 0;
	$head[$h][0] = 'parcelle_card.php?id=' . $object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$h = 1;
	$head[$h][0] = 'parcelle_notes.php?id=' . $object->id;
	$head[$h][1] = $langs->trans("Notes");
	$head[$h][2] = 'notes';
	$h = 2;
	$head[$h][0] = 'parcelle_info.php?id=' . $object->id;
	$head[$h][1] = $langs->trans("Info");
	$head[$h][2] = 'info';
	
	dol_fiche_head($head, 'notes', $langs->trans("Parcelle"), 0, 'parcelle');

	print '<table class="border" width="100%">';

	$linkback = '<a href="'.dol_buildpath('/vignoble/parcelle_list.php',1).'">'.$langs->trans("BackToList").'</a>';
	
	// Ref
	print '<tr><td width="25%">'.$langs->trans("Ref").'</td><td colspan="3">';
	print $form->showrefnav($object, 'ref', $linkback, 1, 'ref', 'ref');
	print "</td></tr>";

	print "</table>";

	print '<br>';

	$cssclass="titlefield";
	include DOL_DOCUMENT_ROOT.'/core/tpl/notes.tpl.php';

	print '</div>';
}


llxFooter();
$db->close();
