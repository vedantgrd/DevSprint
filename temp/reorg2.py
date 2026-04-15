import os
import shutil
import re
import glob

root_dir = r"c:\xampp\htdocs\WP-class"
os.chdir(root_dir)

# Define the new target structure for the remaining root files
folders2 = {
    'about': ['about.php'],
    'hackathons': ['apply_gateway.php', 'hackathons.php'],
    'contact': ['contact.php'],
    'profile': ['inbox.php', 'profile.php'],
    'home': ['index.php'],
    'login': ['login_view.php'],
    'teams': ['matchmaking.php', 'team_details.php', 'teams.php'],
    'register': ['Registerpage_view.php'],
    'temp': ['archit.txt', 'test.txt', 'reorg.py']
}

temp_files = glob.glob('tmp_*.php')
folders2['temp'].extend(temp_files)

# Create folders
for folder in folders2.keys():
    os.makedirs(folder, exist_ok=True)

# File to folder complete mapping
file_to_folder = {
    'styles.css': 'css',
    'regstyles.css': 'css',
    'script.js': 'js',
    'reglogic.js': 'js',
    'spacebg.jpg': 'images',
    'logo.png': 'images',
    'cp.jpg': 'images',
    'git.png': 'images',
    'google.png': 'images',
    'linkedin.png': 'images',
    'db_connect.php': 'includes',
    'csrf.php': 'includes',
    'api_chat.php': 'includes',
    'login.php': 'actions',
    'register.php': 'actions',
    'logout.php': 'actions',
    'update_profile.php': 'actions',
    'send_message.php': 'actions',
    'team_action.php': 'actions',
    'create_team.php': 'actions',
    'apply.php': 'actions',
    'admin_login.php': 'admin',
    'admin_logout.php': 'admin',
    'admin_dashboard.php': 'admin',
    'admin_messages.php': 'admin',
    'admin_reply.php': 'admin',
    'admin_update_application.php': 'admin',
    'admin_view_team.php': 'admin',
    'admin_add_hackathon.php': 'admin',
    'about.html': 'html',
    'apply.html': 'html',
    'contact.html': 'html'
}

for folder, files in folders2.items():
    for f in files:
        if os.path.exists(f):
            shutil.move(f, os.path.join(folder, f))
        file_to_folder[f] = folder

# Get the path to a target file relative to the current file's folder
def get_new_path(target_filename, current_folder):
    target_filename = target_filename.split('?')[0] # strip GET args to find the physical file
    if target_filename in file_to_folder:
        target_folder = file_to_folder[target_filename]
        # Are we currently in root?
        if not current_folder or current_folder == '.':
            return f"{target_folder}/{target_filename}"
        else:
            return f"../{target_folder}/{target_filename}"
    return target_filename

def process_file(filepath, current_folder):
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
    except Exception:
        return

    original_content = content

    def replace_url(match):
        prefix, quote, filename = match.groups()
        if filename.startswith('http') or filename.startswith('mailto') or filename.startswith('#'):
            return match.group(0) # don't touch absolute URLs
        
        base_file = filename.split('?')[0]
        args = filename[len(base_file):]
        
        # We need to strip existing folder prefixes like 'css/' to find the base filename.
        base_file_name = os.path.basename(base_file)
        
        if base_file_name in file_to_folder:
            new_path = get_new_path(base_file_name, current_folder)
            return f"{prefix}={quote}{new_path}{args}{quote}"
        return match.group(0)

    # replace href, src, action
    content = re.sub(r"(href|src|action)=(['\"])([^'\"]+)\2", replace_url, content)

    # replace includes
    def replace_include(match):
        stmt, quote1, filename, quote2 = match.groups()
        base_file_name = os.path.basename(filename)
        if base_file_name in file_to_folder:
            new_path = get_new_path(base_file_name, current_folder)
            return f"{stmt} {quote1}{new_path}{quote2};"
        return match.group(0)

    content = re.sub(r"(include|require|include_once|require_once)\s*(['\"])([^'\"]+)(['\"])\s*;", replace_include, content)

    # replace header locations
    def replace_header(match):
        prefix, quote, filename, suffix = match.groups()
        base_file = filename.split('?')[0]
        args = filename[len(base_file):]
        base_file_name = os.path.basename(base_file)
        if base_file_name in file_to_folder:
            new_path = get_new_path(base_file_name, current_folder)
            return f"{prefix}{quote}{new_path}{args}{suffix}"
        return match.group(0)

    content = re.sub(r"(header\s*\(\s*['\"]Location:\s*)([^'\"]+)(['\"]\s*\))", 
                     lambda m: f"{m.group(1)}{get_new_path(os.path.basename(m.group(2).split('?')[0]), current_folder)}{m.group(2)[len(m.group(2).split('?')[0]):]}{m.group(3)}" if os.path.basename(m.group(2).split('?')[0]) in file_to_folder else m.group(0), content)

    if content != original_content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {filepath}")

# Process ALL php/html files in all folders
for root_path, dirs, files in os.walk('.'):
    # skip .git and outputs and other non-source folders
    if '.git' in root_path or 'outputs' in root_path or 'temp' in root_path or 'images' in root_path:
        continue
    
    current_folder_name = os.path.basename(root_path)
    if root_path == '.':
        current_folder_name = ''

    for f in files:
        if f.endswith('.php') or f.endswith('.html'):
            filepath = os.path.join(root_path, f)
            filepath = os.path.normpath(filepath)
            process_file(filepath, current_folder_name)

print("Phase 2 restructure done.")
