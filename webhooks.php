<?php
//header("Content-type: application/json; charset=utf-8");
header('Content-Type: text/html; charset=utf-8');
    $client_id = '1563228754';
    $client_secret = '94bd50a658cfc05dae429bcc2bec01c9';
    //$accessToken = 'RaZjxS9f4bo3lkA1YDyddtjyWZMPlEBw6GewIKOmVyWgfr2WDGQVU35LlFtnZyTsE8A0qSdtGaFStdQJHauat42zT5o1K+Fz9BVNDOESZLfO9fi3gOHXZG46NAf4yW0BsVc8As60NfNrpI9YpqIU1QdB04t89/1O/w1cDnyilFU=';
    $accessToken = accessToken($client_id, $client_secret);
    $content = file_get_contents('php://input');
    $arrayJson = json_decode($content, true);
    
    $arrayHeader = array();
    $arrayHeader[] = "Content-Type: application/json; charset=UTF-8";
    $arrayHeader[] = "Authorization: Bearer {$accessToken}";

    //รับข้อความจากผู้ใช้
    $message = $arrayJson['events'][0]['message']['text'];
    if(preg_match("/^\/groupadd/", $message)){
        $ln = explode(" ", $message);
        if ($arrayJson['events'][0]['source']['type'] != 'group') {
            $text = "คำสั่งสำหรับ Group เท่านั้น";
        } else {
            $regname = str_replace($ln[0]." ", "", $message);
            if ($regname == "") {
                $text = 'กรุณาระบุชื่อด้วยครับ /groupadd [ชื่อ]';
            } else {
                $text = $arrayJson['events'][0]['source']['groupId']."|". $arrayJson['events'][0]['source']['userId']. "|". $regname ."|".$arrayJson['events'][0]['source']['type'];
                //$text = "https://mybot-line.herokuapp.com/enroll.php?code=".$arrayJson['events'][0]['source']['groupId'];
            }
            
            if(!$fp = fopen("files/registered.txt", "a+"))
            {
                die("Create file fail");
            }

            $lines = file('files/registered.txt');
            $found = false;
            foreach($lines as $line)
            {
            if(strpos($line, $arrayJson['events'][0]['source']['groupId']) !== false)
            {
                $found = true;
            }
            }

            // If the text was not found, show a message
            if(!$found && $regname != "")
            {
                //$fcontent = "Group ID|User ID|Name|Type\n";
                $fcontent .= $arrayJson['events'][0]['source']['groupId']."|". $arrayJson['events'][0]['source']['userId']. "|". str_replace($ln[0]." ", "", $message)."|".$arrayJson['events'][0]['source']['type']."\n";
            
                fwrite($fp, $fcontent);
                
            }
            fclose($fp);
        }
        
        $arrayPostData['replyToken'] = $arrayJson['events'][0]['replyToken'];
        $arrayPostData['messages'][0]['type'] = "text";
        $arrayPostData['messages'][0]['text'] = $text;
        
        replyMsg($arrayHeader,$arrayPostData);
    }

    if(preg_match("/^\/botadd/", $message)){
        $ln = explode(" ", $message);
        if ($arrayJson['events'][0]['source']['type'] != 'user') {
            $text = "คำสั่งสำหรับบุคคลเท่านั้น";
        } else {
            $regname = str_replace($ln[0]." ", "", $message);
            if ($regname == "") {
                $text = 'กรุณาระบุชื่อด้วยครับ /botadd [ชื่อ]';
            } else {
                //$text = $arrayJson['events'][0]['source']['userId']."|". $arrayJson['events'][0]['source']['userId']. "|". str_replace($ln[0]." ", "", $message)."|".$arrayJson['events'][0]['source']['type'];
                $text = "https://mybot-line.herokuapp.com/enroll.php?code=".$arrayJson['events'][0]['source']['userId'];
            }
            
            if(!$fp = fopen("files/registered.txt", "a+"))
            {
                die("Create file fail");
            }

            $lines = file('files/registered.txt');
            $found = false;
            foreach($lines as $line)
            {
            if(strpos($line, $arrayJson['events'][0]['source']['userId']) !== false)
            {
                $found = true;
            }
            }

            // If the text was not found, show a message
            if(!$found && $regname != "")
            {
                //$fcontent = "Group ID|User ID|Name|Type\n";
                $fcontent .= $arrayJson['events'][0]['source']['userId']."|". $arrayJson['events'][0]['source']['userId']. "|". str_replace($ln[0]." ", "", $message)."|".$arrayJson['events'][0]['source']['type']."\n";
            
                fwrite($fp, $fcontent);
            }
            fclose($fp);
        }
        
        $arrayPostData['replyToken'] = $arrayJson['events'][0]['replyToken'];
        $arrayPostData['messages'][0]['type'] = "text";
        $arrayPostData['messages'][0]['text'] = $text;
        
        replyMsg($arrayHeader,$arrayPostData);
    }

    function replyMsg($arrayHeader,$arrayPostData){
        $strUrl = "https://api.line.me/v2/bot/message/reply";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$strUrl);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $arrayHeader);    
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($arrayPostData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close ($ch);
    }

    function accessToken($client_id, $client_secret) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.line.me/v2/oauth/accessToken",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "grant_type=client_credentials&client_id=".$client_id."&client_secret=" . $client_secret,
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/x-www-form-urlencoded"
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
        echo "cURL Error #:" . $err;
        } else {
        $data = json_decode($response);
        }

        return $data->access_token;
    }
?>
OK
