<?php
/*
 * Vignoble Module class
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
 * \defgroup vignoble Vignoble module
 * \brief Vignoble module descriptor.
 * \file core/modules/modVignoble.class.php
 * \ingroup vignoble
 */
include_once DOL_DOCUMENT_ROOT . "/core/modules/DolibarrModules.class.php";

/**
 * Description and activation class for module Vignoble
 */
class modVignoble extends DolibarrModules
{

	/**
	 * Constructor.
	 * Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db
	 *        	Database handler
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
		$this->family = "products";
		// Module label (no space allowed)
		// used if translation string 'ModuleXXXName' not found
		// (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		// Module description
		// used if translation string 'ModuleXXXDesc' not found
		// (where XXX is value of numeric property 'numero' of module)
		$this->description = "Vineyard Management";
		// Possible values for version are: 'development', 'experimental' or version
		$this->version = '0.1';
		// Key used in llx_const table to save module status enabled/disabled
		// (where vignoble is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png
		// use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png
		// use this->picto='pictovalue@module'
		$this->picto = 'vignoble@vignoble'; // mypicto@vignoble
		
		$this->getParts();
		
		$this->getDataDirectories();
		
		$this->getConfigPages();
		
		$this->getDependencies();
		
		$this->getLanguageFiles();
		
		$this->getConstant();
		
		$this->getTabs();
		
		// Dictionaries
		if (! isset($conf->vignoble->enabled)) {
			$conf->vignoble = new stdClass();
			$conf->vignoble->enabled = 0;
		}
		$this->getDictionaries($conf);
		
		$this->getBoxes();
		
		$this->getPermissions();
		
		$this->getMenuEntries();
		
		$this->getExports();
	}
	/**
	 * get custom data directories in documentfolders
	 * directories are created when module is enabled.
	 * Example: this->dirs = array("/module/temp");
	 */
	 private function getDataDirectories()
	{
		$this->dirs = array();
	}

	/**
	 * get configuration pages url stored in config_page_url array
	 * each entry as "<page file name>@<module name>"
	 */
	 private function getConfigPages()
	{
		$this->config_page_url = array(
			"admin_vignoble.php@vignoble"
		);
	}


	/**
	 * get dependencies for the module
	 */
	private function getDependencies()
	{
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
		$this->phpmin = array(
			5,
			3
		);
		// Minimum version of Dolibarr required by module
		$this->need_dolibarr_version = array(
			4,
			0
		);
	}

	/**
	 * get module langage files url stored in langfiles
	 * 
	 * @todo not available in skeleton is this obsolete or a bug in skeleton ?
	 */
	private function getLanguageFiles()
	{
		$this->langfiles = array(
			"vignoble@vignoble"
		);
	}

	/**
	 * get module tabs stored in tabs array
	 * this feature allow to add a tab to an existing objet for the module
	 * // Example: $this->tabs = array('objecttype:+tabname1:Title1:mylangfile@mymodule:$user->rights->mymodule->read:/mymodule/mynewtab1.php?id=__ID__', // To add a new tab identified by code tabname1
	 * // 'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@mymodule:$user->rights->othermodule->read:/mymodule/mynewtab2.php?id=__ID__', // To add another new tab identified by code tabname2.
	 * Label will be result of calling all substitution functions on 'Title2' key.
	 * // 'objecttype:-tabname:NU:conditiontoremove'); // To remove an existing tab identified by code tabname
	 * // where objecttype can be
	 * // 'categories_x' to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
	 * // 'contact' to add a tab in contact view
	 * // 'contract' to add a tab in contract view
	 * // 'group' to add a tab in group view
	 * // 'intervention' to add a tab in intervention view
	 * // 'invoice' to add a tab in customer invoice view
	 * // 'invoice_supplier' to add a tab in supplier invoice view
	 * // 'member' to add a tab in fundation member view
	 * // 'opensurveypoll' to add a tab in opensurvey poll view
	 * // 'order' to add a tab in customer order view
	 * // 'order_supplier' to add a tab in supplier order view
	 * // 'payment' to add a tab in payment view
	 * // 'payment_supplier' to add a tab in supplier payment view
	 * // 'product' to add a tab in product view
	 * // 'propal' to add a tab in propal view
	 * // 'project' to add a tab in project view
	 * // 'stock' to add a tab in stock view
	 * // 'thirdparty' to add a tab in third party view
	 * // 'user' to add a tab in user view
	 */
	private function getTabs()
	{
		$this->tabs = array();
	}

	/**
	 * get module permissions stored in Rights array
	 *
	 * // permission is defined by an id, a label, a boolean and two constant strings.
	 * // Example:
	 * // $this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
	 * // $this->rights[$r][1] = 'Permision label'; // Permission label
	 * // $this->rights[$r][3] = 1; // Permission by default for new user (0/1)
	 * // $this->rights[$r][4] = 'level1'; // In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
	 * // $this->rights[$r][5] = 'level2'; // In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
	 * // $r++;
	 */
	private function getPermissions()
	{
		$this->rights = array();
		$r = 0;
		$this->rights[$r][0] = $this->numero + $r;
		$this->rights[$r][1] = 'Delete plot';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'plot';
		$this->rights[$r][5] = 'delete';
	}

	/**
	 * get module menu entries stored in menu array
	 * Each entry is a key, value array.
	 * - type => ['top' for top menu | 'left' for left menu]
	 * - mainmenu => 'main menu id'
	 * - leftmenu => 'left menu id'
	 * - fkmenu => [ '0' for top | main menu id for left using 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy']
	 * - titre => 'Menu title'
	 * - langs => 'language file for title i.e <module@file>'
	 * - position => integer used to order left menu
	 * - url => 'URL to open'
	 * - target => 'HTML target' use '' to stay on same window
	 * - user => [0 internal users only | 1 external users only | 2 both users type ]
	 * - enabled => Use $conf-><modulename>->enabled if entry must be visible only if module is enabled
	 * - perms => Use 'perms'=>'$user->rights-><modulename>->level1->level2 if you want your menu with a permission rules
	 */
	private function getMenuEntries()
	{
		$this->menu[] = array(
			'type' => 'top',
			'mainmenu' => 'vignoble',
			'leftmenu' => 'vignoble',
			'fk_menu' => 0,
			'titre' => 'Module123001Name',
			'langs' => 'vignoble@vignoble',
			'position' => 123,
			'url' => '/vignoble/plot_list.php',
			'target' => '',
			'user' => 2,
			'enabled' => '$conf->vignoble->enabled',
			'perms' => '1'
		);
		// Left menu
		$this->menu[] = array(
			'type' => 'left',
			'mainmenu' => 'vignoble',
			'leftmenu' => 'plots',
			'fk_menu' => 'fk_mainmenu=vignoble',
			'titre' => 'Plots',
			'langs' => 'vignoble@vignoble',
			'position' => 1,
			'url' => '/vignoble/plot_list.php',
			'target' => '',
			'user' => 2,
			'enabled' => '$conf->vignoble->enabled',
			'perms' => '1'
		);
		// Left Menu sub-level
		$this->menu[] = array(
			'type' => 'left',
			'mainmenu' => 'vignoble',
			'leftmenu' => 'plot_create',
			'fk_menu' => 'fk_mainmenu=vignoble,fk_leftmenu=plots',
			'titre' => 'New Plot',
			'langs' => 'vignoble@vignoble',
			'position' => 20,
			'url' => '/vignoble/plot_card.php?action=create',
			'target' => '',
			'user' => 2,
			'enabled' => '$conf->vignoble->enabled',
			'perms' => '1'
		);
		// Left Menu sub-level
		$this->menu[] = array(
			'type' => 'left',
			'mainmenu' => 'vignoble',
			'leftmenu' => 'plot_list',
			'fk_menu' => 'fk_mainmenu=vignoble,fk_leftmenu=plots',
			'titre' => 'Plots List',
			'langs' => 'vignoble@vignoble',
			'url' => '/vignoble/plot_list.php',
			'target' => '',
			'position' => 10,
			'user' => 2,
			'enabled' => '$conf->vignoble->enabled',
			'perms' => '1'
		);
	}

	/**
	 * get exports available for the module
	 *
	 * @todo check example in Skeleton modMyModuleClass.php
	 */
	private function getExports()
	{
		// Exports
		$r = 0;
		
		// Example:
		// @TODO check example in Skeleton modMyModuleClass.php
		// $this->export_code[$r]=$this->rights_class.'_'.$r;
		// // Translation key (used only if key ExportDataset_xxx_z not found)
		// $this->export_label[$r]='CustomersInvoicesAndInvoiceLines';
		// // Condition to show export in list (ie: '$user->id==3').
		// // Set to 1 to always show when module is enabled.
		// $this->export_enabled[$r]='1';
		// $this->export_permission[$r]=array(array("vignoble","level1","level2"));
		// list of fields from table and label
		// $this->export_fields_array[$r]=array(
		// 's.rowid'=>"IdCompany", // use Alias for each table.
		// 's.nom'=>'CompanyName',
		// 's.address'=>'Address',
		// 's.cp'=>'Zip',
		// 's.ville'=>'Town',
		// 's.fk_pays'=>'Country',
		// 's.tel'=>'Phone',
		// 's.siren'=>'ProfId1',
		// 's.siret'=>'ProfId2',
		// 's.ape'=>'ProfId3',
		// 's.idprof4'=>'ProfId4',
		// 's.code_compta'=>'CustomerAccountancyCode',
		// 's.code_compta_fournisseur'=>'SupplierAccountancyCode',
		// 'f.rowid'=>"InvoiceId",
		// 'f.facnumber'=>"InvoiceRef",
		// 'f.datec'=>"InvoiceDateCreation",
		// 'f.datef'=>"DateInvoice",
		// 'f.total'=>"TotalHT",
		// 'f.total_ttc'=>"TotalTTC",
		// 'f.tva'=>"TotalVAT",
		// 'f.paye'=>"InvoicePaid",
		// 'f.fk_statut'=>'InvoiceStatus',
		// 'f.note'=>"InvoiceNote",
		// 'fd.rowid'=>'LineId',
		// 'fd.description'=>"LineDescription",
		// 'fd.price'=>"LineUnitPrice",
		// 'fd.tva_tx'=>"LineVATRate",
		// 'fd.qty'=>"LineQty",
		// 'fd.total_ht'=>"LineTotalHT",
		// 'fd.total_tva'=>"LineTotalTVA",
		// 'fd.total_ttc'=>"LineTotalTTC",
		// 'fd.date_start'=>"DateStart",
		// 'fd.date_end'=>"DateEnd",
		// 'fd.fk_product'=>'ProductId',
		// 'p.ref'=>'ProductRef'
		// );
		// List of fields icons (refer to the object)
		// $this->export_entities_array[$r]=array('s.rowid'=>"company",
		// 's.nom'=>'company',
		// 's.address'=>'company',
		// 's.cp'=>'company',
		// 's.ville'=>'company',
		// 's.fk_pays'=>'company',
		// 's.tel'=>'company',
		// 's.siren'=>'company',
		// 's.siret'=>'company',
		// 's.ape'=>'company',
		// 's.idprof4'=>'company',
		// 's.code_compta'=>'company',
		// 's.code_compta_fournisseur'=>'company',
		// 'f.rowid'=>"invoice",
		// 'f.facnumber'=>"invoice",
		// 'f.datec'=>"invoice",
		// 'f.datef'=>"invoice",
		// 'f.total'=>"invoice",
		// 'f.total_ttc'=>"invoice",
		// 'f.tva'=>"invoice",
		// 'f.paye'=>"invoice",
		// 'f.fk_statut'=>'invoice',
		// 'f.note'=>"invoice",
		// 'fd.rowid'=>'invoice_line',
		// 'fd.description'=>"invoice_line",
		// 'fd.price'=>"invoice_line",
		// 'fd.total_ht'=>"invoice_line",
		// 'fd.total_tva'=>"invoice_line",
		// 'fd.total_ttc'=>"invoice_line",
		// 'fd.tva_tx'=>"invoice_line",
		// 'fd.qty'=>"invoice_line",
		// 'fd.date_start'=>"invoice_line",
		// 'fd.date_end'=>"invoice_line",
		// 'fd.fk_product'=>'product',
		// 'p.ref'=>'product'
		// );
		// SQL request to get the lines
		// $this->export_sql_start[$r] = 'SELECT DISTINCT ';
		// $this->export_sql_end[$r] = ' FROM (' . MAIN_DB_PREFIX . 'facture as f, '
		// . MAIN_DB_PREFIX . 'facturedet as fd, ' . MAIN_DB_PREFIX . 'societe as s)';
		// $this->export_sql_end[$r] .= ' LEFT JOIN ' . MAIN_DB_PREFIX
		// . 'product as p on (fd.fk_product = p.rowid)';
		// $this->export_sql_end[$r] .= ' WHERE f.fk_soc = s.rowid '
		// . 'AND f.rowid = fd.fk_facture';
		// $this->export_sql_order[$r] .= ' ORDER BY fd.fk_product '
		// . 'AND f.rowid = fd.fk_facture';
		// $r++;
		
		// Can be enabled / disabled only in the main company when multi-company is in use
		// $this->core_enabled = 1;
	}

	/**
	 * Get list of particular constants to add when module is enabled
	 * Each constant is defined by an array with
	 * - name
	 * - type (obsolete use ?)
	 * - value
	 * - description
	 * - visibility [0 not displayed in Dolibarr configuration, 1 displayed]
	 * - entity ['current' or 'allentities']
	 * - delete [0 do not delete when module unactivated, 1 deleted]
	 */
	private function getConstant()
	{
		$this->const = array();
		$this->const[0] = array(
			strtoupper($this->name) . '_CONSTANT',
			'?',
			'Not used yet - set as example',
			'This is a constant for module ' . $this->name,
			1,
			'current',
			0
		);
	}

	/**
	 * Get all module parts (triggers, login, substitutions, menus, css, etc...)
	 */
	private function getParts()
	{
		$this->module_parts = array(
			// Set this to 1 if module has its own trigger directory
			'triggers' => 0,
			// Set this to 1 if module has its own login method directory
			'login' => 0,
			// Set this to 1 if module has its own substitution function file
			'substitutions' => 0,
			// Set this to 1 if module has its own barcode directory \TODO Check if obsolete
			'barcode' => 0,
			// Set this to 1 if module has its own PDF or ODT models directory
			// add models in /core/modules/[originmodulename]/doc/[modelfile]
			'models' => 0,
			// Set this to relative path of css if module has its own css file
			// loaded after Dolibarr CCS
			'css' => array(
				'vignoble/css/mycss.css.php'
			),
			// Set this to relative path of js file if module must load a js on all pages
			// 'js' => array('vignoble/js/vignoble.js'),
			// Set here all hooks context managed by module
			'hooks' => array(
				'searchform'
			)
		);
	}

	/**
	 * get Array describing the module boxes that could be displayed on Dolibarr Dashboard
	 */
	private function getBoxes()
	{
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
	}

	/**
	 * get Array describing the module tables using the Dolibarr dictionary
	 */
	private function getDictionaries($conf)
	{
		$this->dictionaries = array(
			
			// List of tables we want to see into dictionnary editor
			'tabname' => array(
				MAIN_DB_PREFIX . "c_cultivationtype",
				MAIN_DB_PREFIX . "c_varietal",
				MAIN_DB_PREFIX . "c_rootstock"
			),
			// Label of tables
			'tablib' => array(
				"Cultivation Type",
				"Varietal",
				"Rootstock"
			),
			// Request to select fields
			'tabsql' => array(
				'SELECT f.rowid as rowid, f.code, f.label, f.active' . ' FROM ' . MAIN_DB_PREFIX . 'c_cultivationtype as f',
				'SELECT f.rowid as rowid, f.code, f.label, f.active' . ' FROM ' . MAIN_DB_PREFIX . 'c_varietal as f',
				'SELECT f.rowid as rowid, f.code, f.label, f.active' . ' FROM ' . MAIN_DB_PREFIX . 'c_rootstock as f'
			),
			// Sort order
			'tabsqlsort' => array(
				"label ASC",
				"label ASC",
				"label ASC"
			),
			// List of fields (result of select to show dictionary)
			'tabfield' => array(
				"code,label",
				"code,label",
				"code,label"
			),
			// List of fields (list of fields to edit a record)
			'tabfieldvalue' => array(
				"code,label",
				"code,label",
				"code,label"
			),
			// List of fields (list of fields for insert)
			'tabfieldinsert' => array(
				"code,label",
				"code,label",
				"code,label"
			),
			// Name of columns with primary key (try to always name it 'rowid')
			'tabrowid' => array(
				"rowid",
				"rowid",
				"rowid"
			),
			// Condition to show each dictionary
			'tabcond' => array(
				$conf->vignoble->enabled,
				$conf->vignoble->enabled,
				$conf->vignoble->enabled
			)
		);
		// var_dump($this->dictionaries);
	}

	/**
	 * Function called when module is enabled.
	 * The init function add constants, boxes, permissions and menus
	 * (defined in constructor) into Dolibarr database.
	 * It also creates data directories
	 *
	 * @param string $options
	 *        	Options when enabling module ('', 'noboxes')
	 * @return int 1 if OK, 0 if KO
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
	 * @param string $options
	 *        	Options when enabling module ('', 'noboxes')
	 * @return int 1 if OK, 0 if KO
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
	 * @return int <=0 if KO, >0 if OK
	 */
	private function loadTables()
	{
		return $this->_load_tables('/vignoble/sql/');
	}
}
