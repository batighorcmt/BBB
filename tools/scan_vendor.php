<?php
$dirs = ['vendor','app','bootstrap','routes','resources','public','config'];
foreach ($dirs as $d) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($d));
    foreach ($it as $file) {
        if (! $file->isFile()) continue;
        $path = $file->getPathname();
        $contents = @file_get_contents($path);
        if ($contents === false) continue;
        if (strpos($contents, "\0") !== false) {
            $count = substr_count($contents, "\0");
            echo "$path: NULs=$count\n";
        }
    }
}
