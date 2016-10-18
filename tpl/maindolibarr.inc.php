<?php
/*
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
 * \file ./tpl/maindolibarr.inc.php
 *
 * \brief Include main Dolibarr library and global variables.
 *
 * Test various directory to load Main Dolibarr library.
 * 
 * Include library when :
 * - module is in dolibarr root htdocs directory and page is in module directory
 * - module directory is in a subdir of root htdocs directory or page is in module sub-directory
 * - module directory is in a subdir of root htdocs directory and page is in module sub-directory
 * 
 * If include fails it display a message and die the proces
 * 
 * \ingroup component
 */
$incresult = 0; /**< used to check include result */
if (! $incresult && file_exists("../main.inc.php"))
	$incresult = @include '../main.inc.php';

if (! $incresult && file_exists("../../main.inc.php"))
	$incresult = @include '../../main.inc.php'; 

if (! $incresult && file_exists("../../../main.inc.php"))
	$incresult = @include '../../../main.inc.php';

if (! $incresult)
	die("Include of main Dolibarr include fails");

global 
	$db, 	/**< Dolibarr Database environment */
	$conf,  /**< Dolibarr configuration variable */
	$langs, /**< Dolibarr languages table including user language */
	$user;  /**< Dolibarr current user properties */