-- Link Plot Cultivation task table definition - 
-- Copyright (C) 2016 Bruno Généré      <bgenere@webiseasy.org>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see <http://www.gnu.org/licenses/>.

CREATE TABLE llx_plot_taskprogress(
	-- object keys
	rowid INTEGER AUTO_INCREMENT PRIMARY KEY,  
	entity INTEGER DEFAULT 1 NOT NULL, -- multi company id
	-- link attributes
 	fk_plot INTEGER NOT NULL,
 	fk_tasktime	INTEGER NOT NULL,
 	progress INTEGER,
	-- record date time & user
	tms	timestamp,
  	datec	datetime,                   -- creation date
    fk_user_author INTEGER NOT NULL,
	fk_user_modif 	INTEGER NOT NULL
)ENGINE=innodb;
