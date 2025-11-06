UPDATE `#__j2store_zones` SET `enabled` = '0' WHERE `country_id` = 73;

INSERT INTO `#__j2store_zones` (`country_id`, `zone_code`, `zone_name`, `enabled`, `ordering`) VALUES
(73,'FR-ARA','Auvergne-Rhône-Alpes', 1, 0),
(73,'FR-BFC','Bourgogne-Franche-Comté', 1, 0),
(73,'FR-BRE','Bretagne', 1, 0),
(73,'FR-CVL','Centre-Val de Loire', 1, 0),
(73,'FR-COR','Corse', 1, 0),
(73,'FR-GES','Grand Est', 1, 0),
(73,'FR-HDF','Hauts-de-France', 1, 0),
(73,'FR-IDF','Île-de-France', 1, 0),
(73,'FR-NOR','Normandie', 1, 0),
(73,'FR-NAQ','Nouvelle-Aquitaine', 1, 0),
(73,'FR-OCC','Occitanie', 1, 0),
(73,'FR-PDL','Pays de la Loire', 1, 0),
(73,'FR-PAC','Provence-Alpes-Côte d’Azur', 1, 0),
(73,'FR-GUA','Guadeloupe', 1, 0),
(73,'FR-MTQ','Martinique', 1, 0),
(73,'FR-GUF','Guyane', 1, 0),
(73,'FR-REU','La Réunion', 1, 0),
(73,'FR-MAY','Mayotte', 1, 0);
