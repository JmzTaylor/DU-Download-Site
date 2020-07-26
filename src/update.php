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

// Send response back to slack to prevent timeout error
$response = ["response_type"=>"ephemeral", "text"=>"<@" . $_GET['user_id'] . "|" . $_GET['user_name'] . "> On it..."];
echo json_encode($response);
header("Content-Type: application/json");
fastcgi_finish_request();

// Running this in the background to prevent timeout errors from slack
shell_exec('php -q updatefiles.php ' . $_GET['token'] . ' ' . $_GET['user_id'] . ' ' . $_GET['user_name'] . ' ' . $_GET['response_url'] . ' &');