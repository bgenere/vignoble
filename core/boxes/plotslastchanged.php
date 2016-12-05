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
 * \file core/boxes/plotsummarybox.php
 * \ingroup dashboard
 * \brief Get Last Plots modified
 */
include_once DOL_DOCUMENT_ROOT . "/core/boxes/modules_boxes.php";

// load mobule libraries
dol_include_once('/vignoble/class/plot.class.php');

/**
 * Class to manage the box
 *
 * Warning: for the box to be detected correctly by dolibarr,
 * the filename should be the lowercase classname
 */
class plotslastchanged extends ModeleBoxes
{

	/**
	 *
	 * @var string Alphanumeric ID. Populated by the constructor.
	 */
	public $boxcode = "lastplotsmodified";

	/**
	 *
	 * @var string Box icon (in configuration page)
	 *      Automatically calls the icon named with the corresponding "object_" prefix
	 */
	public $boximg = "plot14@vignoble";

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
		
		$this->boxlabel = $langs->transnoentitiesnoconv("LastPlotsModified");
		
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
		
		// include_once DOL_DOCUMENT_ROOT . "/plotsummarymodule/class/plotsummarymodule.class.php";
		
		// Populate the head at runtime
		$text = $langs->trans("LastPlotsModified", $max);
		$this->info_box_head = array(
			// Title text
			'text' => $text,
			// Add a link used for graph with filter
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
		
		$this->info_box_contents = array();
		
		$plot = new plot($db);
		if ($plot->fetchAll('DESC', 't.tms', 5) > 0) {
			$i = 0;
			foreach ($plot->lines as $key => $line) {
				$this->info_box_contents[$i] = array(
					0 => array(
						'tr' => 'align="left"',
						'td' => '',
						'logo' => 'plot14@vignoble',
						'text' => ' ' . $line->ref,
						'url' => DOL_URL_ROOT . '/custom/vignoble/plot_card.php?id=' . $line->id,
						'maxlength' => 30
					),
					1 => array(
						'td' => '',
						'text' => $line->label,
						'maxlength' => 60
					),
					2 => array(
						'td' => 'align="right"',
						'text' => dol_print_date($line->tms, 'dayhour')
					)
				);
				$i ++;
			}
		}
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
		// echo 'Now showing Vignoble test box';
		// … or use the parent's class function using the provided head and contents templates
		parent::showBox($this->info_box_head, $this->info_box_contents);
	}
}
