<?php

define('API',"5613653884"); //Bot Key 

$API_URL = 'https://api.telegram.org/bot'.API.'/';

//$GROUP_D         = '-1001882423063';
$GROUP_ID        = '-1001598459642';
$CHANNEL_ID      = '-1001869521806';

function exec_curl_request($handle){
    $response = curl_exec($handle);
    if ($response === false) {
        $errno = curl_errno($handle);
        $error = curl_error($handle);
        error_log("Curl returned error $errno: $error\n");
        curl_close($handle);
        return false;
    }
    $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
    curl_close($handle);
    if ($http_code >= 500) {
        sleep(10);
        return false;
    } elseif ($http_code != 200) {
        $response = json_decode($response, true);
        error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
        if ($http_code == 401) {
            throw new Exception('Invalid access token provided');
        }
        return false;
    } else {
        $response = json_decode($response, true);
        if (isset($response['description'])) {
            error_log("Request was successfull: {$response['description']}\n");
        }
        $response = $response['result'];
    }
    return $response;
}


function bot($method, $parameters){
    global $API_URL;
    if (!is_string($method)) {
        error_log("Method name must be a string\n");
        return false;
    }
    if (!$parameters) {
        $parameters = array();
    } elseif (!is_array($parameters)) {
        error_log("Parameters must be an array\n");
        return false;
    }
    foreach($parameters as $key => &$val) {
        if (!is_numeric($val) && !is_string($val) && !is_a($val, 'CURLFile')) {
            $val = json_encode($val);
        }
    }
    $url = $API_URL . $method;
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);
    curl_setopt($handle, CURLOPT_POSTFIELDS, $parameters);
    return exec_curl_request($handle);
}




function sendMessage($chat_id,$text,$parse_mode = "HTML"){
    $params = array(
    'chat_id'     => $chat_id,
    "text".       => $text, 
    "parse_mode"  => $parse_mode);
    return bot("sendMessage", $params);
}


function delMessage($chat_id, $message_id){ 
  $params =  array(
        'chat_id'    => $chat_id,
        'message_id' => $message_id);
    return bot("deleteMessage", $params);
}

function makeRandomString($length){
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $out = "";
    for ($i = 1; $i <= $length; $i++) {
        $out .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $out;
}



function sendDocument($chat_id,$document, $caption){ 
         $params = array(
            'chat_id'    => $chat_id, 
            "document"   => $document, 
            'parse_mode' => "HTML",
            "caption"    => $caption);
        return bot("sendDocument",$params);
}
 
 


function downloadRepository($username, $name, $custom = []){
    $url = "https://github.com/{$username}/{$name}";
    $user_agent = [
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/57.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/601.2.7 (KHTML, like Gecko) Version/9.0.1 Safari/601.2.7',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11) AppleWebKit/601.1.56 (KHTML, like Gecko) Version/9.0 Safari/601.1.56',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:41.0) Gecko/20100101 Firefox/41.0',
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.71 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko',
        'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; AS; rv:11.0) like Gecko',
        'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
        'Mozilla/5.0 (compatible, MSIE 11, Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko',
        'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/5.0)',
    ];
    
    $options = [
        CURLOPT_RETURNTRANSFER => true, 
        CURLOPT_HEADER         => true, 
        CURLOPT_FOLLOWLOCATION => true,     
        CURLOPT_ENCODING       => "",   
        CURLOPT_AUTOREFERER    => true,
        CURLOPT_CONNECTTIMEOUT => 120,        
        CURLOPT_TIMEOUT        => 120,       
        CURLOPT_MAXREDIRS      => 10,        
        CURLINFO_HEADER_OUT    => true,
        CURLOPT_SSL_VERIFYPEER => false,     
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_COOKIE         => (array_key_exists('cookies', $custom) ? $custom['cookies'] : null),
        CURLOPT_USERAGENT      => (array_key_exists('user_agent', $custom) ? $custom['user_agent'] : $user_agent[array_rand($user_agent)]),
    ];

    if (array_key_exists('headers', $custom) and is_array($custom['headers'])) {
        $options[CURLOPT_HTTPHEADER] = $custom['headers'];
    }

    $handle = curl_init($url);
    curl_setopt_array($handle, $options);
    $response = curl_exec($handle);
    if ($response === false) {
        $errno = curl_errno($handle);
        $error = curl_error($handle);
        error_log("Curl returned error $errno: $error\n");
        curl_close($handle);
        return false;
    }
    $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
    curl_close($handle);
    if ($http_code >= 500) {
        sleep(10);
        return false;
    } elseif ($http_code != 200) {
        return false;
    } else {
        if (preg_match('/(\/' . $username . '\/' . $name . '\/archive\/refs\/heads\/(\w+)\.zip)/i', $response, $matches)) {
            $repo_file_addr = "https://github.com" . $matches[1];
            $fname = makeRandomString(rand(15, 25));
    
    $file_addr = copy($repo_file_addr, "files/github/{$fname}.zip") ? "files/github/{$fname}.zip" : false;
    
        preg_match('/(\<strong\>\s*(([+-]?([0-9]*[.])?[0-9]+)[kmbt]?)\s*\<\/strong\>\s*stars)/i', $response, $stars);
        preg_match('/(\<strong\>\s*(([+-]?([0-9]*[.])?[0-9]+)[kmbt]?)\s*\<\/strong\>\s*watching)/i', $response, $watchs);
        preg_match('/(\<strong\>\s*(([+-]?([0-9]*[.])?[0-9]+)[kmbt]?)\s*\<\/strong\>\s*forks)/i', $response, $forks);
        preg_match('/(\s*\<title\>.+\/.+\:\s*(.+)\s*\<\/title\>)/i', $response, $title);
        preg_match('/(\s*\<Topics\>.+\/.+\:\s*(.+)\s*\<\/Topics\>)/i', $response,$Topics);
        return [
                "file_addr" => $file_addr,
                "user"      => "https://github.com/{$username}/",
                "link"      => $url,
                "file_name" => "{$username}[{$name}] - {$matches[2]}.zip",
                "title"     => $title[2],
                "stars"     => (int) $stars[2],
                "watchs"    => (int) $watchs[2],
                "forks"     => (int) $forks[2],
                "Topics"    =>$Topics[2],
            ];
        }
    }
    return false;
}






$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (isset($update["message"])) {
    $message        = $update['message'];
    $chat_id        = $message['chat']['id'];
    $chat_username  = $message['chat']['username'];
    $from_id        = $message['from']['id'];
    $text           = $message['text'];
    $message_id     = $message['message_id'];
    $first_name     = $message['from']['first_name'];

    if($text == "/start"){
    sendmessage($from_id,"
🌟 کاربر $first_name خوش امدید.\n
راهنما : 
🌿 لینک ریپو خودتون در گروه @Github_GP ارسال کنید.\n
📥 مانند :
`https://GitHub.com/Ayhan-dev/Project`");
    }

#==================================
    if ($chat_id == $GROUP_ID) {
    delMessage($chat_id,$message_id); 
    
    if(preg_match('/^(?:http(?:s)?\:\/\/)?github\.com\/([\w-]{2,40})\/([\w-]{2,40})(?:(?:\.git)?\/?)?$/i', $text, $matches)) {
        
    $repo = downloadRepository($matches[1], $matches[2]);

$msg = "<b>✨ A new repository was sent 🎉</b>
<b>🏷 Title:</b> <i>{$repo['title']}</i>

<b>👤 From:</b> <a href=\"tg://user?id={$from_id}\">" . htmlspecialchars($first_name) . "</a> (<a href=\"{$repo['user']}\">Github</a>)
<b>🔗 Link:</b> {$repo['link']}
<b>⭐️ Star(s):</b> <code>{$repo['stars']}</code>
<b>👁‍🗨 Watch(s):</b> <code>{$repo['watchs']}</code>
<b>🌐 Fork(s):</b> <code>{$repo['forks']}</code>
";
                
        sendMessage($chat_id,$msg."\n\n🐍 channel: @DevDiwan - DevDiwan.com");
            
        sendDocument($CHANNEL_ID,new CURLFile($repo['file_addr']),
        $msg."\n<b>ℹ️ Message info:</b> <a href=\"https://t.me/{$chat_username}/$message_id\">Link</a>\n\n🐍 channel: @DevDiwan - DevDiwan.com");
        
            unlink($repo['file_addr']);
                
    }}
    
}
