<?php
/* Copyright (C) 2007-2012  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2014       Juanjo Menent       <jmenent@2byte.es>
 * Copyright (C) 2015       Florian Henry       <florian.henry@open-concept.pro>
 * Copyright (C) 2015       Raphaël Doursenaud  <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 * \file    vignoble/parcelle.class.php
 * \ingroup vignoble
 * \brief   This file is an example for a CRUD class file (Create/Read/Update/Delete)
 *          Put some comments here
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class Parcelle
 *
 * Put here description of your class
 * @see CommonObject
 */
class Parcelle extends CommonObject
{
	/**
	 * @var string Id to identify managed objects
	 */
	public $element = 'parcelle';
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'parcelle';

	/**
	 * @var ParcelleLine[] Lines
	 */
	public $lines = array();

	/**
	 */
	
	public $entity=1;
	public $ref;
	public $label;
	public $description;
	public $surface;
	public $nbpieds;
	public $ecartement;
	public $fk_assolement;
	public $fk_cepage;
	public $fk_porte_greffe;
	public $note_private;
	public $tms = '';
	public $datec = '';
	public $fk_user_author;
	public $fk_user_modif;

	/**
	 */
	

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		$this->db = $db;
		return 1;
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
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
		if (isset($this->ref)) {
			 $this->ref = trim($this->ref);
		}
		if (isset($this->label)) {
			 $this->label = trim($this->label);
		}
		if (isset($this->description)) {
			 $this->description = trim($this->description);
		}
		if (isset($this->surface)) {
			 $this->surface = trim($this->surface);
		}
		if (isset($this->nbpieds)) {
			 $this->nbpieds = trim($this->nbpieds);
		}
		if (isset($this->ecartement)) {
			 $this->ecartement = trim($this->ecartement);
		}
		if (isset($this->fk_assolement)) {
			 $this->fk_assolement = trim($this->fk_assolement);
		}
		if (isset($this->fk_cepage)) {
			 $this->fk_cepage = trim($this->fk_cepage);
		}
		if (isset($this->fk_porte_greffe)) {
			 $this->fk_porte_greffe = trim($this->fk_porte_greffe);
		}
		if (isset($this->note_private)) {
			 $this->note_private = trim($this->note_private);
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
		
		$sql.= 'entity,';
		$sql.= 'ref,';
		$sql.= 'label,';
		$sql.= 'description,';
		$sql.= 'surface,';
		$sql.= 'nbpieds,';
		$sql.= 'ecartement,';
		$sql.= 'fk_assolement,';
		$sql.= 'fk_cepage,';
		$sql.= 'fk_porte_greffe,';
		$sql.= 'note_private,';
		$sql.= 'datec,';
		$sql.= 'fk_user_author,';
		$sql.= 'fk_user_modif';

		
		$sql .= ') VALUES (';
		
		$sql .= ' '.((! isset($this->entity) || empty($this->entity))?'1':$this->entity).',';
		$sql .= ' '.(! isset($this->ref)?'NULL':"'".$this->db->escape($this->ref)."'").',';
		$sql .= ' '.(! isset($this->label)?'NULL':"'".$this->db->escape($this->label)."'").',';
		$sql .= ' '.(! isset($this->description)?'NULL':"'".$this->db->escape($this->description)."'").',';
		$sql .= ' '.(! isset($this->surface)?'NULL':"'".$this->surface."'").',';
		$sql .= ' '.(! isset($this->nbpieds)?'NULL':$this->nbpieds).',';
		$sql .= ' '.(! isset($this->ecartement)?'NULL':"'".$this->ecartement."'").',';
		$sql .= ' '.(! isset($this->fk_assolement)?'NULL':$this->fk_assolement).',';
		$sql .= ' '.(! isset($this->fk_cepage)?'NULL':$this->fk_cepage).',';
		$sql .= ' '.(! isset($this->fk_porte_greffe)?'NULL':$this->fk_porte_greffe).',';
		$sql .= ' '.(! isset($this->note_private)?'NULL':"'".$this->db->escape($this->note_private)."'").',';
		$sql .= ' '."'".$this->db->idate(dol_now())."'".',';
		$sql .= ' '.$user->id.',';
		$sql .= ' '.(! isset($this->fk_user_modif)?'NULL':$this->fk_user_modif);

		
		$sql .= ')';

		$this->db->begin();

		$resql = $this->db->query($sql);
		if (!$resql) {
			$error ++;
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
		}

		if (!$error) {
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . $this->table_element);

			if (!$notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action to call a trigger.

				//// Call triggers
				//$result=$this->call_trigger('MYOBJECT_CREATE',$user);
				//if ($result < 0) $error++;
				//// End call triggers
			}
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
	 * @param int    $id  Id object
	 * @param string $ref Ref
	 *
	 * @return int <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = 'SELECT';
		$sql .= ' t.rowid,';
		
		$sql .= " t.entity,";
		$sql .= " t.ref,";
		$sql .= " t.label,";
		$sql .= " t.description,";
		$sql .= " t.surface,";
		$sql .= " t.nbpieds,";
		$sql .= " t.ecartement,";
		$sql .= " t.fk_assolement,";
		$sql .= " t.fk_cepage,";
		$sql .= " t.fk_porte_greffe,";
		$sql .= " t.note_private,";
		$sql .= " t.tms,";
		$sql .= " t.datec,";
		$sql .= " t.fk_user_author,";
		$sql .= " t.fk_user_modif";

		
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
		if (null !== $ref) {
			$sql .= ' WHERE t.ref = ' . '\'' . $ref . '\'';
		} else {
			$sql .= ' WHERE t.rowid = ' . $id;
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$numrows = $this->db->num_rows($resql);
			if ($numrows) {
				$obj = $this->db->fetch_object($resql);

				$this->id = $obj->rowid;
				
				$this->entity = $obj->entity;
				$this->ref = $obj->ref;
				$this->label = $obj->label;
				$this->description = $obj->description;
				$this->surface = $obj->surface;
				$this->nbpieds = $obj->nbpieds;
				$this->ecartement = $obj->ecartement;
				$this->fk_assolement = $obj->fk_assolement;
				$this->fk_cepage = $obj->fk_cepage;
				$this->fk_porte_greffe = $obj->fk_porte_greffe;
				$this->note_private = $obj->note_private;
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
	 * Load object in memory from the database
	 *
	 * @param string $sortorder Sort Order
	 * @param string $sortfield Sort field
	 * @param int    $limit     offset limit
	 * @param int    $offset    offset limit
	 * @param array  $filter    filter array
	 * @param string $filtermode filter mode (AND or OR)
	 *
	 * @return int <0 if KO, >0 if OK
	 */
	public function fetchAll($sortorder='', $sortfield='', $limit=0, $offset=0, array $filter = array(), $filtermode='AND')
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = 'SELECT';
		$sql .= ' t.rowid,';
		
		$sql .= " t.entity,";
		$sql .= " t.ref,";
		$sql .= " t.label,";
		$sql .= " t.description,";
		$sql .= " t.surface,";
		$sql .= " t.nbpieds,";
		$sql .= " t.ecartement,";
		$sql .= " t.fk_assolement,";
		$sql .= " t.fk_cepage,";
		$sql .= " t.fk_porte_greffe,";
		$sql .= " t.note_private,";
		$sql .= " t.tms,";
		$sql .= " t.datec,";
		$sql .= " t.fk_user_author,";
		$sql .= " t.fk_user_modif";

		
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element. ' as t';

		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				$sqlwhere [] = $key . ' LIKE \'%' . $this->db->escape($value) . '%\'';
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= ' WHERE ' . implode(' '.$filtermode.' ', $sqlwhere);
		}
		
		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield,$sortorder);
		}
		if (!empty($limit)) {
		 $sql .=  ' ' . $this->db->plimit($limit + 1, $offset);
		}
		$this->lines = array();

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);

			while ($obj = $this->db->fetch_object($resql)) {
				$line = new ParcelleLine();

				$line->id = $obj->rowid;
				
				$line->entity = $obj->entity;
				$line->ref = $obj->ref;
				$line->label = $obj->label;
				$line->description = $obj->description;
				$line->surface = $obj->surface;
				$line->nbpieds = $obj->nbpieds;
				$line->ecartement = $obj->ecartement;
				$line->fk_assolement = $obj->fk_assolement;
				$line->fk_cepage = $obj->fk_cepage;
				$line->fk_porte_greffe = $obj->fk_porte_greffe;
				$line->note_private = $obj->note_private;
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
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
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
		if (isset($this->ref)) {
			 $this->ref = trim($this->ref);
		}
		if (isset($this->label)) {
			 $this->label = trim($this->label);
		}
		if (isset($this->description)) {
			 $this->description = trim($this->description);
		}
		if (isset($this->surface)) {
			 $this->surface = trim($this->surface);
		}
		if (isset($this->nbpieds)) {
			 $this->nbpieds = trim($this->nbpieds);
		}
		if (isset($this->ecartement)) {
			 $this->ecartement = trim($this->ecartement);
		}
		if (isset($this->fk_assolement)) {
			 $this->fk_assolement = trim($this->fk_assolement);
		}
		if (isset($this->fk_cepage)) {
			 $this->fk_cepage = trim($this->fk_cepage);
		}
		if (isset($this->fk_porte_greffe)) {
			 $this->fk_porte_greffe = trim($this->fk_porte_greffe);
		}
		if (isset($this->note_private)) {
			 $this->note_private = trim($this->note_private);
		}
		if (isset($this->fk_user_author)) {
			 $this->fk_user_author = trim($this->fk_user_author);
		}
		if (isset($this->fk_user_modif)) {
			 $this->fk_user_modif = trim($this->fk_user_modif);
		}

		

		// Check parameters
		// Put here code to add a control on parameters values

		// Update request
		$sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element . ' SET';
		
		$sql .= ' entity = '.(isset($this->entity)?$this->entity:"null").',';
		$sql .= ' ref = '.(isset($this->ref)?"'".$this->db->escape($this->ref)."'":"null").',';
		$sql .= ' label = '.(isset($this->label)?"'".$this->db->escape($this->label)."'":"null").',';
		$sql .= ' description = '.(isset($this->description)?"'".$this->db->escape($this->description)."'":"null").',';
		$sql .= ' surface = '.(isset($this->surface)?$this->surface:"null").',';
		$sql .= ' nbpieds = '.(isset($this->nbpieds)?$this->nbpieds:"null").',';
		$sql .= ' ecartement = '.(isset($this->ecartement)?$this->ecartement:"null").',';
		$sql .= ' fk_assolement = '.(isset($this->fk_assolement)?$this->fk_assolement:"null").',';
		$sql .= ' fk_cepage = '.(isset($this->fk_cepage)?$this->fk_cepage:"null").',';
		$sql .= ' fk_porte_greffe = '.(isset($this->fk_porte_greffe)?$this->fk_porte_greffe:"null").',';
		$sql .= ' note_private = '.(isset($this->note_private)?"'".$this->db->escape($this->note_private)."'":"null").',';
		$sql .= ' tms = '.(dol_strlen($this->tms) != 0 ? "'".$this->db->idate($this->tms)."'" : "'".$this->db->idate(dol_now())."'").',';
		$sql .= ' datec = '.(! isset($this->datec) || dol_strlen($this->datec) != 0 ? "'".$this->db->idate($this->datec)."'" : 'null').',';
		$sql .= ' fk_user_author = '.(isset($this->fk_user_author)?$this->fk_user_author:"null").',';
		$sql .= ' fk_user_modif = '.(isset($this->fk_user_modif)?$this->fk_user_modif:"null");

        
		$sql .= ' WHERE rowid=' . $this->id;

		$this->db->begin();

		$resql = $this->db->query($sql);
		if (!$resql) {
			$error ++;
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
		}

		if (!$error && !$notrigger) {
			// Uncomment this and change MYOBJECT to your own tag if you
			// want this action calls a trigger.

			//// Call triggers
			//$result=$this->call_trigger('MYOBJECT_MODIFY',$user);
			//if ($result < 0) { $error++; //Do also what you must do to rollback action if trigger fail}
			//// End call triggers
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
	 * @param User $user      User that deletes
	 * @param bool $notrigger false=launch triggers after, true=disable triggers
	 *
	 * @return int <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$error = 0;

		$this->db->begin();

		if (!$error) {
			if (!$notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action calls a trigger.

				//// Call triggers
				//$result=$this->call_trigger('MYOBJECT_DELETE',$user);
				//if ($result < 0) { $error++; //Do also what you must do to rollback action if trigger fail}
				//// End call triggers
			}
		}

		if (!$error) {
			$sql = 'DELETE FROM ' . MAIN_DB_PREFIX . $this->table_element;
			$sql .= ' WHERE rowid=' . $this->id;

			$resql = $this->db->query($sql);
			if (!$resql) {
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
	 * @param int $fromid Id of object to clone
	 *
	 * @return int New id of clone
	 */
	public function createFromClone($fromid)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		global $user;
		$error = 0;
		$object = new Parcelle($this->db);

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
		if (!$error) {
			$this->db->commit();

			return $object->id;
		} else {
			$this->db->rollback();

			return - 1;
		}
	}

	/**
	 *  Return a link to the user card (with optionaly the picto)
	 *
	 *	@param	int		$withpicto			Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *	@param	string	$option				On what the link point to
     *  @param	integer	$notooltip			1=Disable tooltip
     *  @param	int		$maxlen				Max length of visible user name
     *  @param  string  $morecss            Add more css on link
	 *	@return	string						String with URL
	 */
	function getNomUrl($withpicto=0, $option='', $notooltip=0, $maxlen=24, $morecss='')
	{
		global $langs, $conf, $db;
        global $dolibarr_main_authentication, $dolibarr_main_demo;
        global $menumanager;


        $result = '';
        $companylink = '';

        $label = '<u>' . $langs->trans("Parcelle") . '</u>';
        $label.= '<div width="100%">';
        $label.= '<b>' . $langs->trans('Ref') . ':</b> ' . $this->ref;

        $link = '<a href="'.dol_buildpath('/vignoble/parcelle_card.php', 1).'?id='.$this->id.'"';
        $link.= ($notooltip?'':' title="'.dol_escape_htmltag($label, 1).'" class="classfortooltip'.($morecss?' '.$morecss:'').'"');
        $link.= '>';
		$linkend='</a>';

        if ($withpicto)
        {
            $result.=($link.img_object(($notooltip?'':$label), 'label', ($notooltip?'':'class="classfortooltip"')).$linkend);
            if ($withpicto != 2) $result.=' ';
		}
		$result.= $link . $this->ref . $linkend;
		return $result;
	}
	
	/**
	 *  Retourne le libelle du status d'un user (actif, inactif)
	 *
	 *  @param	int		$mode          0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
	 *  @return	string 			       Label of status
	 */
	function getLibStatut($mode=0)
	{
		return $this->LibStatut($this->status,$mode);
	}

	/**
	 *  Renvoi le libelle d'un status donne
	 *
	 *  @param	int		$status        	Id status
	 *  @param  int		$mode          	0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
	 *  @return string 			       	Label of status
	 */
	function LibStatut($status,$mode=0)
	{
		global $langs;

		if ($mode == 0)
		{
			$prefix='';
			if ($status == 1) return $langs->trans('Enabled');
			if ($status == 0) return $langs->trans('Disabled');
		}
		if ($mode == 1)
		{
			if ($status == 1) return $langs->trans('Enabled');
			if ($status == 0) return $langs->trans('Disabled');
		}
		if ($mode == 2)
		{
			if ($status == 1) return img_picto($langs->trans('Enabled'),'statut4').' '.$langs->trans('Enabled');
			if ($status == 0) return img_picto($langs->trans('Disabled'),'statut5').' '.$langs->trans('Disabled');
		}
		if ($mode == 3)
		{
			if ($status == 1) return img_picto($langs->trans('Enabled'),'statut4');
			if ($status == 0) return img_picto($langs->trans('Disabled'),'statut5');
		}
		if ($mode == 4)
		{
			if ($status == 1) return img_picto($langs->trans('Enabled'),'statut4').' '.$langs->trans('Enabled');
			if ($status == 0) return img_picto($langs->trans('Disabled'),'statut5').' '.$langs->trans('Disabled');
		}
		if ($mode == 5)
		{
			if ($status == 1) return $langs->trans('Enabled').' '.img_picto($langs->trans('Enabled'),'statut4');
			if ($status == 0) return $langs->trans('Disabled').' '.img_picto($langs->trans('Disabled'),'statut5');
		}
	}
	
	
   /**
     *	Charge les informations d'ordre info dans l'objet commande
     *
     *	@param  int		$id       Id of order
     *	@return	void
     */
    function info($id)
    {
        $sql = 'SELECT c.rowid, datec as datec, tms as datem,';
        $sql.= ' fk_user_author, fk_user_modif';
        $sql.= ' FROM '.MAIN_DB_PREFIX.'parcelle as c';
        $sql.= ' WHERE c.rowid = '.$id;
        $result=$this->db->query($sql);
        if ($result)
        {
            if ($this->db->num_rows($result))
            {
                $obj = $this->db->fetch_object($result);
                $this->id = $obj->rowid;
                if ($obj->fk_user_author)
                {
                    $cuser = new User($this->db);
                    $cuser->fetch($obj->fk_user_author);
                    $this->user_creation   = $cuser;
                }

                if ($obj->fk_user_modif)
                {
                    $vuser = new User($this->db);
                    $vuser->fetch($obj->fk_user_modif);
                    $this->user_validation = $vuser;
                }

                $this->date_creation     = $this->db->jdate($obj->datec);
                $this->date_modification = $this->db->jdate($obj->datem);
            }

            $this->db->free($result);

        }
        else
        {
            dol_print_error($this->db);
        }
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
		
		$this->entity = '';
		$this->ref = '';
		$this->label = '';
		$this->description = '';
		$this->surface = '';
		$this->nbpieds = '';
		$this->ecartement = '';
		$this->fk_assolement = '';
		$this->fk_cepage = '';
		$this->fk_porte_greffe = '';
		$this->note_private = '';
		$this->tms = '';
		$this->datec = '';
		$this->fk_user_author = '';
		$this->fk_user_modif = '';

		
	}

}

/**
 * Class ParcelleLine
 */
class ParcelleLine
{
	/**
	 * @var int ID
	 */
	public $id;
	/**
	 * @var mixed Sample line property 1
	 */
	
	public $entity;
	public $ref;
	public $label;
	public $description;
	public $surface;
	public $nbpieds;
	public $ecartement;
	public $fk_assolement;
	public $fk_cepage;
	public $fk_porte_greffe;
	public $note_private;
	public $tms = '';
	public $datec = '';
	public $fk_user_author;
	public $fk_user_modif;

	/**
	 * @var mixed Sample line property 2
	 */
	
}
