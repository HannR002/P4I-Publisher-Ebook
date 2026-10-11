#!/usr/bin/env python3
"""Fail closed for tracked/staged dumps and recognizable secrets; never print values."""
import argparse
import io
import re
import subprocess
import sys
import zipfile
from pathlib import PurePosixPath

PATH_PATTERN = re.compile(r'\.(?:sql(?:\..*)?|sqlite3?|db|dump|bak|backup|pem|key|p12|pfx)$', re.I)
PATTERNS = {
    'private-key': re.compile(rb'-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----'),
    'github-token': re.compile(rb'\b(?:gh[pousr]_[A-Za-z0-9]{30,}|github_pat_[A-Za-z0-9_]{40,})\b'),
    'aws-access-key': re.compile(rb'\b(?:AKIA|ASIA)[A-Z0-9]{16}\b'),
    'credential-url': re.compile(rb'https?://[^\s/:@]+:[^\s/@]+@'),
    'account-password-hash': re.compile(rb'\$2[aby]\$\d{2}\$[./A-Za-z0-9]{53}'),
    'database-data': re.compile(rb'INSERT\s+INTO\s+[`"]?(?:users|sessions|password_reset_tokens|authors|payout_requests)[`"]?\s', re.I),
}

def forbidden_path(name):
    path = PurePosixPath(name)
    return bool(PATH_PATTERN.search(name) or path.name.startswith('.env') or path.name == 'auth.json'
                or (name.startswith('storage/app/') and path.name != '.gitignore')
                or any(part.lower() in {'backups', 'private-evidence'} for part in path.parts))

def inspect(name, content):
    errors = []
    if forbidden_path(name):
        errors.append((name, 'forbidden-sensitive-path'))
    for label, pattern in PATTERNS.items():
        if pattern.search(content): errors.append((name, label))
    if name.lower().endswith(('.zip', '.docx')):
        try:
            with zipfile.ZipFile(io.BytesIO(content)) as archive:
                for entry in archive.infolist():
                    if entry.is_dir(): continue
                    if entry.file_size > 20 * 1024 * 1024:
                        errors.append((name, 'archive-entry-too-large-to-scan'))
                        continue
                    errors.extend(inspect(name+'!'+entry.filename, archive.read(entry)))
        except zipfile.BadZipFile:
            errors.append((name, 'unreadable-archive'))
    return errors

def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--staged', action='store_true', help='Scan complete Git index, including pending additions/deletions.')
    args = parser.parse_args()
    # Git index is the exact candidate commit. It also contains existing tracked files.
    paths = subprocess.check_output(['git', 'ls-files', '-z']).decode().split('\0')
    errors = []
    for name in filter(None, paths):
        content = subprocess.check_output(['git', 'show', ':'+name]) if args.staged else open(name, 'rb').read()
        errors.extend(inspect(name, content))
    for name, label in sorted(set(errors)):
        print(f'BLOCKED {name}: {label}')
    print(f'Repository security scan: {len(set(errors))} finding(s); values redacted.')
    return 1 if errors else 0

if __name__ == '__main__': sys.exit(main())
