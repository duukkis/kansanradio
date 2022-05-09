<?php

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
// what voikko thinks is paikannimi but is not
$notuppers = ["osta", "koskien", "ostaa", "illalla", "maissa", "auring", 
              "aurinko", "maahan", "nurmelle", "puolin", "maata", "maassa", "auton", "äänistä",
              "asukas", "takasin", "monien", "yleensä", "peruna", 
             ];
// .... keep ^ for a while, start using this
$basenotupper = ["Päivi", "Päivä", "Ilma", "Sikiö", "Helli", "Valta", "Pello", "Osta", "Säde", 
                ];

// todo
// lukusana -luvulla >> 1990-luvulla, 2000 luku >> 2000-luku

// what voikko thinks is not nimi but is
$defuppers = ["kansanradio", "ruotsi", "turku", "skanska", "yit", ];
$pilkku = ["koska", "että", "mutta"];

function isYhdyssana($word, $next): array
{
    $yhdyssanat = [
        "ajo" => ["halli" => ["TRUE"]],
        "asuin" => ["paikalla" => ["TRUE"]],
        "atomi" => ["voima" => ["TRUE"]],
        "etelä" => ["helsin" => ["DOUBLE-UPPER", "DASH"]],
        "huomenta" => ["päivää" => ["TRUE"]],
        "hinnan" => ["nousu" => ["TRUE"]],
        "hoitaja" => ["pula" => ["TRUE"]],
        "itä" => ["suome" => ["DOUBLE-UPPER", "DASH"]],
        "jatko" => ["hakemus" => ["TRUE"]],
        "junan" => ["tuoma" => ["TRUE"]],
        "kansa" => ["radio" => ["UPPER", "TRUE"]],
        "kansan" => [
            "radio" => ["UPPER", "TRUE"],
            "ratio" => ["UPPER", "TRUE"]
        ],
        "karamelli" => ["paper" => ["UPPER", "TRUE"]],
        "kimppa" => ["porukk" => ["TRUE"]],
        "koiran" => ["omistaja" => ["TRUE"]],
        "korotus" => ["vaatimu" => ["TRUE"]],
        "korpi" => [
          "seud" => ["TRUE"],
          "seut" => ["TRUE"]
        ],
        "kunta" => ["ala" => ["DASH", "TRUE"]],
        "laku" => ["jäätelö" => ["TRUE"]],
        "lähi" => ["kuvi" => ["TRUE"]],
        "metalli" => ["kanne" => ["TRUE"]],
        "perunamuusi" => ["jauhe" => ["TRUE"]],
        "peruna" => ["pel" => ["TRUE"]],
        "perus" => ["hoitaj" => ["TRUE"]],
        "piha" => ["kasvillisuu" => ["TRUE"]],
        "pizza" => ["pala" => ["TRUE"]],
        "pohjois" => ["pohjanmaa" => ["DOUBLE-UPPER", "DASH"]],
        "s" => ["market" => ["UPPER", "DASH"]],
        "sian" => ["läski" => ["TRUE"]],
        "sivusta" => ["katsoja" => ["TRUE"]],
        "sota" => ["alu" => ["DASH", "TRUE"]],
        "sotilas" => ["tehtäväs" => ["TRUE"]],
        "säästö" => ["vinkk" => ["TRUE"]],
        "tammer" => ["kosk" => ["UPPER", "TRUE"]],
        "tausta" => ["ään" => ["TRUE"]],
        "televisio" => ["ohjelm" => ["DASH", "TRUE"]],
        "terassi" => ["kesä" => ["TRUE"]],
        "tissi" => ["vako" => ["TRUE"]],
        "tosi" => ["koi" => ["TRUE"]],
        "tuhka" => ["kupis" => ["TRUE"]],
        "tupakan" => ["tump" => ["TRUE"]],
        "tyhjän" => ["toimitta" => ["TRUE"]],
        "tä" => ["ynnä" => ["TRUE"]],
        "yle" => ["areena" => ["DOUBLE-UPPER", "SPACE"]],
        "yli" => ["mainostettu" => ["TRUE"]],
        "varsinais" => ["suome" => ["DOUBLE-UPPER", "DASH"]],
        "vappu" => [
            "pallo" => ["TRUE"],
            "pullo" => ["TRUE"]
        ],
        "whats" => ["app" => ["DOUBLE-UPPER", "TRUE"]],
    ];

    if (isset($yhdyssanat[$word])) {
        foreach ($yhdyssanat[$word] as $key => $value) {
            if (strpos($next, $key) === 0) {
                return $value;
            }
        }
    }
    // sijapääte fix
    if (in_array($next, ["sta", "lle", "kin", "loinen", "uksia", ])) {
        return ["TRUE"];
    }
    return [];
}

// ------------------------------- start action data looks like this
// Min<C3><A4> =pppp nimisana
// olen =pppp teonsana
// johanna =ipppppp etunimi
// <C3><A4>stman
// ja =pp sidesana
$c = file_get_contents("./data/log");
$p = explode("\n", $c);
$next = null;

for ($i = 0;$i < count($p);$i++) {
    $line = $p[$i];
    $ps = explode(" ", $line);
    $word = $ps[0];
    $baseform = isset($ps[1]) ? $ps[1] : null;
    $trimmedWord = trim($word, ".,?!");
    // get next word for possible pilkku and for compound word
    if (isset($p[$i + 1])) {
        $next = explode(" ", $p[$i + 1])[0];
    }

    $isYhdyssana = isYhdyssana(
        mb_strtolower($trimmedWord),
        mb_strtolower(trim($next, ".,?!"))
    );
    if (!empty($isYhdyssana)) {
        $word = $trimmedWord;
        if (in_array("UPPER", $isYhdyssana)) {
            $word = mb_ucfirst($word);
        } else if (in_array("DOUBLE-UPPER", $isYhdyssana)) {
            $word = mb_ucfirst($word);
            $next = mb_ucfirst($next);
        }
        if (in_array("DASH", $isYhdyssana)) {
            $word = $word . "-" . $next;
            $i++; // skip next
        } elseif (in_array("TRUE", $isYhdyssana)) {
            $word = $word . $next;
            $i++; // skip next
        } elseif (in_array("SPACE", $isYhdyssana)) {
            $word = $word . " " . $next;
            $i++; // skip next
        }
    }
    // capitals
    if ((isset($ps[2]) && in_array($ps[2], $uppers) && !in_array($trimmedWord, $notuppers) && !in_array($baseform, $basenotupper)) || in_array($baseform, $defuppers) || in_array($trimmedWord, $defuppers)) {
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
