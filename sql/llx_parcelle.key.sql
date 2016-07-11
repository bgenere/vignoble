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


ALTER TABLE llx_parcelle ADD UNIQUE INDEX uk_parcelle_ref (ref, entity);

ALTER TABLE llx_parcelle ADD INDEX idx_parcelle_label (label);

ALTER TABLE llx_parcelle ADD INDEX idx_parcelle_fk_user_author (fk_user_author);
ALTER TABLE llx_parcelle ADD INDEX idx_parcelle_fk_user_modif (fk_user_modif);


ALTER TABLE  llx_parcelle ADD CONSTRAINT fk_parcelle_fk_assolement FOREIGN KEY (fk_assolement) REFERENCES  llx_c_assolement (rowid);
ALTER TABLE  llx_parcelle ADD CONSTRAINT fk_parcelle_fk_porte_greffe FOREIGN KEY (fk_porte_greffe) REFERENCES  llx_c_porte_greffe (rowid);
ALTER TABLE  llx_parcelle ADD CONSTRAINT fk_parcelle_fk_cepage FOREIGN KEY (fk_cepage) REFERENCES  llx_c_cepage (rowid);
