<?php

set_time_limit(0);

require 'vendor/autoload.php';

use KauserCorreios\Correios;

$correios = new Correios(
    '87047000',
    '', // codigo empresa (opcional)
    '' // senha (opcional)
);

foreach ($correios->capitais as $capital) {
    foreach ($correios->pesos as $key => $peso) {
        $result[] = $correios->getFrete($capital[0], $capital[3], $peso);
    }
}

echo "<pre>";
var_dump($result); // saída com todos os Ceps e seus valores;
echo "</pre>";
