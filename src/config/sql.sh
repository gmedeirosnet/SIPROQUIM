-- Criação da tabela de Pessoas
CREATE TABLE pessoas (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(100),
  data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Criação da tabela de Grupos de Produtos
CREATE TABLE grupos (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  descricao TEXT
);

-- Criação da tabela de Produtos
CREATE TABLE produtos (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  id_grupo INTEGER REFERENCES grupos(id) ON DELETE SET NULL,
  preco NUMERIC(10,2),
  data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Criação da tabela de Lugares de Estoque
CREATE TABLE lugares (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  descricao TEXT
);

-- Criação da tabela de Movimentações
CREATE TABLE movimentos (
  id SERIAL PRIMARY KEY,
  id_produto INTEGER REFERENCES produtos(id) ON DELETE CASCADE,
  id_pessoa INTEGER REFERENCES pessoas(id) ON DELETE CASCADE,
  id_lugar INTEGER REFERENCES lugares(id) ON DELETE CASCADE,
  tipo VARCHAR(10) NOT NULL, -- 'entrada' ou 'saida'
  quantidade INTEGER NOT NULL,
  data_movimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
