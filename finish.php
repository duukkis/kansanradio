<?php
require("./vendor/autoload.php");

use Kansanradio\CompoundWord;
use Kansanradio\Word;

$pilkku = ["koska", "että", "mutta"];
$baseFormArray = CompoundWord::buildCompoundWordArray("./resources/yhdyssanat.txt");

// ------------------------------- start action data looks like this
// Min<C3><A4> =pppp nimisana
// olen =pppp teonsana
// johanna =ipppppp etunimi
// <C3><A4>stman
// ja =pp sidesana
$c = file_get_contents("./data/log");
$p = explode("\n", $c);
$next = null;

function buildWordFromLine($line): Word
{
    $ps = explode(" ", $line);
    $sana = $ps[0];
    $baseform = isset($ps[1]) ? $ps[1] : null;
    $wClass = isset($ps[2]) ? $ps[2] : null;
    return new Word($sana, $baseform, $wClass);
}

for ($i = 0;$i < count($p);$i++) {
    $word = buildWordFromLine($p[$i]);
    // get next word for possible pilkku and for compound word
    if (isset($p[$i + 1])) {
        $next = buildWordFromLine($p[$i + 1]);
    }
  
    $isYhdyssana = CompoundWord::isCompound($word, $next, $baseFormArray);
    
    if (!empty($isYhdyssana)) {
        if (in_array("UPPER", $isYhdyssana)) {
            $word->word = $word->mbUcfirst();
        } else if (in_array("DOUBLE-UPPER", $isYhdyssana)) {
            $word->word = $word->mbUcfirst();
            $next->word = $next->mbUcfirst();
        }
      
        if (in_array("DASH", $isYhdyssana)) {
            $word->word = $word->word . "-" . $next->word;
            $i++; // skip next
        } elseif (in_array("TRUE", $isYhdyssana)) {
            $word->word = $word->word . $next->word;
            $i++; // skip next
        } elseif (in_array("SPACE", $isYhdyssana)) {
            $word->word = $word->word . " " . $next->word;
            $i++; // skip next
        }
    }
    // capital
    if ($word->isCapital()) {
        $word->word = $word->mbUcfirst();
    }
    
    print $word->word;

    $lastLetter = mb_substr($word->word, -1, 1);

    if (in_array($lastLetter, [".", "?"])) {
        print(PHP_EOL);
    } else if ($lastLetter !== "," && $next !== null && in_array($next->trimmed(), $pilkku, true)) {
        print(", ");
    } else {
        print(" ");
    }
}
