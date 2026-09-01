<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$failures = [];
$checkedFiles = 0;

$requiredFiles = [
    'public/assets/css/tokens.css',
    'public/assets/css/reset.css',
    'public/assets/css/base.css',
    'public/assets/css/layout.css',
    'public/assets/css/components.css',
    'public/assets/css/pages.css',
    'public/assets/js/app.js',
    'public/assets/js/attendance.js',
    'public/assets/images/app-icon.svg',
    'public/service-worker.js',
    'public/router.php',
];

foreach ($requiredFiles as $relativePath) {
    if (!is_file($root . '/' . $relativePath)) {
        $failures[] = "Lipsește fișierul obligatoriu: {$relativePath}";
    }
}

$forbiddenFiles = [
    'dev_server.py',
    'indrumar_design_kit.zip',
    'INSTRUCTIUNI_MODERNIZARE_DESIGN_GPT.md',
    'public/app-icon.jpg',
    'public/assets/images/app-icon.jpg',
];
foreach ($forbiddenFiles as $relativePath) {
    if (file_exists($root . '/' . $relativePath)) {
        $failures[] = "A reapărut un fișier vechi/interzis: {$relativePath}";
    }
}

$scanDirectories = [$root . '/app/Views', $root . '/public'];
$forbiddenPatterns = [
    '/\sstyle\s*=\s*["\']/i' => 'stil inline',
    '/\sonclick\s*=\s*["\']/i' => 'handler onclick inline',
    '/<style\b/i' => 'bloc <style> inline',
    '/#4F46E5|#4338CA|#F8F9FA/i' => 'culoare din designul anterior',
    '/indrumar-static-v1/i' => 'cache vechi',
];

foreach ($scanDirectories as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }
        $extension = strtolower($fileInfo->getExtension());
        if (!in_array($extension, ['php', 'html', 'css', 'js', 'json', 'webmanifest', 'svg'], true)) {
            continue;
        }
        $checkedFiles++;
        $contents = file_get_contents($fileInfo->getPathname());
        if ($contents === false) {
            $failures[] = 'Nu poate fi citit: ' . $fileInfo->getPathname();
            continue;
        }
        foreach ($forbiddenPatterns as $pattern => $label) {
            if (preg_match($pattern, $contents) === 1) {
                $relative = str_replace($root . '/', '', $fileInfo->getPathname());
                $failures[] = "{$label} găsit în {$relative}";
            }
        }
    }
}

$css = '';
foreach (glob($root . '/public/assets/css/*.css') ?: [] as $cssFile) {
    $css .= "\n" . (file_get_contents($cssFile) ?: '');
}
preg_match_all('/(--[a-zA-Z0-9_-]+)\s*:/', $css, $definitions);
preg_match_all('/var\((--[a-zA-Z0-9_-]+)/', $css, $references);
$defined = array_unique($definitions[1]);
$referenced = array_unique($references[1]);
$allowedDynamic = ['--accent-color', '--progress', '--progress-width'];
$undefined = array_values(array_diff($referenced, $defined, $allowedDynamic));
if ($undefined) {
    $failures[] = 'Tokenuri CSS nedefinite: ' . implode(', ', $undefined);
}

if ($failures) {
    echo "Design integrity: FAIL\n";
    foreach ($failures as $failure) {
        echo " - {$failure}\n";
    }
    exit(1);
}

echo "Design integrity: PASS\n";
echo "Fișiere verificate: {$checkedFiles}\n";
echo "Tokenuri CSS definite: " . count($defined) . "\n";
echo "Nu există stiluri inline, handlers inline, fișiere vechi sau tokenuri lipsă.\n";
