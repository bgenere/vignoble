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
 * \file productsordersandshipments.php
 * \brief Main page - Displays products orders and shipments numbers between 2 dates
 *
 * \ingroup dashboard
 */
@include './tpl/maindolibarr.inc.php';

/* get library */
dol_include_once('/vignoble/lib/productsordersandshipments.lib.php');

/* get language files */
$langs->load("vignoble@vignoble");
$langs->load("other");

/* Filter variable */
$datebegin = '2016-01-01';
$dateend = '2017-12-01';
$productselected = array();

$ordersfilter = array();
if (! empty($datebegin)) {
	$ordersfilter[] = " commande.date_commande >= '" . $datebegin . "' ";
}
if (! empty($dateend)) {
	$ordersfilter[] = " commande.date_commande <= '" . $dateend . "' ";
}

$orderlines = fetchProductsOrders('ASC', 'Ref', '', '', $ordersfilter, 'AND');

$shipmentsfilter = array();
if (! empty($datebegin)) {
	$shipmentsfilter[] = "((shipment.date_expedition IS NULL AND shipment.date_creation >= '" . $datebegin . "') OR (shipment.date_expedition >= '" . $datebegin . "'))";
}
if (! empty($dateend)) {
	$shipmentsfilter[] = "((shipment.date_expedition IS NULL AND shipment.date_creation <= '" . $dateend . "') OR (shipment.date_expedition <= '" . $dateend . "'))";
}

$shipmentlines = fetchProductsShipments('ASC', 'Ref', '', '', $shipmentsfilter, 'AND');

displayView($orderlines, $shipmentlines);

/* close database */
$db->close();

/* Function List */

/**
 * Displays the view
 */
function displayView($orders, $shipments)
{
	global $db, $conf, $langs, $user;
	
	llxHeader('', $langs->trans('ProductsOrdersandShipments'));
	print load_fiche_titre($langs->trans("ProductsOrdersandShipments"), '', 'object_vignoble@vignoble');
	/**
	 * - Display selection
	 */
	displaySearchForm();
	/**
	 * - Display tables
	 */
	print '<div class="fichecenter">'; // frame
	print '<div class="fichethirdleft">'; // left column
	
	print '<div class="ficheaddleft">';
	displayTable($orders);
	print '</div>';
	
	print '</div>'; // left column end
	
	print '<div class="fichetwothirdright">'; // right column
	
	print '<div class="ficheaddleft">';
	displayTable($shipments);
	print '</div>';
	
	print '</div>'; // right column end
	print '</div>'; // frame end
	
	llxFooter();
}

function displaySearchForm()
{
	global $db, $conf, $langs, $user;
}

function displayTable($table)
{
	global $db, $conf, $langs, $user;
	
	$fields = array(
		'Ref',
		'Label',
		'totalNumber',
		'totalQuantity',
		'totalAmount'
	);
	
	print '<table class="liste" >';
	// Entête des champs
	print '<tr>';
	foreach ($fields as $field) {
		print print_liste_field_titre($langs->trans($field), $_SERVER['PHP_SELF'],$field);
	}
	print '</tr>';
	// liste des lignes
	foreach ($table as $line) {
		print '<tr>';
		foreach ($fields as $field) {
			print '<td>';
			print $line->$field;
			print '</td>';
		}
		print '</tr>';
	}
	print '</table>';
}


