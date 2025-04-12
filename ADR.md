# Arquitetura - Registro de Decisões (ARD/ADR)

Este documento registra as decisões arquiteturais significativas tomadas durante o desenvolvimento do Sistema de Estoque.

## Índice

- [ADR-001: Escolha do Stack Tecnológico para o Sistema de Estoque](#adr-001-escolha-do-stack-tecnológico-para-o-sistema-de-estoque)
- [ADR-002: Melhoria da Conectividade com PostgreSQL e Containerização](#adr-002-melhoria-da-conectividade-com-postgresql-e-containerização)
- [ADR-003: Implementação de Infraestrutura como Código com Terraform](#adr-003-implementação-de-infraestrutura-como-código-com-terraform)

---

# ADR-001: Escolha do Stack Tecnológico para o Sistema de Estoque

**Data:** 2025-04-08
**Status:** Aceito
**Responsáveis:** Equipe de Desenvolvimento

## Contexto

O projeto consiste em desenvolver um sistema de estoque com as seguintes funcionalidades:

- **Cadastro de Pessoas:** Registro de usuários e colaboradores.
- **Cadastro de Produtos e Grupos de Produtos:** Organização dos produtos em categorias para facilitar a gestão.
- **Cadastro de Lugares de Estoque:** Definição dos locais onde os produtos serão armazenados.
- **Registro de Movimentações:** Controle de entradas e saídas dos produtos com rastreamento de quem realizou as operações.
- **Geração de Relatórios:** Consolidação das informações para acompanhamento das movimentações, com destaque para as pessoas responsáveis.

Os requisitos iniciais apontam para a utilização de PHP como linguagem de programação e PostgreSQL como sistema gerenciador de banco de dados. A escolha desses componentes é fundamental para garantir um desenvolvimento ágil, manutenção facilitada e uma boa performance, principalmente em operações críticas de integridade dos dados.

## Decisão

A decisão tomada é a seguinte:

### Linguagem de Programação: PHP

**Motivação:** PHP é amplamente utilizado para o desenvolvimento web, possui uma comunidade ativa e ferramentas maduras para acesso a banco de dados (por exemplo, a extensão PDO) que permitem a utilização de boas práticas de segurança (como o uso de prepared statements).

### Banco de Dados: PostgreSQL

**Motivação:** PostgreSQL oferece robustez, alta confiabilidade e suporte a operações complexas com integridade referencial, fundamentais para um sistema de estoque que envolve transações e movimentações de dados.

### Estruturação do Projeto:

- **Modularidade:** Organização do código em pastas separadas (configuração, cadastros e relatórios), o que facilita a manutenção e futuras expansões.
- **Arquitetura em Camadas:** Separação das responsabilidades, com isolamento da lógica de acesso a dados, validação de entrada e apresentação.
- **Segurança:** Implementação de práticas como o uso de prepared statements para prevenir ataques de SQL Injection, além de validações adequadas dos dados do lado do cliente e servidor.

### Abordagem Inicial vs. Futuras Expansões:

- **Abordagem Inicial:** A implementação será feita usando PHP "puro", mantendo o sistema simples e direto para que ele atenda aos requisitos básicos.
- **Possibilidade de Uso de Frameworks:** Embora frameworks modernos como Laravel ou Symfony tenham sido considerados, a decisão foi iniciar com PHP puro para manter a clareza e simplicidade. Em iterações futuras, a migração ou refatoração para um framework pode ser considerada para facilitar a escalabilidade e a manutenção.

## Alternativas Consideradas

### Uso de MySQL em vez de PostgreSQL:
Apesar de MySQL ser popular, foi descartado em favor do PostgreSQL, que oferece melhor suporte a operações transacionais e integridade referencial, pontos críticos para o sistema de estoque.

### Implementação com Frameworks PHP (Laravel, Symfony):
O uso de um framework pode acelerar o desenvolvimento e introduzir padrões avançados de organização de código. No entanto, para a versão inicial do projeto e para manter o exemplo simples, a decisão foi seguir com PHP puro. A utilização de um framework pode ser reavaliada conforme o projeto evolua.

### Arquitetura Monolítica versus Modular:
Embora uma aplicação monolítica simples fosse suficiente para o MVP, a decisão foi adotar desde o início uma estrutura modular para facilitar a manutenção e a escalabilidade, caso novas funcionalidades precisem ser incorporadas.

## Consequências

### Simplicidade e Rapidez de Desenvolvimento:
A escolha por PHP puro e uma estrutura modular permite um desenvolvimento rápido e facilita a compreensão do código para desenvolvedores que possam ingressar no projeto posteriormente.

### Segurança e Integridade dos Dados:
O uso de PostgreSQL e prepared statements aumenta a segurança e assegura que a integridade dos dados seja mantida, o que é vital para o controle de estoque.

### Escalabilidade:
Embora a solução inicial seja simples, a separação em módulos e a adoção de práticas arquiteturais facilitam futuras expansões, como a implementação de autenticação, autorização de usuários e integração com outros sistemas.

### Curva de Aprendizado e Manutenção:
Desenvolvedores com conhecimento em PHP e PostgreSQL poderão rapidamente compreender e dar manutenção ao sistema, reduzindo o custo de treinamentos e documentações extensas.

## Conclusão

A escolha do stack tecnológico – PHP para o desenvolvimento e PostgreSQL para o gerenciamento de dados – foi fundamentada na robustez, segurança e facilidade de manutenção, atendendo aos requisitos iniciais do sistema de estoque. A estrutura modular adotada viabiliza a escalabilidade e futuras integrações, ao mesmo tempo em que mantém o desenvolvimento inicial simples e ágil.

> **Revisão:** Este ADR deverá ser revisado sempre que houver alterações significativas no escopo do projeto ou no stack tecnológico adotado.

---

# ADR-002: Melhoria da Conectividade com PostgreSQL e Containerização

**Data:** 2025-04-12
**Status:** Aceito
**Responsáveis:** Equipe de Infraestrutura e Desenvolvimento

## Contexto

Durante o processo de desenvolvimento e implantação do Sistema de Estoque, foram identificados problemas recorrentes de conectividade entre a aplicação PHP e o banco de dados PostgreSQL, principalmente no ambiente containerizado Docker. Estes problemas manifestavam-se como "PostgreSQL refusing connections", resultando em falhas na aplicação e dificuldades na configuração de novos ambientes.

As principais causas desses problemas incluíam:

1. Configuração incorreta da string de conexão no ambiente Docker
2. Ausência de mecanismos para diagnóstico de problemas de conectividade
3. Problemas de sincronização na inicialização dos serviços (PostgreSQL não completamente inicializado quando a aplicação tenta conectar)
4. Configurações inadequadas de permissões no PostgreSQL

## Decisão

Implementar uma estratégia abrangente para melhorar a conectividade e a confiabilidade do ambiente de desenvolvimento containerizado:

### 1. Configuração de Conexão Adaptável ao Ambiente

- **Utilizar nomes de serviços Docker em vez de 'localhost'** nas configurações de conexão com banco de dados
- **Implementar suporte a variáveis de ambiente** para permitir configuração flexível em diferentes ambientes
- **Adicionar configuração explícita de portas** para evitar conflitos e problemas de resolução

### 2. Ferramentas de Diagnóstico

- **Criar página de teste de conexão** (`test_connection.php`) que mostre detalhes sobre o status da conexão e parâmetros utilizados
- **Implementar verificação de existência das tabelas** para facilitar diagnóstico de problemas de estrutura do banco

### 3. Configuração Docker Robusta

- **Adicionar health checks** para o serviço PostgreSQL
- **Configurar dependências explícitas** entre serviços
- **Melhorar scripts de inicialização** do banco de dados com verificações mais robustas
- **Definir redes e volumes Docker** explicitamente para melhor isolamento e persistência

### 4. Documentação Detalhada

- **Atualizar a documentação** com instruções claras para solução de problemas
- **Documentar padrões** de configuração e abordagens para troubleshooting

## Alternativas Consideradas

### Uso de Frameworks com ORM Integrado
Utilizar um framework como Laravel com Eloquent ORM ou Symfony com Doctrine poderia abstrair alguns dos problemas de conectividade. No entanto, isso aumentaria significativamente a complexidade do projeto e se afastaria da abordagem de PHP puro inicialmente adotada.

### Configuração Manual sem Containerização
Eliminar o uso de Docker e configurar manualmente os ambientes poderia reduzir alguns problemas específicos de containerização, mas dificultaria a portabilidade e reprodutibilidade dos ambientes de desenvolvimento.

### Migração para MySQL
MySQL poderia oferecer uma alternativa com configuração potencialmente mais simples, mas perderia os benefícios de integridade referencial e transações robustas que o PostgreSQL oferece, que são vitais para o sistema de controle de estoque.

## Consequências

### Positivas
- **Ambiente de desenvolvimento mais confiável** e fácil de configurar
- **Processo simplificado de onboarding** para novos desenvolvedores
- **Melhor capacidade de diagnóstico** de problemas de conexão
- **Maior robustez na operação** do sistema em ambientes Docker
- **Padrões mais claros** para configuração de banco de dados e conectividade

### Negativas
- **Maior complexidade inicial** na configuração do ambiente Docker
- **Necessidade de manutenção** dos scripts de diagnóstico e configuração
- **Possível overhead** devido aos health checks e mecanismos adicionais

## Conclusão

As melhorias na conectividade com PostgreSQL e na configuração de containerização resolverão problemas críticos que afetavam a confiabilidade do ambiente de desenvolvimento e a experiência inicial dos desenvolvedores. Os benefícios de um ambiente de desenvolvimento mais robusto e previsível superam os custos de implementação e manutenção adicionais.

Estas mudanças estão alinhadas com a arquitetura modular inicialmente adotada e preparam o terreno para futuras melhorias, como a possível adoção de frameworks de PHP mais robustos, sem comprometer a simplicidade atual do sistema.

> **Revisão:** Este ADR deve ser revisado caso novas estratégias de containerização ou padrões de conectividade com banco de dados sejam adotados.

---

# ADR-003: Implementação de Infraestrutura como Código com Terraform

**Data:** 2025-04-12
**Status:** Aceito
**Responsáveis:** Equipe de DevOps e Infraestrutura

## Contexto

Com a evolução do sistema de estoque e o aumento da complexidade do ambiente de produção, surgiu a necessidade de uma estratégia mais robusta para provisionamento e gerenciamento de infraestrutura. O processo manual de configuração de ambientes estava se tornando propenso a erros e difícil de manter à medida que novos ambientes eram necessários para desenvolvimento, testes e produção.

Os desafios específicos incluíam:

1. **Inconsistências entre ambientes:** Diferenças sutis na configuração entre desenvolvimento e produção causavam problemas difíceis de diagnosticar
2. **Processos manuais demorados:** A configuração manual de novos ambientes consumia tempo excessivo da equipe
3. **Documentação desatualizada:** A documentação de infraestrutura frequentemente ficava desatualizada em relação à implementação real
4. **Dificuldade em escalabilidade:** A necessidade de escalar a aplicação para múltiplas regiões ou provedores de nuvem se tornava um desafio sem automação

## Decisão

Implementar uma solução de Infraestrutura como Código (IaC) utilizando Terraform para automatizar o provisionamento e gerenciamento da infraestrutura do sistema, com os seguintes componentes:

### 1. Adoção do Terraform como ferramenta de IaC

- Criar módulos Terraform que definam toda a infraestrutura necessária (servidores web, banco de dados PostgreSQL, balanceadores de carga, redes e regras de segurança)
- Parametrizar configurações através de variáveis para suportar diferentes ambientes
- Implementar outputs para facilitar a obtenção de informações importantes após o provisionamento

### 2. Estrutura do Código Terraform

- **Módulos reutilizáveis** para componentes comuns como servidores web, banco de dados e rede
- **Configurações específicas por ambiente** (desenvolvimento, testes, produção)
- **Organização em estado remoto** para colaboração da equipe

### 3. Integração com o Fluxo de Trabalho

- Implementar validações automáticas do código Terraform em pipelines CI/CD
- Criar scripts de automação para aplicar as configurações de forma consistente
- Integrar com o script `run.sh` para facilitar o uso em ambiente local

### 4. Segurança e Gerenciamento de Segredos

- Implementar armazenamento seguro de credenciais e segredos
- Utilizar variáveis de ambiente e backends seguros para armazenamento de estado

## Alternativas Consideradas

### CloudFormation (AWS) ou ARM Templates (Azure)
Ferramentas específicas de provedores poderiam ser mais integradas com seus respectivos ambientes de nuvem, mas restringiriam a portabilidade entre diferentes provedores, uma consideração importante para a estratégia de longo prazo.

### Ansible ou Chef
Estas ferramentas são excelentes para configuração e gerenciamento de configuração, mas menos adequadas para o provisionamento de infraestrutura completa. Poderiam ser usadas em conjunto com o Terraform para uma solução mais abrangente.

### Docker Swarm ou Kubernetes
Soluções de orquestração de contêineres como Kubernetes poderiam oferecer recursos avançados de escalabilidade e gerenciamento, mas introduziriam uma complexidade significativa que não se justifica no estágio atual do projeto.

## Consequências

### Positivas
- **Infraestrutura como código versionado:** Alterações na infraestrutura podem ser revisadas, versionadas e auditadas
- **Consistência entre ambientes:** Redução de problemas de "funciona na minha máquina"
- **Documentação viva:** O código Terraform serve como documentação atualizada da infraestrutura
- **Facilidade de replicação:** Novos ambientes podem ser provisionados rapidamente e de forma idêntica
- **Flexibilidade para migração entre provedores:** Menor dependência de um provedor específico de nuvem

### Negativas
- **Curva de aprendizado inicial:** A equipe precisará investir tempo para aprender Terraform
- **Complexidade adicional:** Introdução de mais uma ferramenta no stack de tecnologia
- **Necessidade de gerenciamento de estado:** O estado do Terraform precisa ser gerenciado adequadamente

## Conclusão

A implementação de Infraestrutura como Código com Terraform representa um passo importante na maturação do sistema de estoque, permitindo um gerenciamento mais consistente, documentado e automatizado da infraestrutura. Embora exista uma curva inicial de aprendizado, os benefícios de longo prazo em termos de confiabilidade, reprodutibilidade e eficiência superam os custos.

Esta decisão se alinha com as práticas modernas de DevOps e fornece uma base sólida para futuros crescimentos e melhorias na infraestrutura do sistema, enquanto mantém as opções abertas para diferentes estratégias de hospedagem.

> **Revisão:** Este ADR deve ser revisado após 6 meses de implementação ou quando houver mudanças significativas na estratégia de infraestrutura.