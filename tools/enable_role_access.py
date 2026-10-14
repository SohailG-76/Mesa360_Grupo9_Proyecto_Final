#!/usr/bin/env python3
from __future__ import annotations
import os, pwd, shutil, stat, subprocess, sys, tempfile
from datetime import datetime
from pathlib import Path

INDEX = Path(os.environ.get('MESA360_INDEX_PATH', '/var/www/html/index.php'))
RBAC_BEGIN = '/* MESA360_RBAC_BEGIN */'
RBAC_END = '/* MESA360_RBAC_END */'
STATE_FILE = Path(os.environ.get('MESA360_STATE_FILE', '/home/mesa360/.mesa360_role_access_backup_path'))

RBAC_BLOCK = r'''/* MESA360_RBAC_BEGIN */
$mesa360RolePermissions = [];
function mesa360RbacDeny(string $message, int $status = 403): never {
    http_response_code($status);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Acceso restringido | Mesa360</title><link rel="stylesheet" href="/assets/css/app.css"><link rel="stylesheet" href="/assets/css/auth.css"></head><body class="login-page"><main class="login-form-panel" style="min-height:100vh;display:grid;place-items:center;padding:32px"><section class="login-form-wrap"><div class="login-heading"><span class="login-eyebrow">MESA360</span><h2>Acceso restringido</h2><p>' . h($message) . '</p></div><a class="login-submit" href="/?page=dashboard">Volver al panel</a></section></main></body></html>';
    exit;
}
function mesa360HasPermission(string $module, string $action): bool {
    global $mesa360RolePermissions;
    return !empty($mesa360RolePermissions[$module][$action]);
}
if (!$pdo instanceof PDO) {
    error_log('[Mesa360] RBAC could not validate permissions because the database connection is unavailable.');
    mesa360RbacDeny('No se pudieron validar tus permisos porque la base de datos no está disponible. Inténtalo de nuevo más tarde.', 503);
}
try {
    $mesa360RoleId = (int)($_SESSION['mesa360_user_role_id'] ?? 0);
    if ($mesa360RoleId < 1) mesa360RbacDeny('Tu sesión no incluye un rol válido. Cierra sesión e inicia de nuevo.', 403);
    $mesa360PermStmt = $pdo->prepare('SELECT modulo, accion FROM PERMISO WHERE id_rol = :role_id');
    $mesa360PermStmt->execute(['role_id' => $mesa360RoleId]);
    foreach ($mesa360PermStmt->fetchAll() as $mesa360Perm) {
        $mesa360RolePermissions[(string)$mesa360Perm['modulo']][(string)$mesa360Perm['accion']] = true;
    }
} catch (Throwable $mesa360RbacException) {
    error_log('[Mesa360] RBAC permission load failed: ' . $mesa360RbacException->getMessage());
    mesa360RbacDeny('No se pudieron cargar tus permisos. Contacta al administrador del sistema.', 503);
}

if (!mesa360HasPermission($page, 'ver')) {
    mesa360RbacDeny('Tu rol no tiene permiso para acceder a este módulo. Si necesitas acceso, contacta al administrador.');
}

$mesa360ExtraGetPermission = null;
if ($page === 'menu') {
    if (isset($_GET['new_product']) || isset($_GET['new_category'])) $mesa360ExtraGetPermission = 'crear';
    if (isset($_GET['edit_product']) || isset($_GET['edit_category'])) $mesa360ExtraGetPermission = 'editar';
}
if (isset($entityModules[$page])) {
    if (isset($_GET['new'])) $mesa360ExtraGetPermission = 'crear';
    if (isset($_GET['edit'])) $mesa360ExtraGetPermission = 'editar';
}
if ($page === 'pedidos') {
    if (isset($_GET['new'])) $mesa360ExtraGetPermission = 'crear';
    if (isset($_GET['edit'])) $mesa360ExtraGetPermission = 'editar';
}
if ($page === 'pagos' && (($_GET['tab'] ?? 'payments') === 'methods' || isset($_GET['new_method']) || isset($_GET['edit_method']))) {
    $mesa360ExtraGetPermission = 'gestionar_metodos';
}
if ($mesa360ExtraGetPermission !== null && !mesa360HasPermission($page, $mesa360ExtraGetPermission)) {
    mesa360RbacDeny('Tu rol no tiene permiso para realizar esta acción.');
}

$mesa360RequiredAction = 'ver';
$mesa360ExtraPostPermission = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $mesa360PostedAction = isset($_POST['action']) && is_scalar($_POST['action']) ? trim((string)$_POST['action']) : '';
    $mesa360RecordId = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $mesa360RecordId = $mesa360RecordId === false ? 0 : (int)$mesa360RecordId;
    if ($page === 'menu') {
        if (in_array($mesa360PostedAction, ['delete_product','delete_category'], true)) $mesa360RequiredAction = 'eliminar';
        elseif (in_array($mesa360PostedAction, ['save_product','save_category'], true)) $mesa360RequiredAction = $mesa360RecordId > 0 ? 'editar' : 'crear';
        else $mesa360RequiredAction = '__invalid__';
    } elseif ($page === 'pedidos') {
        if ($mesa360PostedAction === 'delete_order') $mesa360RequiredAction = 'eliminar';
        elseif ($mesa360PostedAction === 'save_order') $mesa360RequiredAction = $mesa360RecordId > 0 ? 'editar' : 'crear';
        else $mesa360RequiredAction = '__invalid__';
    } elseif (isset($entityModules[$page])) {
        if ($mesa360PostedAction === 'delete_record') $mesa360RequiredAction = 'eliminar';
        elseif ($mesa360PostedAction === 'save_record') $mesa360RequiredAction = $mesa360RecordId > 0 ? 'editar' : 'crear';
        else $mesa360RequiredAction = '__invalid__';
    } elseif ($page === 'movimientos') {
        $mesa360RequiredAction = $mesa360PostedAction === 'record_movement' ? 'registrar' : '__invalid__';
    } elseif ($page === 'pagos') {
        if ($mesa360PostedAction === 'record_payment') $mesa360RequiredAction = 'registrar';
        elseif ($mesa360PostedAction === 'save_method') {
            $mesa360MethodId = filter_var($_POST['id_metodo_pago'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            $mesa360MethodId = $mesa360MethodId === false ? 0 : (int)$mesa360MethodId;
            $mesa360RequiredAction = $mesa360MethodId > 0 ? 'editar' : 'crear';
            $mesa360ExtraPostPermission = 'gestionar_metodos';
        } elseif ($mesa360PostedAction === 'delete_method') {
            $mesa360RequiredAction = 'eliminar';
            $mesa360ExtraPostPermission = 'gestionar_metodos';
        } else $mesa360RequiredAction = '__invalid__';
    } else {
        $mesa360RequiredAction = '__invalid__';
    }
    if ($mesa360RequiredAction === '__invalid__' || !mesa360HasPermission($page, $mesa360RequiredAction)) {
        mesa360RbacDeny('Tu rol no tiene permiso para realizar esta operación.');
    }
    if ($mesa360ExtraPostPermission !== null && !mesa360HasPermission($page, $mesa360ExtraPostPermission)) {
        mesa360RbacDeny('La configuración de métodos de pago está reservada al administrador.');
    }
}
if ($page === 'pagos' && !mesa360HasPermission($page, 'gestionar_metodos')) {
    // Cashiers may use the payments tab, but cannot browse payment-method administration.
    $navigation = array_values(array_filter($navigation, static fn(array $item): bool => $item['page'] !== 'pagos' || mesa360HasPermission('pagos', 'ver')));
}
$navigation = array_values(array_filter($navigation, static fn(array $item): bool => mesa360HasPermission($item['page'], 'ver')));
/* MESA360_RBAC_END */'''

def require_once_text(content: str, old: str, new: str, label: str, expected: int = 1) -> str:
    count = content.count(old)
    if count != expected:
        raise RuntimeError(f'{label}: expected {expected} occurrence(s), found {count}. No changes were made.')
    return content.replace(old, new, expected)

def wrap_form(content: str, opening_tag: str, condition: str, label: str) -> str:
    count = content.count(opening_tag)
    if count != 1:
        raise RuntimeError(f'{label}: expected one form opening, found {count}. No changes were made.')
    marked_open = f"<?php if ({condition}): ?>" + opening_tag
    content = content.replace(opening_tag, marked_open, 1)
    start = content.index(marked_open) + len(marked_open)
    close = content.find('</form>', start)
    if close < 0:
        raise RuntimeError(f'{label}: form closing tag was not found. No changes were made.')
    close_end = close + len('</form>')
    content = content[:close_end] + '<?php endif; ?>' + content[close_end:]
    return content

def patch_content(content: str) -> str:
    if '/* MESA360_AUTH_GUARD */' not in content or '<!-- MESA360_AUTH_SIDEBAR -->' not in content:
        raise RuntimeError('The login/authentication gate is not present. Confirm login works and authentication is enabled first.')
    if RBAC_BEGIN in content or RBAC_END in content:
        raise RuntimeError('Role access markers already exist. No changes were made.')
    content = require_once_text(
        content,
        "if (in_array($page, ['menu', 'clientes', 'empleados', 'mesas', 'reservas', 'pedidos', 'reportes', 'inventario', 'recetas', 'movimientos', 'pagos'], true)) {",
        "if (in_array($page, ['dashboard', 'menu', 'clientes', 'empleados', 'mesas', 'reservas', 'pedidos', 'reportes', 'inventario', 'recetas', 'movimientos', 'pagos'], true)) {",
        'database connection page list'
    )
    anchor = "    if ($page === 'menu' && $_SERVER['REQUEST_METHOD'] === 'POST' && $pdo instanceof PDO) {"
    content = require_once_text(content, anchor, RBAC_BLOCK + '\n\n' + anchor, 'RBAC insertion point')

    # Filter dashboard shortcuts as well as the sidebar. Server-side checks still protect direct URLs/actions.
    content = require_once_text(
        content,
        '                    foreach ($moduleCards as $card): ?>',
        "                    $moduleCards = array_values(array_filter($moduleCards, static fn(array $card): bool => mesa360HasPermission($card['page'], 'ver')));\n                    foreach ($moduleCards as $card): ?>",
        'dashboard module card loop'
    )

    # Hide generic create control and empty-state create link from roles without create permission.
    content = require_once_text(
        content,
        '<a class="primary-button" href="?page=<?= h($page) ?>&amp;new=1">＋ <?= h($module[\'newLabel\']) ?></a>',
        "<?php if (mesa360HasPermission($page, 'crear')): ?><a class=\"primary-button\" href=\"?page=<?= h($page) ?>&amp;new=1\">＋ <?= h($module['newLabel']) ?></a><?php endif; ?>",
        'entity create button'
    )
    content = require_once_text(
        content,
        '<a class="primary-button small-button" href="?page=<?= h($page) ?>&amp;new=1">＋ <?= h($module[\'newLabel\']) ?></a>',
        "<?php if (mesa360HasPermission($page, 'crear')): ?><a class=\"primary-button small-button\" href=\"?page=<?= h($page) ?>&amp;new=1\">＋ <?= h($module['newLabel']) ?></a><?php endif; ?>",
        'entity empty-state create button'
    )
    # Generic entity row actions: hide edit and delete independently.
    content = require_once_text(
        content,
        '<a class="text-action" href="?page=<?= h($page) ?>&amp;edit=<?= h($entityRow[$module[\'pk\']]) ?>">Editar</a>',
        "<?php if (mesa360HasPermission($page, 'editar')): ?><a class=\"text-action\" href=\"?page=<?= h($page) ?>&amp;edit=<?= h($entityRow[$module['pk']]) ?>\">Editar</a><?php endif; ?>",
        'entity row edit link'
    )
    content = wrap_form(
        content,
        '<form method="post" action="?page=<?= h($page) ?>" onsubmit="return confirm(\'¿Eliminar este registro? Si está relacionado con otros datos, el sistema impedirá la eliminación.\');">',
        "mesa360HasPermission($page, 'eliminar')",
        'entity row delete form'
    )

    # Menu catalog controls are hidden for the staff roles, which have read-only menu access.
    for old, new, label in [
        ('<a class="primary-button" href="?page=menu&amp;tab=products&amp;new_product=1">＋ Nuevo producto</a>', "<?php if (mesa360HasPermission('menu', 'crear')): ?><a class=\"primary-button\" href=\"?page=menu&amp;tab=products&amp;new_product=1\">＋ Nuevo producto</a><?php endif; ?>", 'menu new product'),
        ('<a class="primary-button" href="?page=menu&amp;tab=categories&amp;new_category=1">＋ Nueva categoría</a>', "<?php if (mesa360HasPermission('menu', 'crear')): ?><a class=\"primary-button\" href=\"?page=menu&amp;tab=categories&amp;new_category=1\">＋ Nueva categoría</a><?php endif; ?>", 'menu new category'),
        ('<a class="primary-button small-button" href="?page=menu&amp;tab=products&amp;new_product=1">＋ Agregar producto</a>', "<?php if (mesa360HasPermission('menu', 'crear')): ?><a class=\"primary-button small-button\" href=\"?page=menu&amp;tab=products&amp;new_product=1\">＋ Agregar producto</a><?php endif; ?>", 'menu empty product'),
        ('<a class="secondary-button" href="?page=menu&amp;tab=categories&amp;new_category=1">Crear categoría primero</a>', "<?php if (mesa360HasPermission('menu', 'crear')): ?><a class=\"secondary-button\" href=\"?page=menu&amp;tab=categories&amp;new_category=1\">Crear categoría primero</a><?php endif; ?>", 'menu empty category'),
        ('<a class="primary-button small-button" href="?page=menu&amp;tab=categories&amp;new_category=1">＋ Crear primera categoría</a>', "<?php if (mesa360HasPermission('menu', 'crear')): ?><a class=\"primary-button small-button\" href=\"?page=menu&amp;tab=categories&amp;new_category=1\">＋ Crear primera categoría</a><?php endif; ?>", 'menu empty first category'),
    ]:
        content = require_once_text(content, old, new, label)

    # Menu edit links and delete forms are hidden independently.
    product_edit = '<a class="text-action" href="?page=menu&amp;tab=products&amp;edit_product=<?= h($product[\'id_producto\']) ?>">Editar</a>'
    product_edit_new = "<?php if (mesa360HasPermission('menu', 'editar')): ?><a class=\"text-action\" href=\"?page=menu&amp;tab=products&amp;edit_product=<?= h($product['id_producto']) ?>\">Editar</a><?php endif; ?>"
    content = require_once_text(content, product_edit, product_edit_new, 'menu product edit link')
    category_edit = '<a class="text-action" href="?page=menu&amp;tab=categories&amp;edit_category=<?= h($category[\'id_categoria\']) ?>">Editar</a>'
    category_edit_new = "<?php if (mesa360HasPermission('menu', 'editar')): ?><a class=\"text-action\" href=\"?page=menu&amp;tab=categories&amp;edit_category=<?= h($category['id_categoria']) ?>\">Editar</a><?php endif; ?>"
    content = require_once_text(content, category_edit, category_edit_new, 'menu category edit link')
    content = wrap_form(content,
        '<form method="post" action="?page=menu&amp;tab=products" onsubmit="return confirm(\'¿Eliminar este producto? Si está relacionado con recetas o pedidos, MySQL impedirá la eliminación.\');">',
        "mesa360HasPermission('menu', 'eliminar')", 'menu product delete form')
    content = wrap_form(content,
        '<form method="post" action="?page=menu&amp;tab=categories" onsubmit="return confirm(\'¿Eliminar esta categoría? Solo se podrá eliminar si no tiene productos asociados.\');">',
        "mesa360HasPermission('menu', 'eliminar')", 'menu category delete form')

    # Orders: hide add/edit/delete controls based on permission.
    content = require_once_text(content,
        '<a class="primary-button" href="?page=pedidos&amp;new=1">＋ Nuevo pedido</a>',
        "<?php if (mesa360HasPermission('pedidos', 'crear')): ?><a class=\"primary-button\" href=\"?page=pedidos&amp;new=1\">＋ Nuevo pedido</a><?php endif; ?>",
        'order create button')
    content = require_once_text(content,
        '<a class="primary-button small-button" href="?page=pedidos&amp;new=1">＋ Registrar primer pedido</a>',
        "<?php if (mesa360HasPermission('pedidos', 'crear')): ?><a class=\"primary-button small-button\" href=\"?page=pedidos&amp;new=1\">＋ Registrar primer pedido</a><?php endif; ?>",
        'order empty create button')
    order_edit = '<a class="text-action" href="?page=pedidos&amp;edit=<?= h($order[\'id_pedido\']) ?>">Ver / editar</a>'
    order_edit_new = "<?php if (mesa360HasPermission('pedidos', 'editar')): ?><a class=\"text-action\" href=\"?page=pedidos&amp;edit=<?= h($order['id_pedido']) ?>\">Ver / editar</a><?php endif; ?>"
    content = require_once_text(content, order_edit, order_edit_new, 'order edit link')
    content = wrap_form(content,
        '<form method="post" action="?page=pedidos" onsubmit="return confirm(\'¿Eliminar este pedido y sus detalles? Los pedidos con pagos registrados no se pueden eliminar.\');">',
        "mesa360HasPermission('pedidos', 'eliminar')", 'order delete form')

    # Hide payment-method tab from the cashier (server checks also deny its direct URL).
    method_tab = '<a class="menu-tab <?= $paymentTab === \'methods\' ? \'active\' : \'\' ?>" href="?page=pagos&amp;tab=methods">Métodos de pago <span><?= h(count($paymentMethods)) ?></span></a>'
    method_tab_new = "<?php if (mesa360HasPermission('pagos', 'gestionar_metodos')): ?><a class=\"menu-tab <?= $paymentTab === 'methods' ? 'active' : '' ?>\" href=\"?page=pagos&amp;tab=methods\">Métodos de pago <span><?= h(count($paymentMethods)) ?></span></a><?php endif; ?>"
    content = require_once_text(content, method_tab, method_tab_new, 'payment-method tab link')

    # Cashier can view/register payments, but cannot configure payment methods.
    content = require_once_text(content,
        '<a class="primary-button" href="?page=pagos&amp;tab=methods&amp;new_method=1">＋ Nuevo método</a>',
        "<?php if (mesa360HasPermission('pagos', 'gestionar_metodos')): ?><a class=\"primary-button\" href=\"?page=pagos&amp;tab=methods&amp;new_method=1\">＋ Nuevo método</a><?php endif; ?>",
        'payment method create button')

    # Match the table-status-only rule in the UI: configuration fields are read-only for Mesera.
    old_checkbox = '<input id="field-<?= h($fieldName) ?>" type="checkbox" name="<?= h($fieldName) ?>" value="1" <?= (int) ($entityValues[$fieldName] ?? $field[\'default\'] ?? 0) === 1 ? \'checked\' : \'\' ?>>'
    new_checkbox = '<input id="field-<?= h($fieldName) ?>" type="checkbox" name="<?= h($fieldName) ?>" value="1" <?= (int) ($entityValues[$fieldName] ?? $field[\'default\'] ?? 0) === 1 ? \'checked\' : \'\' ?> <?= ($page === \'mesas\' && (string)($_SESSION[\'mesa360_user_role\'] ?? \'\') === \'Mesera\') ? \'disabled\' : \'\' ?>>'
    content = require_once_text(content, old_checkbox, new_checkbox, 'mesera-disabled table checkbox')
    old_input_tail = "<?= ($page === 'inventario' && $fieldName === 'stock_actual') ? 'readonly aria-readonly=\"true\"' : '' ?> <?= !empty($field['required']) ? 'required' : '' ?>"
    new_input_tail = "<?= ($page === 'inventario' && $fieldName === 'stock_actual') ? 'readonly aria-readonly=\"true\"' : '' ?> <?= ($page === 'mesas' && strcasecmp((string)($_SESSION['mesa360_user_role'] ?? ''), 'Mesera') === 0 && $fieldName !== 'estado') ? 'readonly aria-readonly=\"true\"' : '' ?> <?= !empty($field['required']) ? 'required' : '' ?>"
    content = require_once_text(content, old_input_tail, new_input_tail, 'mesera-readonly table fields')

    # Field-level protection: Mesera can update MESA.estado only, not configuration fields.
    marker = "        if ($page === 'inventario') {\n            if ($recordId > 0) {"
    table_status_block = r'''        /* MESA360_MESERA_TABLE_STATUS_ONLY */
        if ($page === 'mesas' && strcasecmp((string)($_SESSION['mesa360_user_role'] ?? ''), 'Mesera') === 0) {
            if ($recordId < 1) moduleFlashRedirect('mesas', 'error', 'La mesera solo puede actualizar el estado de una mesa existente.');
            $currentTable = $pdo->prepare('SELECT numero_mesa, capacidad, ubicacion, activo FROM MESA WHERE id_mesa = :id');
            $currentTable->execute(['id' => $recordId]);
            $currentTableRow = $currentTable->fetch();
            if (!$currentTableRow) moduleFlashRedirect('mesas', 'error', 'La mesa indicada ya no existe.');
            $values = [
                'numero_mesa' => (int)$currentTableRow['numero_mesa'],
                'capacidad' => (int)$currentTableRow['capacidad'],
                'ubicacion' => $currentTableRow['ubicacion'],
                'estado' => $values['estado'],
                'activo' => (int)$currentTableRow['activo'],
            ];
        }

'''
    content = require_once_text(content, marker, table_status_block + marker, 'table status-only enforcement')

    # Prevent direct creation/edit GET requests that bypass links (for role-specific controls).
    return content

if os.geteuid() != 0:
    sys.exit('Run with sudo: sudo python3 enable_role_access.py')
if not INDEX.is_file():
    sys.exit(f'Index file not found: {INDEX}')
original = INDEX.read_text(encoding='utf-8')
try:
    patched = patch_content(original)
except Exception as exc:
    sys.exit(str(exc))

user_name = os.environ.get('SUDO_USER', 'mesa360')
try:
    home = Path(pwd.getpwnam(user_name).pw_dir)
except KeyError:
    home = Path('/home/mesa360')
backup_dir = home / 'mesa360-backups'
backup_dir.mkdir(parents=True, exist_ok=True)
stamp = datetime.now().strftime('%Y-%m-%d_%H-%M-%S')
backup = backup_dir / f'index.php.before-role-access-{stamp}.bak'
shutil.copy2(INDEX, backup)
try:
    info = pwd.getpwnam(user_name)
    os.chown(backup, info.pw_uid, info.pw_gid)
except (KeyError, PermissionError):
    pass

with tempfile.NamedTemporaryFile('w', encoding='utf-8', suffix='.php', dir=str(INDEX.parent), delete=False) as handle:
    temp_name = handle.name
    handle.write(patched)
    handle.flush()
try:
    result = subprocess.run(['php', '-l', temp_name], capture_output=True, text=True)
    if result.returncode != 0:
        print(result.stdout, end=''); print(result.stderr, end='', file=sys.stderr)
        sys.exit('PHP syntax check failed; live index.php was not changed.')
    current_stat = INDEX.stat()
    os.chmod(temp_name, stat.S_IMODE(current_stat.st_mode))
    os.chown(temp_name, current_stat.st_uid, current_stat.st_gid)
    os.replace(temp_name, INDEX)
    STATE_FILE.parent.mkdir(parents=True, exist_ok=True)
    STATE_FILE.write_text(str(backup) + '\n', encoding='utf-8')
    try:
        state_owner = pwd.getpwnam(user_name)
    except KeyError:
        state_owner = pwd.getpwuid(os.getuid())
    os.chown(STATE_FILE, state_owner.pw_uid, state_owner.pw_gid)
    os.chmod(STATE_FILE, 0o600)
    print('Role-based access enabled successfully.')
    print(result.stdout.strip())
    print(f'Backup: {backup}')
    print(f'Rollback state: {STATE_FILE}')
except Exception:
    try: os.unlink(temp_name)
    except OSError: pass
    raise
