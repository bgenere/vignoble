<?php
/* Vignoble Module class 
 * Copyright (C) 2016 Bruno Généré      <bgenere@webiseasy.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\defgroup	vignoble	Vignoble module
 * 	\brief		Vignoble module descriptor.
 * 	\file		core/modules/modVignoble.class.php
 * 	\ingroup	vignoble
 * 	\brief		Description and activation file for module Vignoble
 */
include_once DOL_DOCUMENT_ROOT . "/core/modules/DolibarrModules.class.php";

/**
 * Description and activation class for module Vignoble
 */
class modVignoble extends DolibarrModules
{

	/**
	 * 	Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * 	@param	DoliDB		$db	Database handler
	 */
	public function __construct($db)
	{
		global $langs, $conf;

		// DolibarrModules is abstract in Dolibarr < 3.8
		if (is_callable('parent::__construct')) {
			parent::__construct($db);
		} else {
			$this->db = $db;
		}

		// Id for module (must be unique).
		// Use a free id here
		// (See http://wiki.dolibarr.org/index.php/List_of_modules_id for available ranges).
		$this->numero = 123001;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'vignoble';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "other";
		// Module label (no space allowed)
		// used if translation string 'ModuleXXXName' not found
		// (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		// Module description
		// used if translation string 'ModuleXXXDesc' not found
		// (where XXX is value of numeric property 'numero' of module)
		$this->description = "Wine Yard Management";
		// Possible values for version are: 'development', 'experimental' or version
		$this->version = 'development';
		// Key used in llx_const table to save module status enabled/disabled
		// (where vignoble is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);
		// Where to store the module in setup page
		// (0=common,1=interface,2=others,3=very specific)
		$this->special = 3;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png
		// use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png
		// use this->picto='pictovalue@module'
		$this->picto = 'vignoble@vignoble'; // mypicto@vignoble
		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		// for default path (eg: /vignoble/core/xxxxx) (0=disable, 1=enable)
		// for specific path of parts (eg: /vignoble/core/modules/barcode)
		// for specific css file (eg: /vignoble/css/vignoble.css.php)
		$this->module_parts = array(
			// Set this to 1 if module has its own trigger directory
			'triggers' => 1,
			// Set this to 1 if module has its own login method directory
			//'login' => 0,
			// Set this to 1 if module has its own substitution function file
			//'substitutions' => 0,
			// Set this to 1 if module has its own menus handler directory
			//'menus' => 0,
			// Set this to 1 if module has its own theme directory (theme)
			// 'theme' => 0,
			// Set this to 1 if module overwrite template dir (core/tpl)
			// 'tpl' => 0,
			// Set this to 1 if module has its own barcode directory
			//'barcode' => 0,
			// Set this to 1 if module has its own models directory
			//'models' => 0,
			// Set this to relative path of css if module has its own css file
			'css' => array('vignoble/css/mycss.css.php'),
			// Set this to relative path of js file if module must load a js on all pages
			// 'js' => array('vignoble/js/vignoble.js'),
			// Set here all hooks context managed by module
			// 'hooks' => array('hookcontext1','hookcontext2'),
			// To force the default directories names
			// 'dir' => array('output' => 'othermodulename'),
			// Set here all workflow context managed by module
			// Don't forget to depend on modWorkflow!
			// The description translation key will be descWORKFLOW_MODULE1_YOURACTIONTYPE_MODULE2
			// You will be able to check if it is enabled with the $conf->global->WORKFLOW_MODULE1_YOURACTIONTYPE_MODULE2 constant
			// Implementation is up to you and is usually done in a trigger.
			// 'workflow' => array(
			//     'WORKFLOW_MODULE1_YOURACTIONTYPE_MODULE2' => array(
			//         'enabled' => '! empty($conf->module1->enabled) && ! empty($conf->module2->enabled)',
			//         'picto' => 'yourpicto@vignoble',
			//         'warning' => 'WarningTextTranslationKey',
			//      ),
			// ),
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/vignoble/temp");
		$this->dirs = array();

		// Config pages. Put here list of php pages
		// stored into vignoble/admin directory, used to setup module.
		$this->config_page_url = array("admin_vignoble.php@vignoble");

		// Dependencies
		// A condition to hide module
		$this->hidden = false;
		// List of modules class name as string that must be enabled if this module is enabled
		// Example : $this->depends('modAnotherModule', 'modYetAnotherModule')
		$this->depends = array();
		// List of modules id to disable if this one is disabled
		$this->requiredby = array();
		// List of modules id this module is in conflict with
		$this->conflictwith = array();
		// Minimum version of PHP required by module
		$this->phpmin = array(5, 3);
		// Minimum version of Dolibarr required by module
		$this->need_dolibarr_version = array(3, 2);
		// Language files list (langfiles@vignoble)
		$this->langfiles = array("vignoble@vignoble");
		// Constants
		// List of particular constants to add when module is enabled
		// (name, type ['chaine' or ?], value, description, visibility, entity ['current' or 'allentities'], delete on unactive)
		// Example:
		$this->const = array(
			//	0 => array(
			//		'vignoble_MYNEWCONST1',
			//		'chaine',
			//		'myvalue',
			//		'This is a constant to add',
			//		1,
			//      'current',
			//      0,
			//	),
			//	1 => array(
			//		'vignoble_MYNEWCONST2',
			//		'chaine',
			//		'myvalue',
			//		'This is another constant to add',
			//		0,
			//	)
		);

		// Array to add new pages in new tabs
		// Example:
		$this->tabs = array(
			//	// To add a new tab identified by code tabname1
			//	'objecttype:+tabname1:Title1:langfile@vignoble:$user->rights->vignoble->read:/vignoble/mynewtab1.php?id=__ID__',
			//	// To add another new tab identified by code tabname2
			//	'objecttype:+tabname2:Title2:langfile@vignoble:$user->rights->othermodule->read:/vignoble/mynewtab2.php?id=__ID__',
			//	// To remove an existing tab identified by code tabname
			//	'objecttype:-tabname'		
			// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
			// 'contact'          to add a tab in contact view
			// 'contract'         to add a tab in contract view
			// 'group'            to add a tab in group view
			// 'intervention'     to add a tab in intervention view
			// 'invoice'          to add a tab in customer invoice view
			// 'invoice_supplier' to add a tab in supplier invoice view
			// 'member'           to add a tab in fundation member view
			// 'opensurveypoll'	  to add a tab in opensurvey poll view
			// 'order'            to add a tab in customer order view
			// 'order_supplier'   to add a tab in supplier order view
			// 'payment'		  to add a tab in payment view
			// 'payment_supplier' to add a tab in supplier payment view
			// 'product'          to add a tab in product view
			// 'propal'           to add a tab in propal view
			// 'project'          to add a tab in project view
			// 'stock'            to add a tab in stock view
			// 'thirdparty'       to add a tab in third party view
			// 'user'             to add a tab in user view
		);

		// Dictionaries
		if (! isset($conf->vignoble->enabled)) {
			$conf->vignoble=new stdClass();
			$conf->vignoble->enabled = 0;
		}
		//$this->dictionaries = array();
		
		  // This is to avoid warnings
		  //if (! isset($conf->vignoble->enabled)) $conf->vignoble->enabled=0;
		  $this->dictionaries=array(
			  //'langs'=>'dico', -- not used
			  // List of tables we want to see into dictionnary editor
			  'tabname'=>array(
				  MAIN_DB_PREFIX."c_assolement",
				  MAIN_DB_PREFIX."c_cepage",
				  MAIN_DB_PREFIX."c_porte_greffe"
			  ),
			  // Label of tables
			  'tablib'=>array("Assolement","Cépage","Porte Greffe"),
			  // Request to select fields
			  'tabsql'=>array(
				  'SELECT f.rowid as rowid, f.code, f.label, f.active'
				  . ' FROM ' . MAIN_DB_PREFIX . 'c_assolement as f',
				  'SELECT f.rowid as rowid, f.code, f.label, f.active'
				  . ' FROM ' . MAIN_DB_PREFIX . 'c_cepage as f',
				  'SELECT f.rowid as rowid, f.code, f.label, f.active'
				  . ' FROM ' . MAIN_DB_PREFIX . 'c_porte_greffe as f'
			  ),
			  // Sort order
			  'tabsqlsort'=>array("label ASC","label ASC","label ASC"),
			  // List of fields (result of select to show dictionary)
			  'tabfield'=>array("code,label","code,label","code,label"),
			  // List of fields (list of fields to edit a record)
			  'tabfieldvalue'=>array("code,label","code,label","code,label"),
			  // List of fields (list of fields for insert)
			  'tabfieldinsert'=>array("code,label","code,label","code,label"),
			  // Name of columns with primary key (try to always name it 'rowid')
			  'tabrowid'=>array("rowid","rowid","rowid"),
			  // Condition to show each dictionary
			  'tabcond'=>array(
				  $conf->vignoble->enabled,
				  $conf->vignoble->enabled,
				  $conf->vignoble->enabled
			  )
		  );
		 

		// Boxes
		// Add here list of php file(s) stored in core/boxes that contains class to show a box.
		$this->boxes = array(); // Boxes list
		// Example:
		$this->boxes = array(
			0 => array(
				'file' => 'mybox@vignoble',
				'note' => '',
				'enabledbydefaulton' => 'Home'
			)
		);

		// Permissions
		$this->rights = array(); // Permission array used by this module
		$r = 0;

		// Add here list of permission defined by
		// an id, a label, a boolean and two constant strings.
		// Example:
		//// Permission id (must not be already used)
		//$this->rights[$r][0] = 2000;
		//// Permission label
		//$this->rights[$r][1] = 'Permision label';
		//// Permission by default for new user (0/1)
		//$this->rights[$r][3] = 1;
		//// In php code, permission will be checked by test
		//// if ($user->rights->permkey->level1->level2)
		//$this->rights[$r][4] = 'level1';
		//// In php code, permission will be checked by test
		//// if ($user->rights->permkey->level1->level2)
		//$this->rights[$r][5] = 'level2';
		//$r++;

		// Main menu entries
		// Translations added to fix menu translation issue
		$langs->load("vignoble@vignoble");
		//
		// Top Menu entry 
		$this->menu[]=array(
		// Put 0 if this is a top menu
			'fk_menu'=>0,
		//	// This is a Top menu entry
			'type'=>'top',
		// Menu's title. 
			'titre'=> $langs->trans('Module123001Name'),
		// This menu's mainmenu ID
			'mainmenu'=>'vignoble',
		// This menu's leftmenu ID
			'leftmenu'=>'vignoble',
			'url'=>'/vignoble/parcelle_list.php',
			'position'=>123,
		//	// Define condition to show or hide menu entry.
		// Use '$conf->vignoble->enabled' if entry must be visible if module is enabled.
			'enabled'=>'$conf->vignoble->enabled',
		// Use 'perms'=>'$user->rights->vignoble->level1->level2'
		// if you want your menu with a permission rules
			'perms'=>'1',
			'target'=>'',
		// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2
		);
		// Left menu
		$this->menu[]=array(
			// Use r=value where r is index key used for the parent menu entry
			// (higher parent must be a top menu entry)
			'fk_menu'=>'r=0',
			// This is a Left menu entry
			'type'=>'left',
			//Menu's title. FIXME: use a translation key
			'titre'=> $langs->trans('Parcelles'),
			// This menu's mainmenu ID
			'mainmenu'=>'vignoble',
			//This menu's leftmenu ID
			'leftmenu'=>'parcelles',
			'url'=>'/vignoble/parcelle_list.php',
			'position'=>1,
			// Define condition to show or hide menu entry.
			// Use '$conf->vignoble->enabled' if entry must be visible if module is enabled.
			'enabled'=>'$conf->vignoble->enabled',
			// Use 'perms'=>'$user->rights->vignoble->level1->level2'
			// if you want your menu with a permission rules
			'perms'=>'1',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2
		);
		//
		// Example to declare a Left Menu entry into an existing Top menu entry:
		$this->menu[]=array(
			// Use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy'
			'fk_menu'=>'fk_mainmenu=vignoble,fk_leftmenu=parcelles',
			// This is a Left menu entry
			'type'=>'left',
			//Menu's title. FIXME: use a translation key
			'titre'=>'Nouvelle Parcelle',
			//This menu's mainmenu ID
			'mainmenu'=>'vignoble',
			//This menu's leftmenu ID
			'leftmenu'=>'create',
			'url'=>'/vignoble/parcelle_card.php',
			'position'=>10,
			// Define condition to show or hide menu entry.
			// Use '$conf->vignoble->enabled' if entry must be visible if module is enabled.
			// Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->vignoble->enabled',
			// Use 'perms'=>'$user->rights->vignoble->level1->level2'
			// if you want your menu with a permission rules
			'perms'=>'1',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2
		);
//
		// Example to declare a Left Menu entry into an existing Top menu entry:
		$this->menu[]=array(
			// Use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy'
			'fk_menu'=>'fk_mainmenu=vignoble,fk_leftmenu=parcelles',
			// This is a Left menu entry
			'type'=>'left',
			//Menu's title. FIXME: use a translation key
			'titre'=>'Liste des parcelles',
			//This menu's mainmenu ID
			'mainmenu'=>'vignoble',
			//This menu's leftmenu ID
			'leftmenu'=>'list',
			'url'=>'/vignoble/parcelle_list.php',
			'position'=>20,
			// Define condition to show or hide menu entry.
			// Use '$conf->vignoble->enabled' if entry must be visible if module is enabled.
			// Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->vignoble->enabled',
			// Use 'perms'=>'$user->rights->vignoble->level1->level2'
			// if you want your menu with a permission rules
			'perms'=>'1',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2
		);
		// Exports
		$r = 0;

		// Example:
		//$this->export_code[$r]=$this->rights_class.'_'.$r;
		//// Translation key (used only if key ExportDataset_xxx_z not found)
		//$this->export_label[$r]='CustomersInvoicesAndInvoiceLines';
		//// Condition to show export in list (ie: '$user->id==3').
		//// Set to 1 to always show when module is enabled.
		//$this->export_enabled[$r]='1';
		//$this->export_permission[$r]=array(array("facture","facture","export"));
		//$this->export_fields_array[$r]=array(
		//	's.rowid'=>"IdCompany",
		//	's.nom'=>'CompanyName',
		//	's.address'=>'Address',
		//	's.cp'=>'Zip',
		//	's.ville'=>'Town',
		//	's.fk_pays'=>'Country',
		//	's.tel'=>'Phone',
		//	's.siren'=>'ProfId1',
		//	's.siret'=>'ProfId2',
		//	's.ape'=>'ProfId3',
		//	's.idprof4'=>'ProfId4',
		//	's.code_compta'=>'CustomerAccountancyCode',
		//	's.code_compta_fournisseur'=>'SupplierAccountancyCode',
		//	'f.rowid'=>"InvoiceId",
		//	'f.facnumber'=>"InvoiceRef",
		//	'f.datec'=>"InvoiceDateCreation",
		//	'f.datef'=>"DateInvoice",
		//	'f.total'=>"TotalHT",
		//	'f.total_ttc'=>"TotalTTC",
		//	'f.tva'=>"TotalVAT",
		//	'f.paye'=>"InvoicePaid",
		//	'f.fk_statut'=>'InvoiceStatus',
		//	'f.note'=>"InvoiceNote",
		//	'fd.rowid'=>'LineId',
		//	'fd.description'=>"LineDescription",
		//	'fd.price'=>"LineUnitPrice",
		//	'fd.tva_tx'=>"LineVATRate",
		//	'fd.qty'=>"LineQty",
		//	'fd.total_ht'=>"LineTotalHT",
		//	'fd.total_tva'=>"LineTotalTVA",
		//	'fd.total_ttc'=>"LineTotalTTC",
		//	'fd.date_start'=>"DateStart",
		//	'fd.date_end'=>"DateEnd",
		//	'fd.fk_product'=>'ProductId',
		//	'p.ref'=>'ProductRef'
		//);
		//$this->export_entities_array[$r]=array('s.rowid'=>"company",
		//	's.nom'=>'company',
		//	's.address'=>'company',
		//	's.cp'=>'company',
		//	's.ville'=>'company',
		//	's.fk_pays'=>'company',
		//	's.tel'=>'company',
		//	's.siren'=>'company',
		//	's.siret'=>'company',
		//	's.ape'=>'company',
		//	's.idprof4'=>'company',
		//	's.code_compta'=>'company',
		//	's.code_compta_fournisseur'=>'company',
		//	'f.rowid'=>"invoice",
		//	'f.facnumber'=>"invoice",
		//	'f.datec'=>"invoice",
		//	'f.datef'=>"invoice",
		//	'f.total'=>"invoice",
		//	'f.total_ttc'=>"invoice",
		//	'f.tva'=>"invoice",
		//	'f.paye'=>"invoice",
		//	'f.fk_statut'=>'invoice',
		//	'f.note'=>"invoice",
		//	'fd.rowid'=>'invoice_line',
		//	'fd.description'=>"invoice_line",
		//	'fd.price'=>"invoice_line",
		//	'fd.total_ht'=>"invoice_line",
		//	'fd.total_tva'=>"invoice_line",
		//	'fd.total_ttc'=>"invoice_line",
		//	'fd.tva_tx'=>"invoice_line",
		//	'fd.qty'=>"invoice_line",
		//	'fd.date_start'=>"invoice_line",
		//	'fd.date_end'=>"invoice_line",
		//	'fd.fk_product'=>'product',
		//	'p.ref'=>'product'
		//);
		//$this->export_sql_start[$r] = 'SELECT DISTINCT ';
		//$this->export_sql_end[$r] = ' FROM (' . MAIN_DB_PREFIX . 'facture as f, '
		//	. MAIN_DB_PREFIX . 'facturedet as fd, ' . MAIN_DB_PREFIX . 'societe as s)';
		//$this->export_sql_end[$r] .= ' LEFT JOIN ' . MAIN_DB_PREFIX
		//	. 'product as p on (fd.fk_product = p.rowid)';
		//$this->export_sql_end[$r] .= ' WHERE f.fk_soc = s.rowid '
		//	. 'AND f.rowid = fd.fk_facture';
		//$r++;

		// Can be enabled / disabled only in the main company when multi-company is in use
		// $this->core_enabled = 1;
	}

	/**
	 * Function called when module is enabled.
	 * The init function add constants, boxes, permissions and menus
	 * (defined in constructor) into Dolibarr database.
	 * It also creates data directories
	 *
	 * 	@param		string	$options	Options when enabling module ('', 'noboxes')
	 * 	@return		int					1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		$sql = array();

		$result = $this->loadTables();

		return $this->_init($sql, $options);
	}

	/**
	 * Function called when module is disabled.
	 * Remove from database constants, boxes and permissions from Dolibarr database.
	 * Data directories are not deleted
	 *
	 * 	@param		string	$options	Options when enabling module ('', 'noboxes')
	 * 	@return		int					1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();

		return $this->_remove($sql, $options);
	}

	/**
	 * Create tables, keys and data required by module
	 * Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * and create data commands must be stored in directory /vignoble/sql/
	 * This function is called by this->init
	 *
	 * 	@return		int		<=0 if KO, >0 if OK
	 */
	private function loadTables()
	{
		return $this->_load_tables('/vignoble/sql/');
	}
}
