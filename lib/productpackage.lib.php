<?php

/*
 * Vignoble Module library
 * Copyright (C) 2017 Bruno Généré <bgenere@webiseasy.org>
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
 * \file lib/productpackage.lib.php
 * \ingroup dashboard
 * \brief
 *
 * Contains products package SQL requests to get the data 
 * needed for the product package build.
 */

/**
 * Return list of components for a  package
 * each component is an object with
 * 	Ref product reference
 * 	Label
 * 	Unit number of products per package
 *  Quantity number of products for number of packages
 *  IncDec boolean for stock management (not used)
 * 
 * @param $package reference of the package
 * @param $quantity quantity of package needed
 * 
 * @return -1 if KO, $lines[] if OK 
 */
function fetchProductComponents($package,$quantity)
{
	global $db, $langs, $conf, $user;
	dol_syslog(__METHOD__, LOG_DEBUG);
		
	$sql = 'SELECT';
	$sql .= ' product.ref as Ref,';
	$sql .= ' product.label as Label,';
	$sql .= ' component.qty as Unit,';
	$sql .= ' component.qty*'.$quantity.' as Quantity,';
	$sql .= ' component.incdec as Incdec';
		
	$sql .= ' FROM ' . MAIN_DB_PREFIX . 'product as package ';
	$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'product_association as component ON component.fk_product_pere = package.rowid ';
	$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'product as product ON product.rowid = component.fk_product_fils ';
		
	$sql .= ' WHERE package.ref="'.$package.'"';
		
	$sql .= ' ORDER BY Ref,Label';
	
	$sqlresult = $db->query($sql);
	
	if ($sqlresult) {
		// get result lines
		$lines = Array();
		while ($obj = $db->fetch_object($sqlresult)) {
			$lines[]=  $obj;
		}
		$db->free($sqlresult);
		return $lines;
	} else {
		$errors[] = 'Error ' . $db->lasterror();
		dol_syslog(__METHOD__ . ' ' . join(',', $errors), LOG_ERR);	
		return - 1;
	}
}

/**
 * Return list of packages
 *  (also named virtual products)  
 * 
 * @return -1 if KO, $lines[] if OK 
 * $lines is an indexed Array.
 * using reference and labels
 */
function fetchPackages()
{
	global $db, $langs, $conf, $user;
	dol_syslog(__METHOD__, LOG_DEBUG);
		
	$sql = 'SELECT';
	$sql .= ' package.ref as Ref,';
	$sql .= ' package.label as Label';
		
	$sql .= ' FROM ' . MAIN_DB_PREFIX . 'product as package ';
		
	$sql .= ' WHERE EXISTS (';
	
	$sql .= ' SELECT rowid FROM ' . MAIN_DB_PREFIX . 'product_association as component WHERE component.fk_product_pere = package.rowid)';
		
	$sql .= ' ORDER BY Ref,Label';
	
	$sqlresult = $db->query($sql);
	
	if ($sqlresult) {
		// get result lines
		$lines = Array();
		while ($obj = $db->fetch_object($sqlresult)) {
			$lines[ $obj->Ref ]=  $obj->Label;
		}
		$db->free($sqlresult);
		return $lines;
	} else {
		$errors[] = 'Error ' . $db->lasterror();
		dol_syslog(__METHOD__ . ' ' . join(',', $errors), LOG_ERR);	
		return - 1;
	}
}

/**
 * Return list of warehouses having enough 
 * products to build a package quantity
 *  (also named virtual products)  
 * 
 * @return -1 if KO, $lines[] if OK 
 * $lines is an indexed Array.
 * using reference and labels
 */
function fetchWarehouses($package,$quantity)
{
	global $db, $langs, $conf, $user;
	dol_syslog(__METHOD__, LOG_DEBUG);
		
	$sql = 'SELECT';
	$sql .= ' stock.fk_entrepot as rowid,';
	$sql .= ' entrepot.label as label';
		
	$sql .= ' FROM ' . MAIN_DB_PREFIX . 'product as package ';
	
	$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'product_association as component ON component.fk_product_pere = package.rowid ';
	$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'product as product ON product.rowid = component.fk_product_fils ';
	$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'product_stock as stock on product.rowid = stock.fk_product ';
	$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'entrepot as entrepot on entrepot.rowid = stock.fk_entrepot ';
		
	$sql .= ' WHERE package.ref = "'.$package.'"';
	$sql .= ' AND (component.qty*'.$quantity.') <= stock.reel ';
	
	$sql .= 'GROUP BY fk_entrepot ';
	$sql .= 'HAVING count(product.ref) >= (SELECT count(products.ref) ';
		$sql .= ' FROM ' . MAIN_DB_PREFIX . 'product as packages ';
	
		$sql .= ' JOIN dolibarr.llx_product_association as components ON components.fk_product_pere = packages.rowid ';
		$sql .= ' JOIN dolibarr.llx_product as products ON products.rowid = components.fk_product_fils';
		
		$sql .= ' WHERE packages.ref = "'.$package.'")';
		
	$sql .= ' ORDER BY label';
	
	$sqlresult = $db->query($sql);
	
	if ($sqlresult) {
		// get result lines
		$lines = Array();
		while ($obj = $db->fetch_object($sqlresult)) {
			$lines[ $obj->rowid ]=  $obj->label;
		}
		$db->free($sqlresult);
		return $lines;
	} else {
		$errors[] = 'Error ' . $db->lasterror();
		dol_syslog(__METHOD__ . ' ' . join(',', $errors), LOG_ERR);	
		return - 1;
	}
}

/**
 * Return warehouse stock for  
 * Package and components product
 *  (also named virtual products)  
 * 
 * @return -1 if KO, $lines[] if OK 
 * $lines is an object Array.
 * 
 */
function fetchWarehouseStock($package,$selectedwarehouse)
{
	global $db, $langs, $conf, $user;
	dol_syslog(__METHOD__, LOG_DEBUG);
		
	$sql = 'SELECT';
	$sql .= ' entrepot.label as warehouse,';
	$sql .= ' product.ref as ref,';
	$sql .= ' product.label as label,';
	$sql .= ' stock.reel as quantity';
		
	$sql .= ' FROM ' . MAIN_DB_PREFIX . 'product as package ';
	
	$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'product_association as component ON component.fk_product_pere = package.rowid ';
	$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'product as product ON product.rowid = component.fk_product_fils ';
	$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'product_stock as stock on product.rowid = stock.fk_product ';
	$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'entrepot as entrepot on entrepot.rowid = stock.fk_entrepot ';
		
	$sql .= ' WHERE package.ref = "'.$package.'"';
	$sql .= ' AND entrepot.rowid = '.$selectedwarehouse;
	
	$sql .= ' UNION';
	
	$sql .= ' SELECT';
	$sql .= ' entrepot.label as warehouse,';
	$sql .= ' product.ref as ref,';
	$sql .= ' product.label as label,';
	$sql .= ' stock.reel as quantity';
		
	$sql .= ' FROM ' . MAIN_DB_PREFIX . 'product as product ';
	
	$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'product_stock as stock on product.rowid = stock.fk_product ';
	$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'entrepot as entrepot on entrepot.rowid = stock.fk_entrepot ';
		
	$sql .= ' WHERE product.ref = "'.$package.'"';
	$sql .= ' AND entrepot.rowid = '.$selectedwarehouse;
	
	$sqlresult = $db->query($sql);
	
	if ($sqlresult) {
		// get result lines
		$lines = Array();
		while ($obj = $db->fetch_object($sqlresult)) {
			$lines[]=  $obj;
		}
		$db->free($sqlresult);
		return $lines;
	} else {
		$errors[] = 'Error ' . $db->lasterror();
		dol_syslog(__METHOD__ . ' ' . join(',', $errors), LOG_ERR);	
		return - 1;
	}
}



