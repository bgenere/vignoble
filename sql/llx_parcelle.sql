-- parcelle table definition - A piece of land of the wineyard
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

CREATE TABLE llx_parcelle(
	-- object keys
	rowid INTEGER AUTO_INCREMENT PRIMARY KEY,  
	entity INTEGER DEFAULT 1 NOT NULL, -- multi company id

 	ref VARCHAR(125),				   -- unique reference
	label VARCHAR(255),
	description TEXT,
	-- attributes
	surface real,						-- area size
	nbpieds INTEGER NOT NULL,			-- number of wine roots
	ecartement real, 					-- distance between 2 rows
	-- attributes (in dictionnary) 
	fk_assolement 	INTEGER NOT NULL, 
	fk_cepage 	INTEGER NOT NULL,
	fk_porte_greffe INTEGER NOT NULL,
	-- private note
	note_private TEXT,
	note_public TEXT,
	-- record date time & user
	tms	timestamp,
  	datec	datetime,                   -- creation date
    fk_user_author INTEGER NOT NULL,
	fk_user_modif 	INTEGER NOT NULL   
	
)ENGINE=innodb;
