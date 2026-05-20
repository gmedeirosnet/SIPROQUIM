#!/bin/bash
set -e

NAMESPACE="siproquim"
POD="statefulset/postgres"
DB_USER="admin"
DB_NAME="estoque"
SCRIPTS_DIR="$(dirname "$0")"

echo "Populating database in K3S namespace '${NAMESPACE}'..."
echo ""

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
