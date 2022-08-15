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
                $isYhdyssana = CompoundWord::isCompound($word, $next, $baseFormArray);

                if (!empty($isYhdyssana)) {
                    if (in_array("UPPER", $isYhdyssana)) {
                        $word->setUcFirst();
                        $next->setStrLower();
                    } else if (in_array("DOUBLE-UPPER", $isYhdyssana)) {
                        $word->setUcFirst();
                        $next->setUcFirst();
                    }

                    if (in_array("DASH", $isYhdyssana)) {
                        $word = Word::append($word->trim(), "-", $next);
                        $i++; // skip next
                    } elseif (in_array("DOT", $isYhdyssana)) {
                        $word = Word::append($word->trim(), ". ", $next);
                        $i++; // skip next
                    } elseif (in_array("TRUE", $isYhdyssana)) {
                        $word = Word::append($word->trim(), "", $next);
                        $i++; // skip next
                    } elseif (in_array("SPACE", $isYhdyssana)) {
                        $word = Word::append($word->trim(), " ", $next);
                        $i++; // skip next
                    } elseif (in_array("REMOVE", $isYhdyssana)) {
                        // remove first
                        $word = $next;
                        $next = null;
                        $i++; // skip next
                    } elseif (in_array("COLON", $isYhdyssana)) {
                        $word = Word::append($word->trim(), ":", $next);
                        $i++; // skip next
                    }
                }
            }
            // capital
            if ($word->isCapital()) {
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
        return str_replace(["äää", "aaa", "nice", " pl ", " pl."], ["äkää", "akaa", "nais", " PL ", " PL."], $result);
    }
}
