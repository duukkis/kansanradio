<?php

namespace Kansanradio;

class CompoundWord
{
  
  public function __construct(
    private Word $word,
    private Word $other)
  {
    
  }
  
  public function isCompound(): array
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
        "äiti" => ["rukka" => ["TRUE"]],
    ];

    $word = mb_strtolower($this->word->trimmed());
    $other = mb_strtolower($this->other->trimmed());
    
    if (isset($yhdyssanat[$word])) {
        foreach ($yhdyssanat[$word] as $key => $value) {
            if (strpos($other, $key) === 0) {
                return $value;
            }
        }
    }
    // sijapääte fix
    if (in_array($other, ["sta", "lle", "kin", "loinen", "uksia", ])) {
        return ["TRUE"];
    }
    return [];
  }
 
  
}