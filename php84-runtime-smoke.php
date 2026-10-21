<?php
if (PHP_VERSION !== '8.4.27') {
    throw new RuntimeException('Unexpected PHP version: ' . PHP_VERSION);
}
echo 'PHP ', PHP_VERSION, ' / ', PHP_SAPI, PHP_EOL;
foreach (['bcmath', 'mbstring', 'pdo_mysql', 'mysqli', 'zip', 'gd', 'Zend OPcache', 'pcntl', 'redis', 'imagick'] as $extension) {
    if (!extension_loaded($extension)) {
        throw new RuntimeException('Missing extension: ' . $extension);
    }
    echo $extension, ': ', phpversion($extension), PHP_EOL;
}
if (bcadd('0.10', '0.20', 2) !== '0.30' || mb_strlen('安全更新') !== 4) {
    throw new RuntimeException('Decimal or multibyte check failed');
}
if (!in_array('mysql', PDO::getAvailableDrivers(), true) || !mysqli_init() || !function_exists('pcntl_signal')) {
    throw new RuntimeException('MySQL or process-control API unavailable');
}
$zipPath = tempnam(sys_get_temp_dir(), 'php84-smoke-');
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::OVERWRITE) !== true || !$zip->addFromString('check.txt', 'ok') || !$zip->close()) {
    throw new RuntimeException('ZIP creation failed');
}
if ($zip->open($zipPath) !== true || $zip->getFromName('check.txt') !== 'ok') {
    throw new RuntimeException('ZIP round trip failed');
}
$zip->close();
unlink($zipPath);
$gd = imagecreatetruecolor(2, 2);
ob_start();
imagepng($gd);
$png = ob_get_clean();
if (!str_starts_with($png, "\x89PNG")) {
    throw new RuntimeException('GD PNG encoding failed');
}
$imagick = new Imagick();
$imagick->newImage(2, 2, new ImagickPixel('white'));
$imagick->setImageFormat('png');
if (!str_starts_with($imagick->getImageBlob(), "\x89PNG")) {
    throw new RuntimeException('Imagick PNG encoding failed');
}
new Redis();
echo 'PASS: company extensions / decimal / UTF-8 / ZIP / GD / Imagick', PHP_EOL;
