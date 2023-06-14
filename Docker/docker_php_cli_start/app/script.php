<?php

require_once('vendor/autoload.php');

// use the factory to create a Faker\Generator instance
//$faker = Faker\Factory::create();

// generate data by accessing properties


//echo "Hello, World! " . $faker->name . PHP_EOL;

while (true) {
    echo "Current date and time: " . date("Y-m-d H:i:s") . PHP_EOL;
    sleep(1); // Задержка 1 секунда перед каждой итерацией цикла
}