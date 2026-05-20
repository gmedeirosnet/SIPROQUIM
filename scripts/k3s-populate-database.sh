#!/bin/bash
set -e

NAMESPACE="siproquim"
POD="statefulset/postgres"
DB_USER="admin"
DB_NAME="estoque"
SCRIPTS_DIR="$(dirname "$0")"

echo "Populating database in K3S namespace '${NAMESPACE}'..."
echo ""

echo "0/3 - Initializing database schema..."
kubectl exec -i -n "${NAMESPACE}" "${POD}" -- psql -U "${DB_USER}" -d "${DB_NAME}" <<'EOSQL'
CREATE TABLE IF NOT EXISTS grupos_pessoas (id SERIAL PRIMARY KEY, nome VARCHAR(100) NOT NULL, descricao TEXT);
INSERT INTO grupos_pessoas (nome, descricao) SELECT 'Usuários', 'Grupo padrão de usuários' WHERE NOT EXISTS (SELECT 1 FROM grupos_pessoas);
CREATE TABLE IF NOT EXISTS pessoas (id SERIAL PRIMARY KEY, nome VARCHAR(100) NOT NULL, email VARCHAR(100), id_grupo_pessoa INTEGER REFERENCES grupos_pessoas(id) ON DELETE SET NULL, data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP, enable BOOLEAN DEFAULT FALSE, password VARCHAR(255));
CREATE TABLE IF NOT EXISTS grupos (id SERIAL PRIMARY KEY, nome VARCHAR(100) NOT NULL, descricao TEXT);
CREATE TABLE IF NOT EXISTS fabricantes (id SERIAL PRIMARY KEY, cnpj VARCHAR(18) NOT NULL UNIQUE, nome VARCHAR(100) NOT NULL, observacao TEXT, endereco VARCHAR(255), email VARCHAR(100), data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS produtos (id SERIAL PRIMARY KEY, nome VARCHAR(100) NOT NULL, id_grupo INTEGER REFERENCES grupos(id) ON DELETE SET NULL, id_fabricante INTEGER REFERENCES fabricantes(id) ON DELETE SET NULL, tipo VARCHAR(50), volume VARCHAR(50), unidade_medida VARCHAR(20), preco NUMERIC(10,2), descricao TEXT, data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS lugares (id SERIAL PRIMARY KEY, nome VARCHAR(100) NOT NULL, descricao TEXT);
CREATE TABLE IF NOT EXISTS movimentos (id SERIAL PRIMARY KEY, id_produto INTEGER REFERENCES produtos(id) ON DELETE CASCADE, id_pessoa INTEGER REFERENCES pessoas(id) ON DELETE CASCADE, id_lugar INTEGER REFERENCES lugares(id) ON DELETE CASCADE, tipo VARCHAR(10) NOT NULL, quantidade INTEGER NOT NULL, data_movimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP, observacao TEXT);
CREATE TABLE IF NOT EXISTS login_logs (id SERIAL PRIMARY KEY, id_pessoa INTEGER REFERENCES pessoas(id) ON DELETE CASCADE, data_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP, ip VARCHAR(45), user_agent TEXT);
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO admin;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO admin;
EOSQL

echo "1/3 - Inserting basic data and 30 people..."
kubectl exec -i -n "${NAMESPACE}" "${POD}" -- psql -U "${DB_USER}" -d "${DB_NAME}" < "${SCRIPTS_DIR}/populate_database.sql"

echo "2/3 - Generating 300 products..."
kubectl exec -i -n "${NAMESPACE}" "${POD}" -- psql -U "${DB_USER}" -d "${DB_NAME}" < "${SCRIPTS_DIR}/gerar_produtos.sql"

echo "3/3 - Generating 4,000 stock movements..."
kubectl exec -i -n "${NAMESPACE}" "${POD}" -- psql -U "${DB_USER}" -d "${DB_NAME}" < "${SCRIPTS_DIR}/gerar_movimentos.sql"

echo ""
echo "Done! Database populated with:"
echo "  - 30 people"
echo "  - 300 products"
echo "  - 4,000 stock movements"
