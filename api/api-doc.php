<html>
<body>
<b>GET_MESSAGES</b>
<br><br>
<b>Attributes<br>
=========================================</b><br>
api_id - API ID assigned to you<br>
username - username assigned to you<br>
password - password assigned to you<br>
action - get_messages<br>
<br>
<b>Optional Attributes<br>
=========================================</b><br>
msg_id - Can be passed to return details of one specific message<br>
filter_by - Can be passed to filter for messages<br>
search_by - Search by one of the following fields: all, to_address, from_address, from_domain, to_domain, subject (default = all)<br>
search_operator - Search by the following operators: matches, contains (default = matches)<br>
search - Can be passed to obtain messages that match the specific string<br>
date_start - Record date to start at.  Valid format is YYYY-MM-DD<br>
date_end - Record date to end at.  Valid format is YYYY-MM-DD<br>
time_start - Record time to start at.  Valid format is HH:MM:SS<br>
time_end - Record time to end at.  Valid format is HH:MM:SS<br>
limitstart - Record to start at<br>
limitnum - Number of records to return<br>
isspam -  Obtain messages that are considered spam (yes) or are not considered spam (no)<br>
isvirus - Obtain messages that are considered a virus (yes) or are not considered a virus (no)<br>
isblacklisted - Obtain messages that were blacklisted (yes) or messages that were not blacklisted (no)<br>
iswhitelisted - Obtain messages that were whitelisted (yes) or messages that were not blacklisted (no)<br>
isquarantined - Obtain messages that are quarantined (yes) or messages that are not quarantined (no)<br>
<br>
<b>Example Command<br>
=========================================</b><br>
$postfields["api_id"] = "4562-TQ532-T4528Q459201-4052Z";<br>
$postfields["username"] = "username@yourdomain.com";<br>
$postfields["password" = md5("yourpassword");<br>
$postfields["action"] = "get_messages";<br>
<br>
<b>Returned Variables(JSON FORMAT)<br>
=========================================</b><br>
<samp>{"trinsic": {<br>
  "action": "get_messages",<br>
  "result": "success",<br>
  "totalresults": "2",<br>
  "startnumber": "0",<br>
  "numreturned": "2",<br>
  "messages": [<br>
    { "msg_id": "4Q592TW.Y456",<br>
      "from_address": "john@smith.com",<br>
      "from_domain": "smith.com",<br>
      "from_ip": "xxx.xxx.xxx.xxx",<br>
      "to_address": "you@yourdomain.com",<br>
      "to_domain": "yourdomain.com",<br>
      "subject": "Hey you! Haven't heard from you in a while!",<br>
      "date": "2012-01-15",<br>
      "time": "15:32:04",<br>
      "isspam": "no",<br>
      "isvirus": "no",<br>
      "virusname": "",<br>
      "isblacklisted": "no",<br>
      "iswhitelisted": "yes",<br>
      "isquarantined": "no",<br>
      "size": "980",<br>
      "headers": "header text...."<br>
    },<br>
    { "msg_id": "4X2652TW.T275",<br>
      "from_address": "joe@xxxxx.com",<br>
      "from_domain": "smith.com",<br>
      "from_ip": "xxx.xxx.xxx.xxx",<br>
      "to_address": "you@yourdomain.com",<br>
      "to_domain": "yourdomain.com",<br>
      "subject": "Your credit card information is out-of-date.",<br>
      "date": "2012-01-15",<br>
      "time": "17:35:04",<br>
      "isspam": "no",<br>
      "isvirus": "yes",<br>
      "virusname": "Phishing Scheme xxxxx",<br>
      "isblacklisted": "no",<br>
      "iswhitelisted": "no",<br>
      "isquarantined": "yes",<br>
      "size": "1050",<br>
      "headers": "header text...."<br>
    }<br>
  ]<br>
} }<br></samp>
   <br>
<b>Example PHP API Usage<br>
=========================================</b><br>
<code>&lt;?php<br>
$url = "https://api.trinsictech.com/gateway/api.php";<br>
$postfields["api_id"] = "4562-TQ532-T4528Q459201-4052Z";<br>
$postfields["username"] = "you@domain.com";<br>
$postfields["password"] = md5("yourpassword");<br>
$postfields["action"] = "get_messages";<br>
<br>
$ch = curl_init();<br>
curl_setopt($ch, CURLOPT_URL, $url);<br>
curl_setopt($ch, CURLOPT_POST, 1);<br>
curl_setopt($ch, CURLOPT_TIMEOUT, 100);<br>
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);<br>
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);<br>
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);<br>
curl_setopt($ch, CURLOPT_CAINFO, getcwd() . "/CAcerts/api_trinsictech_com.crt");<br>
curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);<br>
$data = curl_exec($ch);<br>
curl_close($ch);<br>
$msg = json_decode($data, true);<br>
$results = $msg['trinsic']['result'];<br>
if ( $results == "success" )<br>
  echo "&lt;pre&gt;".var_dump($msg)."&lt;/pre&gt;";<br>
else<br>
  echo "The following message occured: ".$results;<br>
?&gt;</code>
</body>
</html>
