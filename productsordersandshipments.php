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
if (!empty($datebegin)){$ordersfilter[]=" commande.date_commande >= '.$datebegin.' ";}
if (!empty($dateend)){$ordersfilter[]=" commande.date_commande <= '.$dateend.' ";}
	
$orderlines = fetchProductsOrders('ASC','productRef','','',$ordersfilter,'AND');

$shipmentlines = fetchProductsShipments();

displayView($orderlines);

/* close database */
$db->close();

/* Function List */

/**
 * Displays the view
 */
function displayView($lines)
{
	global $db, $conf, $langs, $user;
	
	llxHeader('', $langs->trans('ProductsOrdersandShipments'));
	print load_fiche_titre($langs->trans("ProductsOrdersandShipments"), '', 'object_vignoble@vignoble');
	/**
	 * - Display selection
	 * 
	 */
	
	
	/**
	 * - Display table
	 */
	var_dump($lines);
	
	llxFooter();
}
