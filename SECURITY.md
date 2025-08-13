# Política de Segurança

Este documento descreve as políticas e práticas de segurança adotadas no projeto Sistema de Estoque.

## Versões Suportadas

As seguintes versões do Sistema de Estoque recebem atualizações de segurança:

| Versão | Suportada          |
| ------ | ------------------ |
| 0.7.x  | :white_check_mark: |
| 0.6.x  | :white_check_mark: |
| 0.5.x  | :white_check_mark: |
| 0.4.x  | :x:                |
| 0.3.x  | :x:                |
| < 0.3  | :x:                |

## Reportando Vulnerabilidades

Caso você encontre uma vulnerabilidade de segurança neste projeto, siga estas etapas para reportá-la:

1. **Não divulgue publicamente** a vulnerabilidade encontrada
2. Envie um e-mail para [security@gmedeiros.net](mailto:security@gmedeiros.net) com detalhes sobre:
   - O tipo de vulnerabilidade
   - Os passos para reproduzir o problema
   - Possível impacto da vulnerabilidade
   - Sugestões de mitigação (se houver)

3. Espere por uma resposta da equipe de segurança. Comprometemo-nos a:
   - Confirmar o recebimento do relatório em até 48 horas
   - Fornecer uma estimativa para resolução em até 5 dias úteis
   - Manter você atualizado sobre o progresso da correção
   - Reconhecer sua contribuição após a correção (se você desejar)

## Práticas de Segurança Implementadas

### Validação de Dados e Integridade
- **Validações obrigatórias server-side** para campos críticos (Fabricante, Grupo em produtos; Grupo em pessoas)
- **Validação condicional** para senhas (obrigatória apenas para novos usuários)
- **Sanitização robusta** de todas as entradas de usuário antes do processamento
- **Verificação de integridade referencial** no banco de dados
- **Validação de tipos de dados** para prevenir manipulação de parâmetros

### Prevenção contra SQL Injection
- Uso de prepared statements em todas as consultas SQL
- Validação e sanitização de parâmetros de entrada
- Uso de tipos parametrizados para evitar conversões implícitas inseguras
- Implementação de boas práticas de acesso ao banco de dados via PDO
- **Uso de ILIKE para buscas case-insensitive** mantendo segurança contra injection

### Prevenção contra XSS (Cross-Site Scripting)
- Sanitização de saídas em HTML utilizando funções de escape apropriadas (htmlspecialchars)
- Validação rigorosa de dados de entrada em todos os formulários
- **Implementação de headers de segurança HTTP aprimorados:**
  - Content-Security-Policy (CSP)
  - Strict-Transport-Security (HSTS)
  - X-Content-Type-Options
  - X-Frame-Options
  - Referrer-Policy
- Filtragem de conteúdo antes da renderização

### Segurança de Dados
- Sanitização de todas as entradas de usuário
- Validação de dados em camadas (cliente e servidor)
- Implementação de logs de auditoria para operações críticas
- Proteção contra manipulação de dados sensíveis

### Segurança de Configuração
- Configurações sensíveis (como credenciais de banco de dados) armazenadas em variáveis de ambiente
- Configuração segura de contêineres Docker com privilégios mínimos
- Permissões mínimas necessárias para funcionamento do sistema
- Separação clara entre ambientes de desenvolvimento e produção

### Segurança em Infraestrutura (Terraform)
- Controle de acesso baseado em privilégio mínimo
- Criptografia de dados sensíveis em trânsito e em repouso
- Definições de segurança como código nos arquivos Terraform
- Segmentação apropriada de redes e implementação de grupos de segurança
- **Gerenciamento seguro de chaves SSH** com chaves dedicadas para acesso a instâncias EC2
- **Restrição de acesso SSH** apenas a IPs autorizados
- Gerenciamento seguro de estado do Terraform
- **AMIs atualizadas** com Ubuntu 24.04 TLS para base segura

### Segurança de Desenvolvimento
- **Configurações padronizadas** para ambientes de desenvolvimento (VS Code, GitHub Copilot)
- **Diretrizes claras** para desenvolvimento seguro com assistentes de IA
- **Padrões documentados** para implementação de validações e sanitização
- **Templates de código seguro** para operações CRUD e manipulação de dados

## Boas Práticas para Desenvolvedores

Ao contribuir para este projeto, siga estas práticas de segurança:

1. **Nunca** armazene senhas ou chaves de API diretamente no código
2. Use **sempre** prepared statements para consultas SQL
3. **Implemente validações obrigatórias** no servidor para campos críticos
4. Sanitize todas as entradas de usuários antes de processá-las
5. Valide todos os dados de formulário no servidor, independente da validação no cliente
6. **Use ILIKE em vez de LIKE** para buscas case-insensitive seguras no PostgreSQL
7. Mantenha todas as dependências atualizadas
8. Utilize HTTPS para todas as comunicações em produção
9. Aplique o princípio do privilégio mínimo ao configurar permissões de banco de dados
10. **Implemente validação condicional** adequada (ex: senhas obrigatórias apenas para novos usuários)
11. Faça revisão de segurança do código regularmente
12. Utilize variáveis de ambiente para armazenar informações sensíveis
13. **Siga os padrões documentados** no GitHub Copilot instructions para código consistente e seguro
14. **Implemente headers de segurança HTTP** em todas as páginas da aplicação
15. Documente todas as considerações de segurança relevantes ao seu código

## Ciclo de Desenvolvimento Seguro

Nosso processo de desenvolvimento segue estas práticas:

1. **Planejamento:** Considerar requisitos de segurança desde o início
2. **Desenvolvimento:** Seguir padrões de codificação segura
3. **Teste:** Realizar testes de segurança automatizados e manuais
4. **Revisão:** Conduzir revisões de código com foco em segurança
5. **Implantação:** Usar processos automatizados e verificados
6. **Monitoramento:** Implementar detecção de incidentes e resposta

## Auditoria de Segurança

O sistema implementa as seguintes práticas de auditoria:

1. **Logs de atividades críticas:**
   - Movimentações de estoque com rastreabilidade completa
   - Alterações em dados sensíveis (produtos, pessoas, grupos)
   - **Validações de campos obrigatórios** e falhas de validação
   - Tentativas de autenticação (em versões futuras)
   - Mudanças de configuração do sistema

2. **Monitoramento e Alertas:**
   - Configuração para detecção de comportamento anômalo
   - Alertas para tentativas de acesso não autorizado
   - Verificação de integridade do banco de dados
   - **Monitoramento de validações falhadas** e possíveis tentativas de bypass

3. **Revisão Regular:**
   - Análise periódica de logs e permissões
   - Revisão de código focada em segurança a cada release
   - Avaliação contínua de vulnerabilidades
   - **Revisão de padrões de validação** e sua efetividade

## Planos para Futuras Melhorias de Segurança

1. **Implementação de sistema completo de autenticação e autorização** baseado em roles
2. **Auditoria completa de todas as operações** do sistema com logs estruturados
3. Implementação de proteções adicionais contra ataques de força bruta
4. Integração com sistemas de análise de vulnerabilidades automatizada
5. **Implementação de autenticação de dois fatores** para usuários administrativos
6. **Expansão do controle de acesso baseado em papéis (RBAC)** com permissões granulares
7. Criptografia de dados sensíveis em repouso com rotação de chaves
8. **Sistema de alertas em tempo real** para atividades suspeitas
9. **Implementação de rate limiting** para prevenir abuso de APIs
10. **Backup automático criptografado** com testes regulares de recuperação

## Atualização e Patch Management

1. Patches de segurança críticos são lançados imediatamente após validação
2. Atualizações regulares de segurança são incluídas em cada release
3. O status de vulnerabilidades conhecidas é mantido atualizado
4. Processo documentado para atualização de dependências de terceiros
5. Comunicação imediata aos usuários sobre vulnerabilidades críticas

## Resposta a Incidentes

Em caso de incidente de segurança, seguimos este processo:

1. **Contenção:** Limitar o impacto e isolamento dos sistemas afetados
2. **Análise:** Investigar a causa raiz e extensão do incidente
3. **Erradicação:** Remover o problema e implementar correções
4. **Recuperação:** Restaurar os sistemas para operação normal
5. **Lições Aprendidas:** Melhorar o processo e prevenir problemas semelhantes

---

**Última atualização: 13 de agosto de 2025**

**Melhorias de Segurança Recentes:**
- Implementação de validações obrigatórias server-side
- Headers de segurança HTTP aprimorados
- Chaves SSH dedicadas para infraestrutura
- Padrões de desenvolvimento seguro documentados
- Validação condicional para diferentes cenários de uso

Contato para questões de segurança: [security@gmedeiros.net](mailto:security@gmedeiros.net)
