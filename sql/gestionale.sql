SET time_zone = "+00:00";
CREATE DATABASE IF NOT EXISTS `gestionale_` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE gestionale_;
CREATE TABLE `comande` (
  `serata` date NOT NULL,
  `tavolo` int(10) UNSIGNED NOT NULL,
  `indice` int(10) UNSIGNED NOT NULL,
  `menu` varchar(50) NOT NULL,
  `responsabile` varchar(50) NOT NULL,
  `numero_soci` int(10) UNSIGNED DEFAULT '0',
  `pagata` tinyint(1) NOT NULL DEFAULT '1',
  `attiva` tinyint(1) NOT NULL DEFAULT '1',
  `num_comanda` int(11) DEFAULT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_aggiornamento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `annotazioni` text,
  `sconto_manuale` decimal(5,2) UNSIGNED DEFAULT '0.00',
  `conto_inviato` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `composizionemenu` (
  `menu` varchar(50) NOT NULL,
  `portata` varchar(100) NOT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_aggiornamento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `composizioneportata` (
  `portata` varchar(100) NOT NULL,
  `materia_prima` varchar(40) NOT NULL,
  `peso` decimal(6,3) NOT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_aggiornamento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `materieprime` (
  `nome_materia` varchar(40) NOT NULL,
  `genere` varchar(45) NOT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_aggiornamento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `nome_menu` varchar(50) NOT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_aggiornamento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fisso` tinyint(1) NOT NULL DEFAULT '0',
  `prezzo_fisso` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `menuserata` (
  `menu` varchar(50) NOT NULL,
  `serata` date NOT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_aggiornamento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `ordini` (
  `quantita` int(11) NOT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_aggiornamento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `serata` date NOT NULL,
  `tavolo` int(10) UNSIGNED NOT NULL,
  `indice` int(10) UNSIGNED NOT NULL,
  `portata` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `portata` (
  `id` int(11) NOT NULL,
  `nome_portata` varchar(100) NOT NULL,
  `categoria` enum('bevanda','piadina','bruschette e crostoni','pane e coperto','antipasto','primo','secondo','contorno','dolce') NOT NULL,
  `prezzo_finale` decimal(5,2) NOT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_aggiornamento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `quantitàpiattiserata` (
  `serata` date NOT NULL,
  `piatto` varchar(100) NOT NULL,
  `quantità` int(11) NOT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_aggiornamento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `responsabili` (
  `nome` varchar(50) NOT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_aggiornamento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `responsabiliserata` (
  `serata` date NOT NULL,
  `tavolo` int(10) UNSIGNED NOT NULL,
  `responsabile` varchar(50) NOT NULL,
  `numero_progressivo` int(11) NOT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_aggiornamento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `serata` (
  `data` date NOT NULL,
  `descrizione` varchar(100) DEFAULT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_aggiornamento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `inizializzata` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `tavoli` (
  `numero_tavolo` int(10) UNSIGNED NOT NULL,
  `zona` varchar(50) NOT NULL,
  `posti` int(10) UNSIGNED NOT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_aggiornamento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `utente` (
  `username` varchar(20) NOT NULL,
  `password` char(32) NOT NULL,
  `gruppo` enum('amm','mag','cam') NOT NULL,
  `nome` varchar(45) NOT NULL,
  `cognome` varchar(45) NOT NULL,
  `email` varchar(75) NOT NULL,
  `tel_fisso` varchar(20) DEFAULT NULL,
  `tel_mobile` varchar(20) DEFAULT NULL,
  `foto` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `zone` (
  `zona` varchar(50) NOT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_aggiornamento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `prenotazioni` (
  `serata` date NOT NULL,
  `tavoli` int(11) NOT NULL,
  `coperti` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8
ALTER TABLE `comande`
  ADD PRIMARY KEY (`serata`,`tavolo`,`indice`),
  ADD KEY `tavolocom_idx` (`tavolo`),
  ADD KEY `seratacom_idx` (`serata`),
  ADD KEY `indicecom_idx` (`indice`),
  ADD KEY `menfissocom_idx` (`menu`),
  ADD KEY `respcom_idx` (`responsabile`);
ALTER TABLE `composizionemenu`
  ADD PRIMARY KEY (`menu`,`portata`),
  ADD KEY `nome portata_idx` (`portata`);
ALTER TABLE `composizioneportata`
  ADD PRIMARY KEY (`portata`,`materia_prima`),
  ADD KEY `nome materia1_idx` (`materia_prima`);
ALTER TABLE `materieprime`
  ADD PRIMARY KEY (`nome_materia`);
ALTER TABLE `menu`
  ADD PRIMARY KEY (`nome_menu`);
ALTER TABLE `menuserata`
  ADD PRIMARY KEY (`menu`,`serata`),
  ADD KEY `data serata_idx` (`serata`);
ALTER TABLE `ordini`
  ADD PRIMARY KEY (`serata`,`tavolo`,`indice`,`portata`),
  ADD KEY `tavoloord_idx` (`tavolo`),
  ADD KEY `indiceord_idx` (`indice`),
  ADD KEY `portataord_idx` (`portata`),
  ADD KEY `serataord_idx` (`serata`);
ALTER TABLE `portata`
  ADD PRIMARY KEY (`nome_portata`);
ALTER TABLE `quantitàpiattiserata`
  ADD PRIMARY KEY (`serata`,`piatto`),
  ADD KEY `piatto_idx` (`piatto`);
ALTER TABLE `responsabili`
  ADD PRIMARY KEY (`nome`);
ALTER TABLE `responsabiliserata`
  ADD PRIMARY KEY (`serata`,`tavolo`,`responsabile`,`numero_progressivo`),
  ADD KEY `fk_ResponsabiliSerata_2_idx` (`responsabile`),
  ADD KEY `fk_tavolo_resp_idx` (`tavolo`);
ALTER TABLE `serata`
  ADD PRIMARY KEY (`data`);
ALTER TABLE `tavoli`
  ADD PRIMARY KEY (`numero_tavolo`),
  ADD KEY `zonatavolo_idx` (`zona`);
ALTER TABLE `utente`
  ADD PRIMARY KEY (`username`);
ALTER TABLE `zone`
  ADD PRIMARY KEY (`zona`);
ALTER TABLE `comande`
  ADD CONSTRAINT `menfissocom` FOREIGN KEY (`menu`) REFERENCES `menu` (`nome_menu`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `respcom` FOREIGN KEY (`responsabile`) REFERENCES `responsabili` (`nome`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `seratacom` FOREIGN KEY (`serata`) REFERENCES `serata` (`data`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tavolocom` FOREIGN KEY (`tavolo`) REFERENCES `tavoli` (`numero_tavolo`) ON DELETE NO ACTION ON UPDATE CASCADE;
ALTER TABLE `composizionemenu`
  ADD CONSTRAINT `nome menu` FOREIGN KEY (`menu`) REFERENCES `menu` (`nome_menu`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `nome portata` FOREIGN KEY (`portata`) REFERENCES `portata` (`nome_portata`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `composizioneportata`
  ADD CONSTRAINT `nome materia1` FOREIGN KEY (`materia_prima`) REFERENCES `materieprime` (`nome_materia`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `nome portata1` FOREIGN KEY (`portata`) REFERENCES `portata` (`nome_portata`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `menuserata`
  ADD CONSTRAINT `data serata` FOREIGN KEY (`serata`) REFERENCES `serata` (`data`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `nome menu1` FOREIGN KEY (`menu`) REFERENCES `menu` (`nome_menu`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `ordini`
  ADD CONSTRAINT `indiceord` FOREIGN KEY (`indice`) REFERENCES `comande` (`indice`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `portataord` FOREIGN KEY (`portata`) REFERENCES `portata` (`nome_portata`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `serataord` FOREIGN KEY (`serata`) REFERENCES `comande` (`serata`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tavoloord` FOREIGN KEY (`tavolo`) REFERENCES `comande` (`tavolo`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `quantitàpiattiserata`
  ADD CONSTRAINT `fk1_serata` FOREIGN KEY (`serata`) REFERENCES `serata` (`data`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `piatto` FOREIGN KEY (`piatto`) REFERENCES `portata` (`nome_portata`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `responsabiliserata`
  ADD CONSTRAINT `fk_ResponsabiliSerata_2` FOREIGN KEY (`responsabile`) REFERENCES `responsabili` (`nome`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_serata` FOREIGN KEY (`serata`) REFERENCES `serata` (`data`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tavolo_resp` FOREIGN KEY (`tavolo`) REFERENCES `tavoli` (`numero_tavolo`) ON DELETE NO ACTION ON UPDATE CASCADE;
ALTER TABLE `tavoli`
  ADD CONSTRAINT `zonatavolo` FOREIGN KEY (`zona`) REFERENCES `zone` (`zona`) ON DELETE NO ACTION ON UPDATE CASCADE;