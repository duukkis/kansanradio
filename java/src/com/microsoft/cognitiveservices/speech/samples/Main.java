package com.microsoft.cognitiveservices.speech.samples.console;

import java.util.Scanner;

//
// Copyright (c) Microsoft. All rights reserved.
// Licensed under the MIT license. See LICENSE.md file in the project root for full license information.
//

@SuppressWarnings("resource") // scanner
public class Main {

    public static void main(String[] args) {
        try {
            String zone = System.getProperty("zone");
            String key = System.getProperty("key");
            String file = System.getProperty("file");
//            System.out.println(something);
            SpeechRecognitionSamples.recognitionWithAudioStreamAsync(key, zone, file);
            System.exit(0);
        } catch (Exception ex) {
            System.out.println("Unexpected " + ex.toString());
            System.exit(1);
        }
    }
}
