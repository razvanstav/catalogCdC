<?php

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/app/Support/Autoloader.php';
\App\Support\Autoloader::register(APP_ROOT);
require_once APP_ROOT . '/app/Support/Helpers.php';
require_once APP_ROOT . '/tests/SecurityAndReBACTest.php';

echo "\n=======================================================\n";
echo "🧪 Îndrumar (CdC) — Suită de Teste Automate de Securitate\n";
echo "=======================================================\n\n";

$testSuite = new \Tests\SecurityAndReBACTest();
$results = $testSuite->runAll();

$allPassed = true;
$count = 0;
$passedCount = 0;

foreach ($results as $res) {
    if (!$res) continue;
    $count++;
    if ($res['passed']) {
        $passedCount++;
        echo "  \033[32m[PASS]\033[0m " . $res['name'] . "\n";
        echo "         " . $res['details'] . "\n\n";
    } else {
        $allPassed = false;
        echo "  \033[31m[FAIL]\033[0m " . $res['name'] . "\n";
        echo "         " . $res['details'] . "\n\n";
    }
}

echo "-------------------------------------------------------\n";
echo "Rezumat: $passedCount din $count teste au trecut cu succes.\n";
if ($allPassed) {
    echo "\033[32mTOATE TESTELE DE SECURITATE ȘI ReBAC AU TRECUT! (100% OK)\033[0m\n";
} else {
    echo "\033[31mUNELE TESTE AU EȘUAT!\033[0m\n";
}
echo "=======================================================\n\n";

exit($allPassed ? 0 : 1);
