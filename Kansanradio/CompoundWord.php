<?php

namespace Kansanradio;

class CompoundWord
{
    const FIXUS = [
      "kainalo" => ["Schauman" => "Kainalosauvan"],
      "high" => ["Kuha" => "haikuhan"],
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
      "oli" => ["vedeltä" => "Orivedeltä"],
    ];

    public static function makeCompound(Word $word, ?Word $other, array $baseforms): Word
    {
        if (is_null($other)) {
            return $word;
        }
        $word1 = $word->lower();
        $word2 = $other->lower();

        // check local
        if (in_array($word1, ["eu", "tv"]) && in_array($word2, ["n", "ssä"])) {
            return Word::append($word, ":", $other);
        }
        if (in_array($word1, ["yle"]) && strpos($word2, "areena") === 0) {
            return Word::append($word->setUcFirst(), " ", $other->setUcFirst());
        }

        if (in_array($word1, ["etelä", "itä", "länsi", "pohjois", "kanta", "varsinais"])
            && in_array($other->wClass, ["paikannimi", "nimisana"])) {
            return Word::append($word->setUcFirst(), "-", $other->setUcFirst(), $other->wClass);
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
            return Word::append($word->trim(), "", $other, $word->wClass);
        }

        if (
            (
                in_array($other->baseform, ["vuotias", "luku"]) ||
                in_array($other->word, ["vuotiaita", "vuotiaat", "vuotiaasta", "vuotias"])
            )
            && $word->wClass == "lukusana") {
            return Word::append($word, "-", $other, $other->wClass);
        }
        if (in_array($other->baseform, ["luokka"]) && $word->wClass == "lukusana") {
            return Word::append($word, ".", $other);
        }
        if (in_array($other->word, ["€"]) && $word->wClass == "lukusana") {
            return Word::append($word, "", $other);
        }
        // append numbers
        if (
            is_numeric($word->trimmed()) &&
            is_numeric($other->trimmed()) &&
            $word->wClass == "lukusana" &&
            $other->wClass == "lukusana" &&
            strlen($other->trimmed()) == 1
        ) {
            return Word::append($word, "", $other, "lukusana");
        }

        if ($word->word === $other->word && $word->wClass !== "lukusana") {
            return Word::append($word, "", new Word(""));
        }

        // compare lower cases, return what is found on baseforms
        if (!empty($word->baseform) && !empty($other->baseform)) {
            $baseLower = mb_strtolower($other->baseform);
            if (isset($baseforms[$word1.$baseLower])) {
                return self::buildFromWord($word, $other, $baseforms[$word1.$baseLower]);
            } else if (isset($baseforms[$word1 . "-" . $baseLower])) {
                return self::buildFromWord($word, $other, $baseforms[$word1 . "-" . $baseLower]);
            }
        }

        if (isset($baseforms[$word1.$word2])) {
            return self::buildFromWord($word, $other, $baseforms[$word1.$word2]);
        } else if (isset($baseforms[$word1 . "-" . $word2])) {
            return self::buildFromWord($word, $other, $baseforms[$word1."-".$word2]);
        }
        return $word;
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

    private static function buildFromWord(Word $word, Word $other, string $k): Word
    {
        $separator = "";
        $hasDash = explode("-", $k);
        $firstUpper = self::startsWithUpper($hasDash[0]);
        if (count($hasDash) > 1) {
            $separator = "-";
            $secondUpper = self::startsWithUpper($hasDash[1]);
        } else {
            $secondUpper = false;
        }
        if ($firstUpper && $secondUpper) {
            return Word::append($word->setUcFirst(), $separator, $other->setUcFirst());
        } else if ($firstUpper) {
            return Word::append($word->setUcFirst(), $separator, $other->setStrLower());
        }
        return Word::append($word, $separator, $other->setStrLower());
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
                $result[mb_strtolower($k, "UTF-8")] = $k;
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
