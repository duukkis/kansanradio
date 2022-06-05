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
                $word->word = $word->mbStrLower();
                $lowerCaseNext = false;
            }
            // get next word for possible pilkku and for compound word
            if (isset($p[$i + 1])) {
                $next = self::buildWordFromLine($p[$i + 1]);
            }

            $azure = CompoundWord::azureFixes($word, $next);
            if (null !== $azure) {
                $firstLetterCapital = $word->isFirstLetterCapital();
                $word->word = $azure;
                if ($firstLetterCapital) {
                  $word->word = $word->mbUcfirst();
                }
                $i++;
            } else {
                $isYhdyssana = CompoundWord::isCompound($word, $next, $baseFormArray);

                if (!empty($isYhdyssana)) {
                    if (in_array("UPPER", $isYhdyssana)) {
                        $word->word = $word->mbUcfirst();
                        $next->word = $next->mbStrLower();
                    } else if (in_array("DOUBLE-UPPER", $isYhdyssana)) {
                        $word->word = $word->mbUcfirst();
                        $next->word = $next->mbUcfirst();
                    }

                    if (in_array("DASH", $isYhdyssana)) {
                        $word->word = $word->trimmed() . "-" . $next->word;
                        $i++; // skip next
                    } elseif (in_array("DOT", $isYhdyssana)) {
                        $word->word = $word->trimmed() . ". " . $next->word;
                        $i++; // skip next
                    } elseif (in_array("TRUE", $isYhdyssana)) {
                        $word->word = $word->trimmed() . $next->word;
                        $i++; // skip next
                    } elseif (in_array("SPACE", $isYhdyssana)) {
                        $word->word = $word->trimmed() . " " . $next->word;
                        $i++; // skip next
                    } elseif (in_array("REMOVE", $isYhdyssana)) {
                        // remove first
                        $word = $next;
                        $next = null;
                        $i++; // skip next
                    } elseif (in_array("COLON", $isYhdyssana)) {
                        $word->word = $word->trimmed() . ":" . $next->word;
                        $i++; // skip next
                    }
                }
            }
            // capital
            if ($word->isCapital()) {
                $word->word = $word->mbUcfirst();
            }
            $lastLetter = mb_substr($word->word, -1, 1);
            if (in_array($lastLetter, [".", "?"]) && in_array($word->trimmed(), $noPeriodAfter, true)) {
                $word->word = $word->trimmed();
                $lastLetter = mb_substr($word->word, -1, 1);
                // lower case to be sure the next
                $lowerCaseNext = true;
            }

            $result .= $word->word;

            if (in_array($lastLetter, [".", "?"])) {
                $result .= PHP_EOL;
            } elseif ($lastLetter !== "," && $next !== null && in_array($next->trimmed(), $pilkku, true)) {
                $result .= ", ";
            } else {
                $result .= " ";
            }
        }
        return self::cleanUpAans($result);
    }
  
    private static function cleanUpAans(string $result): string
    {
        return str_replace(["äää", "aaa"], ["äkää", "akaa"], $result);
    }
}
