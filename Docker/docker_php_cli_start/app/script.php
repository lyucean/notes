<?php

require_once('vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dotenv->required('ENVIRONMENT')->notEmpty();


while (true) {
    echo $_ENV['ENVIRONMENT'] . PHP_EOL;
    echo "Current date and time: " . date("Y-m-d H:i:s") . PHP_EOL;
    sleep(1); // Задержка 1 секунда перед каждой итерацией цикла
}