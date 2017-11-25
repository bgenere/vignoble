<?php
/*
 * Copyright (C) 2017 Bruno Généré <bgenere@conseil.encom1.fr>
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
 * \file productpackagebuild.php
 * \brief Displays products package and allow to build selected package
 * building is
 * decreasing components products from stock to increase package product in stock
 * this is done on a single warehouse.
 *
 * \ingroup dashboard
 */
@include './tpl/maindolibarr.inc.php';

/* get library */
dol_include_once('/vignoble/lib/productpackage.lib.php');
dol_include_once('/product/class/product.class.php');
dol_include_once('/product/stock/class/entrepot.class.php');

/* get language files */
$langs->load("vignoble@vignoble");
$langs->load("products");
$langs->load("stocks");
$langs->load("other");

/* check permissions */
if (! (! empty($conf->product->enabled) && $user->rights->produit->lire)) {
	accessforbidden();
}

$action = GETPOST('action', 'alpha');
$error = 0;

/*
 * Common Check
 */
if (! empty($action)) {
	$selectedproduct = GETPOST('selectedproduct', 'alpha');
	$quantity = GETPOST('quantity', 'int');
	if (! ($quantity > 0)) {
		$error ++;
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Quantity")), null, 'errors');
	}
	if (! empty($selectedproduct)) {
		$productcomponents = fetchProductComponents($selectedproduct, $quantity);
	}
}
/*
 * Actions
 */

if ($action == 'selectproduct') {
	$codemove = dol_print_date(dol_now(), '%y%m%d%H%M%S');
	$label = $langs->trans("Winemaking") . ' ' . $selectedproduct . ' ' . $langs->trans('on') . ' ' . dol_print_date(dol_now(), '%Y-%m-%d %H:%M');
}
if ($action == 'createmovements') {
	$selectedwarehouse = GETPOST('selectedwarehouse', 'int');
	$codemove = GETPOST('codemove', 'alpha');
	$label = GETPOST('label', 'alpha');
	if (empty($codemove)) {
		$error ++;
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->trans("InventoryCode")), null, 'errors');
		$codemove = dol_print_date(dol_now(), '%y%m%d%H%M%S');
	}
	if (empty($label)) {
		$error ++;
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->trans("LabelMovement")), null, 'errors');
		$label = $langs->trans("PackageBuild") . ' ' . $selectedproduct . ' ' . $langs->trans('on') . ' ' . dol_print_date(dol_now(), '%Y-%m-%d %H:%M');
	}
	
	if ($error == 0) {
		$beforestock = fetchWarehouseStock($selectedproduct, $selectedwarehouse);
		
		updateStock($selectedproduct, $selectedwarehouse, $quantity, $label, $codemove, $productcomponents);
		
		$afterstock = fetchWarehouseStock($selectedproduct, $selectedwarehouse);
	}
}

/*
 * View
 */

displayView($selectedproduct, $quantity, $productcomponents, $selectedwarehouse, $label, $codemove, $beforestock, $afterstock);

/* close database */
$db->close();

/* Function List */

/**
 * Display the page view i.e the page dialog
 *
 * @param string $selectedproduct
 *        	the package reference to build
 * @param int $quantity
 *        	the number of packages to build
 * @param
 *        	object array $productcomponents the components of the package
 * @param int $selectedwarehouse
 *        	the id of the warehouse for the build
 * @param string $label
 *        	the label for the stock movements
 * @param string $codemove
 *        	the code for the stock movements
 * @param
 *        	objet array $beforestock previous stock before build
 * @param
 *        	objet array $afterstock new stock after build
 */
function displayView($selectedproduct, $quantity, $productcomponents, $selectedwarehouse, $label, $codemove, $beforestock, $afterstock)
{
	global $db, $conf, $langs, $user, $error, $action;
	
	$pagetitle = $langs->trans('Winemaking');
	
	llxHeader('', $pagetitle);
	print load_fiche_titre($pagetitle, '', 'object_vignoble@vignoble');
	
	print '<h4>' . $langs->trans('BuildPackageIntro') . '</h4>';
	
	displaySelectionForm($selectedproduct, $quantity);
	
	print '<div class="fichecenter">'; // frame
	
	if (($action == 'selectproduct' && $error == 0) || ($action == 'createmovements' && $error > 0)) {
		if (! empty($productcomponents)) {
			
			displayTable($langs->trans('ProductsUsed'), $productcomponents, productcomponentsfields());
			
			$warehouses = fetchWarehouses($selectedproduct, $quantity);
			
			print '<br/><br/>';
			
			if (! empty($warehouses)) {
				displayConfirmationForm($warehouses, $selectedproduct, $selectedwarehouse, $quantity, $label, $codemove);
			} else {
				print '<h4>' . $langs->trans('NoWarehouseWithComponentsStock') . '</h4>';
			}
		}
	}
	if ($action == 'createmovements') {
		if ($error == 0) {
			print '<h4>' . $langs->trans('BuildSuccesfull') . '</h4>';
			
			displayTable($langs->trans('StockBefore'), $beforestock, warehousestockfields());
			
			displayTable($langs->trans('StockAfter'), $afterstock, warehousestockfields());
		} else {
			print '<h4>' . $langs->trans('BuildFailed') . '</h4>';
		}
	}
	print '</div>'; // frame end
	
	llxFooter();
}

/**
 * Display the top form for package selection
 *
 * @param string $selectedproduct
 *        	the package reference to build
 * @param int $quantity
 *        	the number of packages to build
 */
function displaySelectionForm($selectedproduct, $quantity)
{
	global $db, $conf, $langs, $user;
	
	$form = new Form($db);
	print '<div class="fichecenter">';
	
	print '<form method="post" action="' . DOL_URL_ROOT . '/custom/vignoble/productpackagebuild.php">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="selectproduct">';
	print '<table class="noborder nohover centpercent">';
	// Form header
	print '<tr class="liste_titre"><td colspan="3"> 1 - ' . $langs->trans("Select") . ' ' . $langs->trans("Package") . ' & ' . $langs->trans("Quantity") . '</td></tr>';
	// Product package and Button
	print '<tr>';
	print '<td class="nowrap"><label for="products">' . $langs->trans("Product") . '</label></td>';
	print '<td >' . $form->selectarray('selectedproduct', fetchPackages(), $selectedproduct, 0, 1, 0, 0, '100%') . '</td>';
	print '<td rowspan="2"><input type="submit" value="' . $langs->trans("Select") . '" class="button"></td>';
	print '</tr>';
	// Product Quantity
	print '<tr>';
	print '<td class="nowrap"><label for="quantity">' . $langs->trans("Quantity") . '</label></td>';
	print '<td ><input type="text" class="flat" name="quantity" value="' . $quantity . '">';
	print '</tr>';
	// Form footer
	print '</table>';
	print '</form>';
	print '<br>';
	
	print '</div>'; // fiche
}

/**
 * Display the form to confirm the stock movements
 *
 * @param array $warehouses
 *        	the list of available warehouses
 * @param string $selectedproduct
 *        	the package reference to build
 * @param int $selectedwarehouse
 *        	the id of the warehouse for the build
 * @param int $quantity
 *        	the number of packages to build
 * @param string $label
 *        	the label for the stock movements
 * @param string $codemove
 *        	the code for the stock movements
 */
function displayConfirmationForm($warehouses, $selectedproduct, $selectedwarehouse, $quantity, $label, $codemove)
{
	global $db, $conf, $langs, $user;
	
	$form = new Form($db);
	print '<div class="fichecenter">';
	
	print '<form method="post" action="' . DOL_URL_ROOT . '/custom/vignoble/productpackagebuild.php">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="createmovements">';
	print '<input type="hidden" name="selectedproduct" value="' . $selectedproduct . '">';
	print '<input type="hidden" name="quantity" value="' . $quantity . '">';
	print '<table class="noborder nohover centpercent">';
	// Form header
	print '<tr class="liste_titre"><td colspan="3"> 2 - ' . $langs->trans("ConfirmBuild") . '</td></tr>';
	// Warehouse and Button
	print '<tr>';
	print '<td class="nowrap"><label for="warehouse">' . $langs->trans("Warehouse") . '</label></td>';
	print '<td >' . $form->selectarray('selectedwarehouse', $warehouses, $selectedwarehouse, 0, 1, 0, 0, '100%') . '</td>';
	print '<td rowspan="3"><input type="submit" value="' . $langs->trans("Build") . '" class="button"></td>';
	print '</tr>';
	// Comment
	print '<tr>';
	print '<td class="nowrap"><label for="label">' . $langs->trans("LabelMovement") . '</label></td>';
	print '<td ><input type="text" name="label" class="quatrevingtpercent" value="' . dol_escape_htmltag($label) . '">';
	print '</tr>';
	// Comment
	print '<tr>';
	print '<td class="nowrap"><label for="codemove">' . $langs->trans("InventoryCode") . '</label></td>';
	print '<td ><input type="text" name="codemove" size="10" value="' . dol_escape_htmltag($codemove) . '">';
	print '</tr>';
	// Form footer
	print '</table>';
	print '</form>';
	print '<br>';
	
	print '</div>'; // fiche
}

/**
 * Product components fields to display in table
 *
 * @return string[][]
 */
function productcomponentsfields()
{
	$fields = array(
		'Ref' => array(
			'align' => 'left',
			'label' => 'Reference'
		),
		'Label' => array(
			'align' => 'left',
			'label' => 'Label'
		),
		'Unit' => array(
			'align' => 'left',
			'label' => 'Unit/Package'
		),
		'Quantity' => array(
			'align' => 'left',
			'label' => 'Quantity'
		)
	);
	return $fields;
}

/**
 * Warehouse stock fields to display in table
 *
 * @return string[][]
 */
function warehousestockfields()
{
	$fields = array(
		'warehouse' => array(
			'align' => 'left',
			'label' => 'Warehouse'
		),
		'ref' => array(
			'align' => 'left',
			'label' => 'Reference'
		),
		'label' => array(
			'align' => 'left',
			'label' => 'Label'
		),
		'quantity' => array(
			'align' => 'left',
			'label' => 'Quantity'
		)
	);
	return $fields;
}

/**
 * Display a Table content
 *
 * @param string $tablename
 *        	name of the object
 * @param array[] $table
 *        	result set from SQL query
 * @param array[] $fields
 *        	list of fields (columns) and display properties
 *        	
 */
function displayTable($tablename, $table, $fields)
{
	global $db, $conf, $langs, $user;
	
	print load_fiche_titre($langs->trans($tablename), '', '');
	print '<table class="liste" >';
	// Fields title
	print '<tr class="liste_titre">';
	foreach ($fields as $field => $fieldvalue) {
		print print_liste_field_titre($langs->trans($fieldvalue['label']), '', '', '', '', 'align="' . $fieldvalue['align'] . '"');
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
 * Create all stock movements for the build of a product
 * 
 * @param string $selectedproduct
 *        	the product to build
 * @param int $selectedwarehouse
 *        	the warehouse id to use
 * @param int $quantity
 *        	of product to build
 * @param string $label
 *        	for all movements
 * @param string $codemove
 *        	code for all movements
 * @param array[] $productcomponents
 *        	all components and quantities to consume
 */
function updateStock($selectedproduct, $selectedwarehouse, $quantity, $label, $codemove, $productcomponents)
{
	global $db, $conf, $langs, $user;
	
	$db->begin();
	$product = new Product($db);
	
	// process package - add to stock
	$result = $product->fetch('', $selectedproduct);
	$product->load_stock('novirtual'); // Load array product->stock_warehouse
	
	$result2 = $product->correct_stock($user, $selectedwarehouse, $quantity, 0, $label, $product->price, $codemove);
	if ($result2 < 0) {
		$error ++;
		setEventMessages($product->errors, $product->errorss, 'errors');
	}
	
	// process components - remove from stock
	foreach ($productcomponents as $component) {
		
		$result = $product->fetch('', $component->Ref);
		$product->load_stock('novirtual'); // Load array product->stock_warehouse
		                                   
		// Define value of products moved
		                                   // $pricesrc = 0;
		                                   // if (! empty($product->pmp))
		                                   // $pricesrc = $product->pmp;
		                                   // $pricedest = $pricesrc;
		                                   
		// Remove stock
		$result1 = $product->correct_stock($user, $selectedwarehouse, $component->Quantity, 1, $label, $product->price, $codemove);
		if ($result1 < 0) {
			$error ++;
			setEventMessages($product->errors, $product->errorss, 'errors');
		}
	}
	
	if (! $error) {
		$db->commit();
		setEventMessages($langs->trans("StockMovementRecorded"), null, 'mesgs');
	} else {
		$db->rollback();
		setEventMessages($langs->trans("Error"), null, 'errors');
	}
}


