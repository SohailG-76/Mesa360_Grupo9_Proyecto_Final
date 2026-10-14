#!/usr/bin/env python3
from __future__ import annotations
import os, pwd, shutil, stat, subprocess, sys, tempfile
from pathlib import Path

INDEX = Path(os.environ.get('MESA360_INDEX_PATH', '/var/www/html/index.php'))
STATE = Path(os.environ.get('MESA360_STATE_FILE', '/home/mesa360/.mesa360_role_access_backup_path'))
if os.geteuid() != 0:
    sys.exit('Run with sudo: sudo python3 disable_role_access.py')
if not INDEX.is_file() or not STATE.is_file():
    sys.exit('Role-access rollback state or index.php is missing. No changes were made.')
content = INDEX.read_text(encoding='utf-8')
if '/* MESA360_RBAC_BEGIN */' not in content or '/* MESA360_RBAC_END */' not in content:
    sys.exit('Role-access markers are not present. No changes were made.')
backup = Path(STATE.read_text(encoding='utf-8').strip())
if not backup.is_file():
    sys.exit(f'Recorded backup is missing: {backup}. No changes were made.')
result = subprocess.run(['php', '-l', str(backup)], capture_output=True, text=True)
if result.returncode != 0:
    sys.exit('Recorded pre-role-access backup failed PHP lint; live file was not changed.')
user_name = os.environ.get('SUDO_USER', 'mesa360')

try:
    home = Path(pwd.getpwnam(user_name).pw_dir)
except KeyError:
    home = Path('/home/mesa360')
backup_dir = home / 'mesa360-backups'; backup_dir.mkdir(parents=True, exist_ok=True)
from datetime import datetime
current_backup = backup_dir / f'index.php.before-role-access-rollback-{datetime.now().strftime("%Y-%m-%d_%H-%M-%S")}.bak'
shutil.copy2(INDEX, current_backup)
with tempfile.NamedTemporaryFile('wb', suffix='.php', dir=str(INDEX.parent), delete=False) as handle:
    tmp = handle.name
    handle.write(backup.read_bytes())
try:
    st = INDEX.stat(); os.chmod(tmp, stat.S_IMODE(st.st_mode)); os.chown(tmp, st.st_uid, st.st_gid); os.replace(tmp, INDEX)
except Exception:
    try: os.unlink(tmp)
    except OSError: pass
    raise
print('Role-access patch rolled back. The login gate is still present in the restored backup.')
print(f'Current pre-rollback copy: {current_backup}')
print(f'Restored from: {backup}')
