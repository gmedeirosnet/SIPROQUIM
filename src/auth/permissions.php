<?php
// auth/permissions.php - Sistema de controle de permissões

// Constantes para os diferentes tipos de permissões
define('PERMISSION_CREATE', 'create');
define('PERMISSION_READ', 'read');
define('PERMISSION_UPDATE', 'update');
define('PERMISSION_DELETE', 'delete');

// Constantes para IDs dos grupos (pode ser ajustado conforme a configuração do banco de dados)
define('GROUP_ADMINISTRADORES', 1);
define('GROUP_TECNICOS', 3);  // Verificado através do populate_database.sql
define('GROUP_SUPERVISORES', 4);  // Adicionado conforme populate_database.sql

/**
 * Verifica se o usuário tem uma permissão específica
 *
 * @param string $permission Tipo de permissão (create, read, update, delete)
 * @param int $user_group_id ID do grupo do usuário
 * @return bool True se o usuário tem a permissão, false caso contrário
 */
function hasPermission($permission, $user_group_id) {
    // Administradores possuem acesso total (CRUD)
    if ($user_group_id == GROUP_ADMINISTRADORES) {
        return true;
    }

    // Técnicos e Supervisores possuem acesso parcial (CRU)
    if ($user_group_id == GROUP_TECNICOS || $user_group_id == GROUP_SUPERVISORES) {
        if ($permission == PERMISSION_DELETE) {
            return false;
        }
        return true;
    }

    // Demais grupos possuem acesso restrito (R)
    if ($permission == PERMISSION_READ) {
        return true;
    }

    return false;
}

/**
 * Verifica se o usuário pode criar registros
 *
 * @param int $user_group_id ID do grupo do usuário
 * @return bool True se o usuário pode criar, false caso contrário
 */
function canCreate($user_group_id) {
    return hasPermission(PERMISSION_CREATE, $user_group_id);
}

/**
 * Verifica se o usuário pode ler registros
 *
 * @param int $user_group_id ID do grupo do usuário
 * @return bool True se o usuário pode ler, false caso contrário
 */
function canRead($user_group_id) {
    return hasPermission(PERMISSION_READ, $user_group_id);
}

/**
 * Verifica se o usuário pode atualizar registros
 *
 * @param int $user_group_id ID do grupo do usuário
 * @return bool True se o usuário pode atualizar, false caso contrário
 */
function canUpdate($user_group_id) {
    return hasPermission(PERMISSION_UPDATE, $user_group_id);
}

/**
 * Verifica se o usuário pode excluir registros
 *
 * @param int $user_group_id ID do grupo do usuário
 * @return bool True se o usuário pode excluir, false caso contrário
 */
function canDelete($user_group_id) {
    return hasPermission(PERMISSION_DELETE, $user_group_id);
}

/**
 * Verifica se o usuário tem permissão e redireciona para uma página de erro se não tiver
 *
 * @param string $permission Tipo de permissão (create, read, update, delete)
 * @param int $user_group_id ID do grupo do usuário
 * @param string $redirect_url URL para redirecionar em caso de acesso negado (opcional)
 * @return void
 */
function requirePermission($permission, $user_group_id, $redirect_url = null) {
    if (!hasPermission($permission, $user_group_id)) {
        // Se um URL de redirecionamento específico não foi fornecido, use o padrão
        if ($redirect_url === null) {
            $redirect_url = '/auth/access_denied.php';
        }

        // Redirecionar para a página de acesso negado
        header('Location: ' . $redirect_url);
        exit;
    }
}

/**
 * Retorna um array com todas as permissões do usuário
 *
 * @param int $user_group_id ID do grupo do usuário
 * @return array Array associativo com as permissões
 */
function getUserPermissions($user_group_id) {
    return [
        'create' => canCreate($user_group_id),
        'read' => canRead($user_group_id),
        'update' => canUpdate($user_group_id),
        'delete' => canDelete($user_group_id)
    ];
}

/**
 * Verifica se o usuário é administrador
 *
 * @param int $user_group_id ID do grupo do usuário
 * @return bool True se o usuário for administrador, false caso contrário
 */
function isAdmin($user_group_id) {
    return $user_group_id == GROUP_ADMINISTRADORES;
}

/**
 * Verifica se o usuário é administrador e redireciona para uma página de erro se não for
 *
 * @param int $user_group_id ID do grupo do usuário
 * @param string $redirect_url URL para redirecionar em caso de acesso negado (opcional)
 * @return void
 */
function requireAdmin($user_group_id, $redirect_url = null) {
    if (!isAdmin($user_group_id)) {
        // Se um URL de redirecionamento específico não foi fornecido, use o padrão
        if ($redirect_url === null) {
            $redirect_url = '/auth/access_denied.php';
        }

        // Redirecionar para a página de acesso negado
        header('Location: ' . $redirect_url);
        exit;
    }
}