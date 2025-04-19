-- Script para adicionar o usuário admin@admin com senha 'password'
-- Primeiro verificamos se o usuário já existe
DO $$
DECLARE
    admin_id INTEGER;
    admin_grupo_id INTEGER;
BEGIN
    -- Obter o ID do grupo de administradores ou criar um se não existir
    SELECT id INTO admin_grupo_id FROM grupos_pessoas WHERE nome = 'Administradores' LIMIT 1;

    IF admin_grupo_id IS NULL THEN
        INSERT INTO grupos_pessoas (nome, descricao)
        VALUES ('Administradores', 'Grupo com acesso total ao sistema')
        RETURNING id INTO admin_grupo_id;
    END IF;

    -- Verificar se o usuário admin@admin já existe
    SELECT id INTO admin_id FROM pessoas WHERE email = 'admin@admin';

    IF admin_id IS NULL THEN
        -- Criar o usuário admin com a senha 'password' (hash bcrypt)
        INSERT INTO pessoas (nome, email, id_grupo_pessoa, enable, password)
        VALUES (
            'Administrador',
            'admin@admin',
            admin_grupo_id,
            TRUE,
            '$2y$10$Hcv3XtquK6gAQa8zFf2eoeX7mx9Ic4eL.aDYwS7fCm.rSx5WtLVm2'  -- hash de 'password'
        );
        RAISE NOTICE 'Usuário admin@admin criado com sucesso';
    ELSE
        -- Atualizar o usuário existente
        UPDATE pessoas
        SET nome = 'Administrador',
            id_grupo_pessoa = admin_grupo_id,
            enable = TRUE,
            password = '$2y$10$Hcv3XtquK6gAQa8zFf2eoeX7mx9Ic4eL.aDYwS7fCm.rSx5WtLVm2'  -- hash de 'password'
        WHERE id = admin_id;
        RAISE NOTICE 'Usuário admin@admin atualizado com sucesso';
    END IF;
END
$$;