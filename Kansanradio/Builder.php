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

        // ------------------------------------ build word array
        /** @var array<int,Word> $words */
        $words = [];
        for ($i = 0; $i < count($p); $i++) {
            $words[] = self::buildWordFromLine($p[$i]);
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
                $i++;
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
        for ($i = 0; $i < count($words); $i++) {
            /** @var Word $word */
            $word = $words[$i];
            if (isset($words[$i + 1])) {
                /** @var ?Word $next */
                $next = $words[$i + 1];
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
        return str_replace(
            ["äää", "aaa", "nice", " pl ", " pl.", "whats app", "Whatsapp"],
            ["äkää", "akaa", "nais", " PL ", " PL.", "WhatsApp", "WhatsApp"],
            $result
        );
    }
}
