<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2016- Bruno Généré  <bgenere@webiseasy.org>
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
 * \file    class/actions_mymodule.class.php
 * \ingroup component
 * \brief   Class file for Hooks provided with the module.
 *  Contains :
 * - Hook on search box to provide search on Parcels
 *          
 *           
 */

/**
 * Class ActionsVignoble define the hooks provided with the module.
 */
class ActionsVignoble
{
	/**
	 * Array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * Array collecting Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
	}
	
	/**
	 * Add a search entry for plot in the Dolibarr select search box.
	 * Name of the class is the name required by
	 * 	/htdocs/core/ajax/selectsearchbox.php
	 * 
	 * @return number 0
	 */
	function addSearchEntry()
	{
		global $search_boxvalue;
		global $langs;
		
		$this->results[] = array('img'=>'object_plot@vignoble', 'label'=>$langs->trans("Plots"), 'text'=>img_picto('','object_plot@vignoble','style="width:14px"').' '.$langs->trans("Plots"), 'url'=>dol_buildpath('/vignoble/plot_list.php',1).'?mainmenu=vignoble&sall='.urlencode($search_boxvalue));
		
		return 0;
	}
	
	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		$error = 0; // Error counter
		$myvalue = 'test'; // A result value

		print_r($parameters);
		echo "action: " . $action;
		print_r($object);

		if (in_array('somecontext', explode(':', $parameters['context'])))
		{
		  // do something only for the context 'somecontext'
		}

		if (! $error)
		{
			$this->results = array('myreturn' => $myvalue);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}
}
