-- Database Population Script for SIPROQUIM
-- Atualizado em 19 de Abril de 2025

-- Clear existing data (if you want to start fresh)
-- AVISO: Comentar estas linhas se quiser preservar dados existentes
TRUNCATE TABLE login_logs CASCADE;
TRUNCATE TABLE movimentos CASCADE;
TRUNCATE TABLE lugares CASCADE;
TRUNCATE TABLE produtos CASCADE;
TRUNCATE TABLE fabricantes CASCADE;
TRUNCATE TABLE grupos CASCADE;
TRUNCATE TABLE pessoas CASCADE;
TRUNCATE TABLE grupos_pessoas CASCADE;

-- Reset sequences
ALTER SEQUENCE grupos_pessoas_id_seq RESTART WITH 1;
ALTER SEQUENCE pessoas_id_seq RESTART WITH 1;
ALTER SEQUENCE grupos_id_seq RESTART WITH 1;
ALTER SEQUENCE fabricantes_id_seq RESTART WITH 1;
ALTER SEQUENCE produtos_id_seq RESTART WITH 1;
ALTER SEQUENCE lugares_id_seq RESTART WITH 1;
ALTER SEQUENCE movimentos_id_seq RESTART WITH 1;
ALTER SEQUENCE login_logs_id_seq RESTART WITH 1;

-- 1. Grupos de Pessoas (grupos_pessoas)
INSERT INTO grupos_pessoas (nome, descricao) VALUES
('Administradores', 'Grupo com acesso total ao sistema'),
('Usuários', 'Grupo padrão de usuários'),
('Técnicos', 'Responsáveis pela manipulação de produtos'),
('Supervisores', 'Supervisores de departamento'),
('Auditores', 'Responsáveis pela auditoria e controle de estoque');

-- 2. Pessoas (pessoas) - Adicionando o campo enable e password
INSERT INTO pessoas (nome, email, id_grupo_pessoa, enable, password) VALUES
-- Administradores (id_grupo_pessoa=1)
('Admin Sistema', 'admin@siproquim.gov.br', 1, true, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'), -- senha: 123456
('Maria Gerente', 'maria.gerente@siproquim.gov.br', 1, true, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),

-- Usuários (id_grupo_pessoa=2)
('João Silva', 'joao.silva@siproquim.gov.br', 2, true, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Ana Santos', 'ana.santos@siproquim.gov.br', 2, true, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Carlos Oliveira', 'carlos.oliveira@siproquim.gov.br', 2, true, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Patrícia Lima', 'patricia.lima@siproquim.gov.br', 2, false, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),

-- Técnicos (id_grupo_pessoa=3)
('Roberto Queiroz', 'roberto.queiroz@siproquim.gov.br', 3, true, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Lúcia Marques', 'lucia.marques@siproquim.gov.br', 3, true, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Fernando Costa', 'fernando.costa@siproquim.gov.br', 3, true, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),

-- Supervisores (id_grupo_pessoa=4)
('Marcelo Pereira', 'marcelo.pereira@siproquim.gov.br', 4, true, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Regina Campos', 'regina.campos@siproquim.gov.br', 4, true, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),

-- Auditores (id_grupo_pessoa=5)
('Paulo Cardoso', 'paulo.cardoso@siproquim.gov.br', 5, true, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Amanda Vieira', 'amanda.vieira@siproquim.gov.br', 5, true, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- 3. Grupos de Produtos (grupos)
INSERT INTO grupos (nome, descricao) VALUES
('Solventes', 'Solventes orgânicos e inorgânicos'),
('Ácidos', 'Ácidos inorgânicos e orgânicos'),
('Bases', 'Bases e álcalis'),
('Sais', 'Compostos iônicos'),
('Reagentes Analíticos', 'Reagentes para análise química'),
('Indicadores', 'Indicadores para titulação e análises'),
('Catalisadores', 'Catalisadores para reações químicas');

-- 4. Fabricantes (fabricantes)
INSERT INTO fabricantes (nome, cnpj, endereco, email, observacao) VALUES
('Sigma-Aldrich', '12.345.678/0001-01', 'Rua das Indústrias, 1000, São Paulo, SP', 'contato@sigmaaldrich.com.br', 'Multinacional de produtos químicos'),
('Merck', '23.456.789/0001-02', 'Av. dos Químicos, 2000, Rio de Janeiro, RJ', 'atendimento@merck.com.br', 'Empresa alemã de produtos químicos e farmacêuticos'),
('Synth', '34.567.890/0001-03', 'Rod. Anhanguera, km 120, Diadema, SP', 'vendas@synth.com.br', 'Fabricante nacional de reagentes químicos'),
('J.T. Baker', '45.678.901/0001-04', 'Av. das Américas, 3000, São Paulo, SP', 'comercial@jtbaker.com.br', 'Especializada em solventes de alta pureza'),
('Vetec', '56.789.012/0001-05', 'Rua Industrial, 500, Duque de Caxias, RJ', 'atendimento@vetec.com.br', 'Reagentes para análise e pesquisa'),
('Dinâmica Química', '67.890.123/0001-06', 'Av. Química, 1500, Indaiatuba, SP', 'vendas@dinamicaquimica.com.br', 'Fabricante brasileiro de reagentes');

-- 5. Lugares de Estoque (lugares)
INSERT INTO lugares (nome, descricao) VALUES
('Almoxarifado Central', 'Depósito principal de reagentes'),
('Laboratório Análises', 'Estoque do laboratório de análises químicas'),
('Laboratório Pesquisa', 'Estoque do laboratório de pesquisa'),
('Sala Controlados', 'Sala com acesso restrito para produtos controlados'),
('Câmara Refrigerada', 'Local refrigerado para produtos termolábeis');

-- 6. Produtos (produtos)
INSERT INTO produtos (nome, id_grupo, id_fabricante, tipo, volume, unidade_medida, preco, descricao) VALUES
-- Solventes (id_grupo=1)
('Acetona P.A.', 1, 1, 'Líquido', '1000', 'ml', 45.90, 'Acetona para análise, pureza 99.5%'),
('Metanol P.A.', 1, 2, 'Líquido', '1000', 'ml', 52.30, 'Metanol grau P.A., pureza 99.8%'),
('Etanol Absoluto', 1, 3, 'Líquido', '1000', 'ml', 48.75, 'Etanol absoluto, pureza 99.9%'),
('Hexano P.A.', 1, 4, 'Líquido', '1000', 'ml', 65.40, 'Hexano P.A., pureza 95%'),
('Clorofórmio P.A.', 1, 5, 'Líquido', '1000', 'ml', 89.90, 'Clorofórmio estabilizado com etanol'),

-- Ácidos (id_grupo=2)
('Ácido Clorídrico P.A.', 2, 1, 'Líquido', '1000', 'ml', 38.40, 'HCl 37%, para análise'),
('Ácido Sulfúrico P.A.', 2, 2, 'Líquido', '1000', 'ml', 45.60, 'H2SO4 98%, para análise'),
('Ácido Nítrico P.A.', 2, 3, 'Líquido', '1000', 'ml', 52.30, 'HNO3 65%, para análise'),
('Ácido Acético Glacial', 2, 4, 'Líquido', '1000', 'ml', 41.20, 'Ácido acético glacial 99.7%'),

-- Bases (id_grupo=3)
('Hidróxido de Sódio P.A.', 3, 5, 'Sólido', '500', 'g', 32.50, 'NaOH em lentilhas, pureza 99%'),
('Hidróxido de Potássio P.A.', 3, 6, 'Sólido', '500', 'g', 45.80, 'KOH em lentilhas, pureza 85%'),
('Hidróxido de Amônio P.A.', 3, 1, 'Líquido', '1000', 'ml', 37.90, 'NH4OH 28-30%, para análise'),

-- Sais (id_grupo=4)
('Cloreto de Sódio P.A.', 4, 2, 'Sólido', '1000', 'g', 18.60, 'NaCl cristalino, pureza 99.5%'),
('Sulfato de Cobre P.A.', 4, 3, 'Sólido', '500', 'g', 42.70, 'CuSO4.5H2O cristalino'),
('Nitrato de Prata P.A.', 4, 4, 'Sólido', '100', 'g', 375.50, 'AgNO3 cristalino, pureza 99.0%'),

-- Reagentes Analíticos (id_grupo=5)
('Reagente de Benedict', 5, 5, 'Líquido', '1000', 'ml', 58.90, 'Para detecção de açúcares redutores'),
('Lugol', 5, 6, 'Líquido', '100', 'ml', 28.60, 'Solução iodo-iodetada'),
('Reagente de Fehling A', 5, 1, 'Líquido', '500', 'ml', 35.40, 'Para detecção de açúcares redutores'),
('Reagente de Fehling B', 5, 1, 'Líquido', '500', 'ml', 35.40, 'Para detecção de açúcares redutores'),

-- Indicadores (id_grupo=6)
('Fenolftaleína', 6, 2, 'Líquido', '100', 'ml', 24.90, 'Solução 1%, para titulação ácido-base'),
('Azul de Bromotimol', 6, 3, 'Líquido', '100', 'ml', 32.40, 'Solução 0.1%, pH 6.0-7.6'),
('Vermelho de Metila', 6, 4, 'Líquido', '100', 'ml', 26.80, 'Solução 0.1%, pH 4.4-6.2'),

-- Catalisadores (id_grupo=7)
('Óxido de Alumínio', 7, 5, 'Sólido', '500', 'g', 68.50, 'Al2O3 ativo, granulometria 70-290 mesh'),
('Carvão Ativado', 7, 6, 'Sólido', '500', 'g', 42.80, 'Para adsorção, granulometria 12-20 mesh'),
('Dióxido de Manganês', 7, 1, 'Sólido', '250', 'g', 55.90, 'MnO2 técnico, para catálise');

-- 7. Movimentos (entrada e saída de produtos)
INSERT INTO movimentos (id_produto, id_pessoa, id_lugar, tipo, quantidade, observacao, data_movimento) VALUES
-- Entradas iniciais
(1, 1, 1, 'entrada', 15, 'Compra inicial', '2025-03-10 08:30:00'),
(2, 1, 1, 'entrada', 10, 'Compra inicial', '2025-03-10 08:35:00'),
(3, 1, 1, 'entrada', 8, 'Compra inicial', '2025-03-10 08:40:00'),
(4, 1, 1, 'entrada', 5, 'Compra inicial', '2025-03-10 08:45:00'),
(5, 1, 1, 'entrada', 3, 'Compra inicial', '2025-03-10 08:50:00'),
(6, 2, 1, 'entrada', 8, 'Compra inicial', '2025-03-11 09:30:00'),
(7, 2, 1, 'entrada', 6, 'Compra inicial', '2025-03-11 09:35:00'),
(8, 2, 1, 'entrada', 5, 'Compra inicial', '2025-03-11 09:40:00'),
(9, 2, 1, 'entrada', 4, 'Compra inicial', '2025-03-11 09:45:00'),
(10, 2, 4, 'entrada', 10, 'Compra inicial', '2025-03-12 10:30:00'),
(11, 2, 4, 'entrada', 8, 'Compra inicial', '2025-03-12 10:35:00'),
(12, 2, 4, 'entrada', 6, 'Compra inicial', '2025-03-12 10:40:00'),
(16, 7, 2, 'entrada', 12, 'Compra para laboratório', '2025-03-15 14:30:00'),
(17, 7, 2, 'entrada', 5, 'Compra para laboratório', '2025-03-15 14:35:00'),
(18, 7, 2, 'entrada', 3, 'Compra para laboratório', '2025-03-15 14:40:00'),
(19, 8, 3, 'entrada', 8, 'Compra para pesquisa', '2025-03-18 11:30:00'),
(20, 8, 3, 'entrada', 7, 'Compra para pesquisa', '2025-03-18 11:35:00'),
(21, 8, 3, 'entrada', 6, 'Compra para pesquisa', '2025-03-18 11:40:00'),
(22, 9, 5, 'entrada', 4, 'Material refrigerado', '2025-03-20 09:15:00'),
(23, 9, 5, 'entrada', 3, 'Material refrigerado', '2025-03-20 09:20:00'),

-- Transferências e saídas
(1, 3, 2, 'saida', 2, 'Uso em análises', '2025-03-25 10:45:00'),
(2, 4, 2, 'saida', 1, 'Uso em análises', '2025-03-25 11:10:00'),
(6, 5, 3, 'saida', 1, 'Experimento de laboratório', '2025-03-26 14:20:00'),
(10, 10, 4, 'saida', 2, 'Material para pesquisa', '2025-03-27 15:30:00'),
(16, 7, 2, 'saida', 3, 'Uso em análise de água', '2025-03-28 09:45:00'),
(19, 8, 3, 'saida', 2, 'Pesquisa catalítica', '2025-03-29 11:30:00');

-- 8. Logs de login (login_logs)
INSERT INTO login_logs (id_pessoa, data_login, ip, user_agent) VALUES
(1, '2025-04-01 08:30:25', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(2, '2025-04-01 09:15:10', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)'),
(3, '2025-04-01 10:05:45', '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/102.0'),
(1, '2025-04-02 08:45:32', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(5, '2025-04-02 11:22:18', '192.168.1.105', 'Mozilla/5.0 (X11; Linux x86_64) Chrome/101.0'),
(7, '2025-04-03 09:30:05', '192.168.1.107', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Firefox/100.0'),
(10, '2025-04-03 13:15:40', '192.168.1.110', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15'),
(1, '2025-04-04 08:20:15', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(11, '2025-04-04 10:45:30', '192.168.1.111', 'Mozilla/5.0 (iPad; CPU OS 15_4 like Mac OS X) Mobile/15E148'),
(12, '2025-04-04 14:10:22', '192.168.1.112', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_4 like Mac OS X) Mobile/15E148');