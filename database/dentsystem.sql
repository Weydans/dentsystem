-- -----------------------------------------------------
-- Schema dentista
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `dentsystem` DEFAULT CHARACTER SET utf8 ;
USE `dentsystem` ;

-- -----------------------------------------------------
-- Table `dentista`.`cliente`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `dentsystem`.`cliente`;
CREATE TABLE IF NOT EXISTS `dentsystem`.`cliente` (
  `cliente_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cliente_name` VARCHAR(45) NOT NULL,
  `cliente_rg` VARCHAR(8) NOT NULL,
  `cliente_cpf` VARCHAR(11) NOT NULL,
  `cliente_nascimento` DATE NOT NULL,
  `cliente_phone1` VARCHAR(11) NULL,
  `cliente_phone2` VARCHAR(11) NULL,
  `cliente_email` VARCHAR(45) NULL,
  PRIMARY KEY (`cliente_id`),
  UNIQUE INDEX `cliente_id_UNIQUE` (`cliente_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dentista`.`user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `dentsystem`.`user`;
CREATE TABLE IF NOT EXISTS `dentsystem`.`user` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_name` VARCHAR(45) NOT NULL,
  `user_email` VARCHAR(65) NOT NULL,
  `user_pass` VARCHAR(6) NOT NULL,
  `user_level` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `user_id_UNIQUE` (`user_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dentista`.`endereco`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dentsystem`.`endereco` (
  `endereco_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `endereco_cep` INT(9) UNSIGNED NULL,
  `endereco_logradouro` VARCHAR(60) NOT NULL,
  `endereco_bairro` VARCHAR(45) NULL,
  `endereco_cidade` VARCHAR(60) NOT NULL,
  `endereco_estado` VARCHAR(2) NULL,
  `endereco_pais` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`endereco_id`),
  UNIQUE INDEX `endereco_id_UNIQUE` (`endereco_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dentista`.`cliente_endereco`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dentsystem`.`cliente_endereco` (
  `cliente_endereco_numero` INT UNSIGNED NOT NULL,
  `cliente_endereco_complemento` VARCHAR(45),
  `cliente_id` INT UNSIGNED NOT NULL,
  `endereco_id` INT UNSIGNED NOT NULL)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dentista`.`user_endereco`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dentsystem`.`user_endereco` (
  `user_endereco_numero` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `endereco_id` INT UNSIGNED NOT NULL
)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dentista`.`procedimento`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dentsystem`.`procedimento` (
  `procedimento_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `procedimento_name` VARCHAR(45) NOT NULL,
  `procedimento_desc` TEXT NOT NULL,
  `procedimento_valor` DECIMAL UNSIGNED NOT NULL,
  PRIMARY KEY (`procedimento_id`),
  UNIQUE INDEX `procedimento_id_UNIQUE` (`procedimento_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dentista`.`servico`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dentsystem`.`servico` (
  `servico_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `servico_date` TIMESTAMP NOT NULL,
  `servico_desconto` INT NULL,
  `servico_total` DECIMAL UNSIGNED NOT NULL,
  PRIMARY KEY (`servico_id`),
  UNIQUE INDEX `servico_id_UNIQUE` (`servico_id` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dentista`.`servico_procedimento`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dentsystem`.`servico_procedimento` (
  `servico_id` INT UNSIGNED NOT NULL,
  `procedimento_id` INT UNSIGNED NOT NULL)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dentista`.`agenda`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `dentsystem`.`agenda`;

CREATE TABLE IF NOT EXISTS `dentsystem`.`agenda` (
  `agenda_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `agenda_cliente_id` INT UNSIGNED NOT NULL,
  `agenda_servico_id` INT UNSIGNED NOT NULL,
  `agenda_dia` DATE NOT NULL,
  `agenda_hora` TIME NOT NULL,
  `agenda_servico_desc` VARCHAR(256) NOT NULL,
  PRIMARY KEY (`agenda_id`),
  UNIQUE INDEX `agenda_id_UNIQUE` (`agenda_id` ASC))
ENGINE = InnoDB;
