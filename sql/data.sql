-- Initial load for the Vignoble module
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

-- 
-- Load dictionnary table
--

-- assolement

delete from llx_c_assolement where module = 'vignoble';
insert into llx_c_assolement (rowid,code,label,module) values (1,'VIGNE','Vigne','vignoble');
insert into llx_c_assolement (rowid,code,label,module) values (2,'JEUNEVIGNE','Jeune Vigne','vignoble');
insert into llx_c_assolement (rowid,code,label,module) values (3,'JACHERE','Jachère','vignoble');

-- cepage

delete from llx_c_cepage where module = 'vignoble';
insert into llx_c_cepage (rowid,code,label,module) values (1,'CABERNET','Cabernet','vignoble');
insert into llx_c_cepage (rowid,code,label,module) values (2,'MERLOT','Merlot','vignoble');

-- porte_greffe

delete from llx_c_porte_greffe where module = 'vignoble';
insert into llx_c_porte_greffe (rowid,code,label,module) values (1,'S04','SO4','vignoble');
insert into llx_c_porte_greffe (rowid,code,label,module) values (2,'5BB','5BB','vignoble');
insert into llx_c_porte_greffe (rowid,code,label,module) values (3,'101-14','101-14','vignoble');