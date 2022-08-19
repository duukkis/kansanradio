<?php
namespace Kansanradio;

class Word
{
    const AZURE = [
      "boot" => "puut",
      "boozt" => "suusta",
      "tojota" => "Toyota",
    ];
    const UPPERCLASSES = ["etunimi", "sukunimi", "paikannimi", "nimi"];
    // what voikko thinks is nimi but is not
    const WORDNOTUPPER = [
      "auring", "puolia", "puolin", 
      "aurinko", "nurmelle", "maata", "maassa", "auton", "äänistä",
      "asukas", "takasin", "monien", "yleensä", "peruna", "pian", "jonkin", "taimia", 
      "milloin", "kivisiä", "teoilla",
    ];
    // .... keep ^ for a while, start using this
    const BASENOTUPPER = [
    "Aamu", "Autto", "Asukas", "Aino", "Alanen", 
    "Elo", "Kuha",
    "Ferrari",
    "Hal", "Helli", "Helle", 
    "Ilma", "Ilta", "Ilmi", 
    "Janna", // todo >> jännä
    "Kallinen", "Koski", "Kaste", "Kai", "Kivinen", "Kallio", "Koivu", 
    "Laina", "Lahja", 
    "Maa", "Mona", "Meri", "Marja", "Murto", "Mersu",
    "Osta", "Oman",
    "Pello", "Pohja", "Päivi", "Päivä", "Pelli", 
    "Ranta", "Riski", "Rinna", "Ruusu", 
    "Säde", "Sikiö",
    "Tula",
    "Ukko", "Virta",
    "Valta", "Varjonen", 

    ];
    // what voikko thinks is not nimi but is (replace this with list)
    const DEFUPPERS = [
      "kansanradio", "ruotsi", "alppiharju",
      "turku", "skanska", "tikkakoski", "alppiharju", 
      "yit", "suomia", "haapakangas", "haikala", 
      "venäjä", "ukraina", "nato",
      "ranska", "jukka", "aamulehti", "nordea", "nordean", "nordeaan", "nordealla",
      "hakaniemi", "paasikivi", "selander", "viaplay", "wille", "merkel",
      "iltalehti",
    ];
  
    public string $word = "";
    public string $lastLetter = "";
    public ?string $baseform = null;
    public ?string $wClass = null;
    public bool $isCompound = false;

    public function __construct(
        string $word,
        ?string $baseform = null,
        ?string $wClass = null,
        bool $isCompound = false
    ) {
        if (isset(self::AZURE[$word])) {
          $word = self::AZURE[$word];
        }
        $this->word = trim($word);
        $this->setLastLetter();
        $this->baseform = $baseform;
        $this->wClass = $wClass;
        $this->isCompound = $isCompound;
    }

    private function setLastLetter(): void
    {
        $this->lastLetter = mb_substr($this->word, -1, 1);
    }

    public function trimmed(): string
    {
        return trim($this->word, ".,! ");
    }

    public function trim(): Word
    {
        $this->word = trim($this->word, ".,! ");
        $this->setLastLetter();
        return $this;
    }

    public function isLastLetterComma(): bool
    {
        return ($this->lastLetter === ",");
    }
  
    public function isLastLetterEndingSentence(): bool
    {
        return in_array($this->lastLetter, [".", "?", "!"], true);
    }

    public function isCapital(): bool
    {
        return
        (
        in_array($this->wClass, self::UPPERCLASSES, true) && 
        !in_array($this->trimmed(), self::WORDNOTUPPER, true) && 
        !in_array($this->baseform, self::BASENOTUPPER, true)
        )
        || in_array($this->trimmed(), self::DEFUPPERS, true)
        || in_array($this->baseform, self::DEFUPPERS, true);
    }
  
    public function isFirstLetterCapital(): string
    {
        return ($this->word === $this->mbUcFirst());
    }

    private function mbUcfirst(bool $lower_str_end = true): string
    {
        $str = $this->word;
        $first_letter = mb_strtoupper(mb_substr($str, 0, 1, "UTF-8"), "UTF-8");
        if ($lower_str_end) {
            $str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, "UTF-8"), "UTF-8"), "UTF-8");
        } else {
            $str_end = mb_substr($str, 1, mb_strlen($str, "UTF-8"), "UTF-8");
        }
        return $first_letter . $str_end;
    }

    public function setUcFirst(): Word
    {
        $this->word = $this->mbUcfirst();
        return $this;
    }

    public function setStrLower(): Word
    {
        $this->word = mb_strtolower($this->word, "UTF-8");
        return $this;
    }

    public function lower(): string
    {
        return mb_strtolower($this->trimmed(), "UTF-8");
    }

    public static function append(Word $first, string $append, Word $second, ?string $wClass = null): Word
    {
        $newBaseForm = ($first->baseform !== null && $second->baseform !== null) ? $first->baseform . $append . $second->baseform : null;
        $first->trim();
        return new Word(
            $first->word . $append . $second->word,
            $newBaseForm,
            $wClass,
            true
        );
    }
}
