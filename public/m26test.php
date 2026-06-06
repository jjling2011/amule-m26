<?php

do_test($HTTP_GET_VARS, $_SESSION);

function do_test($get, $session) {
    

    echo "<pre>";
    echo "test_version:\n";
    test_m26_version();

    echo "\ntest_url_param:\n";
    test_url_param($get, $session);

    echo "\n test_m26_typeof:\n";
    test_m26_typeof();

    echo "\n test_m26_escape:\n";
    test_m26_escape();

    echo "\n test_m26_endswith:\n";
    test_m26_endswith();

    echo "\n test_m26_startswith:\n";
    test_m26_startswith();

    echo "\n test_m26_has:\n";
    test_m26_has();

    echo "\n test_m26_hash:\n";
    test_m26_hash();
    echo "</pre>";
}

function test_url_param($get, $session) {
    echo "HTTP_GET_VARS\n";
    print_array($get);
    
    echo "\n_SESSION\n";
    print_array($session);
}

function print_array($arr) {
    foreach ($arr as $key => $value) {
        echo $key." => ".$value."\n";
    }
}


function test_m26_typeof() {
    $s1 = "hello";
    echo "s1: ". $s1.", type: ". m26_typeof($s1)."\n";
    $none;
    echo "none: ". $none.", type: ". m26_typeof($none)."\n";
    echo "d_test() type: ".m26_typeof(do_test)."\n";
    echo "m26_typeof() type: ".m26_typeof()."\n";
    echo "m26_typeof(1) type: ".m26_typeof(1)."\n";
    echo "m26_typeof(1.2) type: ".m26_typeof(1.2)."\n";
    echo "m26_typeof(1 == 2) type: ".m26_typeof(1==2)."\n";
    echo "m26_typeof(3 == 3) type: ".m26_typeof(3==3)."\n";
    echo "m26_typeof(array(1, 2, 3)) type: ".m26_typeof(array(1, 2, 3))."\n";
    echo "m26_typeof(array(a=>b)) type: ".m26_typeof(array("a" => "b"))."\n";

    $downloads = amule_load_vars("downloads");
    
    foreach($downloads as $download){
        $typ = m26_typeof($download);
        echo "download[0] type: ".$typ."\n";

        $ok = $typ == "object(AmuleDownloadFile)";
        echo "type is object(AmuleDownloadFile): ". $ok ."\n";

        $ok = $typ == "object(amuledownloadfile)";
        echo "type is object(amuledownloadfile): ".$ok."\n";
        $ok =  $typ == "object(muledownloadfil)";
        echo "type is object(muledownloadfil): ".$ok."\n";
        $ok =  $typ == "object()";
        echo "type is object(): ".$ok."\n";
        break;
    }
}
    
function test_m26_version(){
    $mver = m26_get_version();
    $mmatch = m26_startswith("amule-m26", "".$mver);
    echo "  m26: ".$mver." match: ".$mmatch."\n";

    $fver = m26_function_that_not_exist();
    $fmatch = m26_startswith("amule-m26", "".$fver);
    echo " fake:".$fver." match: ".$fmatch."\n";

    $aver = amule_get_version();
    $amatch = m26_startswith("amule-m26", "".$aver);
    echo "amule: ".$aver." match: ".$amatch."\n";
}

function test_m26_escape() {
    $s1 = "hello文字🍕";
    $e1 = m26_escape($s1);
    echo "source: \"".$s1."\" escaped: \"".$e1."\"\n";

    $s1 = "\\h\ne\tll\fo文\b字🍕";
    $e1 = m26_escape($s1);
    echo "source: \"".$s1."\" escaped: \"".$e1."\"\n";

    $s1 = "\\\h\\ne\\tll\\fo文\\b字🍕";
    $e1 = m26_escape($s1);
    echo "source: \"".$s1."\" escaped: \"".$e1."\"\n";

}

function test_m26_endswith() {
    $needle = "llo";
    $haystack = "yollooo";

    $ok = m26_endswith($needle, $haystack);
    echo $needle ." endswith ".$haystack;
    if($ok){
        echo " is true\n";
    }else {
        echo " is false\n";
    }

    $needle = "yol";
    $ok = m26_endswith($needle, $haystack);
    echo $needle ." endswith ".$haystack;
    if($ok){
        echo " is true\n";
    }else {
        echo " is false\n";
    }

    $needle = "ooo";
    $ok = m26_endswith($needle, $haystack);
    echo $needle ." endswith ".$haystack;
    if($ok){
        echo " is true\n";
    }else {
        echo " is false\n";
    }

    $needle = $haystack;
    $ok = m26_endswith($needle, $haystack);
    echo $needle ." endswith ".$haystack;
    if($ok){
        echo " is true\n";
    }else {
        echo " is false\n";
    }

    $needle = "yyollooo";
    $ok = m26_endswith($needle, $haystack);
    echo $needle ." endswith ".$haystack;
    if($ok){
        echo " is true\n";
    }else {
        echo " is false\n";
    }
}



function test_m26_startswith() {
    $needle = "llo";
    $haystack = "yollooo";

    $ok = m26_startswith($needle, $haystack);
    echo $needle ." startswith ".$haystack;
    if($ok){
        echo " is true\n";
    }else {
        echo " is false\n";
    }

    $needle = "yol";
    $ok = m26_startswith($needle, $haystack);
    echo $needle ." startswith ".$haystack;
    if($ok){
        echo " is true\n";
    }else {
        echo " is false\n";
    }

    $needle = "ooo";
    $ok = m26_startswith($needle, $haystack);
    echo $needle ." startswith ".$haystack;
    if($ok){
        echo " is true\n";
    }else {
        echo " is false\n";
    }

    $needle = "yolloool";
    $ok = m26_startswith($needle, $haystack);
    echo $needle ." startswith ".$haystack;
    if($ok){
        echo " is true\n";
    }else {
        echo " is false\n";
    }
}


function test_m26_has() {
    $needle = "llo";
    $haystack = "yollooo";

    $ok = m26_has($needle, $haystack);
    echo $needle ." in ".$haystack;
    if($ok){
        echo " is true\n";
    }else {
        echo " is false\n";
    }

    $needle = "lll";
    $ok = m26_has($needle, $haystack);
    echo $needle ." in ".$haystack;
    if($ok){
        echo " is true\n";
    }else {
        echo " is false\n";
    }

    $needle = "yol";
    $ok = m26_has($needle, $haystack);
    echo $needle ." in ".$haystack;
    if($ok){
        echo " is true\n";
    }else {
        echo " is false\n";
    }

    $needle = "yolo";
    $ok = m26_has($needle, $haystack);
    echo $needle ." in ".$haystack;
    if($ok){
        echo " is true\n";
    }else {
        echo " is false\n";
    }

    $needle = "looo";
    $ok = m26_has($needle, $haystack);
    echo $needle ." in ".$haystack;
    if($ok){
        echo " is true\n";
    }else {
        echo " is false\n";
    }

    $needle = "yolloool";
    $ok = m26_has($needle, $haystack);
    echo $needle ." in ".$haystack;
    if($ok){
        echo " is true\n";
    }else {
        echo " is false\n";
    }
}

function test_m26_hash(){
    $s1 = "hello";
    $h1 = m26_hash($s1);
    echo "str: ".$s1.", hash1: ".$h1."\n";

    $h2 = m26_hash($s1, 0);
    echo "str: ".$s1.", hash2: ".$h2."\n";

    $h3 = m26_hash($s1, 1);
    echo "str: ".$s1.", hash3: ".$h3."\n\n";

    $s2 = "world文字🎉";
    $h1 = m26_hash($s2, $h1);
    echo "str: ".$s2.", hash1: ".$h1."\n";

    $h2 = m26_hash($s2, $h2);
    echo "str: ".$s2.", hash2: ".$h2."\n";

    $h3 = m26_hash($s2, $h3);
    echo "str: ".$s2.", hash3: ".$h3."\n\n";

    $s3 = "word";
    $h1 = m26_hash($s3, $h1);
    echo "str: ".$s3.", hash1: ".$h1."\n";

    $h2 = m26_hash($s2, $h2);
    echo "str: ".$s2.", hash2: ".$h2."\n";

    $h3 = m26_hash($s3, $h3);
    echo "str: ".$s3.", hash3: ".$h3."\n";
}

?>