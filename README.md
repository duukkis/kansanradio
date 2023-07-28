# Kansanradio

Get latest episode of Kansanradio and parse it to readable format

## how code works

Code uses [yle-dl](https://github.com/aajanki/yle-dl) to download the latest episode from [Yle-Areena](https://areena.yle.fi/podcastit/1-2143312). [FFmpeg](https://www.ffmpeg.org/) removes the 16 second intro and then formats the episode into Azure readable format. Then the episode is fed into Azure [Speech to text](https://azure.microsoft.com/en-us/products/ai-services/speech-to-text) with java and the result is outputted into data folder. The text is parsed using [Voikko](https://github.com/voikko) line by line into baseform. The lastly the text is cleaned up with self made php script to capitalize names, add commas, clean numbers and form conjugate words from bad Azure raw data.

The new episode is added every week on Sunday, so there is a cron that does this with run.sh. The data is added into tests. raw has the raw data from azure, input has the voikko parsed text and the output has the final result.

The Azure costs nothing as there is 5 hours of free speech-to-text / month and episodes are 30 min.

The technology is a bit of a mess. Java was needed as python speech-to-text did not install to [Raspberry Pi](https://www.raspberrypi.org/) where this runs. satan.py basically does the same thing as java.

## why

See [Kansanradio Twitter bot](https://twitter.com/Kansanradio_) now dead :( and [Kansanradio Mastodon bot](https://mastobotti.eu/@kansanradio) alive.

## making this run

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

¯\\\_(ツ)\_/¯

Won't fix:

- tupakka alkoholi feminismi => perusmuoto, perusmuoto, perusmuoto >> add commas (too many wrong positives)
