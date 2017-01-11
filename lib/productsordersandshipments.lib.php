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
 * \file lib/productsordersandshipments.lib.php
 * \ingroup products
 * \brief
 *
 * Contains Products orders and shipments SQL requests to get the data .
 */

/**
 * Returns a set of Products Orders in memory from the database
 *
 * @param string $sortorder
 *        	Sort Order
 * @param string $sortfield
 *        	Sort field
 * @param int $limit
 *        	offset LimitIterator number of rows to send back
 * @param int $offset
 *        	offset limit number of rows to start the query
 * @param array $filter
 *        	filter array containing field name as key and condition as value.
 * @param string $filtermode
 *        	filter mode (AND or OR)
 *        	
 * @return int <0 if KO, >0 if OK
 */
function fetchProductsOrders($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
{
	global $db, $langs, $conf, $user;
	dol_syslog(__METHOD__, LOG_DEBUG);
	
	// SQL to get the figures on orders
	// WHERE commandedet.fk_product > 0 GROUP BY product.ref,product.label,commande.date_commande
	
	$sql = 'SELECT';
	$sql .= ' product.ref as Ref,';
	$sql .= ' product.label as Label,';
	// $sql .= ' commande.date_commande as orderDate,';
	$sql .= " COUNT(commande.ref) as totalNumber,";
	$sql .= " SUM(commandedet.qty) as totalQuantity,";
	$sql .= " SUM(commandedet.total_ht) as totalAmount";
	
	$sql .= ' FROM ' . MAIN_DB_PREFIX . 'commandedet  as commandedet';
	$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'product as product on commandedet.fk_product = product.rowid';
	$sql .= ' JOIN ' . MAIN_DB_PREFIX . 'commande as commande on commandedet.fk_commande = commande.rowid';
	
	$sql .= ' WHERE commandedet.fk_product > 0';
	
	// Manage filter
	if (count($filter) > 0) {
		$sql .= ' AND ' . implode(' ' . $filtermode . ' ', $filter);
	}
	
	$sql .= ' GROUP BY Ref,Label';
	
	if (! empty($sortfield)) {
		$sql .= $db->order($sortfield, $sortorder);
	}
	if (! empty($limit)) {
		$sql .= ' ' . $db->plimit($limit, $offset);
	}
	$lines = array();
	
	$sqlresult = $db->query($sql);
	
	if ($sqlresult) {
		$num = $db->num_rows($sqlresult);
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

function fetchProductsShipments($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
{
	global $db, $langs, $conf, $user;
	dol_syslog(__METHOD__, LOG_DEBUG);
	
	// SQL to get the figures on shipments
	// SELECT
	// CASE
	// WHEN shipment.date_expedition IS NULL THEN DATE_FORMAT(shipment.date_creation,'%Y-%m')
	// ELSE DATE_FORMAT(shipment.date_expedition,'%Y-%m')
	// END as 'month',
	// product.ref,
	// SUM(shipmentdet.qty),
	// SUM(line.total_ht),
	// SUM(line.total_tva)
	// FROM llx_expedition as shipment
	// JOIN llx_expeditiondet as shipmentdet on shipmentdet.fk_expedition = shipment.rowid
	// JOIN llx_commandedet as line on shipmentdet.fk_origin_line = line.rowid
	// JOIN llx_product as product on line.fk_product = product.rowid
	// WHERE line.fk_product > 0
	// group by
	// month,
	// product.ref
	
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
	
	// Manage filter
	if (count($filter) > 0) {
		$sql .= ' AND ' . implode(' ' . $filtermode . ' ', $filter);
	}
	
	$sql .= ' GROUP BY Ref,Label';
	
	if (! empty($sortfield)) {
		$sql .= $db->order($sortfield, $sortorder);
	}
	if (! empty($limit)) {
		$sql .= ' ' . $db->plimit($limit, $offset);
	}
	$lines = array();
	
	$sqlresult = $db->query($sql);
	
	if ($sqlresult) {
		$num = $db->num_rows($sqlresult);
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