-- filepath: /Users/gutembergmedeiros/Work/SIPROQUIM/scripts/gerar_movimentos.sql
-- Script para gerar 4.000 movimentos para o SIPROQUIM
-- Data: 10/01/2025 à 10/04/2025 (sem sábados e domingos)

DO $$
DECLARE
    total_movimentos INTEGER := 4000;
    contador INTEGER := 0;
    data_inicio DATE := '2025-01-10';
    data_fim DATE := '2025-04-10';
    data_atual DATE;
    dia_semana INTEGER;

    id_produto INTEGER;
    id_pessoa INTEGER;
    id_lugar INTEGER;
    tipo_movimento TEXT;
    quantidade INTEGER;
    observacao TEXT;
    hora_aleatoria TIME;
    data_movimento TIMESTAMP;

    -- Arrays para gerar dados aleatórios
    tipos TEXT[] := ARRAY['entrada', 'saida'];
    observacoes_entrada TEXT[] := ARRAY[
        'Compra de reposição',
        'Aquisição para laboratório',
        'Transferência de outro setor',
        'Doação recebida',
        'Devolução de material',
        'Aquisição para pesquisa',
        'Importação',
        'Reposição de estoque',
        'Compra emergencial',
        'Material para análise',
        'Material para experimentos',
        'Compra programada',
        'Material remanejado'
    ];
    observacoes_saida TEXT[] := ARRAY[
        'Uso em análises',
        'Uso em pesquisa',
        'Transferência para laboratório',
        'Material para experimentos',
        'Consumo em aula prática',
        'Material para calibração',
        'Uso em processo de controle',
        'Demonstração técnica',
        'Transferência para outro setor',
        'Empréstimo para outro laboratório',
        'Utilizado em síntese',
        'Preparo de soluções',
        'Consumo em análise de rotina'
    ];

BEGIN
    -- Inicia o loop para gerar movimentos
    data_atual := data_inicio;

    WHILE contador < total_movimentos AND data_atual <= data_fim LOOP
        -- Verifica se é dia útil (1=segunda, 5=sexta, 6=sábado, 7=domingo)
        dia_semana := EXTRACT(DOW FROM data_atual);

        -- Pula sábados (6) e domingos (0)
        IF dia_semana <> 0 AND dia_semana <> 6 THEN
            -- Cada dia útil terá múltiplos movimentos
            FOR i IN 1..30 LOOP
                -- Sai do loop se já atingiu o número total de movimentos
                IF contador >= total_movimentos THEN
                    EXIT;
                END IF;

                -- Seleciona produto aleatório (1-300)
                id_produto := 1 + floor(random() * 300);

                -- Seleciona pessoa aleatória (1-30)
                id_pessoa := 1 + floor(random() * 30);

                -- Seleciona lugar aleatório (1-5)
                id_lugar := 1 + floor(random() * 5);

                -- Determina tipo de movimento (60% entrada, 40% saída)
                IF random() <= 0.6 THEN
                    tipo_movimento := 'entrada';
                    -- Gera quantidade entre 1 e 20 para entradas
                    quantidade := 1 + floor(random() * 20);
                    -- Seleciona observação aleatória para entrada
                    observacao := observacoes_entrada[1 + floor(random() * array_length(observacoes_entrada, 1))];
                ELSE
                    tipo_movimento := 'saida';
                    -- Gera quantidade entre 1 e 10 para saídas
                    quantidade := 1 + floor(random() * 10);
                    -- Seleciona observação aleatória para saída
                    observacao := observacoes_saida[1 + floor(random() * array_length(observacoes_saida, 1))];
                END IF;

                -- Gera hora aleatória entre 8:00 e 18:00
                hora_aleatoria := make_time(
                    8 + floor(random() * 10)::integer,  -- Hora entre 8 e 17
                    floor(random() * 60)::integer,      -- Minuto entre 0 e 59
                    floor(random() * 60)::integer       -- Segundo entre 0 e 59
                );

                -- Combina data e hora
                data_movimento := data_atual + hora_aleatoria;

                -- Insere o movimento
                INSERT INTO movimentos (id_produto, id_pessoa, id_lugar, tipo, quantidade, observacao, data_movimento)
                VALUES (id_produto, id_pessoa, id_lugar, tipo_movimento, quantidade, observacao, data_movimento);

                contador := contador + 1;
            END LOOP;
        END IF;

        -- Avança para o próximo dia
        data_atual := data_atual + INTERVAL '1 day';
    END LOOP;

    RAISE NOTICE 'Gerados % movimentos com sucesso no período de % a %.', contador, data_inicio, data_fim;
END $$;