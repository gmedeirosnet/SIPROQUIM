#!/bin/bash
set -e

.psql_client.sh

POSTGRES_DB=estoque
POSTGRES_USER=admin
PGPASSWORD=password
POSTGRES_HOST=localhost
POSTGRES_PORT=5432
POSTGRES_URL="postgresql://$POSTGRES_USER:$PGPASSWORD@$POSTGRES_HOST:$POSTGRES_PORT/$POSTGRES_DB"
export PGPASSWORD=$PGPASSWORD
export PGUSER=$POSTGRES_USER
export PGHOST=$POSTGRES_HOST
export PGPORT=$POSTGRES_PORT
export PGDATABASE=$POSTGRES_DB
export PGURL=$POSTGRES_URL
export PGPASSFILE=/tmp/.pgpass
export PGPASS=/tmp/.pgpass
export PGPASS_CONTENT="$POSTGRES_HOST:$POSTGRES_PORT:$POSTGRES_DB:$POSTGRES_USER:$PGPASSWORD"
echo "$PGPASS_CONTENT" > $PGPASSFILE
chmod 600 $PGPASSFILE
## This script creates the database and necessary tables for the Estoque application

/usr/bin/psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    -- Ensure estoque user exists
    DO \$\$
    BEGIN
        IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'estoque') THEN
            CREATE USER estoque WITH PASSWORD 'suasenha';
        END IF;
    END
    \$\$;

    -- Grant privileges
    GRANT ALL PRIVILEGES ON DATABASE estoque TO estoque;
    GRANT ALL PRIVILEGES ON DATABASE estoque TO admin;

    -- Switch to estoque database
    \c estoque;

    -- Grant schema privileges
    GRANT ALL ON SCHEMA public TO estoque;
    GRANT ALL ON SCHEMA public TO admin;

    -- Criação da tabela de Grupos de Pessoas
    CREATE TABLE IF NOT EXISTS grupos_pessoas (
      id SERIAL PRIMARY KEY,
      nome VARCHAR(100) NOT NULL,
      descricao TEXT
    );

    -- Inserir o grupo padrão "Usuários" se a tabela estiver vazia
    INSERT INTO grupos_pessoas (nome, descricao)
    SELECT 'Usuários', 'Grupo padrão de usuários'
    WHERE NOT EXISTS (SELECT 1 FROM grupos_pessoas);

    -- Criação da tabela de Pessoas
    CREATE TABLE IF NOT EXISTS pessoas (
      id SERIAL PRIMARY KEY,
      nome VARCHAR(100) NOT NULL,
      email VARCHAR(100),
      id_grupo_pessoa INTEGER REFERENCES grupos_pessoas(id) ON DELETE SET NULL,
      data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      enable BOOLEAN DEFAULT FALSE,
      password VARCHAR(255)
    );

    -- Adicionar o campo enable se a tabela já existir
    DO \$\$
    BEGIN
        IF EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'pessoas') THEN
            IF NOT EXISTS (SELECT FROM information_schema.columns WHERE table_name = 'pessoas' AND column_name = 'enable') THEN
                ALTER TABLE pessoas ADD COLUMN enable BOOLEAN DEFAULT TRUE;
            END IF;
        END IF;
    END
    \$\$;

    -- Atualizar pessoas existentes para usar o grupo padrão, se necessário
    DO \$\$
    BEGIN
        IF EXISTS (
            SELECT 1 FROM pessoas
            WHERE id_grupo_pessoa IS NULL
        ) THEN
            UPDATE pessoas
            SET id_grupo_pessoa = (SELECT id FROM grupos_pessoas WHERE nome = 'Usuários')
            WHERE id_grupo_pessoa IS NULL;
        END IF;
    END
    \$\$;

    -- Criação da tabela de Grupos de Produtos
    CREATE TABLE IF NOT EXISTS grupos (
      id SERIAL PRIMARY KEY,
      nome VARCHAR(100) NOT NULL,
      descricao TEXT
    );

    -- Criação da tabela de Fabricantes
    CREATE TABLE IF NOT EXISTS fabricantes (
      id SERIAL PRIMARY KEY,
      cnpj VARCHAR(18) NOT NULL UNIQUE,
      nome VARCHAR(100) NOT NULL,
      observacao TEXT,
      endereco VARCHAR(255),
      email VARCHAR(100),
      data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Criação da tabela de Produtos (atualizada para usar fabricantes)
    CREATE TABLE IF NOT EXISTS produtos (
      id SERIAL PRIMARY KEY,
      nome VARCHAR(100) NOT NULL,
      id_grupo INTEGER REFERENCES grupos(id) ON DELETE SET NULL,
      id_fabricante INTEGER REFERENCES fabricantes(id) ON DELETE SET NULL,
      tipo VARCHAR(50),
      volume VARCHAR(50),
      unidade_medida VARCHAR(20),
      preco NUMERIC(10,2),
      descricao TEXT,
      data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Adiciona as novas colunas se a tabela já existir
    DO \$\$
    BEGIN
        IF EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'produtos') THEN
            IF NOT EXISTS (SELECT FROM information_schema.columns WHERE table_name = 'produtos' AND column_name = 'id_fabricante') THEN
                ALTER TABLE produtos ADD COLUMN id_fabricante INTEGER REFERENCES fabricantes(id) ON DELETE SET NULL;
            END IF;

            IF NOT EXISTS (SELECT FROM information_schema.columns WHERE table_name = 'produtos' AND column_name = 'fabricante') THEN
                ALTER TABLE produtos ADD COLUMN fabricante VARCHAR(100);
            END IF;

            IF NOT EXISTS (SELECT FROM information_schema.columns WHERE table_name = 'produtos' AND column_name = 'tipo') THEN
                ALTER TABLE produtos ADD COLUMN tipo VARCHAR(50);
            END IF;

            IF NOT EXISTS (SELECT FROM information_schema.columns WHERE table_name = 'produtos' AND column_name = 'volume') THEN
                ALTER TABLE produtos ADD COLUMN volume VARCHAR(50);
            END IF;

            IF NOT EXISTS (SELECT FROM information_schema.columns WHERE table_name = 'produtos' AND column_name = 'unidade_medida') THEN
                ALTER TABLE produtos ADD COLUMN unidade_medida VARCHAR(20);
            END IF;

            IF NOT EXISTS (SELECT FROM information_schema.columns WHERE table_name = 'produtos' AND column_name = 'descricao') THEN
                ALTER TABLE produtos ADD COLUMN descricao TEXT;
            END IF;
        END IF;
    END
    \$\$;

    -- Criação da tabela de Lugares de Estoque
    CREATE TABLE IF NOT EXISTS lugares (
      id SERIAL PRIMARY KEY,
      nome VARCHAR(100) NOT NULL,
      descricao TEXT
    );

    -- Criação da tabela de Movimentações
    CREATE TABLE IF NOT EXISTS movimentos (
      id SERIAL PRIMARY KEY,
      id_produto INTEGER REFERENCES produtos(id) ON DELETE CASCADE,
      id_pessoa INTEGER REFERENCES pessoas(id) ON DELETE CASCADE,
      id_lugar INTEGER REFERENCES lugares(id) ON DELETE CASCADE,
      tipo VARCHAR(10) NOT NULL, -- 'entrada' ou 'saida'
      quantidade INTEGER NOT NULL,
      data_movimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      observacao TEXT
    );

    -- Adiciona a nova coluna 'observacao' se a tabela já existir
    DO \$\$
    BEGIN
        IF EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'movimentos') THEN
            IF NOT EXISTS (SELECT FROM information_schema.columns WHERE table_name = 'movimentos' AND column_name = 'observacao') THEN
                ALTER TABLE movimentos ADD COLUMN observacao TEXT;
            END IF;
        END IF;
    END
    \$\$;

    -- Criação da tabela de logs de login
    CREATE TABLE IF NOT EXISTS login_logs (
      id SERIAL PRIMARY KEY,
      id_pessoa INTEGER REFERENCES pessoas(id) ON DELETE CASCADE,
      data_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      ip VARCHAR(45),
      user_agent TEXT
    );
EOSQL

# Grant permissions after table creation
/usr/bin/psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "estoque" <<-EOSQL
    -- Grant permissions on all tables
    GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO estoque;
    GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO estoque;
    GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO admin;
    GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO admin;

    -- Set ownership
    ALTER SCHEMA public OWNER TO admin;
EOSQL

echo "Database initialization completed successfully"

## Update the Database with the script: atualizar_dados.sh

echo "Iniciando atualização do banco de dados estoque..."
echo ""

# Diretório do script
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Executar o script principal de população do banco
echo "1/3 - Inserindo dados básicos e 30 pessoas..."
psql -h localhost -U admin -p 5432 -d estoque -f $DIR/populate_database.sql

# Executar script que gera 300 produtos
echo "2/3 - Gerando 300 produtos..."
psql -h localhost -U admin -p 5432 -d estoque -f $DIR/gerar_produtos.sql

# Executar script que gera 4.000 movimentações
echo "3/3 - Gerando 4.000 movimentações no período de 10/01/2025 até 10/04/2025..."
psql -h localhost -U admin -p 5432 -d estoque -f $DIR/gerar_movimentos.sql

echo ""
echo "Atualização concluída com sucesso!"
echo "- 30 pessoas cadastradas"
echo "- 300 produtos cadastrados"
echo "- 4.000 movimentações de produtos"
echo ""
echo "Agora você pode acessar o relatório de movimentações para verificar os dados."