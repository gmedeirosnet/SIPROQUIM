-- Database Population Script for Estoque System
-- Created on April 12, 2025

-- Clear existing data (if you want to start fresh)
-- Comment these lines if you want to keep existing data
TRUNCATE TABLE movimentos CASCADE;
TRUNCATE TABLE produtos CASCADE;
TRUNCATE TABLE fabricantes CASCADE;
TRUNCATE TABLE grupos CASCADE;
TRUNCATE TABLE lugares CASCADE;
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

-- 1. Insert Person Groups (grupos_pessoas)
INSERT INTO grupos_pessoas (nome, descricao) VALUES
('Alunos', 'Estudantes da instituição'),
('Professores', 'Docentes da instituição'),
('Pesquisadores', 'Pesquisadores e cientistas da instituição'),
('Auditores', 'Responsáveis pela auditoria e controle de estoque');

-- 2. Insert Persons (pessoas)
-- Alunos (20)
INSERT INTO pessoas (nome, email, id_grupo_pessoa) VALUES
('João Silva', 'joao.silva@aluno.edu.br', 1),
('Maria Santos', 'maria.santos@aluno.edu.br', 1),
('Pedro Oliveira', 'pedro.oliveira@aluno.edu.br', 1),
('Ana Souza', 'ana.souza@aluno.edu.br', 1),
('Lucas Costa', 'lucas.costa@aluno.edu.br', 1),
('Juliana Lima', 'juliana.lima@aluno.edu.br', 1),
('Matheus Ferreira', 'matheus.ferreira@aluno.edu.br', 1),
('Camila Rodrigues', 'camila.rodrigues@aluno.edu.br', 1),
('Gabriel Almeida', 'gabriel.almeida@aluno.edu.br', 1),
('Laura Martins', 'laura.martins@aluno.edu.br', 1),
('Bruno Pereira', 'bruno.pereira@aluno.edu.br', 1),
('Fernanda Gomes', 'fernanda.gomes@aluno.edu.br', 1),
('Rafael Carvalho', 'rafael.carvalho@aluno.edu.br', 1),
('Isabella Ribeiro', 'isabella.ribeiro@aluno.edu.br', 1),
('Thiago Machado', 'thiago.machado@aluno.edu.br', 1),
('Carolina Castro', 'carolina.castro@aluno.edu.br', 1),
('Felipe Barbosa', 'felipe.barbosa@aluno.edu.br', 1),
('Beatriz Cardoso', 'beatriz.cardoso@aluno.edu.br', 1),
('Vitor Correia', 'vitor.correia@aluno.edu.br', 1),
('Mariana Nunes', 'mariana.nunes@aluno.edu.br', 1);

-- Professores (8)
INSERT INTO pessoas (nome, email, id_grupo_pessoa) VALUES
('Dr. Roberto Mendes', 'roberto.mendes@professor.edu.br', 2),
('Dra. Claudia Fernandes', 'claudia.fernandes@professor.edu.br', 2),
('Dr. Marcelo Gonçalves', 'marcelo.goncalves@professor.edu.br', 2),
('Dra. Patricia Andrade', 'patricia.andrade@professor.edu.br', 2),
('Dr. Ricardo Sousa', 'ricardo.sousa@professor.edu.br', 2),
('Dra. Silvia Oliveira', 'silvia.oliveira@professor.edu.br', 2),
('Dr. Eduardo Monteiro', 'eduardo.monteiro@professor.edu.br', 2),
('Dra. Luciana Cavalcanti', 'luciana.cavalcanti@professor.edu.br', 2);

-- Pesquisadores (4)
INSERT INTO pessoas (nome, email, id_grupo_pessoa) VALUES
('Dr. Fernando Silva', 'fernando.silva@pesquisador.edu.br', 3),
('Dra. Amanda Costa', 'amanda.costa@pesquisador.edu.br', 3),
('Dr. Rodrigo Lima', 'rodrigo.lima@pesquisador.edu.br', 3),
('Dra. Cristina Santos', 'cristina.santos@pesquisador.edu.br', 3);

-- Auditores (4)
INSERT INTO pessoas (nome, email, id_grupo_pessoa) VALUES
('Carlos Mendonça', 'carlos.mendonca@auditor.edu.br', 4),
('Sandra Vieira', 'sandra.vieira@auditor.edu.br', 4),
('Marcos Teixeira', 'marcos.teixeira@auditor.edu.br', 4),
('Renata Cruz', 'renata.cruz@auditor.edu.br', 4);

-- 3. Insert Product Groups (grupos)
INSERT INTO grupos (nome, descricao) VALUES
('Limpeza', 'Produtos para limpeza e higienização'),
('Controlados', 'Medicamentos controlados que precisam de receita'),
('Restritos', 'Medicamentos de acesso restrito'),
('Uso Geral', 'Materiais de uso geral e consumo'),
('Embalagens', 'Materiais para embalagem e acondicionamento');

-- 4. Insert Storage Places (lugares)
INSERT INTO lugares (nome, descricao) VALUES
('Almoxarifado DCCT', 'Almoxarifado para produtos de limpeza'),
('Sala 10 bloco CD', 'Sala segura para armazenamento de medicamentos controlados'),
('Sala Gilmar', 'Sala de acesso restrito para medicamentos especiais'),
('Estante A Laboratório do Bloco CD', 'Estante para produtos de uso geral'),
('Sala estoque Bloco CD', 'Sala para armazenamento de embalagens e materiais');

-- 5. Insert Manufacturers (fabricantes)
INSERT INTO fabricantes (nome, cnpj, endereco, email) VALUES
('Johnson & Johnson', '12.345.678/0001-01', 'Av. Principal, 100, São Paulo, SP', 'contato@jnj.com.br'),
('Pfizer', '23.456.789/0001-02', 'Rua Industrial, 200, Rio de Janeiro, RJ', 'vendas@pfizer.com.br'),
('Roche', '34.567.890/0001-03', 'Av. das Indústrias, 300, Belo Horizonte, MG', 'sac@roche.com.br'),
('Bayer', '45.678.901/0001-04', 'Rua das Farmácias, 400, Porto Alegre, RS', 'contato@bayer.com.br'),
('EMS', '56.789.012/0001-05', 'Av. dos Químicos, 500, Recife, PE', 'comercial@ems.com.br'),
('Novartis', '67.890.123/0001-06', 'Rua dos Laboratórios, 600, Salvador, BA', 'atendimento@novartis.com.br'),
('Sanofi', '78.901.234/0001-07', 'Av. França, 700, São Paulo, SP', 'vendas@sanofi.com.br'),
('GSK', '89.012.345/0001-08', 'Rua Inglaterra, 800, Campinas, SP', 'contato@gsk.com.br'),
('Medley', '90.123.456/0001-09', 'Av. Medicinal, 900, Guarulhos, SP', 'sac@medley.com.br'),
('Aché', '01.234.567/0001-10', 'Rua das Fórmulas, 1000, São Paulo, SP', 'comercial@ache.com.br'),
('OM Química', '12.345.678/0002-11', 'Av. dos Limpadores, 100, Rio de Janeiro, RJ', 'vendas@omquimica.com.br'),
('CleanLife', '23.456.789/0002-12', 'Rua Higiênica, 200, São Paulo, SP', 'contato@cleanlife.com.br'),
('Asséptica', '34.567.890/0002-13', 'Av. da Limpeza, 300, Belo Horizonte, MG', 'comercial@asseptica.com.br'),
('Embalamax', '45.678.901/0002-14', 'Rua do Papelão, 400, Curitiba, PR', 'vendas@embalamax.com.br'),
('PackPro', '56.789.012/0002-15', 'Av. das Caixas, 500, São Paulo, SP', 'atendimento@packpro.com.br');

-- 6. Insert Products (produtos)
-- Produtos de Limpeza (8)
INSERT INTO produtos (nome, id_grupo, id_fabricante, tipo, preco, descricao) VALUES
('Detergente Multiuso', 1, 11, 'Líquido', 15.90, 'Detergente para limpeza geral'),
('Álcool 70%', 1, 12, 'Líquido', 9.50, 'Álcool para desinfecção'),
('Desinfetante', 1, 13, 'Líquido', 12.75, 'Desinfetante para pisos e superfícies'),
('Sabonete Líquido', 1, 11, 'Líquido', 18.90, 'Sabonete para higienização das mãos'),
('Limpa Vidros', 1, 12, 'Líquido', 14.50, 'Limpador específico para vidros'),
('Água sanitária', 1, 13, 'Líquido', 8.25, 'Produto à base de cloro para desinfecção'),
('Cera Líquida', 1, 11, 'Líquido', 22.90, 'Cera para pisos'),
('Removedor de Gordura', 1, 12, 'Líquido', 16.75, 'Removedor de gordura para cozinhas');

-- Produtos Controlados (30)
INSERT INTO produtos (nome, id_grupo, id_fabricante, tipo, preco, descricao) VALUES
('Ritalina 10mg', 2, 1, 'Comprimido', 89.90, 'Metilfenidato para TDAH - caixa com 30'),
('Rivotril 2mg', 2, 2, 'Comprimido', 45.75, 'Clonazepam para ansiedade - caixa com 30'),
('Gardenal 100mg', 2, 3, 'Comprimido', 32.50, 'Fenobarbital para convulsões - caixa com 20'),
('Morfina 10mg', 2, 4, 'Ampola', 120.00, 'Analgésico opioide - caixa com 5 ampolas'),
('Tramadol 50mg', 2, 5, 'Cápsula', 65.30, 'Analgésico opioide fraco - caixa com 20'),
('Fentanil 50mcg', 2, 6, 'Adesivo', 210.75, 'Analgésico opioide - caixa com 5 adesivos'),
('Zolpidem 10mg', 2, 7, 'Comprimido', 55.90, 'Hipnótico para insônia - caixa com 20'),
('Alprazolam 1mg', 2, 8, 'Comprimido', 42.80, 'Benzodiazepínico para ansiedade - caixa com 30'),
('Bromazepam 3mg', 2, 9, 'Comprimido', 35.60, 'Benzodiazepínico para ansiedade - caixa com 30'),
('Diazepam 10mg', 2, 10, 'Comprimido', 29.90, 'Benzodiazepínico para ansiedade - caixa com 30'),
('Clonazepam 2mg', 2, 1, 'Comprimido', 38.45, 'Antiepilético e ansiolítico - caixa com 30'),
('Fenobarbital 100mg', 2, 2, 'Comprimido', 34.20, 'Antiepilético - caixa com 20'),
('Metilfenidato 10mg', 2, 3, 'Comprimido', 82.90, 'Estimulante para TDAH - caixa com 30'),
('Codeína 30mg', 2, 4, 'Comprimido', 48.75, 'Analgésico opioide fraco - caixa com 20'),
('Oxicodona 10mg', 2, 5, 'Comprimido', 145.60, 'Analgésico opioide - caixa com 20'),
('Lorazepam 2mg', 2, 6, 'Comprimido', 36.90, 'Benzodiazepínico para ansiedade - caixa com 30'),
('Midazolam 15mg', 2, 7, 'Comprimido', 65.30, 'Benzodiazepínico para sedação - caixa com 20'),
('Zopiclona 7,5mg', 2, 8, 'Comprimido', 52.40, 'Hipnótico para insônia - caixa com 20'),
('Zaleplon 10mg', 2, 9, 'Cápsula', 54.70, 'Hipnótico para insônia - caixa com 20'),
('Buprenorfina 5mg', 2, 10, 'Adesivo', 180.30, 'Analgésico opioide - caixa com 4 adesivos'),
('Metadona 10mg', 2, 1, 'Comprimido', 125.80, 'Analgésico opioide - caixa com 20'),
('Flunitrazepam 1mg', 2, 2, 'Comprimido', 47.60, 'Benzodiazepínico hipnótico - caixa com 20'),
('Anfepramona 25mg', 2, 3, 'Cápsula', 75.40, 'Anorexígeno - caixa com 20'),
('Femproporex 25mg', 2, 4, 'Cápsula', 82.30, 'Anorexígeno - caixa com 20'),
('Mazindol 1mg', 2, 5, 'Comprimido', 78.90, 'Anorexígeno - caixa com 20'),
('Sibutramina 15mg', 2, 6, 'Cápsula', 95.60, 'Anorexígeno - caixa com 30'),
('Tapentadol 50mg', 2, 7, 'Comprimido', 135.70, 'Analgésico opioide - caixa com 20'),
('Pregabalina 75mg', 2, 8, 'Cápsula', 75.30, 'Antiepilético e analgésico - caixa com 30'),
('Gabapentina 300mg', 2, 9, 'Cápsula', 68.50, 'Antiepilético e analgésico - caixa com 30'),
('Selegilina 5mg', 2, 10, 'Comprimido', 45.80, 'Antiparkinsoniano - caixa com 20');

-- Produtos Restritos (10)
INSERT INTO produtos (nome, id_grupo, id_fabricante, tipo, preco, descricao) VALUES
('Cetamina 50mg/ml', 3, 1, 'Injetável', 230.90, 'Anestésico - frasco 10ml'),
('Propofol 10mg/ml', 3, 2, 'Injetável', 185.75, 'Anestésico - ampola 20ml'),
('Tiopental 500mg', 3, 3, 'Injetável', 174.30, 'Anestésico - frasco-ampola'),
('Pancurônio 2mg/ml', 3, 4, 'Injetável', 198.50, 'Bloqueador neuromuscular - ampola 2ml'),
('Vecurônio 10mg', 3, 5, 'Injetável', 203.40, 'Bloqueador neuromuscular - frasco-ampola'),
('Midazolam 5mg/ml', 3, 6, 'Injetável', 136.70, 'Sedativo - ampola 3ml'),
('Fentanila 0,05mg/ml', 3, 7, 'Injetável', 168.90, 'Analgésico opioide - ampola 5ml'),
('Etomidato 2mg/ml', 3, 8, 'Injetável', 155.80, 'Anestésico - ampola 10ml'),
('Dexmedetomidina 100mcg/ml', 3, 9, 'Injetável', 245.60, 'Sedativo - frasco 2ml'),
('Succinilcolina 100mg', 3, 10, 'Injetável', 178.30, 'Bloqueador neuromuscular - frasco-ampola');

-- Produtos de Uso Geral (20)
INSERT INTO produtos (nome, id_grupo, id_fabricante, tipo, preco, descricao) VALUES
('Paracetamol 500mg', 4, 1, 'Comprimido', 12.90, 'Analgésico e antitérmico - caixa com 20'),
('Ibuprofeno 600mg', 4, 2, 'Comprimido', 15.75, 'Anti-inflamatório - caixa com 20'),
('Dipirona 500mg', 4, 3, 'Comprimido', 9.50, 'Analgésico e antitérmico - caixa com 20'),
('Amoxicilina 500mg', 4, 4, 'Cápsula', 32.80, 'Antibiótico - caixa com 21'),
('Omeprazol 20mg', 4, 5, 'Cápsula', 18.90, 'Protetor gástrico - caixa com 28'),
('Losartana 50mg', 4, 6, 'Comprimido', 22.75, 'Anti-hipertensivo - caixa com 30'),
('Sinvastatina 20mg', 4, 7, 'Comprimido', 24.50, 'Redutor de colesterol - caixa com 30'),
('Metformina 850mg', 4, 8, 'Comprimido', 19.80, 'Antidiabético - caixa com 30'),
('Soro fisiológico 100ml', 4, 9, 'Líquido', 5.90, 'Solução salina - frasco'),
('Água destilada 100ml', 4, 10, 'Líquido', 4.30, 'Água para diluição - frasco'),
('Algodão hidrófilo 100g', 4, 1, 'Material', 8.75, 'Para uso hospitalar - pacote'),
('Gaze estéril 10x10cm', 4, 2, 'Material', 12.90, 'Para curativos - pacote com 10'),
('Atadura elástica 10cm', 4, 3, 'Material', 11.50, 'Para compressão e imobilização - unidade'),
('Esparadrapo 10cmx4,5m', 4, 4, 'Material', 14.90, 'Fita adesiva - rolo'),
('Luvas de procedimento M', 4, 5, 'Material', 35.80, 'Luvas de látex - caixa com 100'),
('Máscara cirúrgica', 4, 6, 'Material', 25.90, 'Tripla camada - caixa com 50'),
('Micropore 25mmx10m', 4, 7, 'Material', 9.75, 'Fita hipoalergênica - rolo'),
('Termômetro digital', 4, 8, 'Material', 28.50, 'Para medição de temperatura - unidade'),
('Seringa descartável 5ml', 4, 9, 'Material', 15.40, 'Com agulha - pacote com 10'),
('Álcool gel 70% 500ml', 4, 10, 'Líquido', 17.90, 'Para higienização das mãos - frasco');

-- Produtos de Embalagens (10)
INSERT INTO produtos (nome, id_grupo, id_fabricante, tipo, preco, descricao) VALUES
('Caixa papelão pequena', 5, 14, 'Embalagem', 3.50, 'Caixa para acondicionamento - unidade'),
('Caixa papelão média', 5, 15, 'Embalagem', 5.75, 'Caixa para acondicionamento - unidade'),
('Caixa papelão grande', 5, 14, 'Embalagem', 8.90, 'Caixa para acondicionamento - unidade'),
('Fita adesiva transparente', 5, 15, 'Material', 7.50, 'Para fechamento de embalagens - rolo'),
('Fita adesiva marrom', 5, 14, 'Material', 8.25, 'Para fechamento de embalagens - rolo'),
('Saco plástico pequeno', 5, 15, 'Embalagem', 12.90, 'Pacote com 100 unidades'),
('Saco plástico médio', 5, 14, 'Embalagem', 17.50, 'Pacote com 100 unidades'),
('Saco plástico grande', 5, 15, 'Embalagem', 22.75, 'Pacote com 100 unidades'),
('Papel bolha 1,2mx100m', 5, 14, 'Material', 85.90, 'Para proteção de itens frágeis - rolo'),
('Etiquetas adesivas', 5, 15, 'Material', 14.30, 'Para identificação - folha com 30');

-- 7. Insert initial movements (movimentos)
-- Inserir algumas entradas de produtos
INSERT INTO movimentos (id_produto, id_pessoa, id_lugar, tipo, quantidade, observacao, data_movimento)
VALUES
-- Entradas de produtos de limpeza no Almoxarifado DCCT
(1, 33, 1, 'entrada', 50, 'Recebimento inicial', NOW() - INTERVAL '30 days'),
(2, 33, 1, 'entrada', 100, 'Recebimento inicial', NOW() - INTERVAL '30 days'),
(3, 33, 1, 'entrada', 40, 'Recebimento inicial', NOW() - INTERVAL '29 days'),
(4, 33, 1, 'entrada', 30, 'Recebimento inicial', NOW() - INTERVAL '28 days'),

-- Entradas de produtos controlados na Sala 10 bloco CD
(9, 25, 2, 'entrada', 20, 'Medicamentos controlados', NOW() - INTERVAL '27 days'),
(10, 25, 2, 'entrada', 15, 'Medicamentos controlados', NOW() - INTERVAL '27 days'),
(11, 25, 2, 'entrada', 25, 'Medicamentos controlados', NOW() - INTERVAL '26 days'),
(12, 25, 2, 'entrada', 30, 'Medicamentos controlados', NOW() - INTERVAL '26 days'),

-- Entradas de produtos restritos na Sala Gilmar
(39, 29, 3, 'entrada', 10, 'Medicamentos restritos', NOW() - INTERVAL '25 days'),
(40, 29, 3, 'entrada', 8, 'Medicamentos restritos', NOW() - INTERVAL '25 days'),
(41, 29, 3, 'entrada', 12, 'Medicamentos restritos', NOW() - INTERVAL '24 days'),

-- Entradas de produtos uso geral na Estante A Laboratório
(49, 21, 4, 'entrada', 100, 'Material uso rotineiro', NOW() - INTERVAL '23 days'),
(50, 21, 4, 'entrada', 80, 'Material uso rotineiro', NOW() - INTERVAL '23 days'),
(51, 21, 4, 'entrada', 120, 'Material uso rotineiro', NOW() - INTERVAL '22 days'),
(52, 21, 4, 'entrada', 50, 'Material uso rotineiro', NOW() - INTERVAL '21 days'),

-- Entradas de embalagens na Sala estoque Bloco CD
(69, 34, 5, 'entrada', 200, 'Estoque inicial embalagens', NOW() - INTERVAL '20 days'),
(70, 34, 5, 'entrada', 150, 'Estoque inicial embalagens', NOW() - INTERVAL '20 days'),
(71, 34, 5, 'entrada', 100, 'Estoque inicial embalagens', NOW() - INTERVAL '19 days');

-- Inserir algumas saídas de produtos
INSERT INTO movimentos (id_produto, id_pessoa, id_lugar, tipo, quantidade, observacao, data_movimento)
VALUES
-- Saídas de produtos de limpeza
(1, 1, 1, 'saida', 5, 'Limpeza laboratório', NOW() - INTERVAL '15 days'),
(2, 2, 1, 'saida', 10, 'Desinfecção sala de aula', NOW() - INTERVAL '14 days'),
(3, 3, 1, 'saida', 3, 'Limpeza banheiro', NOW() - INTERVAL '13 days'),

-- Saídas de produtos controlados
(9, 22, 2, 'saida', 2, 'Prescrição médica', NOW() - INTERVAL '12 days'),
(10, 23, 2, 'saida', 1, 'Prescrição médica', NOW() - INTERVAL '11 days'),
(11, 24, 2, 'saida', 3, 'Prescrição médica', NOW() - INTERVAL '10 days'),

-- Saídas de produtos restritos
(39, 29, 3, 'saida', 2, 'Procedimento médico', NOW() - INTERVAL '9 days'),
(40, 30, 3, 'saida', 1, 'Procedimento médico', NOW() - INTERVAL '8 days'),

-- Saídas de produtos uso geral
(49, 4, 4, 'saida', 10, 'Uso em laboratório', NOW() - INTERVAL '7 days'),
(50, 5, 4, 'saida', 5, 'Uso em laboratório', NOW() - INTERVAL '6 days'),
(51, 6, 4, 'saida', 8, 'Uso em laboratório', NOW() - INTERVAL '5 days'),

-- Saídas de embalagens
(69, 7, 5, 'saida', 20, 'Embalagem para transporte', NOW() - INTERVAL '4 days'),
(70, 8, 5, 'saida', 15, 'Embalagem para armazenamento', NOW() - INTERVAL '3 days');