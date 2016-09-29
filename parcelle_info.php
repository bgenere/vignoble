<?php
/* Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin        <regis.houssin@capnetworks.com>
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
 *      \file       htdocs/commande/info.php
 *      \ingroup    commande
 *		\brief      Page des informations d'une commande
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
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
//require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
//require_once DOL_DOCUMENT_ROOT.'/core/lib/order.lib.php';
dol_include_once('/vignoble/class/parcelle.class.php');

//if (!$user->rights->commande->lire)	accessforbidden();

//$langs->load("orders");
$langs->load("vignoble@vignoble");

// Security check
//$socid=0;
$id = GETPOST("id",'int');
//if ($user->societe_id) $socid=$user->societe_id;
//$result=restrictedArea($user,'commande',$comid,'');



/*
 * View
 */

llxHeader('',$langs->trans('Order'),'EN:Customers_Orders|FR:Commandes_Clients|ES:Pedidos de clientes');

$object = new Parcelle($db);
$object->fetch($id);
$object->info($id);
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
	
dol_fiche_head($head, 'info', $langs->trans("Parcelle"), 0, 'wine-cask@vignoble');


print '<table width="100%"><tr><td>';
dol_print_object_info($object);
print '</td></tr></table>';

print '</div>';

llxFooter();
$db->close();
