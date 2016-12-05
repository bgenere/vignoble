<?php

/*
 * Vignoble Module library
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
 * \file lib/admin.html.lib.php
 * \ingroup admin
 * \brief Module library for Admin Pages
 *
 * Contains main components for admin pages
 */

/**
 * Display begining of card form including : 
 * - Dolibarr header, 
 * - Form title with modules link back
 * - Tabs for administration
 * 
 * @param string $currentTab The tab key of the tab to select/display
 * @param string $page_title The HTML page title common to all tabs
 */
function beginForm($currentTab,$page_title = "Vignoble Setup")
{
	global $db, $langs, $conf, $user;
	
	llxHeader('', $langs->trans($page_title));
	
	$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';
	print load_fiche_titre($langs->trans($page_title), $linkback, 'title_setup');
	
	$tabs = vignobleAdminPrepareTabs();
	
	dol_fiche_head($tabs, $currentTab, $langs->trans("Module123100Name"), 0, "vignoble@vignoble");
}
/**
 * Perform all required operations to end properly a card form.
 */
function endForm()
{
	global $db, $langs, $conf;
	
	dol_fiche_end();
	llxFooter();
	$db->close();
}

/**
 * Populate tabs array with all tabs for the admin page
 * 
 * @return $tabs an array with one line per tab
 * with on each line
 * - full URL of the page for the tab
 * - tab name to display (string)
 * - tab key (string)
 * 
 */
function vignobleAdminPrepareTabs()
{
	global $langs, $conf;
	
	$langs->load("vignoble@vignoble");
	
	$tabs = array();
	$h = 0;
	$tabs[$h][0] = dol_buildpath("/vignoble/admin/module_settings.php", 1);
	$tabs[$h][1] = $langs->trans("Settings");
	$tabs[$h][2] = 'settings';
	$h ++;
	$tabs[$h][0] = dol_buildpath("/vignoble/admin/plot_extrafields.php", 1);
	$tabs[$h][1] = $langs->trans("PlotExtraFields");
	$tabs[$h][2] = 'plotfields';
	$h ++;
	$tabs[$h][0] = dol_buildpath("/vignoble/admin/module_about.php", 1);
	$tabs[$h][1] = $langs->trans("About");
	$tabs[$h][2] = 'about';
	$h ++;
	
	return $tabs;
}
