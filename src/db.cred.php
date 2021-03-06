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

require 'vendor/autoload.php';

DB::$user = getenv('MYSQL_USER');
DB::$password = getenv('MYSQL_PASSWORD');
DB::$dbName = getenv('MYSQL_DATABASE');
DB::$host = getenv('DB_HOST');
$slack_webhook_url = getenv("SLACK_WEBHOOK");
$slack_token = getenv("SLACK_TOKEN");
