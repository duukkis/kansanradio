<?php

namespace Kansanradio;

class CompoundWord
{
    const FIXUS = [
      "kainalo" => ["Schauman" => "Kainalosauvan"],
      "se" => ["lainen" => "sellainen"],
      "roll" => ["alle" => "rouvalle"],
      "herran" => ["rokka" => "hernerokka"],
      "herra" => ["soppa" => "hernesoppa"],
      "liiga" => ["ja" => "liikoja"],
      "varpu" => ["selle" => "varpuselle"],
      "lohduttaja" => ["sieni" => "lahottajasieni"],
      "yksi" => ["valtias" => "yksinvaltias"],
      "tammi" => ["meihin" => "tamineihin"],
      "enter" => ["bakteeri" => "enterobakteeri"],
    ];
  
    const COMPOUNDWORDS = [
      "s" => ["market" => ["UPPER", "DASH"]],
      "sian" => ["läski" => ["TRUE"]],
      "tammer" => ["kosk" => ["UPPER", "TRUE"]],
      "tosi" => ["koi" => ["TRUE"]],
      "tä" => ["ynnä" => ["TRUE"]],
      "vei" => ["tikat" => ["TRUE"]],
      // --------------------------------- eu:n, tv:ssä
      "eu" => ["n" => ["COLON"]],
      "tv" => ["ssä" => ["COLON"]],
      "kansan" => [
        "radio" => ["UPPER", "TRUE"],
        "ratio" => ["UPPER", "TRUE"],
      ],
      // --------------------------------- names
      "ruotsin" => ["pyhtää" => ["UPPER", "TRUE"]],

      // --------------------------------- 
      "whats" => ["app" => ["DOUBLE-UPPER", "TRUE"]],

      // --------------------------------- 
      "yle" => ["areena" => ["DOUBLE-UPPER", "SPACE"]],
    ];
  
    public static function isCompound(Word $word, Word $other, array $baseforms): array
    {
        if (is_null($other)) {
            return [];
        }
        $word1 = $word->lower();
        $word2 = $other->lower();
    
        if (isset(self::COMPOUNDWORDS[$word1])) {
            foreach (self::COMPOUNDWORDS[$word1] as $key => $value) {
                if (strpos($word2, $key) === 0) {
                    return $value;
                }
            }
        }
        if (in_array($word->lower(), ["etelä", "itä", "länsi", "pohjois", "kanta", "varsinais"])
            && in_array($other->wClass, ["paikannimi", "nimisana"])) {
            return ["DOUBLE-UPPER", "DASH"];
        }
      
        // sijapääte fix
        if (in_array($word2, 
                     [
                       "sta", "lle", "lla", "kin", "han", "loinen", "laisista", 
                      "uksia", "täminen", "mme", "ään", "akaan", "vät", "hun", 
                       "ville", // if previous word ends to vowel?
                       "na", "kaan", "n", "set", "a", "llä", 
                     ])
           ) {
            return ["TRUE"];
        }
    
        if (
            (
                in_array($other->baseform, ["vuotias", "luku"]) ||
                in_array($other->word, ["vuotiaita", "vuotiaat", "vuotiaasta", "vuotias"])
            )
            && $word->wClass == "lukusana") {
            return ["DASH"];
        }
        if (in_array($other->baseform, ["luokka"]) && $word->wClass == "lukusana") {
            return ["DOT"];
        }
        if (in_array($other->word, ["€"]) && $word->wClass == "lukusana") {
            return ["TRUE"];
        }
    
        if ($word->word === $other->word && $word->wClass !== "lukusana") {
            return ["REMOVE"];
        }

        // compare lower cases, return what is found on baseforms
        if (!empty($word->baseform) && !empty($other->baseform)) {
            $baseLower = mb_strtolower($other->baseform);
            if (isset($baseforms[$word1.$baseLower])) {
                return $baseforms[$word1.$baseLower];
            } else if (isset($baseforms[$word1 . "-" . $baseLower])) {
                return $baseforms[$word1 . "-" . $baseLower];
            }
        }
        return [];
    }
  
    public static function azureFixes(Word $word, $other): ?string
    {
        if (is_null($other)) {
            return null;
        }
        if (isset(self::FIXUS[$word->baseform][$other->baseform])) {
            return self::FIXUS[$word->baseform][$other->baseform];
        }
        if (isset(self::FIXUS[$word->word][$other->baseform])) {
            return self::FIXUS[$word->word][$other->baseform];
        }
        return null;
    }
 
    /**
     * Call this only once for performance
     *
     * @param string $fileName -
     *
     * @return array
     */
    public static function buildCompoundWordArray(string $fileName): array
    {
        $result = [];
        if (file_exists($fileName)) {
            $c = file_get_contents($fileName);
            $values = explode("\n", $c);
            foreach ($values as $k) {
                $hasDash = explode("-", $k);
                $firstUpper = self::startsWithUpper($hasDash[0]);
                if (count($hasDash) > 1) {
                    $wRes = ["DASH"];
                    $secondUpper = self::startsWithUpper($hasDash[1]);
                } else {
                    $wRes = ["TRUE"];
                    $secondUpper = false;
                }
                if ($firstUpper && $secondUpper) {
                    $wRes[] = "DOUBLE-UPPER";
                } else if ($firstUpper) {
                    $wRes[] = "UPPER";
                }
                $result[mb_strtolower($k, "UTF-8")] = $wRes;
            }
        }
        return $result;
    }

    private static function startsWithUpper(string $str): bool
    {
        $firstLetter = mb_substr($str, 0, 1, "UTF-8");
        $capital = mb_strtoupper($firstLetter, "UTF-8");
        return ($firstLetter === $capital);
    }
}
