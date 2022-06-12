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
      "varpu" => ["selle" => "varpuselle"],
      "lohduttaja" => ["sieni" => "lahottajasieni"],
    ];
  
    const COMPOUNDWORDS = [
      "ajo" => ["halli" => ["TRUE"]],
      "asuin" => ["paikalla" => ["TRUE"]],
      "atomi" => ["voima" => ["TRUE"]],
      "huomenta" => ["päivää" => ["TRUE"]],
      "hinnan" => ["nousu" => ["TRUE"]],
      "hoitaja" => ["pula" => ["TRUE"]],
      "jatko" => ["hakemus" => ["TRUE"]],
      "junan" => ["tuoma" => ["TRUE"]],
      "karamelli" => ["paper" => ["TRUE"]],
      "kimppa" => ["porukk" => ["TRUE"]],
      "koiran" => ["omistaja" => ["TRUE"]],
      "korotus" => ["vaatimu" => ["TRUE"]],
      "korpi" => [
        "seud" => ["TRUE"],
        "seut" => ["TRUE"]
      ],
      "laku" => ["jäätelö" => ["TRUE"]],
      "lähi" => ["kuvi" => ["TRUE"]],
      "metalli" => ["kanne" => ["TRUE"]],
      "osman" => ["käämi" => ["TRUE"]],
      "perunamuusi" => ["jauhe" => ["TRUE"]],
      "peruna" => ["pel" => ["TRUE"]],
      "perus" => ["hoitaj" => ["TRUE"]],
      "piha" => ["kasvillisuu" => ["TRUE"]],
      "pizza" => ["pala" => ["TRUE"]],
      "s" => ["market" => ["UPPER", "DASH"]],
      "sian" => ["läski" => ["TRUE"]],
      "sivusta" => ["katsoja" => ["TRUE"]],
      "sotilas" => ["tehtäväs" => ["TRUE"]],
      "säästö" => ["vinkk" => ["TRUE"]],
      "tammer" => ["kosk" => ["UPPER", "TRUE"]],
      "tausta" => ["ään" => ["TRUE"]],
      "terassi" => ["kesä" => ["TRUE"]],
      "tissi" => ["vako" => ["TRUE"]],
      "tosi" => ["koi" => ["TRUE"]],
      "tuhka" => ["kupis" => ["TRUE"]],
      "tupakan" => ["tump" => ["TRUE"]],
      "tyhjän" => ["toimitta" => ["TRUE"]],
      "tä" => ["ynnä" => ["TRUE"]],
      "yli" => ["mainostettu" => ["TRUE"]],
      "vappu" => [
          "pallo" => ["TRUE"],
          "pullo" => ["TRUE"]
      ],
      "vei" => ["tikat" => ["TRUE"]],
      "äiti" => ["rukka" => ["TRUE"]],
      // --------------------------------- eu:n, tv:ssä
      "eu" => ["n" => ["COLON"]],
      "tv" => ["ssä" => ["COLON"]],
      // --------------------------------- true but with a-a, o-o
      "kunta" => ["ala" => ["DASH", "TRUE"]],
      "sota" => ["alu" => ["DASH", "TRUE"]],
      "televisio" => ["ohjelm" => ["DASH", "TRUE"]],

      // --------------------------------- names
      "kansa" => ["radio" => ["UPPER", "TRUE"]],
      "kansan" => [
          "radio" => ["UPPER", "TRUE"],
          "ratio" => ["UPPER", "TRUE"],
      ],
      "ruotsin" => ["pyhtää" => ["UPPER", "TRUE"]],

      // --------------------------------- 
      "whats" => ["app" => ["DOUBLE-UPPER", "TRUE"]],

      // --------------------------------- 
      "yle" => ["areena" => ["DOUBLE-UPPER", "SPACE"]],

      // --------------------------------- 
      "etelä" => ["helsin" => ["DOUBLE-UPPER", "DASH"]],
      "itä" => ["suome" => ["DOUBLE-UPPER", "DASH"]],
      "pohjois" => ["pohjanmaa" => ["DOUBLE-UPPER", "DASH"]],
      "varsinais" => ["suome" => ["DOUBLE-UPPER", "DASH"]],
      "kanta" => ["häme" => ["DOUBLE-UPPER", "DASH"]],
    ];
  
    public static function isCompound(Word $word, $other, array $baseforms): array
    {
        if (is_null($other)) {
            return [];
        }
        $word1 = mb_strtolower($word->trimmed());
        $word2 = mb_strtolower($other->trimmed());
    
        if (isset(self::COMPOUNDWORDS[$word1])) {
            foreach (self::COMPOUNDWORDS[$word1] as $key => $value) {
                if (strpos($word2, $key) === 0) {
                    return $value;
                }
            }
        }
        if (in_array($word->word, ["etelä", "itä", "länsi", "pohjois", "kanta", "varsinais"]) && $other->wClass == "paikannimi") {
            return ["DOUBLE-UPPER", "DASH"];
        }

      
        // sijapääte fix
        if (in_array($word2, 
                     [
                       "sta", "lle", "lla", "kin", "han", "loinen", "laisista", 
                      "uksia", "täminen", "mme", "ään", "akaan", "vät", "hun", 
                       "ville", // if previous word ends to vowel?
                       "na",
                     ])
           ) {
            return ["TRUE"];
        }
    
        if (in_array($other->word, ["vuotiaat", "vuotiaita", "vuotias", "luvun", "vuotiaasta"]) 
            && $word->wClass == "lukusana") {
            return ["DASH"];
        }
        if (in_array($other->word, ["luokan"]) && $word->wClass == "lukusana") {
            return ["DOT"];
        }
        if (in_array($other->word, ["€"]) && $word->wClass == "lukusana") {
            return ["TRUE"];
        }
    
        if ($word->word === $other->word && $word->wClass !== "lukusana") {
            return ["REMOVE"];
        }

        if (!empty($word->baseform) && !empty($other->baseform)) {
            if (in_array($word1.$other->baseform, $baseforms)) {
                return ["TRUE"];
            } else if (in_array($word1."-".$other->baseform, $baseforms)) {
                return ["TRUE", "DASH"];
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
        if (file_exists($fileName)) {
            $c = file_get_contents($fileName);
            $p = explode("\n", $c);
            return $p;
        }
        return [];
    }  
}
