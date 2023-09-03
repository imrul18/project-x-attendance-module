<?php
require 'vendor/autoload.php';
include 'envconfig.php';

use Rats\Zkteco\Lib\ZKTeco;

$ip = '192.168.0.38';
$port = 4370;

$zk = new ZKTeco($ip, $port);
$attendanceData = '';

$timezone = new DateTimeZone('Asia/Dhaka');
$current_time = new DateTime('now', $timezone);
$current_time_formatted = $current_time->format('Y-m-d H:i:s');

$filePath = getenv('project_dir') . "/log.txt";
try {
    if ($zk->connect()) {
        $attendanceData = $zk->getAttendance();
        $fileContent = "$current_time_formatted (success) : Attendance is being fetched from the machine.\n";

        if (getenv('attendance_dev_api')) {
            $apiUrl = getenv('attendance_dev_api');
            $curl = curl_init($apiUrl);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['attendance' => $attendanceData]));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response1 = curl_exec($curl);
            $fileContent .= "$current_time_formatted (dev response) : $response1 \n";
            curl_close($curl);
        }

        if (getenv('attendance_production_api')) {
            $apiUrl = getenv('attendance_production_api');
            $curl = curl_init($apiUrl);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['attendance' => $attendanceData]));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response2 = curl_exec($curl);
            $fileContent .= "$current_time_formatted (zone response) : $response2";
            curl_close($curl);
        }

        $zk->disconnect();

        if (file_exists($filePath)) {
            file_put_contents($filePath, "\n" . $fileContent, FILE_APPEND);
        } else {
            file_put_contents($filePath, $fileContent);
        }
    } else {
        $fileContent = "$current_time_formatted (error): Machine error - Unable to connect Machine at this moment";

        if (file_exists($filePath)) {
            file_put_contents($filePath, "\n" . $fileContent, FILE_APPEND);
        } else {
            file_put_contents($filePath, $fileContent);
        }
    }
} catch (\Throwable $th) {
    $fileContent = "$current_time_formatted (error): System error -  $th";

    if (file_exists($filePath)) {
        file_put_contents($filePath, "\n" . $fileContent, FILE_APPEND);
    } else {
        file_put_contents($filePath, $fileContent);
    }
}
