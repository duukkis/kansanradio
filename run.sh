#!/bin/sh
# get keys
. ./.env
# for the fun of it
# wget https://areena.yle.fi/audio/1-2143312 > dump

#metadata into episode.json
/home/pi/.local/bin/yle-dl "https://areena.yle.fi/audio/1-2143312" --latestepisode --showmetadata > ./data/episode.json
# Download latest
/home/pi/.local/bin/yle-dl "https://areena.yle.fi/audio/1-2143312" --latestepisode -o ./data/latest.mp3

if [ ! -f "./data/latest.mp3" ]; then
    exit 0
fi

# Convert to wav and remove the 16s intro
ffmpeg -i ./data/latest.mp3 -ss 16 -ar 16000 -ac 1 ./data/latest.wav
rm ./data/latest.mp3
ffmpeg -i ./data/latest.wav -c copy ./data/fixed.wav
mv ./data/fixed.wav ./data/latest.wav

# Sound to text from azure
cd java
java -Dkey=$AZURE_KEY -Dzone=$AZURE_ZONE -Dfile=/home/pi/bots/kansanradio/data/latest.wav -jar ./target/SpeechSDKDemo-0.0.1-SNAPSHOT-jar-with-dependencies.jar > /home/pi/bots/kansanradio/data/result.txt
cd ..
# Run voikko for text
python3 voikko.py > ./data/log
# Clean with php
php finish.php

# todo copy by date the middle results
cp data/result.txt tests/raw/result`date +"%Y%m%d"`.txt
cp data/log tests/input/log`date +"%Y%m%d"`
git add tests/
git commit -m "`date +"%Y%m%d"`" tests/
git push origin main

# clean up files
rm ./data/latest.wav
