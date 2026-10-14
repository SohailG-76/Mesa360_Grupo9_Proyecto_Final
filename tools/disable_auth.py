#!/usr/bin/env python3
from __future__ import annotations

import os
import pwd
import shutil
import stat
import subprocess
import sys
import tempfile
from datetime import datetime
from pathlib import Path

INDEX = Path('/var/www/html/index.php')
GUARD_BLOCK = "/* MESA360_AUTH_GUARD */\nif (empty($_SESSION['mesa360_user_id']) || empty($_SESSION['mesa360_username'])) {\n    header('Location: /login.php', true, 303);\n    exit;\n}\n"
CSS_BLOCK = '    <!-- MESA360_AUTH_STYLESHEET -->\n    <link rel="stylesheet" href="/assets/css/auth.css">\n'
SIDEBAR_BLOCK = '''        <!-- MESA360_AUTH_SIDEBAR -->
        <section class="auth-sidebar-user" aria-label="Sesión actual">
            <div class="auth-sidebar-user-copy">
                <strong><?= h($_SESSION['mesa360_username'] ?? 'Usuario') ?></strong>
                <span><?= h($_SESSION['mesa360_user_role'] ?? 'Personal') ?></span>
            </div>
            <form method="post" action="/logout.php" class="auth-sidebar-logout">
                <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                <button type="submit" class="auth-logout-button">Cerrar sesión</button>
            </form>
        </section>
'''

if os.geteuid() != 0:
    sys.exit('Run this script with sudo: sudo python3 disable_auth.py')
content = INDEX.read_text(encoding='utf-8')
if GUARD_BLOCK not in content or CSS_BLOCK not in content or SIDEBAR_BLOCK not in content:
    sys.exit('Expected authentication patches not all found. No changes were made; send the error for review.')
patched = content.replace(GUARD_BLOCK, '', 1).replace(CSS_BLOCK, '', 1).replace(SIDEBAR_BLOCK, '', 1)

user_name = os.environ.get('SUDO_USER', 'mesa360')
try:
    user_home = Path(pwd.getpwnam(user_name).pw_dir)
except KeyError:
    user_home = Path('/home/mesa360')
backup_dir = user_home / 'mesa360-backups'
backup_dir.mkdir(parents=True, exist_ok=True)
stamp = datetime.now().strftime('%Y-%m-%d_%H-%M-%S')
backup = backup_dir / f'index.php.before-auth-disable-{stamp}.bak'
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
        print(result.stdout, end='')
        print(result.stderr, end='', file=sys.stderr)
        sys.exit('PHP syntax check failed; live index.php was not changed.')
    original_stat = INDEX.stat()
    os.chmod(temp_name, stat.S_IMODE(original_stat.st_mode))
    os.chown(temp_name, original_stat.st_uid, original_stat.st_gid)
    os.replace(temp_name, INDEX)
    print('Authentication gate disabled. The existing dashboard is accessible without login again.')
    print(f'PHP syntax check: {result.stdout.strip()}')
    print(f'Backup: {backup}')
except Exception:
    try:
        os.unlink(temp_name)
    except OSError:
        pass
    raise
