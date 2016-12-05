<?php
/*
 * This page is for module administration
 * Copyright (C) 2016 Bruno Généré <webiseasy.org>
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
 * \file admin/module_setting.php
 * \ingroup admin
 * \brief Setup tab for the module.
 * - Includes the set-up of plot document model
 */

@include '../tpl/maindolibarr.inc.php';

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/admin.html.lib.php';
require_once '../class/plot.class.php';

// Translations
$langs->load("admin");
$langs->load("errors");
$langs->load("other");
$langs->load("vignoble@vignoble");

// Access control admin user only
if (! $user->admin) {
	accessforbidden();
}

// Get Parameters
$action = GETPOST('action', 'alpha');
$value = GETPOST('value', 'alpha');
$label = GETPOST('label', 'alpha');
$scandir = GETPOST('scandir', 'alpha');
$type = 'plot';

/**
 * Process Actions
 * - specimen : define a pdf specimen document
 * - set : activate a document model
 * - del : delete a document model
 * - setdoc : set a default document model
 */

if ($action == 'specimen') {
	$modele = GETPOST('module', 'alpha');
	
	$plot = new Plot($db);
	$plot->initAsSpecimen();
	
	// Search template files
	$file = '';
	$classname = '';
	$filefound = 0;
	$dirmodels = array_merge(array(
		'/'
	), (array) $conf->modules_parts['models']);
	foreach ($dirmodels as $reldir) {
		$file = dol_buildpath('/vignoble/core/modules/vignoble/doc/pdf_' . $modele . ".modules.php", 0);
		if (file_exists($file)) {
			$filefound = 1;
			$classname = "pdf_" . $modele;
			break;
		}
	}
	
	if ($filefound) {
		require_once $file;
		
		$module = new $classname($db);
		
		if ($module->write_file($plot, $langs) > 0) {
			header("Location: " . DOL_URL_ROOT . "/document.php?modulepart=vignoble&file=SPECIMEN.pdf");
			return;
		} else {
			setEventMessages($module->error, null, 'errors');
			dol_syslog($module->error, LOG_ERR);
		}
	} else {
		setEventMessages($langs->trans("ErrorModuleNotFound"), null, 'errors');
		dol_syslog($langs->trans("ErrorModuleNotFound"), LOG_ERR);
	}
} 

// Activate a model
else 
	if ($action == 'set') {
		$ret = addDocumentModel($value, $type, $label, $scandir);
	} 

	else 
		if ($action == 'del') {
			$ret = delDocumentModel($value, $type);
			if ($ret > 0) {
				if ($conf->global->PLOT_ADDON_PDF == "$value")
					dolibarr_del_const($db, 'PLOT_ADDON_PDF', $conf->entity);
			}
		} 		

		// Set default model
		else 
			if ($action == 'setdoc') {
				if (dolibarr_set_const($db, "PLOT_ADDON_PDF", $value, 'chaine', 0, '', $conf->entity)) {
					// The constant that was read before the new set
					// We therefore requires a variable to have a coherent view
					$conf->global->PLOT_ADDON_PDF = $value;
				}
				
				// On active le modele
				$ret = delDocumentModel($value, $type);
				if ($ret > 0) {
					$ret = addDocumentModel($value, $type, $label, $scandir);
				}
			}

/**
 * Display document model view
 */

$dirmodels = array_merge(array(
	'/'
), (array) $conf->modules_parts['models']);

printView($langs, $user);

/**
 * Generate and print the view
 */
function printView($langs, $user)
{
	global $db, $conf, $dirmodels;
	
	$form = new Form($db);
	beginForm('settings','VignobleSetup');
		
	/*
	 * Document templates generators
	 */
	$type = 'plot';
	
	print load_fiche_titre($langs->trans("PlotModelModule"), '', '');
	
	// Load array def with activated templates
	$def = array();
	$sql = "SELECT nom";
	$sql .= " FROM " . MAIN_DB_PREFIX . "document_model";
	$sql .= " WHERE type = '" . $type . "'";
	$sql .= " AND entity = " . $conf->entity;
	$resql = $db->query($sql);
	if ($resql) {
		$i = 0;
		$num_rows = $db->num_rows($resql);
		while ($i < $num_rows) {
			$array = $db->fetch_array($resql);
			array_push($def, $array[0]);
			$i ++;
		}
	} else {
		dol_print_error($db);
	}
	
	print "<table class=\"noborder\" width=\"100%\">\n";
	print "<tr class=\"liste_titre\">\n";
	print '<td>' . $langs->trans("Name") . '</td>';
	print '<td>' . $langs->trans("Description") . '</td>';
	print '<td align="center" width="60">' . $langs->trans("Status") . "</td>\n";
	print '<td align="center" width="60">' . $langs->trans("Default") . "</td>\n";
	print '<td align="center" width="38">' . $langs->trans("ShortInfo") . '</td>';
	print '<td align="center" width="38">' . $langs->trans("Preview") . '</td>';
	print "</tr>\n";
	
	clearstatcache();
	
	$var = true;
	foreach ($dirmodels as $reldir) {
		foreach (array(
			'',
			'/doc'
		) as $valdir) {
			$dir = dol_buildpath($reldir . "core/modules/vignoble" . $valdir);
			
			if (is_dir($dir)) {
				$handle = opendir($dir);
				if (is_resource($handle)) {
					while (($file = readdir($handle)) !== false) {
						$filelist[] = $file;
					}
					closedir($handle);
					arsort($filelist);
					
					foreach ($filelist as $file) {
						if (preg_match('/\.modules\.php$/i', $file) && preg_match('/^(pdf_|doc_)/', $file)) {
							
							if (file_exists($dir . '/' . $file)) {
								$name = substr($file, 4, dol_strlen($file) - 16);
								$classname = substr($file, 0, dol_strlen($file) - 12);
								
								require_once $dir . '/' . $file;
								$module = new $classname($db);
								
								$modulequalified = 1;
								if ($module->version == 'development' && $conf->global->MAIN_FEATURES_LEVEL < 2)
									$modulequalified = 0;
								if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1)
									$modulequalified = 0;
								
								if ($modulequalified) {
									$var = ! $var;
									print '<tr ' . $bc[$var] . '><td width="100">';
									print(empty($module->name) ? $name : $module->name);
									print "</td><td>\n";
									if (method_exists($module, 'info'))
										print $module->info($langs);
									else
										print $module->description;
									print '</td>';
									
									// Active
									if (in_array($name, $def)) {
										print '<td align="center">' . "\n";
										print '<a href="' . $_SERVER["PHP_SELF"] . '?action=del&value=' . $name . '">';
										print img_picto($langs->trans("Enabled"), 'switch_on');
										print '</a>';
										print '</td>';
									} else {
										print '<td align="center">' . "\n";
										print '<a href="' . $_SERVER["PHP_SELF"] . '?action=set&value=' . $name . '&amp;scandir=' . $module->scandir . '&amp;label=' . urlencode($module->name) . '">' . img_picto($langs->trans("Disabled"), 'switch_off') . '</a>';
										print "</td>";
									}
									
									// Default
									print '<td align="center">';
									if ($conf->global->PLOT_ADDON_PDF == $name) {
										print img_picto($langs->trans("Default"), 'on');
									} else {
										print '<a href="' . $_SERVER["PHP_SELF"] . '?action=setdoc&value=' . $name . '&amp;scandir=' . $module->scandir . '&amp;label=' . urlencode($module->name) . '" alt="' . $langs->trans("Default") . '">' . img_picto($langs->trans("Disabled"), 'off') . '</a>';
									}
									print '</td>';
									
									// Info
									$htmltooltip = '' . $langs->trans("Name") . ': ' . $module->name;
									$htmltooltip .= '<br>' . $langs->trans("Type") . ': ' . ($module->type ? $module->type : $langs->trans("Unknown"));
									if ($module->type == 'pdf') {
										$htmltooltip .= '<br>' . $langs->trans("Width") . '/' . $langs->trans("Height") . ': ' . $module->page_largeur . '/' . $module->page_hauteur;
									}
									$htmltooltip .= '<br><br><u>' . $langs->trans("FeaturesSupported") . ':</u>';
									$htmltooltip .= '<br>' . $langs->trans("Logo") . ': ' . yn($module->option_logo, 1, 1);
									$htmltooltip .= '<br>' . $langs->trans("PaymentMode") . ': ' . yn($module->option_modereg, 1, 1);
									$htmltooltip .= '<br>' . $langs->trans("PaymentConditions") . ': ' . yn($module->option_condreg, 1, 1);
									$htmltooltip .= '<br>' . $langs->trans("MultiLanguage") . ': ' . yn($module->option_multilang, 1, 1);
									// $htmltooltip.='<br>'.$langs->trans("Discounts").': '.yn($module->option_escompte,1,1);
									// $htmltooltip.='<br>'.$langs->trans("CreditNote").': '.yn($module->option_credit_note,1,1);
									$htmltooltip .= '<br>' . $langs->trans("WatermarkOnDraftOrders") . ': ' . yn($module->option_draft_watermark, 1, 1);
									
									print '<td align="center">';
									print $form->textwithpicto('', $htmltooltip, 1, 0);
									print '</td>';
									
									// Preview
									print '<td align="center">';
									if ($module->type == 'pdf') {
										print '<a href="' . $_SERVER["PHP_SELF"] . '?action=specimen&module=' . $name . '">' . img_object($langs->trans("Preview"), 'bill') . '</a>';
									} else {
										print img_object($langs->trans("PreviewNotAvailable"), 'generic');
									}
									print '</td>';
									
									print "</tr>\n";
								}
							}
						}
					}
				}
			}
		}
	}
	
	print '</table>';
	print "<br>";
	
	endForm();
}




