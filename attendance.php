<?php
require 'vendor/autoload.php';
include 'envconfig.php';

use Rats\Zkteco\Lib\ZKTeco;

/**
 * @param  array<int, array<string, mixed>>  $attendanceData
 */
function postAttendancePayload(string $apiUrl, array $attendanceData): string
{
    $curl = curl_init($apiUrl);

    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($attendanceData),
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    return $response !== false ? $response : '';
}

$ip = getenv('machine_ip');
$port = getenv('machine_port');

$timezone = new DateTimeZone('Asia/Dhaka');
$current_time = new DateTime('now', $timezone);
$current_time_formatted = $current_time->format('Y-m-d H:i:s');

$filePath = getenv('project_dir') . "/log.txt";

if (!$ip || !$port) {
    $fileContent = "$current_time_formatted (error): Machine error - Machine IP or Port is not set in the environment variables.";

    if (file_exists($filePath)) {
        file_put_contents($filePath, "\n" . $fileContent, FILE_APPEND);
    } else {
        file_put_contents($filePath, $fileContent);
    }
    exit;
}

$zk = new ZKTeco($ip, $port);
$attendanceData = '';

try {
    if ($zk->connect()) {
        $attendanceData = $zk->getAttendance();
        $fileContent = "$current_time_formatted (success) : Attendance is being fetched from the machine.\n";

        $attPath = getenv('project_dir') . "/att.txt";
        file_put_contents($attPath, json_encode($attendanceData, JSON_PRETTY_PRINT));

        if (empty($attendanceData)) {
            $fileContent .= "$current_time_formatted (success): No attendance data found.\n";
        } else {
            if (getenv('attendance_dev_api') && getenv('attendance_dev_api') !== '') {
                $response1 = postAttendancePayload(getenv('attendance_dev_api'), $attendanceData);
                $fileContent .= "$current_time_formatted (dev response) : $response1 \n";
            }

            $productionApis = getenv('attendance_production_api');
            if ($productionApis && $productionApis !== '') {
                foreach (array_filter(array_map('trim', explode(',', $productionApis))) as $productionApi) {
                    $response2 = postAttendancePayload($productionApi, $attendanceData);
                    $fileContent .= "$current_time_formatted (zone response) [$productionApi] : $response2 \n";
                }
                // $zk->clearAttendance();
            }
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
