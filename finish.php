<?php
require("./vendor/autoload.php");

use Kansanradio\CompoundWord;
use Kansanradio\Word;


function mb_ucfirst($str, $encoding = "UTF-8", $lower_str_end = false) {
    $first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
    if ($lower_str_end) {
        $str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
    } else {
        $str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
    }
    $str = $first_letter . $str_end;
    return $str;
}

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
$next = new Word("", "", "");

for ($i = 0;$i < count($p);$i++) {
    $line = $p[$i];
    $ps = explode(" ", $line);
    $sana = $ps[0];
    $baseform = isset($ps[1]) ? $ps[1] : null;
    $wClass = isset($ps[2]) ? $ps[2] : null;
    $word = new Word($sana, $baseform, $wClass);
  
    $trimmedWord = $word->trimmed();
    // get next word for possible pilkku and for compound word
    if (isset($p[$i + 1])) {
        $nn = explode(" ", $p[$i + 1]);
        $nextW = $nn[0];
        $nextB = isset($nn[1]) ? $nn[1] : null;
        $nextC = isset($nn[2]) ? $nn[2] : null;
        $next = new Word($nextW, $nextB, $nextC);
    }
  
    $isYhdyssana = CompoundWord::isCompound($word, $next, $baseFormArray);
    
    if (!empty($isYhdyssana)) {
        if (in_array("UPPER", $isYhdyssana)) {
            $word->word = mb_ucfirst($word->word);
        } else if (in_array("DOUBLE-UPPER", $isYhdyssana)) {
            $word->word = mb_ucfirst($word->word);
            $next->word = mb_ucfirst($next->word);
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
    if ($word->word == "SUOMI") {
      $word->word = "Suomi";
    }
    // capitals
    if ($word->isCapital()) {
        $word->word = mb_ucfirst($word->word, "UTF-8", true);
    }
    
    print $word->word;

    $lastLetter = mb_substr($word->word, -1, 1);

    if (in_array($lastLetter, [".", "?"])) {
        print(PHP_EOL);
    } else if ($lastLetter !== "," && in_array($next->word, $pilkku, true)) {
        print(", ");
    } else {
        print(" ");
    }
}
