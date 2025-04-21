#!/bin/bash
# Script para atualizar o banco de dados com as novas quantidades de dados
# - 30 pessoas
# - 300 produtos
# - 4.000 movimentações entre 10/01/2025 e 10/04/2025 (excluindo finais de semana)

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