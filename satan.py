import os
import time
import wave
import string
import json

try:
    import azure.cognitiveservices.speech as speechsdk
except ImportError:
    import sys
    sys.exit(1)

# https://github.com/Azure-Samples/cognitive-services-speech-sdk/blob/0b2d96951e5620c53d60dab6b53647d45d149bf8/samples/python/console/speech_sample.py
def speech_recognize_continuous_from_file(speech_key, service_region, weatherfilename):
    """performs continuous speech recognition with input from an audio file"""
    # <SpeechContinuousRecognitionWithFile>
    speech_config = speechsdk.SpeechConfig(subscription=speech_key, region=service_region)
    audio_config = speechsdk.audio.AudioConfig(filename=weatherfilename)

    language = 'fi-FI'
    speech_recognizer = speechsdk.SpeechRecognizer(speech_config=speech_config, audio_config=audio_config, language=language)

    done = False

    def stop_cb(evt):
        nonlocal done
        done = True

    speech_recognizer.recognized.connect(lambda evt: print(evt.result.text))
    speech_recognizer.session_stopped.connect(stop_cb)
    speech_recognizer.canceled.connect(stop_cb)

    # Start continuous speech recognition
    speech_recognizer.start_continuous_recognition()
    while not done:
        time.sleep(.5)

    speech_recognizer.stop_continuous_recognition()
    # </SpeechContinuousRecognitionWithFile>

speech_recognize_continuous_from_file(os.getenv('AZURE_KEY'), os.getenv('AZURE_ZONE'), "./data/latest.wav")