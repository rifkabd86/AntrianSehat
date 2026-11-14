<?php
$dir = dirname(__DIR__);
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$count = 0;
$errors = 0;

foreach ($it as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $count++;
        $path = $file->getRealPath();
        $output = [];
        $returnVar = 0;
        exec("C:\\xampp\\php\\php.exe -l " . escapeshellarg($path), $output, $returnVar);
        if ($returnVar !== 0) {
            echo "[LINT ERROR] {$path}\n" . implode("\n", $output) . "\n\n";
            $errors++;
        }
    }
}

echo "Linted {$count} PHP files. Errors found: {$errors}\n";

