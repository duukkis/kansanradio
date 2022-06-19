<?php
require "../vendor/autoload.php";

use Kansanradio\CompoundWord;
use Kansanradio\Word;
use Kansanradio\Builder;

$baseFormArray = CompoundWord::buildCompoundWordArray("../resources/yhdyssanat.txt");

$numberOfLogs = 8;

for ($i = 1;$i <= $numberOfLogs;$i++) {
  $result = Builder::buildResult("./input/log" . $i, $baseFormArray);
  file_put_contents("./output/result" . $i . ".txt", $result);
}

