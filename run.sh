#!/bin/bash
# get keys
source .env

#metadata into episode.json
yle-dl https://areena.yle.fi/audio/1-2143312 --latestepisode --showmetadata > ./data/episode.json
# Download latest
yle-dl https://areena.yle.fi/audio/1-2143312 --latestepisode -o ./data/latest.mp3
# Convert to wav and remove the 16s intro
ffmpeg -i ./data/latest.mp3 -ss 16 -ar 16000 -ac 1 ./data/latest.wav
# Sound to text from azure
cd java
java -Dkey=$AZURE_KEY -Dzone=$AZURE_ZONE -Dfile=/home/pi/bots/kansanradio/data/latest.wav -jar ./target/SpeechSDKDemo-0.0.1-SNAPSHOT-jar-with-dependencies.jar > /home/pi/bots/kansanradio/data/result.txt
cd ..
# Run voikko for text
python3 voikko.py > ./data/log
# Clean with php
php finish.php > ./data/final.txt

# todo copy by date the middle results

# clean up files
rm ./data/latest.mp3
rm ./data/latest.wav
