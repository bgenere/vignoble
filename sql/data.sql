-- Initial load for the module
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

-- Dictionnaries Load

-- cultivationtype

insert into llx_c_cultivationtype (code,label,module) values ('VIGNE','Vigne','vignoble');
insert into llx_c_cultivationtype (code,label,module) values ('JEUNEVIGNE','Jeune Vigne','vignoble');
insert into llx_c_cultivationtype (code,label,module) values ('JACHERE','Jachère','vignoble');

-- varietal

insert into llx_c_varietal (code,label,module) values ('CABERNET','Cabernet','vignoble');
insert into llx_c_varietal (code,label,module) values ('MERLOT','Merlot','vignoble');

-- rootstock

insert into llx_c_rootstock (code,label,module) values ('S04','SO4','vignoble');
insert into llx_c_rootstock (code,label,module) values ('5BB','5BB','vignoble');
insert into llx_c_rootstock (code,label,module) values ('101-14','101-14','vignoble');
