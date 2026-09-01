<?php

namespace App\Support;

class Autoloader
{
    public static function register(string $baseDir): void
    {
        spl_autoload_register(function (string $class) use ($baseDir) {
            $prefixes = [
                'App\\' => '/app/',
                'Database\\' => '/database/',
                'Tests\\' => '/tests/',
            ];

            foreach ($prefixes as $prefix => $dir) {
                $len = strlen($prefix);
                if (strncmp($prefix, $class, $len) === 0) {
                    $relativeClass = substr($class, $len);
                    $file = $baseDir . $dir . str_replace('\\', '/', $relativeClass) . '.php';

                    if (file_exists($file)) {
                        require_once $file;
                        return;
                    }
                }
            }
        });
    }
}
