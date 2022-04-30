<?php

$c = file_get_contents("./data/log");

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

$uppers = ["etunimi", "sukunimi", "paikannimi", "nimi"];
$pilkku = ["koska", "että", "mutta"];

function isYhdyssana($word, $next): array
{
    $yhdyssanat = [
        "asuin" => ["paikalla", ["TRUE"]],
        "huomenta" => ["päivää", ["TRUE"]],
        "itä" => ["suome", ["DOUBLE-UPPER", "DASH"]],
        "jatko" => ["hakemus", ["TRUE"]],
        "kansan" => ["radio", ["UPPER", "TRUE"]],
        "koiran" => ["omistaja", ["TRUE"]],
        "lähi" => ["kuvi", ["TRUE"]],
        "metalli" => ["kanne", ["TRUE"]],
        "perunamuusi" => ["jauhe", ["TRUE"]],
        "perus" => ["hoitaj", ["TRUE"]],
        "piha" => ["kasvillisuu", ["TRUE"]],
        "s" => ["market", ["UPPER", "DASH"]],
        "sian" => ["läski", ["TRUE"]],
        "terassi" => ["kesä", ["TRUE"]],
        "tosi" => ["koi", ["TRUE"]],
        "tuhka" => ["kupis", ["TRUE"]],
        "tupakan" => ["tump", ["TRUE"]],
        "tä" => ["ynnä", ["TRUE"]],
        "varsinais" => ["suome", ["DOUBLE-UPPER", "DASH"]],
        "whats" => ["app", ["DOUBLE-UPPER", "TRUE"]],
    ];

    if (isset($yhdyssanat[$word]) && strpos($next, $yhdyssanat[$word][0]) === 0) {
        return $yhdyssanat[$word][1];
    }
    // sijapääte fix
    if (in_array($next, ["sta", "lle"])) {
        return ["TRUE"];
    }
    return [];
}

// start action
$p = explode("\n", $c);
$next = null;

for ($i = 0;$i < count($p);$i++) {
    $line = $p[$i];
    $ps = explode(" ", $line);
    $word = $ps[0];
    // get next word for possible pilkku and for compound word
    if (isset($p[$i + 1])) {
        $next = explode(" ", $p[$i + 1])[0];
    }

    $isYhdyssana = isYhdyssana(
        mb_strtolower(trim($word, ".,?!")),
        mb_strtolower(trim($next, ".,?!"))
    );
    if (!empty($isYhdyssana)) {
        $word = trim($word, ".,?! ");
        if (in_array("UPPER", $isYhdyssana)) {
            $word = mb_ucfirst($word);
        } else if (in_array("DOUBLE-UPPER", $isYhdyssana)) {
            $word = mb_ucfirst($word);
            $next = mb_ucfirst($next);
        }
        if (in_array("DASH", $isYhdyssana)) {
            $word = $word . "-" . $next;
            $i++; // skip next
        } else if (in_array("TRUE", $isYhdyssana)) {
            $word = $word . $next;
            $i++; // skip next
        }
    }
    // capitals
    if (isset($ps[2]) && in_array($ps[2], $uppers)) {
        $word = mb_ucfirst($word, "UTF-8", true);
    }
    print $word;

    $lastLetter = mb_substr($word, -1, 1);

    if (in_array($lastLetter, [".", "?"])) {
        print(PHP_EOL);
    } else if ($lastLetter !== "," && in_array($next, $pilkku, true)) {
        print(", ");
    } else {
        print(" ");
    }
}
