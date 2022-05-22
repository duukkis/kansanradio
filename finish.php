<?php
require "./vendor/autoload.php";

use Kansanradio\CompoundWord;
use Kansanradio\Word;
use Kansanradio\Builder;

$baseFormArray = CompoundWord::buildCompoundWordArray("./resources/yhdyssanat.txt");

$result = Builder::buildResult("./data/log" . $i, $baseFormArray);
file_put_contents("./data/final.txt", $result);
