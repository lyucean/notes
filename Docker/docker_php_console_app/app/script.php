<?php
require_once('vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dotenv->required('ENVIRONMENT')->notEmpty();

// Где будем хранить логи
$logFile = 'log/work.log';

// Проверяем, существует ли файл
if (!file_exists($logFile)) {
    // Создаем файл
    touch($logFile);
    chmod($logFile, 0777);
}

// Устанавливаем максимальное время выполнения скрипта в 60 секунд
set_time_limit(60);

// Бесконечный цикл, который будет повторяться после завершения
while (true) {
    // Добавляем запись в файл
    $logEntry = $_ENV['ENVIRONMENT'] . ' - ' . date("Y-m-d H:i:s") . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);

    // Завершаем текущую итерацию, чтобы избежать нагрузки на сервер
    sleep(1); // Задержка 1 секунда перед каждой итерацией цикла

    // Определяем текущее время
    $currentTime = time();

    // Проверяем, если прошла минута, завершаем скрипт и перезапускаем его
    if ($currentTime - $_SERVER['REQUEST_TIME'] >= 60) {
        // Запускаем новый экземпляр скрипта
        exec('php ' . __FILE__ . ' >> /app/log/error.log 2>&1 &');
        exit(); // Завершаем текущий экземпляр скрипта
    }
}