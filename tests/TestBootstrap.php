<?php declare(strict_types=1);

use Shopware\Core\TestBootstrapper;

function getProjectDir(): string
{
    if (isset($_SERVER['PROJECT_ROOT']) && is_string($_SERVER['PROJECT_ROOT']) && is_dir($_SERVER['PROJECT_ROOT'])) {
        return $_SERVER['PROJECT_ROOT'];
    }

    $rootDir = dirname(__DIR__, 2);
    $dir = $rootDir;
    while (!is_file($dir . '/.env') && !is_file($dir . '/devenv.nix')) {
        if ($dir === dirname($dir)) {
            return $rootDir;
        }
        $dir = dirname($dir);
    }

    chdir($dir);

    return $dir;
}

$testProjectDir = getProjectDir();
$bootstrapLocations = [
    $testProjectDir . '/src/Core/TestBootstrapper.php',
    $testProjectDir . '/vendor/shopware/core/TestBootstrapper.php',
    $testProjectDir . '/vendor/shopware/platform/src/Core/TestBootstrapper.php',
];

foreach ($bootstrapLocations as $file) {
    if (is_file($file)) {
        require_once $file;

        break;
    }
}

return (new TestBootstrapper())
    ->setProjectDir($testProjectDir)
    ->setLoadEnvFile(true)
    ->addCallingPlugin()
    ->bootstrap()
    ->getClassLoader();
