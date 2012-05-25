<?php

$url = "http://localhost/mailscanner/api.php";
$username = "brad@trinsictech.com";
$password = md5("Sad1eTigg3rs");
$postfields["api_id"] = "4562-TQ532-T4528Q459201-4052Z";
$postfields["username"] = $username;
$postfields["password"] = $password;
$postfields["action"] = "get_users";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 100);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
$data = curl_exec($ch);
curl_close($ch);
$msg = json_decode($data, true);
?>
<pre> <? var_dump($msg) ?> </pre>
