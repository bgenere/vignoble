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
 * List of module components group for doxygen documentation
 *
 * \defgroup plot Plot management
 * 
 * Components providing vineyard plots management
 * 
 * \defgroup cultivation Cultivation management
 * 
 * Pages to manage the cultivation project and tasks
 *
 * \defgroup dashboard Dashboard
 *
 * Components providing the Vignoble dashboard
 *
 * \defgroup admin Administration
 *
 * Pages to administrate the module. Displayed from module settings.
 *
 * \defgroup component Reusable components
 *
 * Components reused in other Vignoble components
 */

/**
 * \file core/modules/modVignoble.class.php
 * \brief File contains the Vignoble module descriptor.
 *
 * The module class extends the DolibarrModule Class and define
 * module properties and components.
 *
 * File name should always be mod%ModuleName%.class.php
 */
include_once DOL_DOCUMENT_ROOT . "/core/modules/DolibarrModules.class.php";

/**
 * Description and activation class for module Vignoble
 */
class modVignoble extends DolibarrModules
{

	/**
	 * Constructor.
	 * Define names, constants, directories, boxes, permissions...
	 * all components and properties for the module
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
		/**
		 * Set up Module properties.
		 */
		/**
		 * numero : Id for module (must be unique).
		 * You should use a free id and check http://wiki.dolibarr.org/index.php/List_of_modules_id for available ranges.
		 */
		$this->numero = 123100;
		/**
		 * rights_class : Key text used to identify module (for permissions, menus, etc...)
		 */
		$this->rights_class = 'vignoble';
		/**
		 * family : used to group modules in module setup page.
		 * Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		 */
		$this->family = "products";
		/**
		 * name : module name using this class name without space
		 * used if translation string 'ModuleXXXName' not found (where XXX is value of $this->numero)
		 */
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		/**
		 * description : used if translation string 'ModuleXXXDesc' not found
		 */
		$this->description = "Vineyard Management";
		/**
		 * descriptionlong : Can be a full HTML content, not used yet
		 */
		$this->descriptionlong = "A very long description. Can be a full HTML content, not used yet";
		/**
		 * editor name and editor url
		 */
		$this->editor_name = 'Bruno Généré';
		$this->editor_url = 'http://webiseasy.org';
		/**
		 * version : module version as x.x.x
		 */
		$this->version = '0.3';
		/**
		 * const_name : module constant to save module status enabled/disabled
		 */
		$this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);
		/**
		 * picto : image file used for the module
		 */
		$this->picto = 'vignoble@vignoble';
		
		/**
		 * Call private functions to initialize complex properties
		 */
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
		
		$this->getImports();
		// var_dump($this);
	}

	/**
	 * Get custom data directories in document folder.
	 * Directories are created when module is enabled.
	 * Example: this->dirs = array("/module/temp");
	 */
	private function getDataDirectories()
	{
		$this->dirs = array();
	}

	/**
	 * Get the configuration pages url stored in config_page_url array.
	 * The entry is written as "<page file name>@<module name>" and should be stored in module/admin directory.
	 * @NOTE Only one entry is needed, others are optional and create extra links in module list.
	 */
	private function getConfigPages()
	{
		$this->config_page_url = array(
			"module_settings.php@vignoble"
		);
	}

	/**
	 * Get dependencies attributes and arrays for the module.
	 */
	private function getDependencies()
	{
		/**
		 * Set hidden with a condition to hide module
		 */
		$this->hidden = false;
		/**
		 * Set depends array with list of modules class name as string that must be enabled if this module is enabled
		 * Example : $this->depends('modAnotherModule', 'modYetAnotherModule')
		 *
		 * @todo check issue with Dolibarr when module needed is after current module in list
		 */
		$this->depends = array();
		/**
		 * Set requiredby array with list of modules id to disable if this one is disabled
		 */
		$this->requiredby = array();
		/**
		 * Set conflictwith array with list of modules id this module is in conflict with
		 */
		$this->conflictwith = array();
		/**
		 * Set phpmin as minimum version of PHP required by the module
		 */
		$this->phpmin = array(
			5,
			3
		);
		/**
		 * set need_dolibarr_version as minimum version of Dolibarr required by module
		 *
		 * @todo check issue when version contains -alpha
		 *      
		 */
		$this->need_dolibarr_version = array(
			4,
			0,
			0
		);
	}

	/**
	 * Get module langage files url stored in langfiles array.
	 *
	 * Language files are stored in /langs/<language code>/<module>.lang
	 */
	private function getLanguageFiles()
	{
		$this->langfiles = array(
			"vignoble@vignoble"
		);
	}

	/**
	 * Get module tabs stored in tabs array.
	 *
	 * Each line in the array add a module tab to an existing object.
	 * The following object type are supported
	 * - 'categories_x' to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
	 * - 'contact' to add a tab in contact view
	 * - 'contract' to add a tab in contract view
	 * - 'group' to add a tab in group view
	 * - 'intervention' to add a tab in intervention view
	 * - 'invoice' to add a tab in customer invoice view
	 * - 'invoice_supplier' to add a tab in supplier invoice view
	 * - 'member' to add a tab in fundation member view
	 * - 'opensurveypoll' to add a tab in opensurvey poll view
	 * - 'order' to add a tab in customer order view
	 * - 'order_supplier' to add a tab in supplier order view
	 * - 'payment' to add a tab in payment view
	 * - 'payment_supplier' to add a tab in supplier payment view
	 * - 'product' to add a tab in product view
	 * - 'propal' to add a tab in propal view
	 * - 'project' to add a tab in project view
	 * - 'resource' to add a tab in resource view
	 * - 'stock' to add a tab in stock view
	 * - 'thirdparty' to add a tab in third party view
	 * - 'user' to add a tab in user view
	 */
	private function getTabs()
	{
		$this->tabs = array(
			/**
			 * add plot tab to resource module
			 */
			'resource:+plot:Plot:vignoble@vignoble:1:/vignoble/admin/admin_vignoble.php?id=__ID__',
			/**
			 * remove project tabs for cultivation project
			 */
			'project:-project:NU:$conf->global->VIGNOBLE_ISCULTIVATIONPROJECT',
			'project:-contact:NU:$conf->global->VIGNOBLE_ISCULTIVATIONPROJECT',
			'project:-element:NU:$conf->global->VIGNOBLE_ISCULTIVATIONPROJECT',
			'project:-notes:NU:$conf->global->VIGNOBLE_ISCULTIVATIONPROJECT',
			'project:-document:NU:$conf->global->VIGNOBLE_ISCULTIVATIONPROJECT',
			'project:-agenda:NU:$conf->global->VIGNOBLE_ISCULTIVATIONPROJECT',
			'project:-tasks:NU:$conf->global->VIGNOBLE_ISCULTIVATIONPROJECT',
			'project:+cultivationtasks:Tasks:project@projet:$conf->global->VIGNOBLE_ISCULTIVATIONPROJECT:/vignoble/cultivationtasks.php',
			'project:-gantt:NU:$conf->global->VIGNOBLE_ISCULTIVATIONPROJECT',
			'task:-task_task:NU:$conf->global->VIGNOBLE_ISCULTIVATIONPROJECT',
			'task:-task_contact:NU:$conf->global->VIGNOBLE_ISCULTIVATIONPROJECT',
			'task:-task_time:NU:$conf->global->VIGNOBLE_ISCULTIVATIONPROJECT',
			'task:-task_notes:NU:$conf->global->VIGNOBLE_ISCULTIVATIONPROJECT',
			'task:-task_document:NU:$conf->global->VIGNOBLE_ISCULTIVATIONPROJECT',
			'task:+cultivationtask:Task:project@projet:$conf->global->VIGNOBLE_ISCULTIVATIONPROJECT:/vignoble/cultivationtask.php?id=__ID__&withproject=1',
			'task:+cultivationtaskplot:Plots:project@projet:$conf->global->VIGNOBLE_ISCULTIVATIONPROJECT:/vignoble/cultivationtaskplot.php?id=__ID__&withproject=1',
			'task:+cultivationtaskcontact:Contacts:project@projet:$conf->global->VIGNOBLE_ISCULTIVATIONPROJECT:/vignoble/cultivationtaskcontact.php?id=__ID__&withproject=1',
			'task:+cultivationtasktime:Time:project@projet:$conf->global->VIGNOBLE_ISCULTIVATIONPROJECT:/vignoble/cultivationtasktime.php?id=__ID__&withproject=1',
			
		);
		// String Examples :
		// To add a tab identified by code tabname
		// 'objecttype:+tabname:tabTitle:mylangfile@mymodule:$user->rights->mymodule->read:/mymodule/mytab.php?id=__ID__'
		// To add a tab with substitution
		// 'objecttype:+tabname:SUBSTITUTION_Title2:mylangfile@mymodule:$user->rights->othermodule->read:/mymodule/mytab.php?id=__ID__'
		// To remove an existing tab identified by code tabname
		// 'objecttype:-tabname:NU:conditiontoremove')
	}

	/**
	 * Get module permissions stored in rights array.
	 *
	 * Each row is a permission defined by an id, a label, a boolean and two constant strings.
	 */
	private function getPermissions()
	{
		$this->rights = array();
		// Example:
		// $this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		// $this->rights[$r][1] = 'Permision label'; // Permission label
		// $this->rights[$r][3] = 1; // Permission by default for new user (0/1)
		// $this->rights[$r][4] = 'level1'; // In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		// $this->rights[$r][5] = 'level2'; // In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		/**
		 * Set read, create/modify, delete, export and import permissions for Plot
		 */
		$r = 0;
		$this->rights[$r][0] = $this->numero + $r;
		$this->rights[$r][1] = 'Read plot';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'plot';
		$this->rights[$r][5] = 'read';
		$r ++;
		$this->rights[$r][0] = $this->numero + $r;
		$this->rights[$r][1] = 'Create/modify plot';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'plot';
		$this->rights[$r][5] = 'create';
		$r ++;
		$this->rights[$r][0] = $this->numero + $r;
		$this->rights[$r][1] = 'Delete plot';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'plot';
		$this->rights[$r][5] = 'delete';
		$r ++;
		$this->rights[$r][0] = $this->numero + $r;
		$this->rights[$r][1] = 'Export plot';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'plot';
		$this->rights[$r][5] = 'export';
		$r ++;
		$this->rights[$r][0] = $this->numero + $r;
		$this->rights[$r][1] = 'Import plot';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'plot';
		$this->rights[$r][5] = 'import';
	}

	/**
	 * Get module menu entries stored in a menu array.
	 *
	 * Each entry is a key, value array in the menu array.
	 *
	 * @todo add permission rules for menu entries
	 */
	private function getMenuEntries()
	{
		/*
		 * Example !
		 * $this->menu[] = array(
		 * type => ['top' for top menu | 'left' for left menu]
		 * mainmenu => 'main menu id'
		 * leftmenu => 'left menu id'
		 * fkmenu => [ '0' for top | main menu id for left using 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy']
		 * titre => 'Menu title'
		 * langs => 'language file for title i.e <module@file>'
		 * position => integer used to order left menu
		 * url => 'URL to open'
		 * target => 'HTML target' use '' to stay on same window
		 * user => [0 internal users only | 1 external users only | 2 both users type ]
		 * enabled => Use $conf-><modulename>->enabled if entry must be visible only if module is enabled
		 * perms => Use 'perms'=>'$user->rights-><modulename>->level1->level2 if you want your menu with a permission rules
		 * );
		 */
		/**
		 * Define Vignoble top menu entry
		 */
		$this->menu[] = array(
			'type' => 'top',
			'mainmenu' => 'vignoble',
			'leftmenu' => 'vignoble',
			'fk_menu' => 0,
			'titre' => 'Module123100Name',
			'langs' => 'vignoble@vignoble',
			'position' => 123,
			'url' => '/vignoble/index.php',
			'target' => '',
			'user' => 2,
			'enabled' => '$conf->vignoble->enabled',
			'perms' => '$user->rights->vignoble->plot->read'
		);
		/**
		 * - Define plot left menu
		 */
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
			'perms' => '$user->rights->vignoble->plot->read'
		);
		/**
		 * 	- Define plot left menu entry New plot
		 */
		$this->menu[] = array(
			'type' => 'left',
			'mainmenu' => 'vignoble',
			'leftmenu' => 'plot_create',
			'fk_menu' => 'fk_mainmenu=vignoble,fk_leftmenu=plots',
			'titre' => 'NewPlot',
			'langs' => 'vignoble@vignoble',
			'position' => 20,
			'url' => '/vignoble/plot_card.php?action=create',
			'target' => '',
			'user' => 2,
			'enabled' => '$conf->vignoble->enabled',
			'perms' => '$user->rights->vignoble->plot->create'
		);
		/**
		 * 	- Define plot left menu entry Plot List
		 */
		$this->menu[] = array(
			'type' => 'left',
			'mainmenu' => 'vignoble',
			'leftmenu' => 'plot_list',
			'fk_menu' => 'fk_mainmenu=vignoble,fk_leftmenu=plots',
			'titre' => 'List',
			'langs' => 'vignoble@vignoble',
			'url' => '/vignoble/plot_list.php',
			'target' => '',
			'position' => 10,
			'user' => 2,
			'enabled' => '$conf->vignoble->enabled',
			'perms' => '$user->rights->vignoble->plot->read'
		);
		/**
		 * - Define cultivation left menu
		 */
		$this->menu[] = array(
			'type' => 'left',
			'mainmenu' => 'vignoble',
			'leftmenu' => 'cultivation',
			'fk_menu' => 'fk_mainmenu=vignoble',
			'titre' => 'Cultivation',
			'langs' => 'vignoble@vignoble',
			'position' => 2,
			'url' => '/vignoble/cultivationtasks.php',
			'target' => '',
			'user' => 2,
			'enabled' => '$conf->vignoble->enabled',
			'perms' => '$user->rights->vignoble->plot->read'
		);
		/**
		 *  - Define cultivation left menu entry new cultivation task
		 */
		$this->menu[] = array(
			'type' => 'left',
			'mainmenu' => 'vignoble',
			'leftmenu' => 'cultivationTask_create',
			'fk_menu' => 'fk_mainmenu=vignoble,fk_leftmenu=cultivation',
			'titre' => 'NewTask',
			'langs' => 'vignoble@vignoble',
			'position' => 10,
			'url' => '/vignoble/cultivationtasks.php?action=create',
			'target' => '',
			'user' => 2,
			'enabled' => '$conf->vignoble->enabled',
			'perms' => '$user->rights->vignoble->plot->read'
		);
		/**
		 *  - Define plot left menu entry Orders and Shipment
		 */
		$this->menu[] = array(
			'type' => 'left',
			'mainmenu' => 'vignoble',
			'leftmenu' => 'Orders&Shipments',
			'fk_menu' => 'fk_mainmenu=vignoble',
			'titre' => 'Orders&Shipments',
			'langs' => 'vignoble@vignoble',
			'url' => '/vignoble/ordersandshipments.php',
			'target' => '',
			'position' => 3,
			'user' => 2,
			'enabled' => '$conf->vignoble->enabled',
			'perms' => '$user->rights->produit->lire'
		);
	}

	/**
	 * Get exports available for the module.
	 *
	 * Exports definition are stored in multiple arrays. Each export is located by its main index
	 *
	 * @todo check example in Skeleton modMyModuleClass.php and see how to implement custom attributes
	 */
	private function getExports()
	{
		Global $db;
		$r = 0;
		/**
		 * Define Plot export
		 */
		$this->export_code[$r] = $this->rights_class . '_' . $r; // export sequential number
		$this->export_label[$r] = 'Plots'; // Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_enabled[$r] = '1'; // Condition to show export in list (ie: '$user->id==3'). Set to 1 to always show when module is enabled.
		$this->export_icon[$r] = 'plot14@vignoble'; // Put here code of icon
		$this->export_permission[$r] = array(
			array(
				"vignoble",
				"plot",
				"export"
			)
		);
		/**
		 * Get extrafields
		 */
		$extrafields = new ExtraFields($db);
		$extrafieldslabels = $extrafields->fetch_name_optionals_label('plot');
		/**
		 * Define export fields
		 */
		$this->export_fields_array[$r] = array(
			'p.ref' => 'Ref',
			'p.label' => 'Label',
			'p.description' => 'Description',
			'p.note_private' => "Private Note",
			'p.note_public' => "Public Note"
		);
		// Add extra fields
		if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) {
			foreach ($extrafields->attribute_label as $key => $val) {
				$fieldname = 'extra.' . $key;
				$fieldlabel = ucfirst($extrafields->attribute_label[$key]);
				$this->export_fields_array[$r][$fieldname] = $fieldlabel;
			}
		}
		/**
		 * Define export type for fields
		 * supported format are Date, Text, Boolean or Numeric
		 */
		$this->export_TypeFields_array[$r] = array(
			'p.ref' => 'Text',
			'p.label' => 'Text',
			'p.description' => 'Text',
			'p.note_private' => "Text",
			'p.note_public' => "Text"
		);
		$type2export = array(
			'varchar' => 'Text',
			'text' => 'Text',
			'int' => 'Numeric',
			'double' => 'Numeric',
			'date' => 'Date',
			'datetime' => 'Date',
			'boolean' => 'Boolean',
			'price' => 'Numeric',
			'phone' => 'Text',
			'mail' => 'Text',
			'url' => 'Text',
			'select' => 'Text',
			'sellist' => 'Text',
			'radio' => 'Boolean',
			'checkbox' => 'Text',
			'chkbxlst' => 'Text',
			'link' => 'Text',
			'password' => 'Text',
			'separate' => 'Text'
		);
		// Add extra fields
		if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) {
			foreach ($extrafields->attribute_type as $key => $val) {
				$fieldname = 'extra.' . $key;
				$fieldtype = $type2export[$val];
				$this->export_TypeFields_array[$r][$fieldname] = $fieldtype;
			}
		}
		// var_dump($this->export_TypeFields_array);
		/**
		 * Define export entities for fields
		 * (all from Plots)
		 */
		$this->export_entities_array[$r] = array(
			'p.ref' => 'Plot',
			'p.label' => 'Plot',
			'p.description' => 'Plot',
			'p.note_private' => 'Plot',
			'p.note_public' => 'Plot'
		);
		// Add extra fields
		if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) {
			foreach ($extrafields->attribute_label as $key => $val) {
				$fieldname = 'extra.' . $key;
				$this->export_entities_array[$r][$fieldname] = 'Plot';
			}
		}
		/**
		 * To add unique key if we ask a field of a child to avoid the DISTINCT to discard them
		 */
		// $this->export_dependencies_array[$r]=array('invoice_line'=>'fd.rowid','product'=>'fd.rowid');
		/**
		 * sql request to extract the data
		 */
		$this->export_sql_start[$r] = 'SELECT DISTINCT ';
		$this->export_sql_end[$r] = ' FROM (' . MAIN_DB_PREFIX . 'plot as p, ' . MAIN_DB_PREFIX . 'plot_extrafields as extra)';
		$this->export_sql_end[$r] .= ' WHERE p.rowid = extra.fk_object ';
		$this->export_sql_order[$r] .= ' ORDER BY 1';
		$r ++;
	}

	/**
	 * Get list of custom constants from const array.
	 *
	 * Constant are added when module is enabled
	 */
	private function getConstant()
	{
		$this->const = array();
		// Example to set a constant
		// $this->const[] = array(
		// strtoupper($this->name) . '_CONSTANT', // set constant name
		// '?', // type (obsolete use ?)
		// 'Not used yet - set as example', // value
		// 'This is a constant for module ' . $this->name, // description
		// 1, // visibility [0 not displayed in Dolibarr configuration, 1 displayed]
		// 'current', // entity ['current' or 'allentities']
		// 0 // [0 do not delete when module unactivated, 1 deleted]
		// );
	}

	/**
	 * Get module parts array.
	 * Part are custom components overriding Dolibarr defaults
	 */
	private function getParts()
	{
		$this->module_parts = array(
			/**
			 * Set triggers to 1 if module has its own trigger directory
			 */
			'triggers' => 0,
			/**
			 * Set login to 1 if module has its own login method directory
			 */
			'login' => 0,
			/**
			 * Set substitutions to 1 if module has its own substitution function file
			 */
			'substitutions' => 0,
			/**
			 * Set barcode to 1 if module has its own barcode directory
			 * \TODO Check if obsolete
			 */
			'barcode' => 0,
			/**
			 * Set models this to 1 if module has its own PDF or ODT models directory
			 * add models in /core/modules/[originmodulename]/doc/[modelfile]
			 */
			'models' => 1,
			/**
			 * Set css array to relative path of css if module has its own css file loaded after Dolibarr CCS
			 */
			'css' => array(
				'vignoble/css/mycss.css.php'
			),
			/**
			 * Set js array to relative path of js files if module must load js on all pages
			 */
			// 'js' => array('vignoble/js/vignoble.js'),
			/**
			 * Set hooks array by adding all hooks context managed by module
			 */
			'hooks' => array(
				'searchform' /* hook context in Dolibarr/htdocs/core/ajax/selectsearchbox.php */
			)
		);
	}

	/**
	 * Get module boxes array.
	 *
	 * Module boxes or widgets could be displayed on Dolibarr Dashboard
	 *	- file contains the box class file name
	 *	- note displays in widget set-up page
	 *	- enablebydefaulton to have widget on home page
	 * 
	 */
	private function getBoxes()
	{
		$this->boxes = array(
			0 => array(
				'file' => 'plotslastchanged.php@vignoble',
				'note' => '5 last plots changed',
				'enabledbydefaulton' => 'home'
			),
			0 => array(
				'file' => 'vignoblebox.php@vignoble',
				'note' => 'Links to module informations',
				'enabledbydefaulton' => ''
			)
		);
	}

	/**
	 * Get module tables using the Dolibarr dictionary
	 *
	 * List of tables and properties are stored in dictionnaries array.
	 */
	private function getDictionaries($conf)
	{
		$this->dictionaries = array(
			
			/**
			 * Define the following dictionnaries
			 * - Cultivation Type
			 * - Varietal
			 * - Rootstock
			 */
			// List of tables we want to see into dictionnary editor
			'tabname' => array(
				MAIN_DB_PREFIX . "c_cultivationtype",
				MAIN_DB_PREFIX . "c_varietal",
				MAIN_DB_PREFIX . "c_rootstock"
			),
			// Label of tables
			'tablib' => array(
				"CultivationType",
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
			// List of fields (result of select to read dictionary)
			'tabfield' => array(
				"code,label",
				"code,label",
				"code,label"
			),
			// List of fields to edit a record
			'tabfieldvalue' => array(
				"code,label",
				"code,label",
				"code,label"
			),
			// List of fields for insert
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
		
		setEventMessage('Vignoble module activated');
		
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
		
		setEventMessage('Vignoble module unactivated');
		
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

	/**
	 * Get imports available for the module.
	 *
	 * Imports definition are stored in multiple arrays. Each import is located by its main index.
	 */
	private function getImports()
	{
		global $db;
		/**
		 * Define Plots Import
		 */
		$r = 'plot';
		$this->import_code[$r] = $this->rights_class . '_' . $r;
		$this->import_label[$r] = 'Plots';
		$this->import_icon[$r] = 'plot14@vignoble';
		/**
		 * - Get Plot extrafields
		 */
		$extrafields = new ExtraFields($db);
		$extrafieldslabels = $extrafields->fetch_name_optionals_label('plot');
		/**
		 * - Define imported entities for each field (needed to fix Dolibarr issue) 
		 */
		$this->import_entities_array[$r] = array(
			's.ref' => $r,
			's.label' => $r,
			's.description' => $r,
			's.note_private' => $r,
			's.note_public' => $r
		);
		// Add extra fields
		if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) {
			foreach ($extrafields->attribute_label as $key => $val) {
				$fieldname = 'extra.' . $key;
				$this->import_entities_array[$r][$fieldname] = $r;
			}
		}
		;
		/**
		 * - Define List of tables to insert into (insert done in same order)
		 */
		$this->import_tables_array[$r] = array(
			's' => MAIN_DB_PREFIX . $r
		);
		// Add extra fields table
		if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) {
			$this->import_tables_array[$r]['extra'] = MAIN_DB_PREFIX . $r . '_extrafields';
		}
		/**
		 * - Define List of fields updated by import
		 */
		$this->import_fields_array[$r] = array(
			's.ref' => "Ref*",
			's.label' => "Label",
			's.description' => "Description",
			's.note_private' => "Private Note",
			's.note_public' => "Public Note"
		);
		// Add extra fields
		if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) {
			foreach ($extrafields->attribute_label as $key => $val) {
				$fieldname = 'extra.' . $key;
				$fieldlabel = ucfirst($extrafields->attribute_label[$key]);
				$this->import_fields_array[$r][$fieldname] = $fieldlabel . ($extrafields->attribute_required[$key] ? '*' : '');
			}
		}
		/**
		 * - define rules to populate user and key fields
		 * - use aliastable.field =>'user->id' to get current user
		 * - use aliastable.field => 'lastrowid-'.tableparent to get parent row id
		 * .
		 */
		$this->import_fieldshidden_array[$r] = array(
			's.fk_user_author' => 'user->id',
			's.fk_user_modif' => 'user->id',
			'extra.fk_object' => 'lastrowid-' . MAIN_DB_PREFIX . $r
		);
		
		// foreign key management rule to get id from a label cf core/module/import/import*.php files
		$this->import_convertvalue_array[$r] = array();
		// 's.fk_typent' => array(
		// 'rule' => 'fetchidfromcodeorlabel',
		// 'classfile' => '/core/class/ctypent.class.php',
		// 'class' => 'Ctypent',
		// 'method' => 'fetch',
		// 'dict' => 'DictionaryCompanyType'
		// ),
		// 's.fk_departement' => array(
		// 'rule' => 'fetchidfromcodeid',
		// 'classfile' => '/core/class/cstate.class.php',
		// 'class' => 'Cstate',
		// 'method' => 'fetch',
		// 'dict' => 'DictionaryState'
		// ),
		// 's.fk_pays' => array(
		// 'rule' => 'fetchidfromcodeid',
		// 'classfile' => '/core/class/ccountry.class.php',
		// 'class' => 'Ccountry',
		// 'method' => 'fetch',
		// 'dict' => 'DictionaryCountry'
		// ),
		// 's.fk_stcomm' => array(
		// 'rule' => 'zeroifnull'
		// ),
		// 's.code_client' => array(
		// 'rule' => 'getcustomercodeifauto'
		// ),
		// 's.code_fournisseur' => array(
		// 'rule' => 'getsuppliercodeifauto'
		// ),
		// 's.code_compta' => array(
		// 'rule' => 'getcustomeraccountancycodeifauto'
		// ),
		// 's.code_compta_fournisseur' => array(
		// 'rule' => 'getsupplieraccountancycodeifauto'
		// )
		
		/**
		 * - Populate validation rules using regex
		 */
		$this->import_regex_array[$r] = array();
		if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) {
			foreach ($extrafields->attribute_label as $key => $val) {
				$fieldname = 'extra.' . $key;
				$fieldtype = $extrafields->attribute_type[$key];
				if (in_array($fieldtype, array(
					'select'
				))) {
					$out = '^';
					foreach ($extrafields->attribute_param[$key]['options'] as $optkey => $optval) {
						$out .= $optkey . '|';
					}
					$out = rtrim($out, '|');
					$out .= '$';
					$this->import_regex_array[$r][$fieldname] = $out;
				} elseif (in_array($fieldtype, array(
					'sellist'
				))) {
					$out = '^';
					foreach ($extrafields->attribute_param[$key]['options'] as $optkey => $optval) {
						$dict = explode(':', $optkey);
						$sql = 'SELECT ' . $dict[2] . ' FROM ' . MAIN_DB_PREFIX . $dict[0] . ' WHERE ' . $dict[4] . ' ORDER BY 1';
						$resql = $this->db->query($sql);
						if ($resql) {
							while ($obj = $this->db->fetch_object($resql)) {
								foreach ($obj as $key => $value) {
									$out .= $value . '|';
								}
							}
							$this->db->free($resql);
						} else {
							$this->errors[] = 'Error ' . $this->db->lasterror();
							dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
						}
					}
					$out = rtrim($out, '|');
					$out .= '$';
					$this->import_regex_array[$r][$fieldname] = $out;
				}
			}
		}
		// 's.status' => '^[0|1]',
		// 's.client' => '^[0|1|2|3]',
		// 's.fk_typent' => 'id@' . MAIN_DB_PREFIX . 'c_typent',
		// 's.datec' => '^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]( [0-9][0-9]:[0-9][0-9]:[0-9][0-9])?$'
		// 'fieldname' => '^codea|codeb$'
		/**
		 * - Populate values for the example file
		 */
		$this->import_examplevalues_array[$r] = array(
			's.ref' => '"' . ucfirst($r) . ' ref %%%"',
			's.label' => '"Label for ' . $r . ' %%%"',
			's.description' => '"Description for ' . $r . ' %%%"',
			's.note_private' => '"Private Note for ' . $r . ' %%%"',
			's.note_public' => '"Public Note for  ' . $r . ' %%%"'
		);
		if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) {
			foreach ($extrafields->attribute_label as $key => $val) {
				$fieldname = 'extra.' . $key;
				$fieldlabel = ucfirst($extrafields->attribute_label[$key]);
				$fieldtype = $extrafields->attribute_type[$key];
				if (in_array($fieldtype, array(
					'varchar',
					'text'
				)))
					$this->import_examplevalues_array[$r][$fieldname] = '"' . $fieldlabel . ' for ' . $r . ' %%% "';
				elseif (in_array($fieldtype, array(
					'int',
					'float',
					'double',
					'price'
				)))
					$this->import_examplevalues_array[$r][$fieldname] = 0;
				elseif (in_array($fieldtype, array(
					'select'
				))) {
					$out = '[';
					foreach ($extrafields->attribute_param[$key]['options'] as $optkey => $optval) {
						$out .= $optkey . '|';
					}
					$out = rtrim($out, '|');
					$out .= ']';
					$this->import_examplevalues_array[$r][$fieldname] = $out;
				} elseif (in_array($fieldtype, array(
					'sellist'
				))) {
					$out = '[';
					foreach ($extrafields->attribute_param[$key]['options'] as $optkey => $optval) {
						$dict = explode(':', $optkey);
						$sql = 'SELECT ' . $dict[2] . ' FROM ' . MAIN_DB_PREFIX . $dict[0] . ' WHERE ' . $dict[4] . ' ORDER BY 1';
						$resql = $this->db->query($sql);
						if ($resql) {
							while ($obj = $this->db->fetch_object($resql)) {
								foreach ($obj as $key => $value) {
									$out .= $value . '|';
								}
							}
							$this->db->free($resql);
						} else {
							$this->errors[] = 'Error ' . $this->db->lasterror();
							dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
						}
					}
					$out = rtrim($out, '|');
					$out .= ']';
					$this->import_examplevalues_array[$r][$fieldname] = $out;
				} else {
					$this->import_examplevalues_array[$r][$fieldname] = 'enter a ' . $fieldtype . " value";
				}
			}
		}
	}
}




