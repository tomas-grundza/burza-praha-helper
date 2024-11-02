CREATE TABLE `rawData` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `date` date NOT NULL,
 `jmeno` varchar(512) NOT NULL,
 `isin` varchar(64) NOT NULL,
 `kurz` decimal(16,2) NOT NULL,
 `mena` varchar(8) NOT NULL,
 `zmena` decimal(16,2) NOT NULL,
 `pocet` int(8) NOT NULL,
 `objem` decimal(16,2) NOT NULL,
 `uniqueKey` varchar(64) NOT NULL,
 `timestamp` datetime DEFAULT current_timestamp(),
 PRIMARY KEY (`id`),
 UNIQUE KEY `uniqueKey[date,isin]` (`uniqueKey`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4
