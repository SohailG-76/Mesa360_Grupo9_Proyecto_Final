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
CSS = Path('/var/www/html/assets/css/auth.css')
LOGIN = Path('/var/www/html/login.php')
LOGOUT = Path('/var/www/html/logout.php')
GUARD_MARKER = '/* MESA360_AUTH_GUARD */'
PANEL_MARKER = '<!-- MESA360_AUTH_SIDEBAR -->'
CSS_MARKER = '<!-- MESA360_AUTH_STYLESHEET -->'

if os.geteuid() != 0:
    sys.exit('Run this script with sudo: sudo python3 enable_auth.py')
if not all(p.is_file() for p in (INDEX, CSS, LOGIN, LOGOUT)):
    sys.exit('Required login files are missing. Install login.php, logout.php, and assets/css/auth.css first.')

content = INDEX.read_text(encoding='utf-8')
if GUARD_MARKER in content or PANEL_MARKER in content or CSS_MARKER in content:
    sys.exit('Authentication patch markers already exist. No changes were made.')

session_anchor = "if (session_status() !== PHP_SESSION_ACTIVE) {\n    session_start();\n}\n"
css_anchor = '    <link rel="stylesheet" href="/assets/css/app.css">\n'
sidebar_anchor = '        </nav>\n    </aside>'

if content.count(session_anchor) != 1:
    sys.exit('Could not find the expected session initialization exactly once. No changes were made.')
if content.count(css_anchor) != 1:
    sys.exit('Could not find the expected stylesheet link exactly once. No changes were made.')
if content.count(sidebar_anchor) != 1:
    sys.exit('Could not find the expected sidebar closing block exactly once. No changes were made.')

session_patch = session_anchor + "\n" + GUARD_MARKER + "\nif (empty($_SESSION['mesa360_user_id']) || empty($_SESSION['mesa360_username'])) {\n    header('Location: /login.php', true, 303);\n    exit;\n}\n"
css_patch = css_anchor + '    ' + CSS_MARKER + '\n    <link rel="stylesheet" href="/assets/css/auth.css">\n'
panel = '''        </nav>
        <!-- MESA360_AUTH_SIDEBAR -->
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
    </aside>'''

patched = content.replace(session_anchor, session_patch, 1)
patched = patched.replace(css_anchor, css_patch, 1)
patched = patched.replace(sidebar_anchor, panel, 1)

user_name = os.environ.get('SUDO_USER', 'mesa360')
try:
    user_home = Path(pwd.getpwnam(user_name).pw_dir)
except KeyError:
    user_home = Path('/home/mesa360')
backup_dir = user_home / 'mesa360-backups'
backup_dir.mkdir(parents=True, exist_ok=True)
stamp = datetime.now().strftime('%Y-%m-%d_%H-%M-%S')
backup = backup_dir / f'index.php.before-auth-enable-{stamp}.bak'
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
    print('Authentication gate enabled successfully.')
    print(f'PHP syntax check: {result.stdout.strip()}')
    print(f'Backup: {backup}')
except Exception:
    try:
        os.unlink(temp_name)
    except OSError:
        pass
    raise
