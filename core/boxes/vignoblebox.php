<?php
/*
 * <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) <year> <name of author>
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
 * \file core/boxes/vignoblebox.php
 * \ingroup component
 * \brief The module box with links to documentation.
 */
include_once DOL_DOCUMENT_ROOT . "/core/boxes/modules_boxes.php";

// load mobule libraries
dol_include_once('/vignoble/core/modules/modVignoble.class.php');

/**
 * Class to manage the box
 */
class vignoblebox extends ModeleBoxes
{

	/**
	 *
	 * @var string Alphanumeric ID. Populated by the constructor.
	 */
	public $boxcode = "vignoblebox";

	/**
	 *
	 * @var string Box icon (in configuration page)
	 *      Automatically calls the icon named with the corresponding "object_" prefix
	 */
	public $boximg = "vignoble@vignoble";

	/**
	 *
	 * @var string Box label (in configuration page)
	 */
	public $boxlabel;

	/**
	 *
	 * @var string[] Module dependencies
	 */
	public $depends = array(
		'vignoble'
	);

	/**
	 *
	 * @var DoliDb Database handler
	 */
	public $db;

	/**
	 *
	 * @var mixed More parameters
	 */
	public $param;

	/**
	 *
	 * @var array Header informations. Usually created at runtime by loadBox().
	 */
	public $info_box_head = array();

	/**
	 *
	 * @var array Contents informations. Usually created at runtime by loadBox().
	 */
	public $info_box_contents = array();

	/**
	 * Constructor
	 *
	 * @param DoliDB $db
	 *        	Database handler
	 * @param string $param
	 *        	More parameters
	 */
	public function __construct(DoliDB $db, $param = '')
	{
		global $langs;
		$langs->load("boxes");
		
		$this->boxlabel = $langs->transnoentitiesnoconv("About");
		
		$this->db = $db;
		$this->param = $param;
	}

	/**
	 * Load data into info_box_contents array to show array later.
	 * Called by Dolibarr before displaying the box.
	 *
	 * @param int $max
	 *        	Maximum number of records to load
	 * @return void
	 */
	public function loadBox($max = 5)
	{
		global $db, $langs;
		
		// Use configuration value for max lines count
		$this->max = $max;
		
		// include_once DOL_DOCUMENT_ROOT . "/mymodule/class/mymodule.class.php";
		
		// Populate the head at runtime
		$text = $langs->trans("vignobleAbout", $max);
		$this->info_box_head = array(
			// Title text
			'text' => $text,
			// Add a link
			'sublink' => '',
			// Sublink icon placed after the text
			'subpicto' => '',
			// Sublink icon HTML alt text
			'subtext' => '',
			// Sublink HTML target
			'target' => '',
			// HTML class attached to the picto and link
			'subclass' => 'center',
			// Limit and truncate with "…" the displayed text lenght, 0 = disabled
			'limit' => 0,
			// Adds translated " (Graph)" to a hidden form value's input (?)
			'graph' => false
		);
		$modvignoble = new modVignoble($db);
		
		// Populate the contents at runtime
		$this->info_box_contents = array(
			0 => array( // First line
				0 => array( // First Column
				            // HTML properties of the TR element. Only available on the first column.
					'tr' => 'align="left"',
					// HTML properties of the TD element
					'td' => '',
					// Fist line logo
					'logo' => '',
					// Main text
					'text' => $langs->trans("vignobleAuthor"),
					// Link on 'text' and 'logo' elements
					'url' => 'http://webiseasy.org',
					// Link's target HTML property
					'target' => 'new',
					// Truncates 'text' element to the specified character length, 0 = disabled
					'maxlength' => 0,
					// Prevents HTML cleaning (and truncation)
					'asis' => false
				),
				1 => array(
					'td' => 'align=right',
					'text' => 'version ' . $modvignoble->version
				)	
			),
			1 => array(
				0 => array( 
					'tr' => 'align="left"',
					'td' => 'colspan=2',
					'text' => $langs->trans('VignobleSetup'),
					'url' => DOL_URL_ROOT .'/custom/vignoble/admin/module_settings.php',
					'asis' => true
				)
			),
			2 => array(
				0 => array( 
					'tr' => 'align="left"',
					'td' => 'colspan=2',
					'text' => $langs->trans('doxyDocumentation'),
					'url' => DOL_URL_ROOT .'/custom/vignoble/docs/html/index.html',
					'target' => 'new',
					'asis' => true
				)
			),
			3 => array( 
				0 => array( 
					'tr' => 'align="left"',
					'td' => 'colspan=2',
					'text' => $langs->trans('GetModuleRelease'),
					'url' => 'https://github.com/bgenere/vignoble/releases',
					'target' => 'new',
					'asis' => true
				)
			),
			4 => array( 
				0 => array( 
					'tr' => 'align="left"',
					'td' => 'colspan=2',
					'text' => $langs->trans('SubmitIssue'),
					'url' => 'https://github.com/bgenere/vignoble/issues',
					'target' => 'new',
					'asis' => true
				)
			)
		);
	}

	/**
	 * Method to show box.
	 * Called by Dolibarr each time it wants to display the box.
	 *
	 * @param array $head
	 *        	Array with properties of box title
	 * @param array $contents
	 *        	Array with properties of box lines
	 * @return void
	 */
	public function showBox($head = null, $contents = null)
	{
		// You may make your own code here…
		// … or use the parent's class function using the provided head and contents templates
		parent::showBox($this->info_box_head, $this->info_box_contents,0);
	}
}
