import importlib.util
import io
import unittest
import zipfile
from pathlib import Path

spec = importlib.util.spec_from_file_location('scanner', Path(__file__).parents[2] / 'scripts/check_repository_security.py')
scanner = importlib.util.module_from_spec(spec)
spec.loader.exec_module(scanner)

class RepositorySecurityTest(unittest.TestCase):
    def test_backup_and_env_paths_are_blocked(self):
        for path in ['database.sql', 'archive.sql.gz', 'backup.sqlite', '.env.production', 'storage/app/private/kyc/document.jpg']:
            self.assertTrue(scanner.forbidden_path(path), path)

    def test_migrations_are_allowed(self):
        self.assertEqual([], scanner.inspect('database/migrations/create_users.php', b'Schema::create("users")'))

    def test_embedded_dump_in_archive_is_blocked(self):
        stream = io.BytesIO()
        with zipfile.ZipFile(stream, 'w') as archive:
            archive.writestr('data.sql', 'INSERT '+ 'INTO users VALUES (1);')
        labels = [label for name, label in scanner.inspect('reports.zip', stream.getvalue())]
        self.assertIn('forbidden-sensitive-path', labels)
        self.assertIn('database-data', labels)

    def test_private_keys_and_tokens_are_detected_without_echoing_values(self):
        for payload in [b'-----BEGIN '+b'PRIVATE KEY-----', b'ghp_'+b'A'*36]:
            findings = scanner.inspect('example.txt', payload)
            self.assertTrue(findings)
            self.assertNotIn(payload.decode(), str(findings))

if __name__ == '__main__': unittest.main()
