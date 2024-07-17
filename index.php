<?php

    function getRequest($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output, true);
    }

    if ($_GET["player"] === "fpl") {
        $clips = getRequest("https://api.faceit.com/search/v1/videos?organizer=228c0eef-f33d-4320-a477-e8e4ab8d6584&limit=100&sort=-viewsMonthly");
        if (!empty($clips["payload"]["results"])) {
          $clips = $clips["payload"]["results"];
          $clipCount = count($clips);
        }
    }
    else if (!empty($_GET["player"]) && preg_match("/^[\w\-\_]{3,12}$/", $_GET["player"])) {
        $playerDetails = getRequest('https://api.faceit.com/users/v1/nicknames/'.$_GET["player"]);
        if (!empty($playerDetails["payload"]["id"])) {
            $clips = getRequest("https://api.faceit.com/search/v1/videos?owner=".$_GET["player"]."&limit=48&sort=-created&player=".$playerDetails["payload"]["id"]);
            if (!empty($clips["payload"]["results"])) {
              $clips = $clips["payload"]["results"];
              $clipCount = count($clips);
            }
        }
    }  

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FACEIT Clip Downloader</title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <link rel="shortcut icon" href="https://faceit.com/favicon.ico">
  </head>
  <body>
    <div class="col-lg-8 mx-auto p-2 py-md-2">
      <header class="d-flex align-items-center pb-3 mb-2 border-bottom">
        <div class="d-flex align-items-center text-dark text-decoration-none">
          <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
            <title>faceit_logo_icon</title>
            <path d="M31.933 3.6c0-0.267-0.267-0.333-0.4-0.133-3.867 6-6.067 9.4-8.067 12.467h-23.2c-0.267 0-0.4 0.4-0.133 0.467 9.667 3.667 23.6 9.133 31.333 12.2 0.2 0.067 0.533-0.133 0.533-0.267l-0.067-24.733z"></path>
          </svg>
          <span class="fs-4 ms-2">FACEIT Clip Downloader</span>
        </div>
      </header>
      <form method="GET" action="">
        <div class="row g-3 align-items-center">
            <div class="col-auto">
                <label class="col-form-label">FACEIT Username:</label>
            </div>
            <div class="col-auto">
                <input type="text" name="player" class="form-control" placeholder="Case sensitive." pattern="[A-Za-z0-9_-]{3,12}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-secondary">Get Clips</button>
            </div>
        </div>
      </form>
      <div class="row">
      <?php
        if (isset($clipCount)) {
            for ($i = 0; $i < $clipCount; $i++) {
                echo '<div class="mt-3 col-lg-3 col-md-4 col-sm-6">
                    <div class="card">
                        <img class="card-img-top" src="'.$clips[$i]["url_preview"].'">
                        <div class="card-body">
                            <a target="_blank" href="https://www.faceit.com/en/players/'.$clips[$i]["players"][0]["nickname"].'/videos?id='.$clips[$i]["id"].'"><h6 class="card-title" style="height:2.5rem;">'.(isset($clips[$i]["name"]) ? $clips[$i]["name"] : "Unnamed").'</h6></a>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">'.$clips[$i]["type"].'</li>
                            <li class="list-group-item">'.$clips[$i]["created_at"].'</li>
                            <li class="list-group-item">'.(!empty($clips[$i]["views_count"]) ? $clips[$i]["views_count"] : 0).' Views ('.(!empty($clips[$i]["views_count_7d"]) ? $clips[$i]["views_count_7d"] : 0).' in last 7 days)</li>
                            <li class="list-group-item">'.$clips[$i]["competition"]["name"].'</li>
                        </ul>
                        <div class="card-body">
                            <button data-id="'.$clips[$i]["id"].'" data-playlist="'.$clips[$i]["url"].'" class="card-link btn btn-sm btn-outline-secondary">Download</button>
                        </div>
                    </div>
                </div>';
            }
        }
        else if(!empty($_GET["player"])) {
            echo '<p class="mt-5">No clips found.</p>';
        }
      ?>
      </div>
      <footer class="mt-5 text-muted border-top">
        FACEIT &copy; 2021
      </footer>
    </div>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>
    <script>
      $(function() {
        function getParameterByName(name, url) {
            name = name.replace(/[\[\]]/g, '\\$&');
            var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
                results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, ' '));
        }

        $("button.card-link").on("click", function(e) {
          const button = $(this);
          const clipId = button.data("id");
          const clipPlaylist = button.data("playlist");

          if (button.data("ready")) {
            window.location.replace(`video.php?id=${clipId}`);
          }
          else {
            if (clipPlaylist.startsWith("https://clips.twitch.tv/")) {
              window.open(`https://gabed.net/iloader/twitch-clip-downloader#url=https://clips.twitch.tv/${getParameterByName("clip", clipPlaylist)}`)
            }
            else {
              button.addClass("disabled");
              button.text("Preparing...");

              $.ajax({
                type: "POST",
                url:  "prepare_clip.php",
                data: { "id": clipId, "playlist": clipPlaylist }
              })
              .done(function(data){
                button.removeClass("disabled");
                button.text("Download");
                button.data("ready", "true");
                window.location.replace(`video.php?id=${clipId}`);
              })
              .fail(function() {
                button.text("Failed");
              });
            }
          }
        });
      });
    </script>
  </body>
</html>
