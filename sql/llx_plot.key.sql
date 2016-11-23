-- plot table definition - A piece of land of the wineyard
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

-- unique index on reference
ALTER TABLE llx_plot ADD UNIQUE INDEX uk_plot_ref (ref, entity);
-- index for label search
ALTER TABLE llx_plot ADD INDEX idx_plot_label (label);
-- index on user
ALTER TABLE llx_plot ADD INDEX idx_plot_fk_user_author (fk_user_author);
ALTER TABLE llx_plot ADD INDEX idx_plot_fk_user_modif (fk_user_modif);
