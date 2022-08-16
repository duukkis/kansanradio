# kansanradio

Python build
```
pip3 install yle-dl
pip3 install libvoikko
```

Java build
```
cd java
mvn clean package
```

Php build
```
composer install
```

Running the code
```
cp .env_example .env
bash run.sh
```

Todo:
- vihreä liitto >> Vihreä
- jakomielitauti >> 3 osainen yhdyssana
- ei välimerkkiä joidenkin Isokirjaimisten sanoja ennen
- maksakortilla, maksakortti nimisana
- Kotkan pesä >> yhdyssana, same case as yhdyssana
- alppiharju sta

Won't fix
- tupakka alkoholi feminismi => perusmuoto, perusmuoto, perusmuoto >> add commas (too many wrong positives)
