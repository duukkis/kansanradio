#!/bin/bash
# get keys
source .env
# Download latest
yle-dl https://areena.yle.fi/audio/1-2143312 --latestepisode -o ./data/latest.mp3
# Convert to wav and remove the 16s intro
ffmpeg -i ./data/latest.mp3 -ss 16 ./data/latest.wav
# Sound to text from azure
python3 satan.py > ./data/result.txt
# Run voikko for text
python3 voikko.py > ./data/log
# Clean with php
php finish.php > ./data/final.txt
