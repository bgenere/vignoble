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
 * \file ordersandshipments.php
 * \brief Displays products orders and shipments numbers between 2 dates
 *
 * \ingroup dashboard
 */
@include './tpl/maindolibarr.inc.php';

/* get library */
dol_include_once('/vignoble/lib/ordersandshipments.lib.php');

/* get language files */
$langs->load("vignoble@vignoble");
$langs->load("products");
$langs->load("orders");
$langs->load("sendings");
$langs->load("other");

/* check permissions */
if (! (! empty($conf->product->enabled) && $user->rights->produit->lire)) {
	accessforbidden();
}

$sort = getsort();

$filter = getfilter();

$orderlines = fetchProductsOrders($sort, '', '', $filter["orders"], 'AND');

$shipmentlines = fetchProductsShipments($sort, '', '', $filter["shipments"], 'AND');

displayView($orderlines, $shipmentlines, $sort, $filter);

/* close database */
$db->close();

/* Function List */

/**
 * Displays the page view
 * 
 * A filter form to select date begin, date end, products.
 * Then Orders and Shipment summary by products on 2 columns.
 * 
 * @param array[] $orders the orders summary result set
 * @param array[] $shipments the shipments summary result set
 * @param array[] $sort sort field and order 
 * @param array[] $filter the filter parameters
 * 
 */
function displayView($orders, $shipments, $sort, $filter)
{
	global $db, $conf, $langs, $user;
	
	$pagetitle = $langs->trans('Orders') . ' & ' . $langs->trans('Shipments');
	
	llxHeader('', $pagetitle);
	print load_fiche_titre($pagetitle, '', 'object_vignoble@vignoble');
	
	displaySearchForm($filter, $sort);
	
	$urlparam = buildSearchParameters($filter);
	
	print '<div class="fichecenter">'; // frame
	
	print '<div class="fichehalfleft">'; // left column
	
	displayTable('Orders', $orders, $sort, $urlparam);
	
	print '</div>'; // left column end
	
	print '<div class="fichehalfright">'; // right column
	
	print '<div class="ficheaddleft">'; // add white space on left
	displayTable('Shipments', $shipments, $sort, $urlparam);
	print '</div>'; //
	
	print '</div>'; // right column end
	print '</div>'; // frame end
	
	llxFooter();
}

/**
 * Display the filter Form :
 * 
 *  Date begin, date end, products list.
 *  
 * @param array[] $filter the filter parameters
 * @param array[] $sort sort field and order      	
 */
function displaySearchForm($filter, $sort)
{
	global $db, $conf, $langs, $user;
	
	$form = new Form($db);
	print '<div class="fichecenter">';
	
	print '<form method="post" action="' . DOL_URL_ROOT . '/custom/vignoble/ordersandshipments.php">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="sortfield" value="' . $sort['field'] . '">';
	print '<input type="hidden" name="sortorder" value="' . $sort['order'] . '">';
	print '<table class="noborder nohover centpercent">';
	// Form header
	print '<tr class="liste_titre"><td colspan="3">' . $langs->trans("Search") . '</td></tr>';
	// Date Begin and Button
	print '<tr>';
	print '<td class="nowrap"><label for="datebegin">' . $langs->trans("DateStart") . '</label></td>';
	print '<td>' . $form->select_date($filter['datebegin'], 'datebegin', 0, 0, 0, "datebegin", 1, 1, 1) . '</td>';
	print '<td rowspan="3"><input type="submit" value="' . $langs->trans("Search") . '" class="button"></td>';
	print '</tr>';
	// Date End
	print '<tr>';
	print '<td class="nowrap"><label for="dateend">' . $langs->trans("DateEnd") . '</label></td>';
	print '<td>' . $form->select_date($filter['dateend'], 'dateend', 0, 0, 0, "dateend", 1, 1, 1) . '</td>';
	print '</tr>';
	// Products
	print '<tr>';
	print '<td class="nowrap"><label for="products">' . $langs->trans("Products") . '</label></td>';
	print '<td>' . $form->multiselectarray('multiproducts', fetchProducts(), $filter['products'], 1, 0, '', 0, '90%') . '</td>';
	print '</tr>';
	print '</table>';
	print '</form>';
	print '<br>';
	
	print '</div>'; // fiche
}

/**
 * Display the result table :
 * 
 * Product ref, product label, object count, quantity, amount
 * 
 * @param string $tablename name of the object        	
 * @param array[] $table    result set from SQL query    	
 * @param array[] $sort     sort field and order
 * @param string  $urlparam filter parameters as a URL part for field sort 	
 */
function displayTable($tablename, $table, $sort, $urlparam)
{
	global $db, $conf, $langs, $user;
	
	$fields = array(
		'Ref' => array(
			'align' => 'left',
			'label' => 'Reference'
		),
		'Label' => array(
			'align' => 'left',
			'label' => 'Label'
		),
		'totalNumber' => array(
			'align' => 'right',
			'label' => $tablename
		),
		'totalQuantity' => array(
			'align' => 'right',
			'label' => 'Quantity'
		),
		'totalAmount' => array(
			'align' => 'right',
			'display' => 'price',
			'label' => 'Amount'
		)
	);
	
	print load_fiche_titre($langs->trans($tablename), '', '');
	print '<table class="liste" >';
	// Fields title
	print '<tr class="liste_titre">';
	foreach ($fields as $field => $fieldvalue) {
		print print_liste_field_titre($langs->trans($fieldvalue['label']), $_SERVER['PHP_SELF'], $field, '', $urlparam, 'align="' . $fieldvalue['align'] . '"', $sort["field"], $sort["order"]);
	}
	print '</tr>';
	// Table lines
	foreach ($table as $line) {
		print '<tr>';
		foreach ($fields as $field => $fieldvalue) {
			print '<td align="' . $fieldvalue['align'] . '">';
			if (! empty($fieldvalue['display'])) {
				print $fieldvalue['display']($line->$field);
			} else {
				print $line->$field;
			}
			print '</td>';
		}
		print '</tr>';
	}
	print '</table>';
}

/**
 * Get field and order used for the tables sort.
 * 
 * Use Ref Ascending by default.
 *
 * @return Array[] with keys : field, order.
 */
function getsort()
{
	$sortfield = GETPOST(sortfield, 'alpha');
	if (empty($sortfield)) {
		$sortfield = 'Ref';
	}
	$sortorder = GETPOST(sortorder, 'alpha');
	if (empty($sortorder)) {
		$sortorder = 'ASC';
	}
	return $sort = array(
		"field" => $sortfield,
		"order" => $sortorder
	);
}

/**
 * Get all data needed to filter the SQL requests and produce the results
 *
 * @return Array[] containing the following keys :
 *         datebegin,
 *         dateend,
 *         products (array of selected products),
 *         orders (array of sql filter conditions for orders),
 *         shipments (array of sql filter conditions for shipments),
 */
function getfilter()
{
	$datebegin = GETPOST("datebeginyear") . '-' . GETPOST("datebeginmonth") . '-' . GETPOST("datebeginday");
	if ($datebegin == '--') { // not in Form check URL
		$datebegin = GETPOST("datebegin");
	}
	if (empty($datebegin)) {
		$datebegin = date("Y-m-d", strtotime("now - 1 month"));
	}
	
	$dateend = GETPOST("dateendyear") . '-' . GETPOST("dateendmonth") . '-' . GETPOST("dateendday");
	if ($dateend == '--') { // not in Form check URL
		$dateend = GETPOST("dateend");
	}
	if (empty($dateend)) {
		$dateend = date("Y-m-d");
	}
	
	$products = GETPOST('multiproducts', 'array');
	if (empty($products)) { // not in Form check URL
		$products = GETPOST('products', 'alpha');
		if (empty($products)) {
			$selectedproducts = "product.ref IS NOT NULL";
		} else {
			$selectedproducts = " product.ref IN " . $products;
			$products = explode("','", trim($products,"('')"));
		}
	} else {
		$selectedproducts = " product.ref IN ('" . implode("','", $products) . "')";
	}
	
	$filter = array(
		"datebegin" => $datebegin,
		"dateend" => $dateend,
		"products" => $products,
		"orders" => array(
			" commande.date_commande >= '" . $datebegin . "' ",
			" commande.date_commande <= '" . $dateend . "' ",
			$selectedproducts
		),
		"shipments" => array(
			"((shipment.date_expedition IS NULL AND shipment.date_creation >= '" . $datebegin . "') OR (shipment.date_expedition >= '" . $datebegin . "'))",
			"((shipment.date_expedition IS NULL AND shipment.date_creation <= '" . $dateend . "') OR (shipment.date_expedition <= '" . $dateend . "'))",
			$selectedproducts
		)
	);
	
	return $filter;
}

/**
 * Build the parameters string to be added to URL to keep the filter conditions.
 * 
 * (used for list sort)
 *  
 * @param Array $filter the filter conditions
 * @return string to be added to URL
 */
function buildSearchParameters($filter)
{
	$param = "";
	if (! empty($filter['datebegin']))
		$param .= "&amp;datebegin=" . urlencode($filter['datebegin']);
	if (! empty($filter['dateend']))
		$param .= "&amp;dateend=" . urlencode($filter['dateend']);
	if (! empty($filter['products']))
		$param .= "&amp;products=" . urlencode("('" . implode("','", $filter['products']) . "')");
	return $param;
}


