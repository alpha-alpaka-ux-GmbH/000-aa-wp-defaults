<?php
require plugin_dir_path(__FILE__) . 'vendor/autoload.php';

use function Env\env;

$directories = ['functions'];
foreach ($directories as $dir) {
    $pattern = plugin_dir_path(__FILE__) . $dir . '/*.php';

    foreach (glob($pattern) as $filePath) {
        require $filePath;
    }
}
