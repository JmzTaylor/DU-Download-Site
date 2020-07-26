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
require 'db.cred.php';

/**
 * Returns device names from database
 *
 * @return array
 */
function getDbDevices() {
    return DB::query("SELECT * FROM devices");
}

/**
 * Returns device name by id from database
 *
 * @param int $id
 * @return string
 */
function getDeviceName($id) {
    return ucfirst(DB::query("SELECT deviceName FROM devices WHERE id=%d", $id)['0']['deviceName']);
}

/**
 * Returns files for device
 *
 * @param int $id
 * @return array
 */
function getFilesForDevice($id) {
    return DB::query("SELECT * FROM files WHERE deviceName=%d ORDER BY baseName ASC", $id);
}

/**
 * Returns files for device for API
 *
 * @param int $id
 * @return array
 */
function getFilesForDeviceApi($id) {
    return DB::query("SELECT files.id, files.fileName, files.MD5, files.downloadCount, files.fileSize, files.baseName, devices.deviceName AS deviceName, releaseTypes.types AS fileType FROM files INNER JOIN devices ON files.deviceName = devices.id INNER JOIN releaseTypes ON files.fileType = releaseTypes.id WHERE files.deviceName=%d ORDER BY fileType ASC, files.baseName ASC", $id);
}

/**
 * Returns all devices alphabetically
 *
 * @return array
 */
function getAllDevices() {
    $text = DB::query("SELECT * FROM devices ORDER BY deviceName ASC");
    for ($i=0; $i < count($text); $i++) {
        $text[$i]['deviceName'] = ucfirst($text[$i]['deviceName']);
    }
    return $text;
}

/**
 * Returns id for specified device
 *
 * @param string $device
 * @return array
 */
function getDeviceId($device) {
    return DB::queryFirstRow("SELECT id FROM devices WHERE deviceName=%s", $device)['id'];
}

/**
 * Returns id of release type
 *
 * @param int $fileType
 * @return array
 */
function getFileTypeId($fileType) {
    return DB::queryFirstRow("SELECT id FROM releaseTypes WHERE types=%s", $fileType)['id'];
}

/**
 * Returns all release types
 *
 * @return array
 */
function getFileTypes() {
    return DB::query("SELECT * FROM releaseTypes");
}

/**
 * Adds new device to database
 *
 * @param string $deviceName
 */
function addNewDevice($deviceName) {
    DB::query("INSERT INTO devices SET deviceName=%s", $deviceName);
}

/**
 * Adds new release type
 *
 * @param string $releaseType
 */
function addFileType($releaseType) {
    DB::query("INSERT INTO releaseTypes SET types=%s", $releaseType);
}

/**
 * Deletes file from database
 *
 * @param string $file
 */
function deleteFile($file) {
    DB::query("DELETE FROM files WHERE fileName=%s", $file);
}

/**
 * Checks if file exists in database
 *
 * Also verifies the file MD5 matches current filesystem
 *
 * @param string $file
 * @return boolean
 */
function doesFileExist($file) {
    $results = DB::query("SELECT fileName, MD5 FROM files");
    for ($i=0; $i < count($results); $i++) {
        if ($results[$i]['fileName'] == $file) {
            if ($results[$i]['MD5'] != md5_file($file)) {
                deleteFile($file);
                return false;
            } else {
                return true;
            }
        }
    }
    return false;
}

/**
 * Checks for files removed from filesystem
 *
 * Removes entries from datbase also
 *
 */
function cleanFiles() {
    $results = DB::query("SELECT fileName FROM files");
    foreach ($results as $file) {
        if (!file_exists($file['fileName'])) {
            DB::query("DELETE FROM files WHERE fileName=%s", $file['fileName']);
        }
    }

    $devices = DB::query("SELECT * from devices");
    foreach ($devices as $device) {
        if (!file_exists('devices/' . $device['deviceName']) || countFilesForDevice($device['id']) == 0) {
            DB::query("DELETE FROM devices WHERE id=%d", $device['id']);
        }
    }
}

/**
 * Counts files for device
 *
 * @param int $id
 * @return int
 */
function countFilesForDevice($id) {
    return DB::query("SELECT COUNT(*) AS total FROM files WHERE deviceName=%d", $id)[0]['total'];
}

/**
 * Adds file to database
 *
 * @param int $device
 * @param int $fileType
 * @param string $file
 */
function addFile($device, $fileType, $file) {
    $md5 = md5_file($file, false);
    DB::query("INSERT INTO files SET `fileName`=%s, `MD5`=%s, `fileSize`=%s, `deviceName`=%d, `fileType`=%d, `baseName`=%s",
        $file,
        $md5,
        human_filesize(filesize($file)),
        getDeviceId($device),
        getFileTypeId($fileType),
        basename($file)
    );
}

/**
 * Returns if device exists in database
 *
 * @param string $device
 * @return boolean
 */
function doesDeviceExist($device) {
    $devices = getDbDevices();
    for ($i = 0; $i < count($devices); $i++) {
        if ($devices[$i]['deviceName'] == $device) {
            return true;
        }
    }
    return false;
}

/**
 * Returns if release exists in database
 *
 * @param string $releaseType
 * @return boolean
 */
function doesReleaseTypeExist($releaseType) {
    $releaseTypes = getFileTypes();
    $found=false;
    for ($i=0; $i < count($releaseTypes); $i++) {
        if ($releaseType == $releaseTypes[$i]['types']) {
            $found = true;
        }
    }
    return $found;
}

/**
 * Processes new files added to filesystem
 *
 * @return string $text
 */
function getDevices() {
    $text="";
    $exclude = array("cgi-bin");
    $iterator = new DirectoryIterator("devices");
    foreach($iterator as $device) {
        if (!$device->isDot() && $device->isDir() && !in_array($device, $exclude)) {
            if (!doesDeviceExist($device)) {
                $releaseTypes = new DirectoryIterator("devices/" . $device);
                foreach ($releaseTypes as $releaseType) {
                    if (!$releaseType->isDot() && $releaseType->isDir() && !doesReleaseTypeExist($releaseType)) {
                        addFileType($releaseType);
                    }
                }
                addNewDevice($device);
            }
            $text = $text . getDeviceFiles($device);
        }
    }

    return $text;
}

/**
 * Get files for device and process filesystem
 *
 * @param string $device
 * @return string $text
 */
function getDeviceFiles($device) {
    $docRoot = substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']));
    $text = "";

    $dirIterator = new DirectoryIterator("devices/" . $device);

    foreach ($dirIterator as $dir) {
        if (!$dir->isDot() && $dir->isDir() && !doesReleaseTypeExist(str_replace("devices/" . $device . "/", "", $dir->getPathname()))) {
            addFileType(str_replace("devices/" . $device . "/", "", $dir->getPathname()));
        }
    }

    $releaseTypes = getFileTypes();

    foreach($releaseTypes as $releaseType) {
        $path = "devices/" . $device . "/" . $releaseType['types'];
        if (file_exists($path)) {
            $iterator = new DirectoryIterator($path);
            foreach ($iterator as $file) {
                if (!$file->isDot()) {
                    $file = str_replace($docRoot, "", $file->getPathname());
                    if (!doesFileExist($file)) {
                        $text = $text . "Found new file: " . $file . PHP_EOL;
                        addFile($device, $releaseType['types'], $file);
                    }
                }
            }
        }
    }

    return $text;
}

/**
 * Gets release types for device
 *
 * @param int $deviceId
 * @return string $text
 */
function getReleaseTypesForDevice($deviceId) {
    $text = DB::query("SELECT DISTINCT releaseTypes.id,types FROM releaseTypes JOIN files ON files.fileType = releaseTypes.id AND files.deviceName=%d", $deviceId);
    for ($i=0; $i < count($text); $i++) {
        $text[$i]['types'] = strtoupper($text[$i]['types']);
    }
    return $text;
}

/**
 * Increments download count for file
 *
 * @param int $fileId
 */
function updateDownload($fileId) {
    DB::query("UPDATE files SET downloadCount = downloadCount + 1 WHERE id=%d", $fileId);
}

/**
 * Processes file for download
 *
 * @param int $fileId
 */
function getDownload($fileId) {
    $file_url = DB::query("SELECT fileName, baseName FROM files WHERE id=%d", $fileId)[0];
    $filePath = $file_url['fileName'];
    $fileName = $file_url['baseName'];

    if(file_exists($filePath)) {
        $fileSize = filesize($filePath);

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Transfer-Encoding: binary');
        header('Content-Type: application/zip');
        header("Content-Length: ".$fileSize);
        header("Content-Disposition: attachment; filename=".$fileName);

        $chunkSize = 1024 * 1024;
        $handle = fopen($filePath, 'rb');
        while (!feof($handle))
        {
            $buffer = fread($handle, $chunkSize);
            echo $buffer;
            ob_flush();
            flush();
        }
        fclose($handle);
        exit;
    }
    else {
        die('The provided file path is not valid.');
    }
}

/**
 * Converts bytes to human readable filesize
 *
 * @param int $bytes
 * @return string
 */
function human_filesize($bytes) {
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.2f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}