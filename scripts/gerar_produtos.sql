-- filepath: /Users/gutembergmedeiros/Work/SIPROQUIM/scripts/gerar_produtos.sql
-- Script para gerar 300 produtos para o SIPROQUIM
-- Executar após os produtos iniciais

DO $$
DECLARE
    i INTEGER;
    nome_produto TEXT;
    grupo_id INTEGER;
    fabricante_id INTEGER;
    referencia_produto TEXT;
    tipo_produto TEXT;
    volume_produto TEXT;
    unidade TEXT;
    preco NUMERIC(10,2);
    descricao TEXT;

    -- Arrays para gerar dados aleatórios
    tipos TEXT[] := ARRAY['Líquido', 'Sólido', 'Gás', 'Pó', 'Cristal', 'Gel'];
    volumes TEXT[] := ARRAY['50', '100', '250', '500', '1000', '2000', '5000'];
    unidades TEXT[] := ARRAY['ml', 'g', 'L', 'mg', 'kg'];

    -- Array para gerar referências
    codigos TEXT[] := ARRAY['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

    -- Prefixos e sufixos para nomes de produtos
    prefixos TEXT[] := ARRAY['Meta', 'Etil', 'Poli', 'Mono', 'Tri', 'Di', 'Tetra', 'Hidro', 'Oxi', 'Nitro', 'Cloro', 'Flúor', 'Bromo', 'Iodo', 'Carboxi'];
    substancias TEXT[] := ARRAY['benzeno', 'metano', 'etanol', 'propanol', 'fenol', 'acetato', 'cloreto', 'sulfato', 'nitrato', 'fosfato', 'carbonato', 'amina', 'amida', 'tolueno', 'xileno', 'glicerol', 'glicose', 'sacarose', 'lactose', 'frutose'];
    sufixos TEXT[] := ARRAY['P.A.', 'U.S.P.', 'Técnico', 'Comercial', 'Ultra Puro', 'Grau HPLC', 'Grau Cromatográfico', 'Reagente', 'Puro', 'Anidro', 'Monohidratado'];

    -- Descrições
    desc_prefixos TEXT[] := ARRAY['Pureza mínima ', 'Concentração ', 'Teor mínimo ', 'Grau ', 'Qualidade ', 'Especificação '];
    desc_valores TEXT[] := ARRAY['99%', '99.5%', '99.9%', '98%', '95%', '90%', 'ACS', 'Analítico', 'Farmacêutico', 'Industrial', 'Laboratorial'];
    desc_usos TEXT[] := ARRAY[
        'para análises químicas',
        'para uso em laboratório',
        'para síntese orgânica',
        'para cromatografia',
        'para espectrofotometria',
        'para pesquisa',
        'para controle de qualidade',
        'para processos industriais',
        'para preparação de soluções',
        'para calibração de instrumentos',
        'para uso em bioquímica',
        'para uso farmacêutico'
    ];
BEGIN
    -- Criar produtos do id 26 ao 300
    FOR i IN 26..300 LOOP
        -- Gerar nome de produto aleatório
        nome_produto := prefixos[1 + floor(random() * array_length(prefixos, 1))] ||
                      substancias[1 + floor(random() * array_length(substancias, 1))] || ' ' ||
                      sufixos[1 + floor(random() * array_length(sufixos, 1))];

        -- Atribuir a um grupo (1-7)
        grupo_id := 1 + floor(random() * 7);

        -- Atribuir a um fabricante (1-6)
        fabricante_id := 1 + floor(random() * 6);

        -- Gerar referência aleatória (formato: XX-9999)
        referencia_produto :=
            codigos[1 + floor(random() * array_length(codigos, 1))] ||
            codigos[1 + floor(random() * array_length(codigos, 1))] || '-' ||
            LPAD(CAST((1000 + floor(random() * 8999)) AS TEXT), 4, '0');

        -- Gerar tipo aleatório
        tipo_produto := tipos[1 + floor(random() * array_length(tipos, 1))];

        -- Gerar volume aleatório
        volume_produto := volumes[1 + floor(random() * array_length(volumes, 1))];

        -- Gerar unidade aleatória
        unidade := unidades[1 + floor(random() * array_length(unidades, 1))];

        -- Gerar preço aleatório entre 10 e 500
        preco := 10 + random() * 490;

        -- Gerar descrição aleatória
        descricao := desc_prefixos[1 + floor(random() * array_length(desc_prefixos, 1))] ||
                   desc_valores[1 + floor(random() * array_length(desc_valores, 1))] || ', ' ||
                   desc_usos[1 + floor(random() * array_length(desc_usos, 1))];

        -- Inserir o produto
        INSERT INTO produtos (nome, id_grupo, id_fabricante, referencia, tipo, volume, unidade_medida, preco, descricao)
        VALUES (nome_produto, grupo_id, fabricante_id, referencia_produto, tipo_produto, volume_produto, unidade, ROUND(preco::numeric, 2), descricao);
    END LOOP;

    RAISE NOTICE 'Gerados 275 produtos adicionais com sucesso (total 300).';
END $$;