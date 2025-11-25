pipeline {
    agent {
        docker {
            image 'docker:24-dind'
            args '--privileged -v /var/run/docker.sock:/var/run/docker.sock'
        }
    }

    environment {
        // Database Configuration
        DB_HOST = credentials('siproquim-db-host')
        DB_NAME = credentials('siproquim-db-name')
        DB_USER = credentials('siproquim-db-user')
        DB_PASSWORD = credentials('siproquim-db-password')
        DB_PORT = credentials('siproquim-db-port')

        // SonarQube Configuration
        SONAR_TOKEN = credentials('sonarqube-token')
        SONAR_HOST_URL = 'https://sonarcloud.io'

        // Deployment Configuration
        DEPLOY_ENVIRONMENT = "${env.BRANCH_NAME == 'main' ? 'production' : 'staging'}"
    }

    options {
        buildDiscarder(logRotator(numToKeepStr: '10'))
        timestamps()
        timeout(time: 30, unit: 'MINUTES')
    }

    triggers {
        // Trigger on SCM changes
        pollSCM('H/5 * * * *')
    }

    stages {
        stage('Checkout') {
            steps {
                echo "Checking out branch: ${env.BRANCH_NAME}"
                checkout scm
                sh 'git --version'
                sh 'docker --version'
                sh 'docker compose version'
            }
        }

        stage('Build Docker Images') {
            steps {
                echo 'Building Docker images...'
                script {
                    sh '''
                        docker compose build --no-cache php
                        docker images | grep siproquim || true
                    '''
                }
            }
        }

        stage('Code Quality - SonarQube') {
            steps {
                echo 'Running SonarQube analysis...'
                script {
                    sh '''
                        docker run --rm \
                            -e SONAR_HOST_URL="${SONAR_HOST_URL}" \
                            -e SONAR_TOKEN="${SONAR_TOKEN}" \
                            -v "${WORKSPACE}:/usr/src" \
                            sonarsource/sonar-scanner-cli \
                            -Dsonar.projectKey=SIPROQUIM \
                            -Dsonar.organization=gmedeirosnet \
                            -Dsonar.sources=src \
                            -Dsonar.exclusions=src/assets/**,frontend/build/**,logs/**
                    '''
                }
            }
        }

        stage('Start Services') {
            steps {
                echo 'Starting Docker Compose services...'
                script {
                    sh '''
                        # Create .env file for Docker Compose
                        cat > .env << EOF
DB_HOST=${DB_HOST}
DB_NAME=${DB_NAME}
DB_USER=${DB_USER}
DB_PASSWORD=${DB_PASSWORD}
DB_PORT=${DB_PORT}
EOF

                        # Start services
                        docker compose up -d db php nginx

                        # Wait for services to be healthy
                        echo "Waiting for database to be ready..."
                        for i in {1..30}; do
                            if docker compose exec -T db pg_isready -U ${DB_USER}; then
                                echo "Database is ready!"
                                break
                            fi
                            echo "Waiting... ($i/30)"
                            sleep 2
                        done

                        echo "Waiting for PHP service to be ready..."
                        sleep 10

                        # Show running containers
                        docker compose ps
                    '''
                }
            }
        }

        stage('Database Initialization') {
            steps {
                echo 'Initializing database...'
                script {
                    sh '''
                        # Check if database is already initialized
                        TABLE_COUNT=$(docker compose exec -T db psql -U ${DB_USER} -d ${DB_NAME} -t -c "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public';" 2>/dev/null || echo "0")

                        if [ "$TABLE_COUNT" -lt 5 ]; then
                            echo "Database not initialized. Running init-db.sh..."
                            docker compose exec -T db bash /docker-entrypoint-initdb.d/init-db.sh
                        else
                            echo "Database already initialized. Skipping..."
                        fi
                    '''
                }
            }
        }

        stage('Health Check') {
            steps {
                echo 'Performing health checks...'
                script {
                    sh '''
                        # Check database connectivity
                        docker compose exec -T db pg_isready -U ${DB_USER} || exit 1

                        # Check PHP service
                        docker compose exec -T php php -v || exit 1

                        # Check nginx
                        docker compose exec -T nginx nginx -t || exit 1

                        echo "All health checks passed!"
                    '''
                }
            }
        }

        stage('Deploy to Staging') {
            when {
                branch 'Improvements'
            }
            steps {
                echo 'Deploying to staging environment...'
                script {
                    sh '''
                        # Run deployment with staging SSL
                        chmod +x run.sh
                        ./run.sh --staging

                        echo "Staging deployment completed!"
                    '''
                }
            }
        }

        stage('Approval for Production') {
            when {
                branch 'main'
            }
            steps {
                timeout(time: 1, unit: 'HOURS') {
                    input message: 'Deploy to Production?', ok: 'Deploy'
                }
            }
        }

        stage('Deploy to Production') {
            when {
                branch 'main'
            }
            steps {
                echo 'Deploying to production environment...'
                script {
                    sh '''
                        # Run deployment with production SSL
                        chmod +x run.sh
                        ./run.sh --production

                        # Verify deployment
                        sleep 5
                        docker compose ps

                        echo "Production deployment completed!"
                    '''
                }
            }
        }
    }

    post {
        always {
            echo 'Cleaning up...'
            script {
                sh '''
                    # Collect logs for artifacts
                    mkdir -p build-logs
                    docker compose logs --no-color > build-logs/docker-compose.log 2>&1 || true

                    # Clean up .env file
                    rm -f .env
                '''

                // Archive logs
                archiveArtifacts artifacts: 'build-logs/*.log', allowEmptyArchive: true
            }
        }

        success {
            echo 'Pipeline completed successfully!'
            // Optional: Add notification (email, Slack, etc.)
        }

        failure {
            echo 'Pipeline failed!'
            script {
                sh 'docker compose logs || true'
            }
            // Optional: Add notification (email, Slack, etc.)
        }

        cleanup {
            echo 'Final cleanup...'
            script {
                sh '''
                    # Stop containers but keep volumes for next run
                    docker compose down || true
                '''
            }
        }
    }
}
