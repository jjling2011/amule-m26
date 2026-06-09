<?php

# this file must ends with "?" and ">"
# last element of array can not has ","
# $a = array(); // crash!
# var_dump($var); // crash!
# echo("hello %s", "world"); // crash!
# include "utils.php"; // no response

handle_get_req($HTTP_GET_VARS);

function handle_get_req($get)
{
    $true = 1 == 1;
    $false = 1 == 0;

    $cmd = $get["cmd"];
    if ($cmd == "Test") {
        echo "Hello from test!";
    } elseif ($cmd == "Echo") {
        $text = $get["msg"];
        response($true, $text, "");
    } elseif ($cmd == "GetServerAddressHash") {
        $hash = calc_server_addr_hash();
        response($true, "", $hash);
    } elseif ($cmd == "GetSearchHash") {
        $hash = calc_search_result_hash();
        response($true, "", $hash);
    } elseif ($cmd == "GetTaskHash") {
        $hash = calc_task_hash();
        $data = array("hash" => $hash);
        response($true, "", $data);
    } elseif ($cmd == "GetSearchResult") {
        $result = amule_load_vars("searchresult");
        response($true, "", $result);
    } elseif ($cmd == "DoTaskCmd") {
        $msg = doTaskCommand($get);
        response($true, $msg, "");
    } elseif ($cmd == "DoSearch") {
        $kw = $get["keyword"];
        $stype = $get["stype"];
        doSearch($kw, $stype);
    } elseif ($cmd == "GetStats") {
        $stats = amule_get_stats();
        response($true, "", $stats);
    } elseif ($cmd == "GetPrefs") {
        $opts = amule_get_options();
        response($true, "", $opts);
    } elseif ($cmd == "ApplyPrefs") {
        $opts = doApplyPrefs($get);
        response($true, "done", "");
    } elseif ($cmd == "RemoveServer") {
        $ip = $get["ip"];
        $port = $get["port"];
        amule_do_server_cmd($ip, $port, "remove");
        response($true, "done", "");
    } elseif ($cmd == "ConnectServer") {
        $ip = $get["ip"];
        $port = $get["port"];
        amule_do_server_cmd($ip, $port, "connect");
        response($true, "done", "");
    } elseif ($cmd == "AddServer") {
        $msg = doAddEd2kServer($get);
        response($true, $msg, "");
    } elseif ($cmd == "DisconnectServer") {
        amule_server_disconnect();
        response($true, "done", "");
    } elseif ($cmd == "StartKAD") {
        amule_kad_start();
        response($true, "start KAD done", "");
    } elseif ($cmd == "KADConnectIPPort") {
        $ipv4 = $get["ipv4"] + 0;
        $port = $get["port"] + 0;
        amule_kad_connect($ipv4, $port);
        response($true, "KAD connect to IPv4 ".$ipv4.":".$port, "");
    } elseif ($cmd == "KADConnectURL") {
        $nodes_url = $get["url"];
        amule_kad_update_from_url($nodes_url);
        response($true, "KAD connect to URL ".$nodes_url, "");
    } elseif ($cmd == "DisconnectKAD") {
        amule_kad_disconnect();
        response($true, "disconnect KAD done", "");
    } elseif ($cmd == "GetServers") {
        $servs = amule_load_vars("servers");
        response($true, "", $servs);
    } elseif ($cmd == "GetCats") {
        $cats = amule_get_categories();
        response($true, "", $cats);
    } elseif ($cmd == "GetLogs") {
        $logs = get_logs($false);
        response($true, "", $logs);
    } elseif ($cmd == "ClearLogs") {
        $logs = get_logs($true);
        response($true, "done", $logs);
    } elseif ($cmd == "DoDownloadEd2k") {
        $n = doDownloadEd2k($get);
        response($true, $n, "");
    } elseif ($cmd == "DoDownloadFiles") {
        $msg = doDownloadFiles($get);
        response($true, $msg, "");
    } elseif ($cmd == "SetDownloadFilesCat") {
        $msg = setDownloadFilesCat($get);
        response($true, $msg, "");
    } elseif ($cmd == "GetAllTaskHashes") {
        $data = getAllTaskHashes();
        response($true, "", $data);
    } elseif ($cmd == "GetTasks") {
        $data = array(
            "cats" => amule_get_categories(),
            "downloads" => amule_load_vars("downloads")
        );
        response($true, "", $data);
    } else {
        $msg = "unknow cmd: '" . $cmd . "'";
        response($false, $msg, "");
    }
}

function getAllTaskHashes() {
    $r;
    $tasks = amule_load_vars("downloads");
    foreach($tasks as $file) {
        $r[$file->hash] = 1;
    }
    $shared = amule_load_vars("shared");
    foreach($shared as $file) {
        $r[$file->hash] = 1;
    }
    return $r;
}

function doAddEd2kServer($get) {
    $ip = $get["ip"];
    $port = $get["port"];
    $name = $get["name"];
    amule_do_add_server_cmd($ip, $port, $name);
    $msg = "add ".$ip.":".$port." as ".$name;
    return $msg;
}

function doApplyPrefs($get) {
    $webserver_opts = array("use_gzip", "autorefresh_time");
    $servers_opts = array("add_from_server", "add_from_client");
    $file_opts = array(
        "ich_en", "aich_trust",
        "new_files_paused", "new_files_auto_dl_prio", "new_files_auto_ul_prio",
        "extract_metadata", "alloc_full", "check_free_space", "min_free_space",
        "udp_dis", "autoconn_en","reconn_en", "network_ed2k","network_kad"
    );
    $conn_opts = array(
        "max_up_limit", "max_down_limit", 
        "slot_alloc", "tcp_port", "udp_port", "udp_dis",
        "max_file_src", "max_conn_total",
        "autoconn_en","reconn_en", "network_ed2k","network_kad"
    );
    $all_opts;
    foreach ($conn_opts as $i) {
        $curr_value =  $get[$i];
        $all_opts["connection"][$i] = $curr_value;
    }
    foreach ($file_opts as $j) {
        $curr_value = $get[$j];
        $all_opts["files"][$j] = $curr_value;
    }
    foreach ($webserver_opts as $i) {
        $curr_value = $get[$i];
        $all_opts["webserver"][$i] = $curr_value;
    }
     foreach ($servers_opts as $i) {
        $curr_value = $get[$i];
        $all_opts["servers"][$i] = $curr_value;
    }

    // general: nickname is a single top-level string, not nested.
    $nick_value = $get["nick"];
    if ( $nick_value != "" ) {
        $all_opts["nick"] = $nick_value;
    }
    //var_dump($all_opts);
    amule_set_options($all_opts);
    return $all_opts;
}

function doDownloadEd2k($get)
{
    $c = 0;
    $cat = $get["cat"];
    foreach ($get as $k => $link) {
        if (m26_startswith("link", $k)) {
            $c++;
            amule_do_ed2k_download_cmd($link, $cat);
        }
    }
    return $c;
}

function doDownloadFiles($get)
{
    $c = 0;
    $cat = "";
    foreach ($get as $maybe_hash => $tcat) {
        if (m26_is_hash($maybe_hash)) {
            $c++;
            $cat = $tcat;
            amule_do_search_download_cmd($maybe_hash, $tcat);
        }
    }
    $msg = "add " . $c . " download tasks to " . $cat . ".";
    return $msg;
}

function setDownloadFilesCat($get)
{
    $c = 0;
    $cat = "";
    foreach ($get as $maybe_hash => $tcat) {
        if (m26_is_hash($maybe_hash)) {
            $c++;
            $cat = $tcat + 0;
            amule_do_download_cmd($maybe_hash, "setcat", $cat);
        }
    }
    $msg = "set " . $c . " download tasks to " . $cat . ".";
    return $msg;
}

function doTaskCommand($get)
{
    $c = 0;
    $taskcmd = "";
    foreach ($get as $maybe_hash => $tkcmd) {
        if (
            $tkcmd == "pause" || $tkcmd == "resume" || $tkcmd == "cancel"
            || $tkcmd == "priodown" || $tkcmd == "prioup"
        ) {
            if (m26_is_hash($maybe_hash)) {
                $c++;
                $taskcmd = $tkcmd;
                amule_do_download_cmd($maybe_hash, $tkcmd);
            }
        }
    }
    $msg = "do " . $taskcmd . " on " . $c . " tasks";
    return $msg;
}

function doSearch($kw, $stype)
{
    # amule_do_search_start_cmd(keyword, ext, filetype, searchtype, avail:"", min_size: "0", max_size: "0")
    $st = 0; # local
    if ($stype == "global") {
        $st = 1;
    } elseif ($stype == "kad") {
        $st = 2;
    }
    amule_do_search_start_cmd($kw, "", "", $st, "", "", "");
    $msg = "search:" . $kw . ", type[" . $st . "]: " . $stype;
    response(1 == 1, $msg, "");
}

function m26_is_hash($str)
{
    $ok = strlen($str) == 32;
    return $ok;
}

function m26_status_to_string($file)
{
    if ($file->status == 7) {
        return "Paused";
    } elseif ($file->src_count_xfer > 0) {
        return "Downloading";
    } else {
        return "Waiting";
    }
}

function m26_prio_to_string($file)
{
    $prionames = array(
        0 => "Low",
        1 => "Normal",
        2 => "High",
        3 => "Very high",
        4 => "Very low",
        5 => "Auto",
        6 => "Release"
    );
    $result = $prionames[$file->prio];
    if ($file->prio_auto == 1) {
        $result = $result . "(auto)";
    }
    return $result;
}

function m26_print_download_file($file)
{
    echo '{"cat": ' . $file->category . ',';
    echo '"hash": "' . $file->hash . '",';
    echo '"name": "' . m26_escape($file->name) . '",';
    echo '"link": "' . m26_escape($file->link) . '",';
    echo '"size": ' . $file->size . ',';
    echo '"size_done": ' . $file->size_done . ',';
    echo '"speed": ' . $file->speed . ',';
    echo '"src_count": ' . $file->src_count . ',';
    echo '"status": "' . m26_status_to_string($file) . '",';
    echo '"prio": "' . m26_prio_to_string($file) . '"}';
}

function m26_print_search_result($obj)
{
    echo '{"hash": "' . $obj->hash . '", "name": "' . m26_escape($obj->name) . '",';
    echo '"size": ' . $obj->size . ', "sources": ' . $obj->sources . "}";
}

function m26_print_server($obj)
{
    // ip is an integet represent ipv4 in reversed order 
    echo '{"ip": ' . $obj->ip . ',';
    echo '"port": ' . $obj->port . ',';
    echo '"name": "' . m26_escape($obj->name) . '",';
    echo '"address": "' . $obj->addr . '",';
    echo '"description": "' . m26_escape($obj->desc) . '",';
    echo '"users": ' . $obj->users . ',';
    echo '"files": ' . $obj->files . '}';
}

function m26_print_object($otype, $obj)
{
    if ($otype == "object(AmuleDownloadFile)") {
        m26_print_download_file($obj);
    } elseif ($otype == "object(AmuleSearchFile)") {
        m26_print_search_result($obj);
    } elseif ($otype == "object(AmuleServer)") {
        m26_print_server($obj);
    } else {
        echo '"Unknow Object Type: ' . $otype . '"';
    }
}

function m26_print_map($val)
{
    $first = 1;
    echo "{";
    foreach ($val as $k => $v) {
        if ($first == 0) {
            echo ",";
        }
        $first = 0;
        echo '"' . $k . '": ';
        m26_print_json($v);
    }
    echo "}";
}

function m26_print_array($val)
{
    $first = 1;
    echo "[";
    foreach ($val as $k => $v) {
        if ($first == 0) {
            echo ",";
        }
        $first = 0;
        m26_print_json($v);
    }
    echo "]";
}

function m26_print_json($val)
{
    $vtype = m26_typeof($val);
    if ($vtype == "map") {
        m26_print_map($val);
    } elseif ($vtype == "array") {
        m26_print_array($val);
    } elseif (m26_startswith("object(", $vtype)) {
        m26_print_object($vtype, $val);
    } elseif ($vtype == "string") {
        echo '"' . m26_escape($val) . '"';
    } elseif ($vtype == "int" || $vtype == "float") {
        echo (string)$val;
    } elseif ($vtype == "bool") {
        if ($val) {
            echo "true";
        } else {
            echo "false";
        }
    } elseif ($vtype == "none") {
        echo "null";
    } else {
        echo '"unknow value type:' . $vtype.'"';
    }
}

function response($ok, $msg, $data)
{
    $resp = array(
        "ok" => $ok,
        "msg" => $msg,
        "data" => $data
    );
    m26_print_json($resp);
}

function format_stats_tree($data, $indentLevel) {
    // 如果不是数组，直接返回空字符串
    $dt = m26_typeof($data);
    if ( $dt != "map") {
        return "";
    }

    $result = '';
    // 计算当前的缩进空格（每层 4 个空格）
    $indent = "";
    for ($i = 0; $i < $indentLevel; $i++) {
        $indent .= "    ";
    } 

    foreach ($data as $key => $value) {
        // 拼接当前 key 和换行符（PHP 中用 \n 或 PHP_EOL）
        $result .= $indent . m26_escape($key) . "\n";
        // 如果值是数组（嵌套结构），则递归进入下一层，缩进等级 + 1
        $vt = m26_typeof($value);
        if ($vt == "map") {
            $result .= format_stats_tree($value, $indentLevel + 1);
        }
    }

    return $result;
}

function get_logs($reset)
{
    $p = "0";
    if($reset){
        $p = "1";
    }
    $alog = amule_get_log($p);
    $slog = amule_get_serverinfo($p);
    $tree = amule_load_vars("stats_tree");
    $stats = format_stats_tree($tree, 0);
    $logs = array(
        "amule" => $alog,
        "server" => $slog,
        "stats" => $stats
    );
    return $logs;
}

function calc_server_addr_hash()
{
    $result = amule_load_vars("servers");
    $c = count($result);
    $hash = 0;
    foreach ($result as $obj) {
        $addr = $obj->addr;
        $hash = m26_hash($addr, $hash);
    }

    $stats = amule_get_stats();
    $hash = m26_hash("".$stats["serv_addr"], $hash);
    $hash = m26_hash("".$stats["id"], $hash);

    return array("count" => $c, "hash" => $hash);
}

function calc_search_result_hash()
{
    $files = amule_load_vars("searchresult");
    $c = count($files);
    $hash = 0;
    foreach ($files as $obj) {
        $hash = m26_hash($obj->hash, $hash);
    }
    $files = amule_load_vars("downloads");
    foreach ($files as $obj) {
        $hash = m26_hash($obj->hash, $hash);
    }
    $files = amule_load_vars("shared");
    foreach ($files as $obj) {
        $hash = m26_hash($obj->hash, $hash);
    }
    return array("count" => $c, "hash" => $hash);
}

function calc_task_hash()
{
    $files = amule_load_vars("downloads");
    $hash = 0;
    foreach ($files as $obj) {
        $hash = m26_hash($obj->hash, $hash);
        $attrs = $obj->size_done . ", " . $obj->category . ", ";
        $attrs .= $obj->prio . ", " . $obj->prio_auto . ", " . $obj->status;
        $hash = m26_hash($attrs, $hash);
    }
    return $hash;
}

?>