<?php
namespace Kansanradio;

class Word
{
    const UPPERCLASSES = ["etunimi", "sukunimi", "paikannimi", "nimi"];
    // what voikko thinks is nimi but is not
    const WORDNOTUPPER = [
    "koskien", "maissa", "auring", 
    "aurinko", "nurmelle", "puolin", "maata", "maassa", "auton", "äänistä",
    "asukas", "takasin", "monien", "yleensä", "peruna", "pian", "jonkin", "taimia", 
    ];
    // .... keep ^ for a while, start using this
    const BASENOTUPPER = [
    "Aamu",
    "Elo",
    "Hal", "Helli",
    "Ilma", "Ilta",
    "Kallinen",
    "Laina",
    "Maa",
    "Osta", "Oman",
    "Pello", "Pohja", "Päivi", "Päivä", 
    "Ranta", "Riski",
    "Säde", "Sikiö",
    "Ukko", 
    "Valta",

    ];
    // what voikko thinks is not nimi but is (replace this with list)
    const DEFUPPERS = ["kansanradio", "ruotsi", "turku", "skanska", "yit", "suomia", "venäjä", "ukraina", ];
  
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
  
    public function mbUcfirst(string $encoding = "UTF-8", bool $lower_str_end = true): string
    {
        $str = $this->word;
        $first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
        if ($lower_str_end) {
            $str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
        } else {
            $str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
        }
        return $first_letter . $str_end;
    }
}