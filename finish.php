<?php
require "./vendor/autoload.php";

use Kansanradio\CompoundWord;
use Kansanradio\Word;
use Kansanradio\Builder;

$baseFormArray = CompoundWord::buildCompoundWordArray("./resources/yhdyssanat.txt");
$replacer = Word::buildReplacerFromFile("./resources/replaces.txt");

$result = Builder::buildResult("./data/log", $baseFormArray, $replacer);
file_put_contents("./data/final.txt", $result);
