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
 * \file ./tpl/cultivationtask.inc.php
 *
 * \brief Includes Dolibarr classes and libraries needed for cultivation task management
 *
 * \ingroup component
 */

// Include project and task class
dol_include_once('/projet/class/project.class.php');
dol_include_once('/projet/class/task.class.php');

// Include extrafields class
dol_include_once('/core/class/extrafields.class.php');

// Include formother and formfile class
dol_include_once('/core/class/html.formother.class.php');
//dol_include_once('/core/class/html.formfile.class.php');

// Include admin library
dol_include_once('/core/lib/admin.lib.php');

// Include project library
dol_include_once('/core/lib/project.lib.php');

// Include date management library
dol_include_once('/core/lib/date.lib.php');

// Include cultivation task library
require_once './lib/cultivationtask.lib.php';
/**
 * get language files
 */
$langs->load("users");
$langs->load("projects");
$langs->load("companies");
$langs->load("vignoble@vignoble");


