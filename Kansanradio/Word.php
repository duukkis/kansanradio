<?php
namespace Kansanradio;

class Word
{
  const UPPERCLASSES = ["etunimi", "sukunimi", "paikannimi", "nimi"];
  // what voikko thinks is nimi but is not
  const NOTUPPERS = ["osta", "koskien", "ostaa", "maissa", "auring", 
              "aurinko", "maahan", "nurmelle", "puolin", "maata", "maassa", "auton", "äänistä",
              "asukas", "takasin", "monien", "yleensä", "peruna", "pian", "jonkin", "taimia", 
             ];
  // .... keep ^ for a while, start using this
  const BASENOTUPPER = [
    "Päivi", "Päivä", "Ilma", "Sikiö",
    "Helli", "Valta", "Pello", "Osta", "Hal",
    "Säde", "Aamu", "Ilta", "Kallinen", "Oman", "Riski", "Laina", "Maa", "Ranta", 
  ];
  // what voikko thinks is not nimi but is
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
        !in_array($this->trimmed(), self::NOTUPPERS, true) && 
        !in_array($this->baseform, self::BASENOTUPPER, true)
      )
      || in_array($this->trimmed(), self::DEFUPPERS, true)
      || in_array($this->baseform, self::DEFUPPERS, true)
      ;
  }
}