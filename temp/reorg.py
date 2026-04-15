import os
import shutil
import re

root_dir = r"c:\xampp\htdocs\WP-class"
os.chdir(root_dir)

# Define the target structure
folders = {
    'css': ['styles.css', 'regstyles.css'],
    'js': ['script.js', 'reglogic.js'],
    'images': ['spacebg.jpg', 'logo.png', 'cp.jpg', 'git.png', 'google.png', 'linkedin.png'],
    'html': ['about.html', 'apply.html', 'contact.html'],
    'sql': ['database*.sql'],
    'admin': [
        'admin_login.php', 'admin_logout.php', 'admin_dashboard.php',
        'admin_messages.php', 'admin_reply.php', 'admin_update_application.php',
        'admin_view_team.php', 'admin_add_hackathon.php'
    ],
    'actions': [
        'login.php', 'register.php', 'logout.php', 'update_profile.php',
        'send_message.php', 'team_action.php', 'create_team.php', 'apply.php'
    ],
    'includes': ['db_connect.php', 'csrf.php', 'api_chat.php']
}

# Add wildcards explicitly for sql
import glob
sql_files = glob.glob('database*.sql')
folders['sql'] = sql_files

# Create folders
for folder in folders.keys():
    os.makedirs(folder, exist_ok=True)

# Move PHPMailer to includes
if os.path.exists('PHPMailer'):
    shutil.move('PHPMailer', os.path.join('includes', 'PHPMailer'))

# Move files to folders
file_to_folder = {}
for folder, files in folders.items():
    for f in files:
        if os.path.exists(f):
            dest = os.path.join(folder, f)
            shutil.move(f, dest)
            file_to_folder[f] = folder

# Mapping of file to its new subpath
def get_new_path(filename, current_file_path):
    if filename in file_to_folder:
        folder = file_to_folder[filename]
        # If current file is in root
        if not current_file_path or current_file_path == '.':
            return f"{folder}/{filename}"
        else:
            # Current file is in a subdirectory, so we need ../
            return f"../{folder}/{filename}"
    return filename

def process_file(filepath, current_folder=""):
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
    except UnicodeDecodeError:
        print(f"Skipping binary/unreadable file: {filepath}")
        return

    original_content = content

    # Replace include/require statements
    # Match: include 'db_connect.php'; -> include 'includes/db_connect.php';
    # Regex for includes: (include|require|include_once|require_once)\s*(['"])([^'"]+)(['"])\s*;
    def include_repl(match):
        stmt, quote1, filename, quote2 = match.groups()
        new_path = get_new_path(filename, current_folder)
        return f"{stmt} {quote1}{new_path}{quote2};"
    
    content = re.sub(r"(include|require|include_once|require_once)\s*(['\"])([^'\"]+)(['\"])\s*;", include_repl, content)

    # Replace href, src, action
    def attr_repl(match):
        attr, quote, filename = match.groups()
        new_path = get_new_path(filename, current_folder)
        return f"{attr}={quote}{new_path}{quote}"
    
    content = re.sub(r"(href|src|action)=(['\"])([^'\"]+)\2", attr_repl, content)

    # Replace header locations (header("Location: something.php"))
    def header_repl(match):
        prefix, quote, filename, suffix = match.groups()
        # For header location, sometimes it has ?args
        base_file = filename.split('?')[0]
        args = filename[len(base_file):]
        new_path = get_new_path(base_file, current_folder)
        return f"{prefix}{quote}{new_path}{args}{suffix}"

    content = re.sub(r"(header\s*\(\s*Location:\s*)(['\"])([^'\"]+)\2(\s*\))", header_repl, content)
    content = re.sub(r"(header\s*\(\s*['\"]Location:\s*)([^'\"]+)(['\"]\s*\))", lambda m: f"{m.group(1)}{get_new_path(m.group(2).split('?')[0], current_folder)}{m.group(2)[len(m.group(2).split('?')[0]):]}{m.group(3)}", content)

    # Edge cases like header("Location: admin_dashboard.php");
    # We covered header("Location: ...") above using two regex. Let's make sure.
    
    if content != original_content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {filepath}")

# Update files in root
for f in os.listdir('.'):
    if f.endswith('.php') or f.endswith('.html'):
        if os.path.isfile(f):
            process_file(f, "")

# Update files in subdirectories
for folder in ['admin', 'actions', 'includes', 'html']:
    if os.path.isdir(folder):
        for f in os.listdir(folder):
            if f.endswith('.php') or f.endswith('.html'):
                file_path = os.path.join(folder, f)
                if os.path.isfile(file_path):
                    process_file(file_path, folder)

print("Done restructuring and updating links.")
