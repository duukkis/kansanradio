<?php

namespace Kansanradio;

class CompoundWord
{
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
  
  public static function isCompound(Word $word, Word $other, array $baseforms): array
  {
    $word1 = mb_strtolower($word->trimmed());
    $word2 = mb_strtolower($other->trimmed());
    
    if (isset(self::COMPOUNDWORDS[$word1])) {
        foreach (self::COMPOUNDWORDS[$word1] as $key => $value) {
            if (strpos($word2, $key) === 0) {
                return $value;
            }
        }
    }
    // sijapääte fix
    if (in_array($word2, ["sta", "lle", "kin", "loinen", "uksia", "täminen", ])) {
        return ["TRUE"];
    }
    
    if (in_array($other->word, ["vuotiaat", "vuotiaita", "vuotias"]) && $word->wClass == "lukusana") {
        return ["DASH"];
    }
    
    if (!empty($word->baseform) && !empty($other->baseform)) {
      if (in_array($word->word.$other->baseform, $baseforms)) {
        return ["TRUE"];
      } else if (in_array($word->word."-".$other->baseform, $baseforms)) {
        return ["TRUE", "DASH"];
      }
    }
    return [];
  }
 
  /**
   * call this only once for performance
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