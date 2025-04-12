# Changelog

Todas as alterações notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Versionamento Semântico](https://semver.org/lang/pt-BR/spec/v2.0.0.html).

## [0.5.0] - 2025-04-12

### Adicionado
- Arquivos de listagem para os cadastros (`list_fabricantes.php`, `list_grupos_pessoas.php`, `list_pessoas.php`, `list_produtos.php`, `list_lugares.php`, `list_grupos.php`)
- Integração completa com Terraform para facilitar o provisionamento em produção
- Adição do arquivo `run.sh` para inicialização simplificada do ambiente
- Mensagens mais detalhadas nos logs de erro e registros de auditoria
- Script `populate_database.sql` para dados iniciais de demonstração
- Nova documentação de segurança em SECURITY.md

### Alterado
- Documentação atualizada com estrutura mais clara e representativa da organização atual
- Melhorias no script de Docker Compose com volumes e networks mais bem definidos
- Fluxo de operações otimizado para cadastro de produtos e pessoas
- Interface de relatórios aprimorada para melhor visualização dos dados
- Estrutura do repositório reorganizada para melhor separação de responsabilidades

### Segurança
- Implementação adicional contra Cross-Site Scripting (XSS) nos formulários
- Atualização de dependências para eliminar vulnerabilidades conhecidas
- Revisão de permissões no acesso a arquivos no container Docker
- Sanitização aprimorada de entradas de usuário em todos os formulários

## [0.4.0] - 2025-03-15

### Adicionado
- Arquivo `test_connection.php` para diagnóstico de problemas de conexão com o PostgreSQL
- Suporte a variáveis de ambiente para configuração do banco de dados
- Healthcheck para o serviço PostgreSQL no docker-compose.yml
- Documentação detalhada para solução de problemas de conexão
- Relatório de produtos por local (`produtos_por_local.php`)
- Relatório detalhado de movimentação por produto (`movimentacao_produtos.php`)

### Alterado
- Configuração de conexão em `db.php` para usar o nome do serviço Docker ao invés de localhost
- Melhorias no script de inicialização do banco de dados para melhor gerenciamento de permissões
- Docker Compose atualizado com definição explícita de volumes e redes
- Documentação atualizada com novas instruções de instalação e solução de problemas
- Melhorias visuais em todos os relatórios existentes

### Corrigido
- Problema de "PostgreSQL refusing connections" ao inicializar o ambiente Docker
- Erros de permissão no acesso ao banco de dados
- Inconsistências na configuração dos volumes Docker
- Cálculos incorretos de saldo em estoque em alguns relatórios

## [0.3.0] - 2023-05-01

### Adicionado
- Atualização para PHP 8.4 estável
- Adicionado arquivo de configuração php.ini customizado
- Adicionado .dockerignore para otimizar builds
- Documentação atualizada com requisitos PHP 8.4
- Campo de observação nas movimentações de produtos
- Suporte a fabricantes de produtos
- Interface aprimorada para seleção de tipo de movimento (entrada/saída)

### Alterado
- Docker Compose configurado para usar a imagem oficial php:8.4-apache
- Corrigidos os comandos de inicialização do container PHP
- Configuração de portas e ambiente para melhor compatibilidade
- Formulários reformulados com melhor estilização e validação
- Layout responsivo para melhor visualização em dispositivos móveis

### Corrigido
- Problemas de compatibilidade com PHP 8.4
- Falhas na exibição de caracteres especiais
- Erros na validação de formulários

## [0.2.0] - 2023-04-15

### Adicionado
- Movido o arquivo principal `index.php` para o diretório `src`
- Criação de arquivos `test_connection.php` e `setup_database.php` para auxiliar na configuração
- Implementação de um relatório de estoque atual com saldos por produto e local
- Arquivo ADR.md formatado para documentar decisões arquiteturais
- Redirecionador na raiz do projeto para o novo local do index.php
- Cadastro de grupos de pessoas

### Alterado
- Reorganização da estrutura de diretórios para usar `src` como diretório padrão
- Atualizados todos os caminhos relativos para navegação entre arquivos
- Corrigidos os caminhos de inclusão de arquivos em todos os formulários
- Movidos os arquivos de cadastro do diretório `config/cadastros` para `src/cadastros`
- Estrutura modular mais clara com separação de responsabilidades

### Corrigido
- Caminhos incorretos para voltar à página principal
- Problemas de referência aos arquivos de configuração
- Consulta SQL do relatório de estoque para cálculo correto dos saldos
- Validação de entradas nos formulários de cadastro

## [0.1.0] - 2023-04-01

### Adicionado
- Configuração inicial do banco de dados PostgreSQL
- Implementação do arquivo de conexão com o banco (`db.php`)
- Script SQL para criação das tabelas do sistema
- Formulários para cadastro de pessoas, produtos, grupos e lugares
- Formulário para registro de movimentações de estoque (entradas e saídas)
- Relatório básico de movimentações
- Estrutura básica do projeto com separação em diretórios funcionais
- Documentação inicial com README.md
