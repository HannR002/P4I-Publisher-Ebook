import paramiko
import os
import json

env = {}
with open('D:\\P4I_Rehearsal_Data\\.env.production-access', 'r') as f:
    for line in f:
        if '=' in line: key, val = line.strip().split('=', 1); env[key] = val

ssh = paramiko.SSHClient()
ssh.load_system_host_keys()
ssh.connect(env['SSH_HOST'], port=int(env['SSH_PORT']), username=env['SSH_USERNAME'], password=env['SSH_PASSWORD'])

php_script = """<?php
$users = App\\Models\\User::with('authorProfile')->get()->map(function($u) {
    $role = 'User';
    if ($u->is_admin) $role = 'Admin';
    elseif ($u->authorProfile) $role = 'Author';
    
    return [
        'id' => $u->id,
        'name' => $u->name,
        'email' => $u->email,
        'role' => $role,
        'status' => $u->is_active ? 'Active' : 'Inactive',
        'password_present' => !empty($u->password)
    ];
});
echo json_encode($users);
"""

sftp = ssh.open_sftp()
with sftp.file('/home/u239415845/scripts/p4i/get_users.php', 'w') as f:
    f.write(php_script)
sftp.close()

stdin, stdout, stderr = ssh.exec_command('cd /home/u239415845/domains/p4ijournal.org/P4I_Publisher_Ebook && php artisan tinker /home/u239415845/scripts/p4i/get_users.php')
out = stdout.read().decode('utf-8')
err = stderr.read().decode('utf-8')
ssh.close()

try:
    # Tinker outputs some extra lines sometimes, let's find the json array.
    start = out.find('[')
    end = out.rfind(']') + 1
    data = json.loads(out[start:end])
    print("ACCOUNT INVENTORY:")
    print("ID | NAME | EMAIL | ROLE | STATUS | PASSWORD_PRESENT")
    print("-" * 70)
    for row in data:
        print(f"{row['id']} | {row['name']} | {row['email']} | {row['role']} | {row['status']} | {row['password_present']}")
except Exception as e:
    print(f"Error parsing JSON: {e}")
    print("Raw output:", out)
    print("Error output:", err)
