<?php
/*
 * Copyright (C) 2007-2012 Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2014 Juanjo Menent <jmenent@2byte.es>
 * Copyright (C) 2015 Florian Henry <florian.henry@open-concept.pro>
 * Copyright (C) 2015 Raphaël Doursenaud <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2016 Bruno Généré <webiseasy.org>
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
 * \file vignoble/class/plotcultivationtask.class.php
 * \ingroup plot
 * \brief CRUD class file for the Plot Cultivation Task object (Create/Read/Update/Delete).
 * This is a many to many link between plot and task with properties attached
 */

// inherits from common object class
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';

/**
 * Class Plotcultivationtask 
 *
 * Define the object and the CRUD methods and some more.
 *
 * @see CommonObject
 */
class Plotcultivationtask extends CommonObject
{

	/**
	 *
	 * @var string Id to identify managed objects
	 */
	public $element = 'plotcultivationtask';

	/**
	 *
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'plot_cultivationtask';

	/**
	 *
	 * @var Line[] Lines
	 */
	public $lines = array();

	/**
	 *
	 * @var object properties
	 */
	public $entity = 1;

	public $fk_plot;

	public $fk_task;

	public $coverage;
	
	public $note;

	public $tms = '';

	public $datec = '';

	public $fk_user_author;

	public $fk_user_modif;

	/**
	 */
	
	/**
	 * Constructor
	 *
	 * @param DoliDb $db
	 *        	Database handler
	 */
	public function __construct(DoliDB $db)
	{
		$this->db = $db;
		return 1;
	}

	/**
	 * Create object into database
	 *
	 * @param User $user
	 *        	User that creates
	 * @param bool $notrigger
	 *        	false=launch triggers after, true=disable triggers
	 *        	
	 * @return int <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);
		
		$error = 0;
		
		// Clean parameters
		
		if (isset($this->entity)) {
			$this->entity = trim($this->entity);
		}
		if (isset($this->fk_plot)) {
			$this->fk_plot = trim($this->fk_plot);
		}
		if (isset($this->fk_task)) {
			$this->fk_task = trim($this->fk_task);
		}
		if (isset($this->coverage)) {
			$this->coverage = trim($this->coverage);
		}

		if (isset($this->note)) {
			$this->note = trim($this->note);
		}
		if (isset($this->note_public)) {
			$this->note_public = trim($this->note_public);
		}
		if (isset($this->fk_user_author)) {
			$this->fk_user_author = trim($this->fk_user_author);
		}
		if (isset($this->fk_user_modif)) {
			$this->fk_user_modif = trim($this->fk_user_modif);
		}
		
		// Check parameters
		// Put here code to add control on parameters values
		
		// Insert request
		$sql = 'INSERT INTO ' . MAIN_DB_PREFIX . $this->table_element . '(';
		
		$sql .= 'entity,';
		$sql .= 'fk_plot,';
		$sql .= 'fk_task,';
		$sql .= 'coverage,';
		$sql .= 'note,';
		$sql .= 'datec,';
		$sql .= 'fk_user_author,';
		$sql .= 'fk_user_modif';
		
		$sql .= ') VALUES (';
		
		$sql .= ' ' . ((! isset($this->entity) || empty($this->entity)) ? '1' : $this->entity) . ',';
		$sql .= ' ' . (! isset($this->fk_plot) ? 'NULL' : "'" . $this->db->escape($this->fk_plot) . "'") . ',';
		$sql .= ' ' . (! isset($this->fk_task) ? 'NULL' : "'" . $this->db->escape($this->fk_task) . "'") . ',';
		$sql .= ' ' . (! isset($this->coverage) ? 'NULL' : "'" . $this->db->escape($this->coverage) . "'") . ',';
		$sql .= ' ' . (! isset($this->note) ? 'NULL' : "'" . $this->db->escape($this->note) . "'") . ',';
		$sql .= ' ' . "'" . $this->db->idate(dol_now()) . "'" . ',';
		$sql .= ' ' . $user->id . ',';
		$sql .= ' ' . $user->id;
		
		$sql .= ')';
		
		$this->db->begin();
		
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
		}
		
		if (! $error) {
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . $this->table_element);
			
			}
			
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action to call a trigger.
				
				// // Call triggers
				// $result=$this->call_trigger('MYOBJECT_CREATE',$user);
				// if ($result < 0) $error++;
				// // End call triggers
			}
		
		// Commit or rollback
		if ($error) {
			$this->db->rollback();
			
			return - 1 * $error;
		} else {
			$this->db->commit();
			
			return $this->id;
		}
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int $id
	 *        	Object Id
	 * @param string $ref always null for this object
	 *        	Object Reference
	 * @param $force True,
	 *        	force SELECT in DB when $this->id is not empty
	 *        	False, do not acces DB if $this-> id is not empty
	 *        	
	 * @return int <0 if KO, 0 if not found, Id of object if OK
	 */
	public function loadObject($id, $ref = null, $force = false)
	{
		if (empty($this->id) || $force) {
			dol_syslog(__METHOD__, LOG_DEBUG);
			
			$sql = 'SELECT';
			$sql .= ' t.rowid,';
			$sql .= " t.entity,";
			$sql .= " t.fk_plot,";
			$sql .= " t.fk_task,";
			$sql .= " t.coverage,";
			$sql .= " t.note,";
			$sql .= " t.note_public,";
			$sql .= " t.tms,";
			$sql .= " t.datec,";
			$sql .= " t.fk_user_author,";
			$sql .= " t.fk_user_modif";
			
			$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
			$sql .= ' WHERE t.rowid = ' . $id;
			
			$resql = $this->db->query($sql);
			if ($resql) {
				$numrows = $this->db->num_rows($resql);
				if ($numrows) {
					$obj = $this->db->fetch_object($resql);
					
					$this->id = $obj->rowid;
					$this->entity = $obj->entity;
					$this->fk_plot = $obj->fk_plot;
					$this->fk_task = $obj->fk_task;
					$this->coverage = $obj->coverage;
					$this->note = $obj->note;
					$this->tms = $this->db->jdate($obj->tms);
					$this->datec = $this->db->jdate($obj->datec);
					$this->fk_user_author = $obj->fk_user_author;
					$this->fk_user_modif = $obj->fk_user_modif;
				}
				$this->db->free($resql);
				
				if ($numrows) {
					return $this->id;
				} else {
					return 0;
				}
			} else {
				$this->errors[] = 'Error ' . $this->db->lasterror();
				dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
				
				return - 1;
			}
		}
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int $id
	 *        	Id object
	 * @param string $ref
	 *        	Ref
	 *        	
	 * @return int <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);
		
		$sql = 'SELECT';
		$sql .= ' t.rowid,';
		
		$sql .= " t.entity,";
		$sql .= " t.fk_plot,";
		$sql .= " t.fk_task,";
		$sql .= " t.coverage,";
		$sql .= " t.note,";
		$sql .= " t.tms,";
		$sql .= " t.datec,";
		$sql .= " t.fk_user_author,";
		$sql .= " t.fk_user_modif";
		
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
		$sql .= ' WHERE t.rowid = ' . $id;
	
		$resql = $this->db->query($sql);
		if ($resql) {
			$numrows = $this->db->num_rows($resql);
			if ($numrows) {
				$obj = $this->db->fetch_object($resql);
				
				$this->id = $obj->rowid;
				$this->entity = $obj->entity;
				$this->fk_plot = $obj->fk_plot;
				$this->fk_task = $obj->fk_task;
				$this->coverage = $obj->coverage;
				$this->note = $obj->note;
				$this->tms = $this->db->jdate($obj->tms);
				$this->datec = $this->db->jdate($obj->datec);
				$this->fk_user_author = $obj->fk_user_author;
				$this->fk_user_modif = $obj->fk_user_modif;
			}
			$this->db->free($resql);
			
			if ($numrows) {
				return 1;
			} else {
				return 0;
			}
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
			
			return - 1;
		}
	}

	/**
	 * Load a set of objects in memory from the database
	 *
	 * @param string $sortorder
	 *        	Sort Order
	 * @param string $sortfield
	 *        	Sort field
	 * @param int $limit
	 *        	offset limit
	 * @param int $offset
	 *        	offset limit
	 * @param array $filter
	 *        	filter array
	 * @param string $filtermode
	 *        	filter mode (AND or OR)
	 *        	
	 * @return int <0 if KO, >0 if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		dol_syslog(__METHOD__, LOG_DEBUG);
		
		$sql = 'SELECT';
		$sql .= ' t.rowid,';
		
		$sql .= " t.entity,";
		$sql .= " t.fk_plot,";
		$sql .= " t.fk_task,";
		$sql .= " t.coverage,";
		$sql .= " t.note,";
		$sql .= " t.tms,";
		$sql .= " t.datec,";
		$sql .= " t.fk_user_author,";
		$sql .= " t.fk_user_modif";
		
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
		
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				$sqlwhere[] = $key . ' LIKE \'%' . $this->db->escape($value) . '%\'';
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= ' WHERE ' . implode(' ' . $filtermode . ' ', $sqlwhere);
		}
		
		if (! empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (! empty($limit)) {
			$sql .= ' ' . $this->db->plimit($limit , $offset);
		}
		$this->lines = array();
		
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			
			while ($obj = $this->db->fetch_object($resql)) {
				$line = new plotcultivationtaskLine();
				
				$line->id = $obj->rowid;
				
				$line->entity = $obj->entity;
				$line->fk_plot = $obj->fk_plot;
				$line->fk_task = $obj->fk_task;
				$line->coverage = $obj->coverage;
				$line->note = $obj->note;
				$line->tms = $this->db->jdate($obj->tms);
				$line->datec = $this->db->jdate($obj->datec);
				$line->fk_user_author = $obj->fk_user_author;
				$line->fk_user_modif = $obj->fk_user_modif;
				
				$this->lines[] = $line;
			}
			$this->db->free($resql);
			
			return $num;
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
			
			return - 1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param User $user
	 *        	User that modifies
	 * @param bool $notrigger
	 *        	false=launch triggers after, true=disable triggers
	 *        	
	 * @return int <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		$error = 0;
		
		dol_syslog(__METHOD__, LOG_DEBUG);
		
		// Clean parameters
		
		if (isset($this->entity)) {
			$this->entity = trim($this->entity);
		}
		if (isset($this->fk_plot)) {
			$this->fk_plot = trim($this->fk_plot);
		}
		if (isset($this->fk_task)) {
			$this->fk_task = trim($this->fk_task);
		}
		if (isset($this->coverage)) {
			$this->coverage = trim($this->coverage);
		}
	if (isset($this->note)) {
			$this->note = trim($this->note);
		}

		// Check parameters
		// Put here code to add a control on parameters values
		
		// Update request
		$sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element . ' SET';
		
		$sql .= ' fk_plot = ' . (isset($this->fk_plot) ? "'" . $this->db->escape($this->fk_plot) . "'" : "null") . ',';
		$sql .= ' fk_task = ' . (isset($this->fk_task) ? "'" . $this->db->escape($this->fk_task) . "'" : "null") . ',';
		$sql .= ' coverage = ' . (isset($this->coverage) ? "'" . $this->db->escape($this->coverage) . "'" : "null") . ',';
		$sql .= ' note = ' . (isset($this->note) ? "'" . $this->db->escape($this->note) . "'" : "null") . ',';
		$sql .= ' tms = ' . (dol_strlen($this->tms) != 0 ? "'" . $this->db->idate($this->tms) . "'" : "'" . $this->db->idate(dol_now()) . "'") . ',';
		$sql .= ' fk_user_modif = ' . (isset($this->fk_user_modif) ? $this->fk_user_modif : $user->id);
		
		$sql .= ' WHERE rowid=' . $this->id;
		
		$this->db->begin();
		
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
		}
		
		
		if (! $error && ! $notrigger) {
			// Uncomment this and change MYOBJECT to your own tag if you
			// want this action calls a trigger.
			
			// // Call triggers
			// $result=$this->call_trigger('MYOBJECT_MODIFY',$user);
			// if ($result < 0) { $error++; //Do also what you must do to rollback action if trigger fail}
			// // End call triggers
		}
		
		// Commit or rollback
		if ($error) {
			$this->db->rollback();
			
			return - 1 * $error;
		} else {
			$this->db->commit();
			
			return 1;
		}
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user
	 *        	User that deletes
	 * @param bool $notrigger
	 *        	false=launch triggers after, true=disable triggers
	 *        	
	 * @return int <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);
		
		$error = 0;
		
		$this->db->begin();
		
		if (! $error) {
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action calls a trigger.
				
				// // Call triggers
				// $result=$this->call_trigger('MYOBJECT_DELETE',$user);
				// if ($result < 0) { $error++; //Do also what you must do to rollback action if trigger fail}
				// // End call triggers
			}
		}
		
		if (! $error) {
			$sql = 'DELETE FROM ' . MAIN_DB_PREFIX . $this->table_element;
			$sql .= ' WHERE rowid=' . $this->id;
			
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = 'Error ' . $this->db->lasterror();
				dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
			}
		}
			
		// Commit or rollback
		if ($error) {
			$this->db->rollback();
			
			return - 1 * $error;
		} else {
			$this->db->commit();
			
			return 1;
		}
	}

	/**
	 * Load an object from its id and create a new one in database
	 *
	 * @param int $fromid
	 *        	Id of object to clone
	 *        	
	 * @return int New id of clone
	 */
	public function createFromClone($fromid)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);
		
		global $user;
		$error = 0;
		$object = new plot($this->db);
		
		$this->db->begin();
		
		// Load source object
		$object->fetch($fromid);
		// Reset object
		$object->id = 0;
		
		// Clear fields
		// ...
		
		// Create clone
		$result = $object->create($user);
		
		// Other options
		if ($result < 0) {
			$error ++;
			$this->errors = $object->errors;
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
		}
		
		// End
		if (! $error) {
			$this->db->commit();
			
			return $object->id;
		} else {
			$this->db->rollback();
			
			return - 1;
		}
	}

	/**
	 * Return a link to the user card (with optionaly the picto)
	 *
	 * @param int $withpicto
	 *        	Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 * @param string $option
	 *        	On what the link point to
	 * @param integer $notooltip
	 *        	1=Disable tooltip
	 * @param int $maxlen
	 *        	Max length of visible user name
	 * @param string $morecss
	 *        	Add more css on link
	 * @return string String with URL
	 */
	function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $maxlen = 24, $morecss = '')
	{
		global $langs, $conf, $db;
		global $dolibarr_main_authentication, $dolibarr_main_demo;
		global $menumanager;
		
		$result = '';
		$companylink = '';
		
		$fk_task = '<u>' . $langs->trans("Plot") . '</u>';
		$fk_task .= '<div width="100%">';
		$fk_task .= '<b>' . $langs->trans('Ref') . ':</b> ' . $this->ref;
		
		$link = '<a href="' . dol_buildpath('/vignoble/plot_card.php', 1) . '?id=' . $this->id . '"';
		$link .= ($notooltip ? '' : ' title="' . dol_escape_htmltag($fk_task, 1) . '" class="classfortooltip' . ($morecss ? ' ' . $morecss : '') . '"');
		$link .= '>';
		$linkend = '</a>';
		
		if ($withpicto) {
			$result .= ($link . img_object(($notooltip ? '' : $fk_task), 'fk_task', ($notooltip ? '' : 'class="classfortooltip"')) . $linkend);
			if ($withpicto != 2)
				$result .= ' ';
		}
		$result .= $link . $this->ref . $linkend;
		return $result;
	}

	/**
	 * Retourne le libelle du status d'un user (actif, inactif)
	 *
	 * @param int $mode
	 *        	0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
	 * @return string Label of status
	 */
	function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	/**
	 * Renvoi le libelle d'un status donne
	 *
	 * @param int $status
	 *        	Id status
	 * @param int $mode
	 *        	0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
	 * @return string Label of status
	 */
	function LibStatut($status, $mode = 0)
	{
		global $langs;
		
		if ($mode == 0) {
			$prefix = '';
			if ($status == 1)
				return $langs->trans('Enabled');
			if ($status == 0)
				return $langs->trans('Disabled');
		}
		if ($mode == 1) {
			if ($status == 1)
				return $langs->trans('Enabled');
			if ($status == 0)
				return $langs->trans('Disabled');
		}
		if ($mode == 2) {
			if ($status == 1)
				return img_picto($langs->trans('Enabled'), 'statut4') . ' ' . $langs->trans('Enabled');
			if ($status == 0)
				return img_picto($langs->trans('Disabled'), 'statut5') . ' ' . $langs->trans('Disabled');
		}
		if ($mode == 3) {
			if ($status == 1)
				return img_picto($langs->trans('Enabled'), 'statut4');
			if ($status == 0)
				return img_picto($langs->trans('Disabled'), 'statut5');
		}
		if ($mode == 4) {
			if ($status == 1)
				return img_picto($langs->trans('Enabled'), 'statut4') . ' ' . $langs->trans('Enabled');
			if ($status == 0)
				return img_picto($langs->trans('Disabled'), 'statut5') . ' ' . $langs->trans('Disabled');
		}
		if ($mode == 5) {
			if ($status == 1)
				return $langs->trans('Enabled') . ' ' . img_picto($langs->trans('Enabled'), 'statut4');
			if ($status == 0)
				return $langs->trans('Disabled') . ' ' . img_picto($langs->trans('Disabled'), 'statut5');
		}
	}

	/**
	 * get object info
	 *
	 * @param int $id
	 *        	Id of object
	 * @return void
	 */
	function info($id, $ref = null)
	{
		$sql = 'SELECT c.rowid, datec as datec, tms as datem,';
		$sql .= ' fk_user_author, fk_user_modif';
		$sql .= ' FROM ' . MAIN_DB_PREFIX . 'plotcultivationtask as c';
		$sql .= ' WHERE c.rowid = ' . $id;
		
		$result = $this->db->query($sql);
		if ($result) {
			if ($this->db->num_rows($result)) {
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
				if ($obj->fk_user_author) {
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_author);
					$this->user_creation = $cuser;
				}
				
				if ($obj->fk_user_modif) {
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_modif);
					$this->user_validation = $vuser;
				}
				
				$this->date_creation = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
			}
			
			$this->db->free($result);
		} else {
			dol_print_error($this->db);
		}
	}

	/**
	 * Create a document onto disk accordign to template module.
	 *
	 * @param string $modele
	 *        	Force le mnodele a utiliser ('' to not force)
	 * @param Translate $outputlangs
	 *        	objet lang a utiliser pour traduction
	 * @param int $hidedetails
	 *        	Hide details of lines
	 * @param int $hidedesc
	 *        	Hide coverage
	 * @param int $hideref
	 *        	Hide ref
	 * @return int 0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0)
	{
		global $conf, $langs;
		
		$langs->load("vignoble@vignoble");
		
		// Positionne le modele sur le nom du modele a utiliser
		if (! dol_strlen($modele)) {
			if (! empty($conf->global->PLOT_ADDON_PDF)) {
				$modele = $conf->global->PLOT_ADDON_PDF;
			} else {
				$modele = 'plot';
			}
		}
		
		$modelpath = "core/modules/vignoble/doc/";
		
		return $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref);
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		$this->id = 0;
		
		$this->specimen = 1;
		$this->entity = '1';
		$this->fk_plot = '1';
		$this->fk_task = '1';
		$this->coverage = '100';
		$this->note = 'This is a note on task for the plot';
		$this->tms = '';
		$this->datec = '';
		$this->fk_user_author = '';
		$this->fk_user_modif = '';
	}
}

/**
 * Class plotcultivationtaskLine
 */
class plotcultivationtaskLine
{
	public $id;

	public $entity = 1;

	public $fk_plot;

	public $fk_task;

	public $note;

	public $coverage;

	public $tms = '';

	public $datec = '';

	public $fk_user_author;

	public $fk_user_modif;

}
