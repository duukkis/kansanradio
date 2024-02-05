<?php
namespace Kansanradio;

class Builder
{
    private static function buildWordFromLine($line, array $replacer): Word
    {
        $ps = explode(" ", $line);
        $sana = $ps[0];
        if (isset($replacer[$sana])) {
            $sana = $replacer[$sana];
        }
        $baseform = isset($ps[1]) ? $ps[1] : null;
        $wClass = isset($ps[2]) ? $ps[2] : null;
        return new Word($sana, $baseform, $wClass);
    }

    public static function buildResult(string $fileName, array $baseFormArray, array $replacer = []): string
    {
        $result = "";
        // not kuin >> muuta kuin, ennen kuin
        $commaWords = ["mutta", "että", "jotta", "koska", "kunnes", "jos", "vaikka", "jollei", "ellei", "kunhan", "joskin"];
        $noPeriodAfter = array_merge($commaWords, ["ja"]);
        $c = file_get_contents($fileName);
        $p = explode("\n", $c);

        // ------------------------------------ build word array
        /** @var array<int,Word> $words */
        $words = [];
        for ($i = 0; $i < count($p); $i++) {
            $words[] = self::buildWordFromLine($p[$i], $replacer);
        }

        // ------------------------------------ compound words commas fixes
        $next = null;
        $lowerCaseNext = false;
        for ($i = 0; $i < count($words); $i++) {
            /** @var Word $word */
            $word = $words[$i];
            if ($lowerCaseNext) {
                $word->setStrLower();
                $lowerCaseNext = false;
            }
            // get next word for possible pilkku and for compound word
            if (isset($words[$i + 1])) {
                /** @var ?Word $word */
                $next = $words[$i + 1];
            }

            $azure = CompoundWord::azureFixes($word, $next);
            if (null !== $azure) {
                $firstLetterCapital = $word->isFirstLetterCapital();
                $word->word = $azure;
                if ($firstLetterCapital) {
                  $word->setUcFirst();
                }
                $words[$i] = $word;
                unset($words[$i+1]);
                $words = array_values($words);
            } else {
                $word = CompoundWord::makeCompound($word, $next, $baseFormArray);
                // if a new word is formed, remove the next and redo for more compound
                // g 7 maat >> G7 maat >> G7-maat
                if ($word->isCompound) {
                    // set false on the other round
                    $word->isCompound = false;
                    $words[$i] = $word;
                    unset($words[$i+1]);
                    $words = array_values($words); // 'reindex' array
                    $next = null;
                    $i--;
                }
            }
            // capital
            if ($word->isCapital() && !$word->isCompound) {
                $word->setUcFirst();
            }
            // if next is että, mutta
            if ($word->isLastLetterEndingSentence() && in_array($word->trimmed(), $noPeriodAfter, true)) {
                $word->trim();
                // lower case to be sure the next
                $lowerCaseNext = true;
            }
        }

        // ------------------------------------ build the result
        ;

        for ($i = 0; $i < count($words); $i++) {
            /** @var Word $word */
            $word = $words[$i];
            /** @var ?Word $next */
            $next = (isset($words[$i + 1])) ? $words[$i + 1] : null;

            $result .= $word->word;
            if ($word->isLastLetterEndingSentence()) {
                $result .= PHP_EOL;
            } elseif (
                !$word->isLastLetterComma() &&
                $next !== null &&
                in_array($next->trimmed(), $commaWords, true) &&
                !($word->wClass == $next->wClass && $word->wClass == "sidesana") // että mutta, että jos, etc.
            ) {
                $result .= ", ";
            } else {
                $result .= " ";
            }
        }

        return self::cleanUpAans($result);
    }
  
    private static function cleanUpAans(string $result): string
    {
        return str_replace(
            ["äää", "aaa", "nice", " pl ", " pl.", "whats app", "Whatsapp"],
            ["äkää", "akaa", "nais", " PL ", " PL.", "WhatsApp", "WhatsApp"],
            $result
        );
    }
}
