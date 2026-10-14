<?php
require_once "DbConnector.php";

header("Access-Control-Allow-Origin: *");
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
set_time_limit(0);

$activeStream = true;
$lastMessageId = $_GET["lastMessageId"];
$chatId = $_GET["chatId"];
$connection = null;
$messageQueryResult = null;

if(!array_key_exists("chatSSEAuth", $_COOKIE)){
    $activeStream = false;
    echo json_encode(["error" => "Code 401 Unauthorized"]);
}
else{
    echo json_encode([$_COOKIE["chatSSEAuth"]]);
    //verifySSEAuth($_COOKIE["chatSSEAuth"]);
}

/*while($activeStream){
    if($connection == null){
        $connection = new DbConnector("localhost", "root", "root", "chat_mock");
    }
    $messageQueryResult = checkNewChatMessage($connection, $chatId, $lastMessageId);
    if($messageQueryResult["id"] > $lastMessageId){
        $lastMessageId = $messageQueryResult["id"];
        echo "data: ".json_encode($messageQueryResult)."\n\n";
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();
        $messageQueryResult = null;
    }
    sleep(1);
}*/

function checkNewChatMessage($connection, $chatId, $lastMessageId){
    $statement = $connection->prepare("
        SELECT *
        FROM chat_messages
        WHERE chat_id = :chatId and id > :lastMessageId
        ORDER BY id DESC
    ");
    $statement->bindParam(":chatId", $chatId);
    $statement->bindParam(":lastMessageId", $lastMessageId);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    if($result){
        return $result;
    }
    else{
        return false;
    }
}

function verifySSEAuth(){

}
?>