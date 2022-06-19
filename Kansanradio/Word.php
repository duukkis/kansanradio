<?php
namespace Kansanradio;

class Word
{
    const UPPERCLASSES = ["etunimi", "sukunimi", "paikannimi", "nimi"];
    // what voikko thinks is nimi but is not
    const WORDNOTUPPER = [
      "auring", "puolia", "puolin", 
      "aurinko", "nurmelle", "maata", "maassa", "auton", "äänistä",
      "asukas", "takasin", "monien", "yleensä", "peruna", "pian", "jonkin", "taimia", 
      "milloin", "kivisiä",
    ];
    // .... keep ^ for a while, start using this
    const BASENOTUPPER = [
    "Aamu", "Autto", "Asukas", "Aino", "Alanen", 
    "Elo",
    "Ferrari",
    "Hal", "Helli",
    "Ilma", "Ilta", "Ilmi", 
    "Janna", // todo >> jännä
    "Kallinen", "Koski", "Kaste", "Kai", "Kivinen", "Kallio", "Koivu", 
    "Laina", "Lahja", 
    "Maa", "Mona", "Meri", 
    "Osta", "Oman",
    "Pello", "Pohja", "Päivi", "Päivä", 
    "Ranta", "Riski", "Rinna", "Ruusu", 
    "Säde", "Sikiö",
    "Ukko", 
    "Valta",

    ];
    // what voikko thinks is not nimi but is (replace this with list)
    const DEFUPPERS = [
      "kansanradio", "ruotsi", 
      "turku", "skanska", "tikkakoski", "alppiharju", 
      "yit", "suomia",
      "venäjä", "ukraina",
      "ranska",
      "hakaniemi", "paasikivi"
    ];
  
    public string $word = "";
    public ?string $baseform = null;
    public ?string $wClass = null;
  
    public function __construct(
        string $word,
        ?string $baseform,
        ?string $wClass
    ) {
        $this->word = $word;
        $this->baseform = $baseform;
        $this->wClass = $wClass;
    }
  
    public function trimmed(): string
    {
        return trim($this->word, ".,! ");
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

    public function mbUcfirst(bool $lower_str_end = true): string
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

    public function mbStrLower(): string
    {
        return mb_strtolower($this->word, "UTF-8");
    }
}
