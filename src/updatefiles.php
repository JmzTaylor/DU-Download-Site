<?php

/*
 * Copyright (C) 2020 The Dirty Unicorns Project
 * Copyright (C) 2020 James Taylor <jmz.taylor16@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once __DIR__.'/vendor/autoload.php';
require_once 'functions.php';

$token = $argv[1];
$user_id = $argv[2];
$user_name = $argv[3];

$file_lock = __DIR__ . "/.file_lock";

$client = new Maknz\Slack\Client($argv[4]);

// Ensure the slack token matches the GET request and check
// if lock file exists before beginning
if ($token == $slack_token && !file_exists($file_lock)) {
    // Create lock file to prevent conflicts
    $handle = fopen($file_lock, "w");
    fclose($handle);
    // Process database for deleted files
    cleanFiles();
    // Process file system for changes
    $text = getDevices();

    // Delete lock file
    unlink($file_lock);

    // If $text has any value then new files were found
    if (strlen($text) > 0) {
        $message_text = $text;
    } else {
        $message_text = "No new files were found";
    }

    // Send response to user that requested the update
    $client->send("<@" . $user_id . "|" . $user_name . "> " . $message_text);
} else {
    $client->send("<@" . $user_id . "|" . $user_name . "> " . "Not authorized or update already in progress");
}