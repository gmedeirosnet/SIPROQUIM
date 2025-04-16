# SIPROQUIM - Sistema de Controle de Produtos Químicos

## DESCRIÇÃO

Sistema para controle e gerenciamento de produtos químicos desenvolvido com PHP 8.4 e PostgreSQL 15, implementando os seguintes módulos:

- Cadastro de Pessoas e Grupos de Pessoas
- Cadastro de Produtos, Grupos de Produtos e Fabricantes
- Cadastro de Lugares de Estocagem (Almoxarifados)
- Movimentações (entradas e saídas) de Produtos
- Geração de Relatórios de Movimentações e Estoque Atual

O sistema é estruturado em camadas, separando configuração, conexão com banco de dados, operações CRUD para cada módulo e geração de relatórios. Este projeto demonstra boas práticas de modelagem relacional e implementação PHP com foco em segurança e confiabilidade.

## Requisitos

- PHP 8.4 ou superior
- PostgreSQL 15
- Docker e Docker Compose (para ambiente de desenvolvimento)
- Terraform 1.5+ (para provisionamento da infraestrutura)
- Nginx (para produção)

## Estrutura de Arquivos e Organização do projeto

```
SIPROQUIM/
├── src/                      # Código-fonte da aplicação principal
│   ├── index.php             # Página inicial/Dashboard
│   ├── test_connection.php   # Ferramenta para testar a conexão com o DB
│   ├── php.ini               # Configuração personalizada do PHP
│   ├── api/                  # Endpoints de API
│   │   └── uploads/          # Diretório para uploads de arquivos
│   ├── assets/               # Recursos estáticos
│   │   └── css/              # Folhas de estilo
│   │       └── main.css      # Estilo principal da aplicação
│   ├── cadastros/            # Formulários e operações CRUD
│   │   ├── pessoa.php        # Cadastro de pessoas
│   │   ├── grupo.php         # Cadastro de grupos de produtos
│   │   ├── grupo_pessoa.php  # Cadastro de grupos de pessoas
│   │   ├── produto.php       # Cadastro de produtos
│   │   ├── fabricante.php    # Cadastro de fabricantes
│   │   ├── lugar.php         # Cadastro de lugares de estoque
│   │   ├── movimento.php     # Registro de movimentações
│   │   └── list_*.php        # Listagens de cadastros
│   ├── config/               # Configurações da aplicação
│   │   ├── db.php            # Conexão com o banco de dados
│   │   ├── sql.sh            # Scripts SQL auxiliares
│   │   ├── cadastros/        # Configurações específicas para cadastros
│   │   └── relatorios/       # Configurações para relatórios
│   ├── includes/             # Componentes reutilizáveis
│   │   ├── header.php        # Cabeçalho comum das páginas
│   │   └── footer.php        # Rodapé comum das páginas
│   ├── relatorios/           # Geração de relatórios
│   │   ├── relatorio_estoque.php          # Relatório geral de estoque
│   │   ├── relatorio_movimentos.php       # Relatório de movimentações
│   │   ├── produtos_por_local.php         # Relatório de produtos por local
│   │   └── movimentacao_produtos.php      # Relatório de movimentação por produto
│   └── updates/              # Scripts de atualização do sistema
├── frontend/                 # Interface de usuário moderna (em desenvolvimento)
│   ├── build/                # Arquivos compilados do frontend
│   ├── public/               # Arquivos estáticos públicos
│   └── src/                  # Código-fonte do frontend
│       ├── components/       # Componentes reutilizáveis
│       ├── context/          # Contextos e estado global
│       ├── pages/            # Páginas da aplicação
│       └── services/         # Serviços e APIs
├── scripts/                  # Scripts de inicialização e utilitários
│   ├── init-db.sh            # Script para inicialização do banco de dados
│   ├── populate_database.sql # Script para popular o banco com dados iniciais
│   ├── clone_github_repo.sh  # Script para clonar repositório
│   ├── docker.sh             # Utilitários para Docker
│   ├── psql_client.sh        # Cliente PostgreSQL
│   └── siproquim             # Chaves SSH (siproquim.pub)
├── nginx/                    # Configuração do servidor web
│   ├── default.conf          # Configuração padrão do Nginx
│   ├── nginx.conf            # Configuração principal
│   └── ssl/                  # Certificados SSL/TLS
├── logs/                     # Logs de aplicação e servidor
├── certbot/                  # Configuração para certificados Let's Encrypt
│   ├── conf/                 # Configurações do Certbot
│   └── www/                  # Desafios de validação do Let's Encrypt
├── terraform/                # Arquivos para infraestrutura como código
│   ├── main.tf               # Configuração principal do Terraform
│   ├── network.tf            # Configuração de rede
│   ├── outputs.tf            # Saídas do Terraform
│   ├── variables.tf          # Variáveis configuráveis do Terraform
│   └── tfvars.tfvars         # Valores das variáveis por ambiente
├── docker-compose.yml        # Configuração dos containers Docker
├── run.sh                    # Script de execução rápida
├── README.md                 # Este arquivo
├── CHANGELOG.md              # Histórico de alterações
├── ADR.md                    # Registro de decisões arquiteturais
├── SECURITY.md               # Políticas de segurança
└── Estoque.code-workspace    # Configuração do espaço de trabalho VS Code
```

## Instalação e Execução

### Com Docker (Recomendado)

1. Certifique-se de ter o Docker e o Docker Compose instalados:
   ```bash
   docker --version
   docker-compose --version
   ```

2. Clone o repositório:
   ```bash
   git clone https://github.com/seu-usuario/siproquim.git
   cd siproquim
   ```

3. Execute o script de inicialização:
   ```bash
   ./run.sh
   ```
   Ou inicie os contêineres manualmente:
   ```bash
   docker-compose up -d
   ```

4. Acesse o sistema:
   - Aplicação Web: http://localhost:8080
   - PgAdmin (gerenciador PostgreSQL): http://localhost:5050
     - Email: admin@admin.com
     - Senha: admin

5. Para testar a conexão com o banco de dados:
   - Acesse http://localhost:8080/test_connection.php

### Instalação Manual

1. Configure um servidor web com PHP 8.4
2. Configure um servidor PostgreSQL 15
3. Execute o script `scripts/init-db.sh` para criar o banco de dados
4. Configure os parâmetros de conexão em `src/config/db.php`
5. Acesse a aplicação pelo seu servidor web

### Provisionamento com Terraform

Para provisionamento em ambientes de produção:

1. Configure as credenciais do seu provedor de nuvem
2. Navegue até o diretório `terraform/`:
   ```bash
   cd terraform
   ```
3. Inicialize o Terraform:
   ```bash
   terraform init
   ```
4. Personalize as variáveis em `variables.tf` ou crie um arquivo `terraform.tfvars`
5. O sistema já está configurado com uma chave SSH dedicada (aws@gmedeiros.net) para acesso à instância EC2. Se necessário, você pode modificar ou adicionar chaves SSH no arquivo `main.tf`
6. Valide o plano de execução:
   ```bash
   terraform plan -out=tfplan.out
   ```
7. Aplique a configuração:
   ```bash
   terraform apply tfplan.out
   ```

## Solução de Problemas de Conexão

Se encontrar problemas de conexão com o PostgreSQL:

1. Verifique se o serviço PostgreSQL está em execução:
   ```bash
   docker-compose ps
   ```

2. Acesse a página de teste de conexão:
   ```
   http://localhost:8080/test_connection.php
   ```

3. Verifique os logs do container PostgreSQL:
   ```bash
   docker-compose logs db
   ```

4. Certifique-se de que as credenciais de banco de dados estão corretas em `src/config/db.php`

5. Aguarde a inicialização completa do PostgreSQL, que pode levar alguns segundos após o início do container

## Considerações Técnicas

### Validações e Segurança:
- Prepared statements para prevenção de SQL Injection
- Validação e sanitização de dados nos formulários
- Estrutura que permite implementação futura de autenticação de usuários
- Configuração segura de contêineres Docker e infraestrutura
- Acesso SSH seguro às instâncias EC2 utilizando chaves dedicadas

### Modularização:
- Organização em diretórios funcionais
- Separação clara entre lógica de dados e apresentação
- Fácil manutenção e extensão do código

### Interface e Usabilidade:
- Interface HTML simples e funcional com CSS responsivo
- Possibilidade de integração com frameworks CSS no futuro
- Formulários validados tanto no cliente quanto no servidor
- Frontend moderno em desenvolvimento (diretório frontend/)

### Infraestrutura:
- Configuração containerizada para desenvolvimento
- Infraestrutura como código usando Terraform para ambientes de produção
- Utilização de Ubuntu 24.04 TLS como base para instâncias EC2
- Facilidade para escalar em diferentes provedores de nuvem

## Contribuição

1. Faça um fork do repositório
2. Crie um branch para sua feature (`git checkout -b feature/nova-funcionalidade`)
3. Faça commit das suas mudanças (`git commit -m 'Adiciona nova funcionalidade'`)
4. Envie para o branch (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request

Antes de enviar seu código, certifique-se de seguir as diretrizes de segurança descritas em `SECURITY.md`.

## Licença

Este projeto está licenciado sob a licença MIT - veja o arquivo LICENSE para detalhes.

## Última Atualização

16 de abril de 2025