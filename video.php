<?php

    if (!empty($_GET["id"]) && preg_match("/^[a-z0-9\-]{36}$/", $_GET["id"])) {
        $clipId = $_GET["id"];
        $clipFile = "clips/$clipId.mp4";

        if (file_exists($clipFile) && ($clipSize = filesize($clipFile)) > 3*1024*1024) {
            header('Content-Description: File Transfer'); 
            header('Content-Type: application/octet-stream'); 
            header('Content-Disposition: attachment; filename="'.$clipId.'.mp4"'); 
            header('Content-Length: '.$clipSize);
            readfile($clipFile);
            die();
        }
    }

    http_response_code(404);
    die();