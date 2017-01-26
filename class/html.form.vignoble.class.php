<?php

/*
 * Copyright (C) 2008-2012 Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2008-2012 Regis Houssin <regis.houssin@capnetworks.com>
 * Copyright (C) 2014 Juanjo Menent <jmenent@2byte.es>
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
 * \file htdocs/core/class/html.form.vignoble.class.php
 * \ingroup component
 * \brief File of class to build HTML component for third parties management
 */

/**
 * Class to build HTML component for third parties management
 * Only common components are here.
 */
class FormVignoble
{

	var $db;

	var $error;

	/**
	 * Constructor
	 *
	 * @param DoliDB $db
	 *        	Database handler
	 */
	function __construct($db)
	{
		$this->db = $db;
		
		return 1;
	}
	
/**
	 * Return a combo list for a dictionnary
	 *
	 * @param string $dictable
	 * 			table of the dictionnary
	 * @param string $dicname
	 * 			Table name for user
	 * 
	 * @param string $selected
	 *        	Title preselected
	 * @param string $htmlname
	 *        	Name of HTML select combo field
	 * @param  boolean $useempty      
	 *          True if you need a white line in combo
	 *        	
	 * @return string String with HTML select
	 */
	function displayDicCombo($dictable,$dicname, $selected = '', $htmlname = 'varietal_id', $useempty = false)
	{
		global $conf, $langs, $user;
		$langs->load("dict");
		
		$out = '';
		
		$sql = "SELECT rowid, code, label, active FROM " . MAIN_DB_PREFIX . $dictable;
		$sql .= " WHERE active = 1";
		
		dol_syslog("Form::select_".$dictable , LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$out .= '<select class="flat" name="' . $htmlname . '" id="' . $htmlname . '">';
			if ($useempty)
				$out .= '<option value="-1"> &nbsp;</option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					if ($selected == $obj->rowid) {
						$out .= '<option value="' . $obj->rowid . '" selected>';
					} else {
						$out .= '<option value="' . $obj->rowid . '">';
					}
					// Si traduction existe, on l'utilise, sinon on prend le libelle par defaut
					$out .= ($langs->trans($dicname . $obj->code) != $dicname . $obj->code ? $langs->trans($dicname . $obj->code) : ($obj->label != '-' ? $obj->label : ''));
					$out .= '</option>';
					$i ++;
				}
			}
			$out .= '</select>';
			if ($user->admin)
				$out .= info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
			$out .= ajax_combobox($htmlname);
		} else {
			dol_print_error($this->db);
		}
		
		return $out;
	}

	/**
	 * Print the object reference with links to navigate the current list
	 * 
	 * @param
	 *        	form
	 * @param
	 *        	langs
	 * @param
	 *        	$object
	 */
	function printObjectRef(Form $form, $langs, $object)
	{
		$linkback = '<a href="' . dol_buildpath('/vignoble/plot_list.php', 1) . '">' . $langs->trans("BackToList") . '</a>';
		
		print '<table class="noborder" width="100%">';
		print '<tr>';
		print '<td>';
		print $form->showrefnav($object, 'ref', $linkback, 1, 'ref', 'ref','','&tab='.GETPOST('tab','alpha'));
		print $object->label ;
		print '</td>';
		print '</tr>';
		print "</table>";
	}

	/**
	 * Set up 3 Tabs : Card, Notes, Info
	 */
	function getTabsHeader($langs, $object)
	{
		// print load_fiche_titre($langs->trans("plot"));
		$head = array();
		$h = 0;
		$head[$h][0] = 'plot_card.php?tab=card&id=' . $object->id;
		$head[$h][1] = $langs->trans("Card");
		$head[$h][2] = 'card';
		$h = 1;
		$head[$h][0] = 'plot_card.php?tab=notes&id=' . $object->id;
		$head[$h][1] = $langs->trans("Notes");
		$head[$h][2] = 'notes';
		$h = 2;
		$head[$h][0] = 'plot_card.php?tab=info&id=' . $object->id;
		$head[$h][1] = $langs->trans("Info");
		$head[$h][2] = 'info';
		return $head;
	}
}
