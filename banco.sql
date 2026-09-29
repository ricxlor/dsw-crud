-- Script de criação do banco de dados do CRUD de alunos
-- utf8mb4 aceita todos os caracteres (acentos, emojis etc.)

-- Informa ao MySQL que este arquivo está em UTF-8 (senão os acentos são corrompidos)
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS crud_recuperacao
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE crud_recuperacao;

DROP TABLE IF EXISTS alunos;

CREATE TABLE alunos (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nome      VARCHAR(100) NOT NULL,
    email     VARCHAR(100) NOT NULL,
    curso     VARCHAR(100) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Registros de exemplo para a demonstração
INSERT INTO alunos (nome, email, curso) VALUES
    ('Ana Souza',       'ana.souza@email.com',       'Análise e Desenvolvimento de Sistemas'),
    ('Bruno Lima',      'bruno.lima@email.com',      'Licenciatura em Química'),
    ('Carla Mendes',    'carla.mendes@email.com',    'Análise e Desenvolvimento de Sistemas'),
    ('Diego Ferreira',  'diego.ferreira@email.com',  'Engenharia Elétrica'),
    ('Eduarda Rocha',   'eduarda.rocha@email.com',   'Técnico em Informática');
