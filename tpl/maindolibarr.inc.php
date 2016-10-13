<?php
/* Copyright (C) 2016 Bruno Généré      <bgenere@webiseasy.org>
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
 * \file
 * \brief template to include main Dolibarr library
 * 
 * Test various directory to load Main Dolibarr library so it works
 * when module directory is in <dolibarr_root>/htdocs directory or
 * in a subdir (as <dolibarr_root>/htdocs/custom).
 * 
 * \ingroup template
 * 
 */

$incresult = 0; /**< used to check include result */  
if (! $incresult && file_exists("../main.inc.php"))
	$incresult = @include '../main.inc.php'; // to work if your module directory is into dolibarr root htdocs directory
if (! $incresult && file_exists("../../main.inc.php"))
	$incresult = @include '../../main.inc.php'; // to work if your module directory is into a subdir of root htdocs directory
if (! $incresult)
	die("Include of main Dolibarr include fails");

