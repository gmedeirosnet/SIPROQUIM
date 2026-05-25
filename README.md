# SIPROQUIM — Sistema de Controle de Produtos Químicos

SIPROQUIM is a web-based chemical inventory management system built with **PHP 8.4** and **PostgreSQL 15**. It gives organizations full traceability of chemical products across storage locations, with role-based access control and detailed movement history.

---

## Features

### Inventory Management
- Register and track chemical products with manufacturer, group, and storage location
- Record **entries and exits** with quantity, date, and responsible person
- Real-time stock balance per product and location
- Low-stock visual indicators on reports

### People & Access Control
- Role-based access control (RBAC) with four permission levels:
  - **Administrators** — full CRUD access
  - **Técnicos / Supervisores** — create, read, update
  - **Auditores** — read-only
- User and group management with enable/disable controls

### Reporting
- **Current stock report** — balance per product grouped by storage location
- **Movement history** — filterable log of all entries and exits
- **Products by location** — what is stored where
- **Movement by product** — full lifecycle of a specific chemical
- **Movement by person** — audit trail per responsible user

### Master Data (Cadastros)
- Products, product groups, and manufacturers
- People and people groups
- Storage locations (almoxarifados)
- All list views include search, pagination, and inline filters

### Security
- All queries use **PDO prepared statements** — SQL injection is not possible
- Input sanitization (`trim`, `htmlspecialchars`) on every form
- Session-based authentication with `session.cookie_secure`
- HTTP security headers: CSP, HSTS, X-Content-Type-Options
- Least-privilege Docker volume permissions

### Infrastructure & Deployment
- **Docker Compose** stack: nginx, PHP 8.4 Apache, PostgreSQL 15, Certbot
- **Kubernetes / K3S** manifests with ArgoCD GitOps sync (`manifests/siproquim.yaml`)
- **Terraform** (AWS) — EC2 t4g.micro, VPC, security groups, Elastic IP, Let's Encrypt SSL
- Multi-platform Docker images (`linux/amd64` + `linux/arm64`) published to Docker Hub

### CI/CD
- **GitHub Actions — SonarQube** — static analysis on every push and pull request
- **GitHub Actions — CD** — triggered on merge to `main`: auto-bumps semantic version, builds and pushes Docker image, updates the Kubernetes manifest
- **CodeQL** — automated security scanning on every push

---

## Quick Start

```bash
git clone https://github.com/gmedeirosnet/SIPROQUIM.git
cd SIPROQUIM
./run.sh          # starts nginx + PHP + PostgreSQL via Docker Compose
```

Access the application at **http://localhost:8080**

---

## Requirements

| Component | Version |
|---|---|
| PHP | 8.4+ |
| PostgreSQL | 15 |
| Docker & Docker Compose | any recent |
| Terraform | 1.5+ (production only) |

---

## Project Structure

```
SIPROQUIM/
├── src/
│   ├── cadastros/       # CRUD forms and list pages
│   ├── relatorios/      # Reports
│   ├── auth/            # Authentication and permissions
│   ├── config/          # Database connection
│   └── includes/        # Shared header/footer
├── manifests/           # Kubernetes manifests (K3S / ArgoCD)
├── terraform/           # AWS infrastructure as code
├── nginx/               # Reverse proxy and SSL configuration
├── scripts/             # Database init and deployment utilities
├── .github/workflows/   # GitHub Actions (SonarQube, CD, CodeQL)
└── docker-compose.yml
```

---

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Commit your changes: `git commit -m 'feat: add my feature'`
4. Push and open a Pull Request against `main`

See `SECURITY.md` for security guidelines before submitting.

---

## License

MIT — see [LICENSE](LICENSE) for details.
