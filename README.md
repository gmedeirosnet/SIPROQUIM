# ESTOQUE

## DESCRIÇÃO

Sistema de estoque simples desenvolvido com PHP 8.4 e PostgreSQL 15, implementando os seguintes módulos:

- Cadastro de Pessoas e Grupos de Pessoas
- Cadastro de Produtos, Grupos de Produtos e Fabricantes
- Cadastro de Lugares de Estocagem
- Movimentações (entradas e saídas) de Produtos
- Geração de Relatórios de Movimentações e Estoque Atual

O sistema é estruturado em camadas, separando configuração, conexão com banco de dados, operações CRUD para cada módulo e geração de relatórios. Este projeto demonstra boas práticas de modelagem relacional e implementação PHP com foco em segurança e confiabilidade.

## Requisitos

- PHP 8.4 ou superior
- PostgreSQL 15
- Docker e Docker Compose (para ambiente de desenvolvimento)
- Terraform 1.5+ (para provisionamento da infraestrutura)

## Estrutura de Arquivos e Organização do projeto

```
Estoque/
├── src/                      # Código-fonte da aplicação
│   ├── index.php             # Página inicial
│   ├── test_connection.php   # Ferramenta para testar a conexão com o DB
│   ├── php.ini               # Configuração personalizada do PHP
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
│   └── relatorios/           # Geração de relatórios
│       ├── relatorio_estoque.php          # Relatório geral de estoque
│       ├── relatorio_movimentos.php       # Relatório de movimentações
│       ├── produtos_por_local.php         # Relatório de produtos por local
│       └── movimentacao_produtos.php      # Relatório de movimentação por produto
├── scripts/                  # Scripts de inicialização
│   ├── init-db.sh            # Script para inicialização do banco de dados
│   └── populate_database.sql # Script para popular o banco com dados iniciais
├── terraform/                # Arquivos para infraestrutura como código
│   ├── main.tf               # Configuração principal do Terraform
│   ├── outputs.tf            # Saídas do Terraform
│   └── variables.tf          # Variáveis configuráveis do Terraform
├── docker-compose.yml        # Configuração dos containers Docker
├── run.sh                    # Script de execução rápida
├── README.md                 # Este arquivo
├── CHANGELOG.md              # Histórico de alterações
├── ADR.md                    # Registro de decisões arquiteturais
└── SECURITY.md               # Políticas de segurança
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
   git clone https://github.com/seu-usuario/estoque.git
   cd estoque
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
5. Valide o plano de execução:
   ```bash
   terraform plan
   ```
6. Aplique a configuração:
   ```bash
   terraform apply
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

5. Aguarde a inicialização completa do PostgreSQL, que pode levar alguns segundos

## Considerações Técnicas

### Validações e Segurança:
- Prepared statements para prevenção de SQL Injection
- Validação e sanitização de dados nos formulários
- Estrutura que permite implementação futura de autenticação de usuários
- Configuração segura de contêineres Docker e infraestrutura

### Modularização:
- Organização em diretórios funcionais
- Separação clara entre lógica de dados e apresentação
- Fácil manutenção e extensão do código

### Interface e Usabilidade:
- Interface HTML simples e funcional com CSS responsivo
- Possibilidade de integração com frameworks CSS no futuro
- Formulários validados tanto no cliente quanto no servidor

### Infraestrutura:
- Configuração containerizada para desenvolvimento
- Infraestrutura como código usando Terraform para ambientes de produção
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

12 de abril de 2025