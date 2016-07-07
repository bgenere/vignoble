-- Initial load for the Vignoble module
-- Copyright (C) 2016  Bruno Généré
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

-- assolement

delete from llx_c_assolement;
ALTER TABLE llx_c_assolement AUTO_INCREMENT = 1;
insert into llx_c_assolement (code,label) values ('VIGNE','Vigne');
insert into llx_c_assolement (code,label) values ('JEUNEVIGNE','Jeune Vigne');
insert into llx_c_assolement (code,label) values ('JACHERE','Jachère');

-- cepage

delete from llx_c_cepage;
ALTER TABLE llx_c_cepage AUTO_INCREMENT = 1;
insert into llx_c_cepage (code,label) values ('CABERNET','Cabernet');
insert into llx_c_cepage (code,label) values ('MERLOT','Merlot');

-- porte_greffe

delete from llx_c_porte_greffe;
ALTER TABLE llx_c_porte_greffe AUTO_INCREMENT = 1;
insert into llx_c_porte_greffe (code,label) values ('S04','SO4');
insert into llx_c_porte_greffe (code,label) values ('5BB','5BB');
insert into llx_c_porte_greffe (code,label) values ('101-14','101-14');