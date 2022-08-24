<?php
require "../vendor/autoload.php";

use Kansanradio\CompoundWord;
use Kansanradio\Word;
use Kansanradio\Builder;

$baseFormArray = CompoundWord::buildCompoundWordArray("../resources/yhdyssanat.txt");

$dir_handle = opendir("./input/");

// reading the contents of the directory
while(($fileName = readdir($dir_handle)) !== false)
{
    if (!in_array($fileName, [".", ".."])) {
        $output = str_replace("log", "result", $fileName) . ".txt";
        $result = Builder::buildResult("./input/" . $fileName, $baseFormArray);
        file_put_contents("./output/" . $output, $result);
        print $fileName . " > " . $output . PHP_EOL;
    }
}

