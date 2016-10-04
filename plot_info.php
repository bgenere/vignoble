<?php
/*
 * Copyright (C) 2005-2006 Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin <regis.houssin@capnetworks.com>
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
 * \file htdocs/commande/info.php
 * \ingroup commande
 * \brief Page des informations d'une commande
 */

// Change this following line to use the correct relative path (../, ../../, etc)
$res = 0;
if (! $res && file_exists("../main.inc.php"))
	$res = @include '../main.inc.php'; // to work if your module directory is into dolibarr root htdocs directory
if (! $res && file_exists("../../main.inc.php"))
	$res = @include '../../main.inc.php'; // to work if your module directory is into a subdir of root htdocs directory
if (! $res)
	die("Include of main fails");
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
// require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
// require_once DOL_DOCUMENT_ROOT.'/core/lib/order.lib.php';
dol_include_once('/vignoble/class/plot.class.php');
dol_include_once('/vignoble/class/html.form.vignoble.class.php');
// if (!$user->rights->commande->lire) accessforbidden();

// $langs->load("orders");
$langs->load("vignoble@vignoble");

// Security check
// $socid=0;
$id = GETPOST("id", 'int');
$ref = GETPOST("ref", 'alpha');
if ($ref == '') {
	$ref=null;
}
;
// if ($user->societe_id) $socid=$user->societe_id;
// $result=restrictedArea($user,'commande',$comid,'');

/*
 * View
 */
llxHeader('', $langs->trans('PlotCardTitle'));

$form = new Form($db);
$formvignoble = new FormVignoble($db);

$currentPlot = new plot($db);

$currentPlot->loadObject($id, $ref, true);

$currentPlot->info($id, $ref);
$form = new Form($db);
$formvignoble = new FormVignoble($db);
$head = $formvignoble->getTabsHeader($langs, $currentPlot);
dol_fiche_head($head, 'info', $langs->trans("Plot"), 0, 'plot@vignoble');

$formvignoble->printObjectRef($form, $langs, $currentPlot);
print '<table width="100%"><tr><td>';
dol_print_object_info($currentPlot);
print '</td></tr></table>';

print '</div>';

llxFooter();
$db->close();
