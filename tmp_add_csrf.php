<?php
$files = glob("*.{php,html}", GLOB_BRACE);

foreach ($files as $file) {
    if ($file === 'csrf.php' || $file === 'tmp_add_csrf.php') continue;
    
    $content = file_get_contents($file);
    $modified = false;

    // Convert html files to php if they have a form
    if (strpos($file, '.html') !== false && strpos($content, 'method="POST"') !== false) {
        $new_file = str_replace('.html', '_view.php', $file);
        rename($file, $new_file);
        $file = $new_file;
    }

    // Add CSRF to forms
    if (preg_match_all('/<form[^>]*method="POST"[^>]*>/i', $content, $matches)) {
        foreach ($matches[0] as $form_tag) {
            // Check if already injected
            $form_pos = strpos($content, $form_tag);
            $sub = substr($content, $form_pos, 200);
            if (strpos($sub, 'csrf_token') === false) {
                $csrf_field = "\n    <input type=\"hidden\" name=\"csrf_token\" value=\"<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>\">\n";
                $content = str_replace($form_tag, $form_tag . $csrf_field, $content);
                $modified = true;
            }
        }
    }

    if ($modified) {
        // Ensure csrf.php is included if not already
        if (strpos($content, 'csrf.php') === false && strpos($file, '.php') !== false && !in_array($file, ['db_connect.php'])) {
            $content = "<?php require_once 'csrf.php'; ?>\n" . preg_replace('/^<\?php\s*require_once \'csrf.php\';\s*\?>\n/m', '', $content);
        }
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
?>
