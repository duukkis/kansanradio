//
// Copyright (c) Microsoft. All rights reserved.
// Licensed under the MIT license. See LICENSE.md file in the project root for full license information.
//

package com.microsoft.cognitiveservices.speech.samples.console;

import java.io.IOException;
import java.io.InputStream;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;
import java.util.Scanner;
import java.util.concurrent.ExecutionException;
import java.util.concurrent.Semaphore;
import java.util.concurrent.TimeUnit;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.StringReader;
import jakarta.json.Json;
import jakarta.json.JsonArray;
import jakarta.json.JsonObject;
import jakarta.json.JsonReader;
import java.net.URI;

// <toplevel>
import com.microsoft.cognitiveservices.speech.*;
import com.microsoft.cognitiveservices.speech.audio.*;
// </toplevel>

@SuppressWarnings("resource") // scanner
public class SpeechRecognitionSamples {
  
    // The Source to stop recognition.
    private static Semaphore stopRecognitionSemaphore;
  
    // Speech recognition with audio stream
    public static void recognitionWithAudioStreamAsync(String key, String zone, String filePath) throws InterruptedException, ExecutionException, FileNotFoundException
    {
        stopRecognitionSemaphore = new Semaphore(0);

        // Creates an instance of a speech config with specified
        // subscription key and service region. Replace with your own subscription key
        // and service region (e.g., "westus").
        SpeechConfig config = SpeechConfig.fromSubscription(key, zone);
        config.setSpeechRecognitionLanguage("fi-FI");
        config.setProfanity(ProfanityOption.Raw);

        // Create an object that parses the WAV file and implements PullAudioInputStreamCallback to read audio data from the file.
        // Replace with your own audio file name.
        WavStream wavStream = new WavStream(new FileInputStream(filePath));

        // Create a pull audio input stream from the WAV file
        PullAudioInputStream inputStream = PullAudioInputStream.createPullStream(wavStream, wavStream.getFormat());

        // Create a configuration object for the recognizer, to read from the pull audio input stream
        AudioConfig audioInput = AudioConfig.fromStreamInput(inputStream);

        // Creates a speech recognizer using audio stream input.
        SpeechRecognizer recognizer = new SpeechRecognizer(config, audioInput);
        {

            recognizer.recognized.addEventListener((s, e) -> {
                if (e.getResult().getReason() == ResultReason.RecognizedSpeech) {
                    System.out.println(e.getResult().getText());
                }
                else if (e.getResult().getReason() == ResultReason.NoMatch) {
                    // System.out.println("NOMATCH: Speech could not be recognized.");
                }
            });

            recognizer.canceled.addEventListener((s, e) -> {
                stopRecognitionSemaphore.release();
            });

            recognizer.sessionStopped.addEventListener((s, e) -> {
                stopRecognitionSemaphore.release();
            });

            // Starts continuous recognition. Uses stopContinuousRecognitionAsync() to stop recognition.
            recognizer.startContinuousRecognitionAsync().get();

            // Waits for completion.
            stopRecognitionSemaphore.acquire();

            // Stops recognition.
            recognizer.stopContinuousRecognitionAsync().get();
        }

        config.close();
        audioInput.close();
        recognizer.close();
    }
}
