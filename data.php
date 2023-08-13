<?php
$url = "https://d.apkpure.com/b/XAPK/com.PigeonGames.Phigros?versionCode=";
$info_url = "https://zh.moegirl.org.cn/Phigros/%E8%B0%B1%E9%9D%A2%E4%BF%A1%E6%81%AF";
$accept = explode(",", $argv[1]);
for ($i = 0; $i < count($accept); $i++) $accept[$i] = trim($accept[$i]);
print_r($accept);
$additional_info = [
    "Aleph-0" => [
        "chart" => [
            [
                "difficulty" => "Legacy",
                "notes" => "684"
            ]
        ]
    ], 
    "Break Through The Barrier" => [
        "chart" => [
            [
                "difficulty" => "Legacy",
                "notes" => "1293"
            ]
        ]
    ],
    "ENERGY SYNERGY MATRIX" => [
        "chart" => [
            [
                "difficulty" => "Legacy",
                "notes" => "615"
            ]
        ]
    ],
    "今年も「雪降り、メリクリ」目指して頑張ります！！" => [
        "title" => "今年も「雪降り、メリクリ」目指して頑張ります！！",
        "minbpm" => "156",
        "maxbpm" => "234",
        "thumbnail" => "https://img.moegirl.org.cn/common/3/33/%E4%BB%8A%E5%B9%B4%E3%82%82%E3%80%8C%E9%9B%AA%E9%99%8D%E3%82%8A%E3%80%81%E3%83%A1%E3%83%AA%E3%82%AF%E3%83%AA%E3%80%8D%E7%9B%AE%E6%8C%87%E3%81%97%E3%81%A6%E9%A0%91%E5%BC%B5%E3%82%8A%E3%81%BE%E3%81%99%EF%BC%81%EF%BC%81.jpg",
        "chart" => [
            [
                "difficulty" => "SP",
                "notes" => "2500"
            ]
        ]
    ]
];

function geturl($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function random_string($length) {
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"; $res = "";
    for ($i = 0; $i < $length; $i++) $res .= $chars[random_int(0, 51)];
    return $res;
}

$info = geturl($info_url); $chart_info = [];
preg_match_all('/<table[^>]*class="wikitable"[^>]*>(.*?)<\/table>/is', $info, $match);
$match = $match[1];
for ($i = 0; $i < count($match); $i++) {
    preg_match_all('/<th[^>]*colspan="5"[^>]*>(.*?)<\/th>/is', $match[$i], $th);
    $title = html_entity_decode(trim(strip_tags($th[1][0])));
    preg_match_all('/<td[^>]*colspan="2"[^>]*>(.*?)<\/td>/is', $match[$i], $td);
    $minbpm = $maxbpm = trim(strip_tags($td[1][0]));
    $time = trim(strip_tags($td[1][1]));
    if (strpos($minbpm, "~") !== false) {
        $minbpm = explode("~", $minbpm)[0];
        $maxbpm = explode("~", $maxbpm)[1];
    }
    preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $match[$i], $tr);
    $tr = $tr[1];
    preg_match_all('/<img[^>]*src=\"(.*?)\"[^>]*>/is', $match[$i], $img);
    if (count($img[1]) >= 1) $img = $img[1][0];
    else $img = "";
    $img = str_replace("/thumb", "", $img);
    $img = substr($img, 0, strrpos($img, "/"));
    $chart = [];
    for ($j = 6; $j < count($tr); $j++) {
        preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $tr[$j], $td);
        $td = $td[1]; for ($k = 0; $k < count($td); $k++) $td[$k] = trim(strip_tags($td[$k]));
        $chart[count($chart)] = [
            "difficulty" => $td[0],
            "level" => $td[1],
            "ranking" => $td[2],
            "notes" => $td[3],
            "charter" => html_entity_decode($td[4]),
        ];
    }
    $chart_info[$title] = [
        "title" => $title,
        "minbpm" => $minbpm,
        "maxbpm" => $maxbpm,
        "time" => $time,
        "thumbnail" => $img,
        "chart" => $chart
    ];
}
foreach ($additional_info as $k => $v) {
    if ($chart_info[$k] == null) $chart_info[$k] = $v;
    else for ($i = 0; $i < count($v["chart"]); $i++) 
        $chart_info[$k]["chart"][count($chart_info[$k]["chart"])] = $v["chart"][$i];
}
foreach ($chart_info as $k => $v) {
    $chart_info[$k]["title"] = str_replace("/", "_", str_replace("\\", "_", $chart_info[$k]["title"]));
    $chart_info[$k]["title"] = str_replace("ρ", "_", $chart_info[$k]["title"]);
    $chart_info[$k]["title"] = str_replace("&", "_", $chart_info[$k]["title"]);
    $chart_info[$k]["title"] = str_replace("<", "_", $chart_info[$k]["title"]);
    $chart_info[$k]["title"] = str_replace("|", "_", $chart_info[$k]["title"]);
    $chart_info[$k]["title"] = str_replace(":", "_", $chart_info[$k]["title"]);
    if ($chart_info[$k]["title"][strlen($chart_info[$k]["title"]) - 1] == ".") 
        $chart_info[$k]["title"] = substr($chart_info[$k]["title"], 0, strlen($chart_info[$k]["title"]) - 1);
}
echo "Identify Chart File...\n";
$assetPath = "./assets/TextAsset";
$temp = scandir($assetPath);
$secondRound = [];
@mkdir("chart");
foreach ($temp as $n) {
    if (is_dir($n)) continue;
    $path = $assetPath . "/" . $n;
    $fp = fopen($path, "r");
    $json = fread($fp, filesize($path));
    fclose($fp);
    $arr = json_decode($json, true);
    if ($arr == null) continue;
    $arr = $arr["judgeLineList"];
    $minBpm = 1e18; $maxBpm = -1e18; $notes = 0;
    for ($i = 0; $i < count($arr); $i++) {
        $minBpm = min($minBpm, $arr[$i]["bpm"]);
        $maxBpm = max($maxBpm, $arr[$i]["bpm"]);
        $notes += count($arr[$i]["notesAbove"]) + count($arr[$i]["notesBelow"]);
    }
    $res = [];
    foreach ($chart_info as $k => $v) {
        $allowed = false;
        if ($minBpm >= $v["minbpm"] && $maxBpm <= $v["maxbpm"]) $allowed = true;
        if (intval($minBpm) % intval($v["minbpm"]) == 0 || intval($maxBpm) % intval($v["maxbpm"]) == 0) $allowed = true;
        if (intval($v["minbpm"]) % intval($minBpm) == 0 || intval($v["maxbpm"]) % intval($maxBpm) == 0) $allowed = true;
        if (!$allowed) continue;
        for ($j = 0; $j < count($v["chart"]); $j++) {
            if (strpos($n, $v["chart"][$j]["difficulty"]) === false) continue;
            if ($v["chart"][$j]["notes"] == $notes) {
                $res[count($res)] = [
                    "id" => $k,
                    "chart" => $j
                ];
            }
        }
    }
    if (count($res) == 0) {
        $secondRound[count($secondRound)] = $n;
        continue;
    }
    if (count($res) != 1) {
        echo $n . " => Multiple Result!(";
        for ($i = 0; $i < count($res); $i++) {
            echo $chart_info[$res[$i]["id"]]["title"] . " " . $chart_info[$res[$i]["id"]]["chart"][$res[$i]["chart"]]["difficulty"];
            if ($i != count($res) - 1) echo " & ";
            @mkdir("./chart/" . $chart_info[$res[$i]["id"]]["title"]);
            $fp = fopen("./chart/" . $chart_info[$res[$i]["id"]]["title"] . "/Chart_" . $chart_info[$res[$i]["id"]]["chart"][$res[$i]["chart"]]["difficulty"] . "_" . random_string(8) . ".json", "w");
            fwrite($fp, $json);
            fclose($fp);
        }
        echo ")($notes)\n";
        continue;
    }
    $chart_info[$res[0]["id"]]["chart"][$res[0]["chart"]]["used"] = $n;
    echo $n . " => " . $chart_info[$res[0]["id"]]["title"] . " " . $chart_info[$res[0]["id"]]["chart"][$res[0]["chart"]]["difficulty"] . "\n";
    @mkdir("./chart/" . $chart_info[$res[0]["id"]]["title"]);
    $fp = fopen("./chart/" . $chart_info[$res[0]["id"]]["title"] . "/Chart_" . $chart_info[$res[0]["id"]]["chart"][$res[0]["chart"]]["difficulty"] . ".json", "w");
    fwrite($fp, $json);
    fclose($fp);
}
for ($x = 0; $x < count($secondRound); $x++) {
    $n = $secondRound[$x];
    $path = $assetPath . "/" . $n;
    $fp = fopen($path, "r");
    $json = fread($fp, filesize($path));
    fclose($fp);
    $arr = json_decode($json, true);
    if ($arr == null) continue;
    $arr = $arr["judgeLineList"];
    $minBpm = 1e18; $maxBpm = -1e18; $notes = 0;
    for ($i = 0; $i < count($arr); $i++) {
        $minBpm = min($minBpm, $arr[$i]["bpm"]);
        $maxBpm = max($maxBpm, $arr[$i]["bpm"]);
        $notes += count($arr[$i]["notesAbove"]) + count($arr[$i]["notesBelow"]);
    }
    $res = [];
    foreach ($chart_info as $k => $v) {
        for ($j = 0; $j < count($v["chart"]); $j++) {
            if (strpos($n, $v["chart"][$j]["difficulty"]) === false) continue;
            if ($v["chart"][$j]["notes"] == $notes) {
                if (array_key_exists("used", $v["chart"][$j])) continue;
                $res[count($res)] = [
                    "id" => $k,
                    "chart" => $j
                ];
            }
        }
    }
    if (count($res) == 0) {
        echo "$n => No Result($notes)\n";
        @mkdir("./chart/Unknown");
        $fp = fopen("./chart/Unknown/$n", "w");
        fwrite($fp, $json);
        fclose($fp);
        continue;
    }
    if (count($res) != 1) {
        echo $n . " => Multiple Result!(";
        for ($i = 0; $i < count($res); $i++) {
            echo $chart_info[$res[$i]["id"]]["title"] . " " . $chart_info[$res[$i]["id"]]["chart"][$res[$i]["chart"]]["difficulty"];
            if ($i != count($res) - 1) echo " & ";
            @mkdir("./chart/" . $chart_info[$res[$i]["id"]]["title"]);
            $fp = fopen("./chart/" . $chart_info[$res[$i]["id"]]["title"] . "/Chart_" . $chart_info[$res[$i]["id"]]["chart"][$res[$i]["chart"]]["difficulty"] . "_" . random_string(8) . ".json", "w");
            fwrite($fp, $json);
            fclose($fp);
        }
        echo ")($notes)\n";
        continue;
    }
    echo $n . " => " . $chart_info[$res[0]["id"]]["title"] . " " . $chart_info[$res[0]["id"]]["chart"][$res[0]["chart"]]["difficulty"] . "\n";
    @mkdir("./chart/" . $chart_info[$res[0]["id"]]["title"]);
    $fp = fopen("./chart/" . $chart_info[$res[0]["id"]]["title"] . "/Chart_" . $chart_info[$res[0]["id"]]["chart"][$res[0]["chart"]]["difficulty"] . ".json", "w");
    fwrite($fp, $json);
    fclose($fp);
}
@mkdir("result");
foreach ($chart_info as $k => $v) {
    $ok = false;
    for ($i = 0; $i < count($accept); $i++) {
        if ($accept[$i] == $k || $accept[$i] == "*") {
            $ok = true;
            break;
        }
    }
    if (!$ok) continue;
    $chart_info[$k]["title"] = str_replace("/", "_", str_replace("\\", "_", $chart_info[$k]["title"]));
    echo "curl \"" . $v["thumbnail"] . "\" -o \"./chart/" . $chart_info[$k]["title"] ."/thumbnail.png\"\n";
    system("curl \"" . $v["thumbnail"] . "\" -o \"./chart/" . $chart_info[$k]["title"] ."/thumbnail.png\"");
    $cmd = "cd \"./chart/" . $chart_info[$k]["title"] ."\" && zip \"../../result/" . $chart_info[$k]["title"] . ".zip\" *";
    echo $cmd . "\n"; system($cmd);
}
?>
