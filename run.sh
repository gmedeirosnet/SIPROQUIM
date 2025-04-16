#!/bin/bash
cd "$(dirname "$0")"

echo "Stopping any running containers..."
docker-compose down

echo "Removing persistent database data..."
docker volume rm estoque_postgres_data

echo "Starting containers with new configuration..."
docker-compose up -d --build

echo "Waiting for services to initialize..."
sleep 5

echo "Done! Your application is available at:"
echo "- Web Application: http://localhost"
echo "- PgAdmin: http://localhost:5050 (admin@admin.com / admin)"
echo "- Connection test: http://localhost:8080/test_connection.php"
