-- Banco de dados: filterdocs

CREATE DATABASE IF NOT EXISTS filterdocs;
USE filterdocs;

-- --------------------------------------------------------
-- Estrutura da tabela filterdocs.blacklist

CREATE TABLE filterdocs.blacklist (
  id int(11) NOT NULL AUTO_INCREMENT,
  nome varchar(50) NOT null,
  telefone varchar(20) NOT NULL,
  telefone1 varchar(20),
  telefone2 varchar(20),
  telefone3 varchar(20),
  email varchar(50),
  dt_inclusao datetime,
  PRIMARY KEY (`id`)
);