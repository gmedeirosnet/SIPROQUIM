# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

SIPROQUIM is a PHP 8.4/PostgreSQL 15 chemical inventory management system with Docker deployment, Terraform AWS infrastructure, and role-based access control (RBAC).

## Development Commands

### Local Development
```bash
./run.sh                    # Quick start with Docker (options: --staging, --production, --no-ssl)
docker-compose up -d        # Start all services
docker-compose down         # Stop services
docker-compose logs -f db   # Watch database logs
```

### Database Operations
```bash
./scripts/init-db.sh                    # Initialize database schema
./scripts/psql_client.sh                # Connect to PostgreSQL CLI
docker-compose exec db psql -U admin estoque  # Direct database access

# Load sample data (run from host):
docker exec -i estoque_db psql -U admin -d estoque < scripts/populate_database.sql
```

### Production Deployment (Terraform)
```bash
cd terraform
terraform init
terraform plan -out=tfplan.out
terraform apply tfplan.out
```

### Access Points
- Application: http://localhost:8080
- Database test: http://localhost:8080/test_connection.php
- Query diagnostics: http://localhost:8080/test_search.php

## Architecture

### 3-Layer Structure
1. **Configuration** (`src/config/db.php`): PDO database connection using Docker service name `db`
2. **Business Logic** (`src/cadastros/`, `src/relatorios/`): CRUD operations and reports
3. **Presentation** (`src/includes/header.php`, `src/includes/footer.php`): Server-rendered PHP templates

### Authentication & Permissions
All pages require `src/auth/auth_check.php` (except login). Permission levels in `src/auth/permissions.php`:
- Group 1 (Administrators): Full CRUD
- Groups 3,4 (Técnicos/Supervisores): Create/Read/Update
- Group 5 (Auditores): Read-only

### Standard Page Pattern
```php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/auth_check.php';
$pageTitle = 'Page Name';

requirePermission(PERMISSION_CREATE, $current_user_grupo);  // Check before operations

// Use prepared statements for all queries
$stmt = $pdo->prepare("SELECT * FROM table WHERE id = :id");
$stmt->execute(['id' => $id]);
```

## Key Conventions

### Database
- Foreign key naming: `id_produto`, `id_pessoa`, `id_grupo`, `id_lugar`, `id_fabricante`
- Case-insensitive search: Use `ILIKE` (PostgreSQL)
- Always use prepared statements with PDO parameter binding

### Forms & Validation
- Input sanitization: `trim()` all inputs
- HTML escaping: `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`
- Search parameters: `$_GET['search_produto']`, `$_GET['search_pessoa']`
- Edit mode detection: Check `$_GET['id']`

### UI
- Primary color: `#006D77` (CSS custom property in `:root`)
- Responsive tables: Wrap in `<div class="table-responsive">`
- Active nav state: `isActive()` function in header

## File Organization

| Directory | Purpose |
|-----------|---------|
| `src/cadastros/` | CRUD forms (pessoa.php, produto.php, movimento.php, list_*.php) |
| `src/relatorios/` | Reports with filtering (relatorio_estoque.php, movimentacao_*.php) |
| `src/auth/` | Authentication (auth_check.php, permissions.php, login.php) |
| `frontend/` | React frontend (separate, not integrated with PHP) |
| `scripts/` | Database and deployment utilities |
| `terraform/` | AWS infrastructure as code |
| `nginx/` | Reverse proxy and SSL configuration |

## Infrastructure

- **Docker services**: nginx (ports 80/443), php (port 8080), db (PostgreSQL 15), certbot
- **AWS**: EC2 t4g.micro, VPC with security groups, Let's Encrypt SSL
- **CI/CD**: GitHub Actions (SonarQube), Jenkins pipeline

## Git Workflow

- Main branch: `main` (production)
- Commit format: `type: description` (feat:, fix:, refactor:, docs:)
- Feature branches: `feature/feature-name` or `Feature--feature-name`
