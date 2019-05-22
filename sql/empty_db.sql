
CREATE DATABASE IF NOT EXISTS gestionale_;


CREATE TABLE IF NOT EXISTS comande (
  serata date NOT NULL,
  tavolo int(10) unsigned NOT NULL,
  indice int(10) unsigned NOT NULL,
  menu varchar(50) NOT NULL,
  responsabile varchar(50) NOT NULL,
  numero_soci int(10) unsigned DEFAULT 0,
  pagata tinyint(1) NOT NULL DEFAULT 1,
  attiva tinyint(1) NOT NULL DEFAULT 1,
  num_comanda int(11) DEFAULT NULL,
  data_creazione timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  data_aggiornamento timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  annotazioni text,
  sconto_manuale decimal(5,2) unsigned DEFAULT 0.00,
  conto_inviato tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (serata,tavolo,indice),
  KEY tavolocom_idx (tavolo),
  KEY seratacom_idx (serata),
  KEY indicecom_idx (indice),
  KEY menfissocom_idx (menu),
  KEY respcom_idx (responsabile),
  CONSTRAINT menfissocom FOREIGN KEY (menu) REFERENCES menu (nome_menu) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT respcom FOREIGN KEY (responsabile) REFERENCES responsabili (nome) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT seratacom FOREIGN KEY (serata) REFERENCES serata (data) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT tavolocom FOREIGN KEY (tavolo) REFERENCES tavoli (numero_tavolo) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS composizionemenu (
  menu varchar(50) NOT NULL,
  portata varchar(100) NOT NULL,
  data_creazione timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  data_aggiornamento timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (menu,portata),
  KEY nome portata_idx (portata),
  CONSTRAINT nome menu FOREIGN KEY (menu) REFERENCES menu (nome_menu) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT nome portata FOREIGN KEY (portata) REFERENCES portata (nome_portata) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS composizioneportata (
  portata varchar(100) NOT NULL,
  materia_prima varchar(40) NOT NULL,
  peso decimal(6,3) NOT NULL,
  data_creazione timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  data_aggiornamento timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (portata,materia_prima),
  KEY nome materia1_idx (materia_prima),
  CONSTRAINT nome materia1 FOREIGN KEY (materia_prima) REFERENCES materieprime (nome_materia) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT nome portata1 FOREIGN KEY (portata) REFERENCES portata (nome_portata) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS materieprime (
  nome_materia varchar(40) NOT NULL,
  genere varchar(45) NOT NULL,
  data_creazione timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  data_aggiornamento timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (nome_materia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS menu (
  nome_menu varchar(50) NOT NULL,
  data_creazione timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  data_aggiornamento timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  fisso tinyint(1) NOT NULL DEFAULT 0,
  prezzo_fisso decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (nome_menu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS menuserata (
  menu varchar(50) NOT NULL,
  serata date NOT NULL,
  data_creazione timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  data_aggiornamento timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (menu,serata),
  KEY data serata_idx (serata),
  CONSTRAINT data serata FOREIGN KEY (serata) REFERENCES serata (data) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT nome menu1 FOREIGN KEY (menu) REFERENCES menu (nome_menu) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ordini (
  quantita int(11) NOT NULL,
  data_creazione timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  data_aggiornamento timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  serata date NOT NULL,
  tavolo int(10) unsigned NOT NULL,
  indice int(10) unsigned NOT NULL,
  portata varchar(100) NOT NULL,
  PRIMARY KEY (serata,tavolo,indice,portata),
  KEY tavoloord_idx (tavolo),
  KEY indiceord_idx (indice),
  KEY portataord_idx (portata),
  KEY serataord_idx (serata),
  CONSTRAINT indiceord FOREIGN KEY (indice) REFERENCES comande (indice) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT portataord FOREIGN KEY (portata) REFERENCES portata (nome_portata) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT serataord FOREIGN KEY (serata) REFERENCES comande (serata) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT tavoloord FOREIGN KEY (tavolo) REFERENCES comande (tavolo) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS portata (
  nome_portata varchar(100) NOT NULL,
  categoria enum(bevanda,piadina,bruschette e crostoni,pane e coperto,antipasto,primo,secondo,contorno,dolce) NOT NULL,
  prezzo_finale decimal(5,2) NOT NULL,
  data_creazione timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  data_aggiornamento timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  id int(11) NOT NULL,
  PRIMARY KEY (nome_portata)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS prenotazioni (
  serata date NOT NULL,
  tavoli int(11) NOT NULL,
  coperti int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS quantitàpiattiserata (
  serata date NOT NULL,
  piatto varchar(100) NOT NULL,
  quantità int(11) NOT NULL,
  data_creazione timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  data_aggiornamento timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (serata,piatto),
  KEY piatto_idx (piatto),
  CONSTRAINT fk1_serata FOREIGN KEY (serata) REFERENCES serata (data) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT piatto FOREIGN KEY (piatto) REFERENCES portata (nome_portata) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS responsabili (
  nome varchar(50) NOT NULL,
  data_creazione timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  data_aggiornamento timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS responsabiliserata (
  serata date NOT NULL,
  tavolo int(10) unsigned NOT NULL,
  responsabile varchar(50) NOT NULL,
  numero_progressivo int(11) NOT NULL,
  data_creazione timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  data_aggiornamento timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (serata,tavolo,responsabile,numero_progressivo),
  KEY fk_ResponsabiliSerata_2_idx (responsabile),
  KEY fk_tavolo_resp_idx (tavolo),
  CONSTRAINT fk_ResponsabiliSerata_2 FOREIGN KEY (responsabile) REFERENCES responsabili (nome) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT fk_serata FOREIGN KEY (serata) REFERENCES serata (data) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_tavolo_resp FOREIGN KEY (tavolo) REFERENCES tavoli (numero_tavolo) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS serata (
  data date NOT NULL,
  descrizione varchar(100) DEFAULT NULL,
  data_creazione timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  data_aggiornamento timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  inizializzata tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (data)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS tavoli (
  numero_tavolo int(10) unsigned NOT NULL,
  zona varchar(50) NOT NULL,
  posti int(10) unsigned NOT NULL,
  data_creazione timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  data_aggiornamento timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (numero_tavolo),
  KEY zonatavolo_idx (zona),
  CONSTRAINT zonatavolo FOREIGN KEY (zona) REFERENCES zone (zona) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS utente (
  username varchar(20) NOT NULL,
  password char(32) NOT NULL,
  gruppo enum(amm,mag,cam) NOT NULL,
  nome varchar(45) NOT NULL,
  cognome varchar(45) NOT NULL,
  email varchar(75) NOT NULL,
  tel_fisso varchar(20) DEFAULT NULL,
  tel_mobile varchar(20) DEFAULT NULL,
  foto varchar(45) DEFAULT NULL,
  PRIMARY KEY (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS zone (
  zona varchar(50) NOT NULL,
  data_creazione timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  data_aggiornamento timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (zona)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO utente (username, password, gruppo, nome, cognome, email, tel_fisso, tel_mobile, foto) VALUES
(daniele, 47477de04d78d5a360c5fda47a986955, amm, Daniele, Binucci, , NULL, NULL, NULL),
(pier_ferri, 201f00b5ca5d65a1c118e5e32431514c, amm, Piergiorgio, Ferri, , NULL, NULL, NULL); 

