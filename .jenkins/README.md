# Jenkins Pipeline Configuration for SIPROQUIM

This document provides instructions for setting up and configuring the Jenkins pipeline for the SIPROQUIM project.

## Prerequisites

- Jenkins 2.400+ with Pipeline plugin
- Docker installed on Jenkins agents
- Docker Compose v2.x
- Access to SonarCloud (or SonarQube server)
- Git plugin for Jenkins

## Jenkins Agent Requirements

The pipeline uses a Docker-in-Docker (DinD) agent. Ensure your Jenkins instance has:

1. **Docker plugin** installed
2. **Pipeline plugin** installed
3. **Git plugin** installed
4. **Credentials Binding plugin** installed

### Agent Configuration

The pipeline uses the `docker:24-dind` image with the following requirements:
- Privileged mode enabled
- Docker socket mounted: `/var/run/docker.sock`

## Required Jenkins Credentials

Configure the following credentials in Jenkins (Dashboard → Manage Jenkins → Credentials):

### 1. Database Credentials

| Credential ID | Type | Description | Example Value |
|--------------|------|-------------|---------------|
| `siproquim-db-host` | Secret text | Database hostname | `db` or `your-db-host` |
| `siproquim-db-name` | Secret text | Database name | `estoque` |
| `siproquim-db-user` | Secret text | Database username | `admin` |
| `siproquim-db-password` | Secret text | Database password | `your-secure-password` |
| `siproquim-db-port` | Secret text | Database port | `5432` |

### 2. SonarQube Credentials

| Credential ID | Type | Description | How to Obtain |
|--------------|------|-------------|---------------|
| `sonarqube-token` | Secret text | SonarCloud authentication token | Generate at https://sonarcloud.io/account/security |

## Pipeline Stages

The pipeline consists of the following stages:

### 1. Checkout
- Clones the repository
- Verifies Docker and Git installations

### 2. Build Docker Images
- Builds the PHP service Docker image
- Uses the existing `Dockerfile` in the project root

### 3. Code Quality - SonarQube
- Runs SonarQube code analysis
- Scans `src/` directory
- Excludes assets, build artifacts, and logs
- Publishes results to SonarCloud

### 4. Start Services
- Creates `.env` file with database credentials
- Starts Docker Compose services (db, php, nginx)
- Waits for services to become healthy

### 5. Database Initialization
- Checks if database is already initialized
- Runs `init-db.sh` script if needed
- Idempotent - safe to run multiple times

### 6. Health Check
- Verifies database connectivity
- Validates PHP service
- Tests nginx configuration

### 7. Deploy to Staging
- **Trigger**: Runs on `Improvements` branch
- Executes `run.sh --staging`
- Uses Let's Encrypt staging environment

### 8. Approval for Production
- **Trigger**: Runs on `main` branch
- Manual approval gate (1-hour timeout)
- Requires user confirmation before production deployment

### 9. Deploy to Production
- **Trigger**: Runs on `main` branch after approval
- Executes `run.sh --production`
- Uses Let's Encrypt production certificates

## Creating a Jenkins Job

### Option 1: Multibranch Pipeline (Recommended)

1. Go to Jenkins Dashboard → New Item
2. Enter name: `SIPROQUIM`
3. Select **Multibranch Pipeline**
4. Click **OK**

#### Branch Sources Configuration

1. **Add source** → Git
2. **Project Repository**: `https://github.com/gmedeirosnet/SIPROQUIM.git`
3. **Credentials**: Add GitHub credentials if private repository
4. **Behaviors** → Add:
   - Discover branches
   - Filter by name (with regular expression): `(main|Improvements)`

#### Build Configuration

1. **Mode**: by Jenkinsfile
2. **Script Path**: `Jenkinsfile`

#### Scan Multibranch Pipeline Triggers

1. Check **Periodically if not otherwise run**
2. **Interval**: 5 minutes

### Option 2: Pipeline Job

1. Go to Jenkins Dashboard → New Item
2. Enter name: `SIPROQUIM-Pipeline`
3. Select **Pipeline**
4. Click **OK**

#### Pipeline Configuration

1. **Definition**: Pipeline script from SCM
2. **SCM**: Git
3. **Repository URL**: `https://github.com/gmedeirosnet/SIPROQUIM.git`
4. **Branch Specifier**: `*/main` or `*/Improvements`
5. **Script Path**: `Jenkinsfile`

#### Build Triggers

- Check **Poll SCM**
- **Schedule**: `H/5 * * * *` (every 5 minutes)

## Environment-Specific Configuration

### Staging Environment
- **Branch**: `Improvements`
- **SSL Mode**: Let's Encrypt staging
- **Auto-deploy**: Yes (no approval required)
- **Domain**: Configure in `run.sh` or nginx configuration

### Production Environment
- **Branch**: `main`
- **SSL Mode**: Let's Encrypt production
- **Auto-deploy**: No (requires manual approval)
- **Domain**: `siproquim.gmedeiros.net`

## Post-Build Actions

The pipeline automatically:

1. **Archives artifacts**: Docker Compose logs stored in `build-logs/`
2. **Cleanup**: Removes temporary `.env` file
3. **Container management**: Stops containers but preserves volumes

## Troubleshooting

### Database Connection Issues

If database initialization fails:

```bash
# Check database logs
docker compose logs db

# Verify database is running
docker compose ps db

# Test connection manually
docker compose exec db pg_isready -U admin
```

### Docker Permission Issues

If you encounter permission errors:

```bash
# Ensure Jenkins user is in docker group
sudo usermod -aG docker jenkins
sudo systemctl restart jenkins
```

### SonarQube Analysis Failures

If SonarQube analysis fails:

1. Verify `sonarqube-token` credential is correctly configured
2. Check SonarCloud project exists: `SIPROQUIM`
3. Verify organization: `gmedeirosnet`
4. Check network connectivity to `https://sonarcloud.io`

### SSL Certificate Issues

If SSL provisioning fails:

```bash
# Check certbot logs
docker compose logs certbot

# Verify domain DNS points to server
nslookup siproquim.gmedeiros.net

# Use staging mode first
./run.sh --staging
```

## Pipeline Customization

### Adding Test Stages

To add testing stages in the future (currently no tests exist):

```groovy
stage('Unit Tests') {
    steps {
        echo 'Running PHPUnit tests...'
        sh 'docker compose exec -T php vendor/bin/phpunit'
    }
}

stage('Frontend Tests') {
    steps {
        echo 'Running Jest tests...'
        sh 'cd frontend && npm test'
    }
}
```

### Adding Notifications

Add to `post` section:

```groovy
post {
    success {
        emailext(
            subject: "Pipeline Success: ${env.JOB_NAME} #${env.BUILD_NUMBER}",
            body: "Build successful!",
            to: "team@example.com"
        )
    }
    failure {
        emailext(
            subject: "Pipeline Failed: ${env.JOB_NAME} #${env.BUILD_NUMBER}",
            body: "Build failed. Check logs.",
            to: "team@example.com"
        )
    }
}
```

## Security Best Practices

1. **Never commit credentials** to the repository
2. **Use Jenkins credentials** for all sensitive data
3. **Rotate credentials** regularly
4. **Limit pipeline permissions** using Jenkins role-based access control
5. **Review approval gates** for production deployments
6. **Enable audit logging** in Jenkins

## Monitoring and Logs

### Viewing Pipeline Logs

1. Go to Jenkins Dashboard
2. Click on the job name
3. Select the build number
4. Click **Console Output**

### Archived Artifacts

Build logs are archived and can be accessed:
1. Go to the build page
2. Click **Build Artifacts**
3. Download `docker-compose.log`

## Further Enhancements

### Future Improvements (Not in Minimal Implementation)

1. **Testing**:
   - Add PHPUnit for PHP backend tests
   - Add Jest for React frontend tests
   - Add Cypress for E2E tests

2. **Database Migrations**:
   - Implement idempotent migration scripts
   - Version control for schema changes
   - Rollback capabilities

3. **Deployment Strategies**:
   - Blue-green deployment
   - Canary releases
   - Docker Swarm or Kubernetes orchestration

4. **Monitoring**:
   - Prometheus metrics
   - Grafana dashboards
   - Application performance monitoring (APM)

5. **Security Scanning**:
   - OWASP Dependency Check
   - Trivy container scanning
   - SAST/DAST integration

## Support

For issues or questions:
- **Project**: https://github.com/gmedeirosnet/SIPROQUIM
- **Security**: security@gmedeiros.net
- **Documentation**: See `README.md` in the repository

## Version History

- **v1.0.0** (November 2025): Initial Jenkins pipeline implementation
  - Basic build and deploy stages
  - SonarQube integration
  - Staging/production deployment workflow
  - Docker-based CI/CD
