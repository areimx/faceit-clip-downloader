<?php

    if (!empty($_POST["playlist"]) && !empty($_POST["id"]) 
    && preg_match("/^[a-z0-9\-]{36}$/", $_POST["id"]) && filter_var($_POST["playlist"], FILTER_VALIDATE_URL)) {
        $clipId = $_POST["id"];
        $playlist = $_POST["playlist"];

        if (preg_match("/(https:\/\/(.+?\.)?msvdn\.net(\/[A-Za-z0-9\-\._~:\/\?#\[\]@!$&'\(\)\*\+,;\=]*)?)/", $playlist) && preg_match("/.*playlist\.m3u8$/", $playlist)) {
            $files = glob('clips/*.mp4');
            array_multisort(
            array_map('filemtime', $files),
                SORT_NUMERIC,
                SORT_ASC,
                $files
            );
            unlink($files[0]);

            $clipFile = "clips/$clipId.mp4";
            if (!file_exists($clipFile)) {
                shell_exec("ffmpeg -i $playlist -c copy -bsf:a aac_adtstoasc $clipFile 2>&1");
            }
            if (file_exists($clipFile) && filesize($clipFile) > 3*1024*1024) {
                http_response_code(200);
                die();
            }
        }
    }
    http_response_code(500);
    die();