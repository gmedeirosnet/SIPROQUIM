-- Script para adicionar senha à tabela pessoas
ALTER TABLE pessoas ADD COLUMN IF NOT EXISTS password VARCHAR(255);

-- Define uma senha padrão (hash de '123456') para todos os usuários que não têm senha
UPDATE pessoas SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE password IS NULL;sac