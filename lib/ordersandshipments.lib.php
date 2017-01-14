<?php

/*
 * Vignoble Module library
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
 * \file lib/ordersandshipments.lib.php
 * \ingroup dashboard
 * \brief
 *
 * Contains orders, shipments and products SQL requests to get the data 
 * needed for the ordersandshipments page.
 */

/**
 * Returns Orders count and total quantity and amount by products  
 * 
 * @param array $sort
 *        	Sort parameters : field and order. 
 * @param int $limit
 *        	offset LimitIterator number of rows to send back.
 * @param int $offset
 *        	offset limit number of rows to start the query.
 * @param array $filter
 *        	filter array containing conditions to use.
 * @param string $filtermode
 *        	filter mode (AND or OR)
 *        	
 * @return -1 if KO, $lines[] if OK
 */
function fetchProductsOrders($sort= '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
{
	global $db, $langs, $conf, $user;
	dol_syslog(__METHOD__, LOG_DEBUG);
		
	$sql = 'SELECT';
	$sql .= ' product.ref as Ref,';
	$sql .= ' product.label as Label,';
	$sql .= " COUNT(commande.ref) as totalNumber,";
	$sql .= " SUM(commandedet.qty) as totalQuantity,";
	$sql .= " SUM(commandedet.total_ht) as totalAmount";
	
	$sql .= ' FROM ' . MAIN_DB_PREFIX . 'commandedet  as commandedet';
	$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'product as product on commandedet.fk_product = product.rowid';
	$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'commande as commande on commandedet.fk_commande = commande.rowid';
	
	$sql .= ' WHERE commandedet.fk_product > 0';
	
	if (count($filter) > 0) {
		// add clauses to WHERE
		$sql .= ' AND ' . implode(' ' . $filtermode . ' ', $filter);
	}
	
	$sql .= ' GROUP BY Ref,Label';
	
	if (! empty($sort)) {
		// add ORDER BY
		$sql .= $db->order($sort['field'], $sort['order']);
	}
	if (! empty($limit)) {
		// add LIMIT
		$sql .= ' ' . $db->plimit($limit, $offset);
	}
	
	$sqlresult = $db->query($sql);
	
	if ($sqlresult) {
		$lines = Array();
		while ($obj = $db->fetch_object($sqlresult)) {
			$lines[] = $obj;
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
 * Returns Shipments count and total quantity and amount by products  
 *  
 * @param array $sort
 *        	Sort parameters : field and order. 
 * @param int $limit
 *        	offset LimitIterator number of rows to send back.
 * @param int $offset
 *        	offset limit number of rows to start the query.
 * @param array $filter
 *        	filter array containing conditions to use.
 * @param string $filtermode
 *        	filter mode (AND or OR)
 *        	
 * @return -1 if KO, $lines[] if OK
 */
function fetchProductsShipments($sort = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
{
	global $db, $langs, $conf, $user;
	dol_syslog(__METHOD__, LOG_DEBUG);
	
	$sql = 'SELECT';
	$sql .= ' product.ref as Ref,';
	$sql .= ' product.label as Label,';
	$sql .= " COUNT(shipmentdet.rowid) as totalNumber,";
	$sql .= " SUM(shipmentdet.qty) as totalQuantity,";
	$sql .= " SUM(line.total_ht) as totalAmount";
	
	$sql .= ' FROM ' . MAIN_DB_PREFIX . 'expedition as shipment';
	$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'expeditiondet as shipmentdet on shipmentdet.fk_expedition = shipment.rowid';
	$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'commandedet as line on shipmentdet.fk_origin_line = line.rowid';
	$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'product as product on line.fk_product = product.rowid';
	
	$sql .= ' WHERE line.fk_product > 0';
	
	if (count($filter) > 0) {
		// add clauses to WHERE
		$sql .= ' AND ' . implode(' ' . $filtermode . ' ', $filter);
	}
	
	$sql .= ' GROUP BY Ref,Label';
	
	if (! empty($sort)) {
		// add ORDER BY
		$sql .= $db->order($sort['field'], $sort['order']);
	}
	if (! empty($limit)) {
		// add LIMIT
		$sql .= ' ' . $db->plimit($limit, $offset);
	}
	
	$sqlresult = $db->query($sql);
	
	if ($sqlresult) {
		// get result lines
		$lines = Array();
		while ($obj = $db->fetch_object($sqlresult)) {
			$lines[] = $obj;
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
 * Return list of product reference and labels for multi selection.
 * 
 * @return -1 if KO, $lines[] if OK 
 */
function fetchProducts()
{
	global $db, $langs, $conf, $user;
	dol_syslog(__METHOD__, LOG_DEBUG);
		
	$sql = 'SELECT';
	$sql .= ' product.ref as Ref,';
	$sql .= ' product.label as Label';
		
	$sql .= ' FROM ' . MAIN_DB_PREFIX . 'product as product ';
		
	$sql .= ' WHERE 1';
		
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