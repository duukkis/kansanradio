<?php
namespace Kansanradio;

class Builder
{
    private static function buildWordFromLine($line): Word
    {
        $ps = explode(" ", $line);
        $sana = $ps[0];
        $baseform = isset($ps[1]) ? $ps[1] : null;
        $wClass = isset($ps[2]) ? $ps[2] : null;
        return new Word($sana, $baseform, $wClass);
    }

    public static function buildResult(string $fileName, array $baseFormArray): string
    {
        $result = "";
        $pilkku = ["koska", "että", "mutta"];
        $noPeriodAfter = ["ja", "että"];
        $c = file_get_contents($fileName);
        $p = explode("\n", $c);
        $next = null;
        $lowerCaseNext = false;
        for ($i = 0;$i < count($p);$i++) {
            $word = self::buildWordFromLine($p[$i]);
            if ($lowerCaseNext) {
                $word->setStrLower();
                $lowerCaseNext = false;
            }
            // get next word for possible pilkku and for compound word
            if (isset($p[$i + 1])) {
                $next = self::buildWordFromLine($p[$i + 1]);
            }
            /*    
EU MTK etc
            if ($word->baseform == "lyhenne" && mb_strlen($word->word) === mb_strlen($word->baseform)) {
              $word->word = $word->baseform;
            }
*/

            $azure = CompoundWord::azureFixes($word, $next);
            if (null !== $azure) {
                $firstLetterCapital = $word->isFirstLetterCapital();
                $word->word = $azure;
                if ($firstLetterCapital) {
                  $word->setUcFirst();
                }
                $i++;
            } else {
                $word = CompoundWord::makeCompound($word, $next, $baseFormArray);
                if ($word->isCompound) {
                    $next = null;
                    $i++;
                }
                // move this logic to Compoundword and have it return a new word
                // add isCompound to Word and if($newword->isCompound) { $i++ }
                // fix Nato-jäsenyys and Peru Pello
            }
            // capital
            if ($word->isCapital() && !$word->isCompound) {
                $word->setUcFirst();
            }
            $lastLetter = mb_substr($word->word, -1, 1);
            if ($word->isLastLetterEndingSentence() && in_array($word->trimmed(), $noPeriodAfter, true)) {
                $word->trim();
                // lower case to be sure the next
                $lowerCaseNext = true;
            }

            $result .= $word->word;

            if ($word->isLastLetterEndingSentence()) {
                $result .= PHP_EOL;
            } elseif (!$word->isLastLetterComma() && $next !== null && in_array($next->trimmed(), $pilkku, true)) {
                $result .= ", ";
            } else {
                $result .= " ";
            }
        }
        return self::cleanUpAans($result);
    }
  
    private static function cleanUpAans(string $result): string
    {
        return str_replace(["äää", "aaa", "nice", " pl ", " pl.", "whats app", "Whatsapp"], ["äkää", "akaa", "nais", " PL ", " PL.", "WhatsApp", "WhatsApp"], $result);
    }
}
