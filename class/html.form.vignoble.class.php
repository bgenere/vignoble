<?php
/* Copyright (C) 2008-2012	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2008-2012	Regis Houssin		<regis.houssin@capnetworks.com>
 * Copyright (C) 2014		Juanjo Menent		<jmenent@2byte.es>
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
 *	\file       htdocs/core/class/html.form.vignoble.class.php
 *  \ingroup    core
 *	\brief      File of class to build HTML component for third parties management
 */


/**
 *	Class to build HTML component for third parties management
 *	Only common components are here.
 */
class FormVignoble
{
	var $db;
	var $error;



	/**
	 *	Constructor
	 *
	 *	@param	DoliDB	$db		Database handler
	 */
	function __construct($db)
	{
		$this->db = $db;

		return 1;
	}


	/**
	 *  Return combo list with cépage name
	 *
	 *  @param  string	$selected   	Title preselected
	 * 	@param	string	$htmlname		Name of HTML select combo field
	 * 
	 *  @return	string					String with HTML select
	 */
	function select_varietal($selected='',$htmlname='varietal_id', $useempty=0)
	{
		global $conf,$langs,$user;
		$langs->load("dict");

		$out='';

		$sql = "SELECT rowid, code, label, active FROM ".MAIN_DB_PREFIX."c_varietal";
		$sql.= " WHERE active = 1";

		dol_syslog("Form::select_varietal", LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$out.= '<select class="flat" name="'.$htmlname.'" id="'.$htmlname.'">';
			if ($useempty) $out.= '<option value="-1"> &nbsp;</option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num)
			{
				while ($i < $num)
				{
					$obj = $this->db->fetch_object($resql);
					if ($selected == $obj->rowid)
					{
						$out.= '<option value="'.$obj->rowid.'" selected>';
					}
					else
					{
						$out.= '<option value="'.$obj->rowid.'">';
					}
					// Si traduction existe, on l'utilise, sinon on prend le libelle par defaut
					$out.= ($langs->trans("varietal".$obj->code)!="varietal".$obj->code ? $langs->trans("varietal".$obj->code) : ($obj->label!='-'?$obj->label:''));
					$out.= '</option>';
					$i++;
				}
			}
			$out.= '</select>';
			if ($user->admin) $out.= info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
			$out.=ajax_combobox($htmlname);
		}
		else
		{
			dol_print_error($this->db);
		}

		return $out;
	}


/**
	 *  Return combo list with cépage name
	 *
	 *  @param  string	$selected   	Title preselected
	 * 	@param	string	$htmlname		Name of HTML select combo field
	 *  @return	string					String with HTML select
	 */
	function select_rootstock($selected='',$htmlname='rootstock_id')
	{
		global $conf,$langs,$user;
		$langs->load("dict");

		$out='';

		$sql = "SELECT rowid, code, label, active FROM ".MAIN_DB_PREFIX."c_rootstock";
		$sql.= " WHERE active = 1";

		dol_syslog("Form::select_rootstock", LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$out.= '<select class="flat" name="'.$htmlname.'" id="'.$htmlname.'">';
			$out.= '<option value="">&nbsp;</option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num)
			{
				while ($i < $num)
				{
					$obj = $this->db->fetch_object($resql);
					if ($selected == $obj->rowid)
					{
						$out.= '<option value="'.$obj->rowid.'" selected>';
					}
					else
					{
						$out.= '<option value="'.$obj->rowid.'">';
					}
					// Si traduction existe, on l'utilise, sinon on prend le libelle par defaut
					$out.= ($langs->trans("rootstock".$obj->code)!="rootstock".$obj->code ? $langs->trans("rootstock".$obj->code) : ($obj->label!='-'?$obj->label:''));
					$out.= '</option>';
					$i++;
				}
			}
			$out.= '</select>';
			if ($user->admin) $out.= info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
		}
		else
		{
			dol_print_error($this->db);
		}

		return $out;
	}
	
/**
	 *  Return combo list with cépage name
	 *
	 *  @param  string	$selected   	Title preselected
	 * 	@param	string	$htmlname		Name of HTML select combo field
	 *  @return	string					String with HTML select
	 */
	function select_cultivationtype($selected='',$htmlname='cultivationtype_id')
	{
		global $conf,$langs,$user;
		$langs->load("dict");

		$out='';

		$sql = "SELECT rowid, code, label, active FROM ".MAIN_DB_PREFIX."c_cultivationtype";
		$sql.= " WHERE active = 1";

		dol_syslog("Form::select_cultivationtype", LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$out.= '<select class="flat" name="'.$htmlname.'" id="'.$htmlname.'">';
			$out.= '<option value="">&nbsp;</option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num)
			{
				while ($i < $num)
				{
					$obj = $this->db->fetch_object($resql);
					if ($selected == $obj->rowid)
					{
						$out.= '<option value="'.$obj->rowid.'" selected>';
					}
					else
					{
						$out.= '<option value="'.$obj->rowid.'">';
					}
					// Si traduction existe, on l'utilise, sinon on prend le libelle par defaut
					$out.= ($langs->trans("cultivationtype".$obj->code)!="cultivationtype".$obj->code ? $langs->trans("cultivationtype".$obj->code) : ($obj->label!='-'?$obj->label:''));
					$out.= '</option>';
					$i++;
				}
			}
			$out.= '</select>';
			if ($user->admin) $out.= info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
			$out.=ajax_combobox($htmlname);
			
		}
		else
		{
			dol_print_error($this->db);
		}

		return $out;
	}

}
