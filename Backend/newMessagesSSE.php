<?php
require_once "DbConnector.php";

header("Access-Control-Allow-Origin: *");
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');


$chatId = $_GET["chatId"];
$messageId = $_GET["messageId"];
$sseToken = $_GET["sseToken"];
$activeStream = false;
$connection = null;

if(validateSSEToken($sseToken)){
    $activeStream = true;
}
else{
    echo "data: ".json_encode(["error" => "401 Unauthorized"])."\n\n";
}

while($activeStream){
    if($connection == null){
        $connection = new DbConnector("localhost", "root", "root", "chat_mock");
    }
    $messages = getNewMessages($connection, $chatId, $messageId);
    if(count($messages) > 0){
        echo "data: ".json_encode($messages)."\n\n";
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();
        $messageId = $messages[count($messages) - 1]["id"];
        $messages = null;
    }
    
    if(connection_aborted()){
        $activeStream = false;
    }
    sleep(1);
}

exit();

function validateSSEToken($sseToken){
    $parts = explode(".", $sseToken);
    if (hash_equals($parts[2], hash_hmac('sha512', $parts[0].$parts[1], 'abc123'))){
        return true;
    }
    else{
        return false;
    }
}

//necessary to add cm.user_id != currentUser
function getNewMessages($connection, $chatId, $messageId){
    $statement = $connection->prepare('
        SELECT users.username, cm.id, cm.chat_id, cm.message
        FROM chat_messages as cm JOIN users ON cm.user_id = users.id
        WHERE (cm.chat_id IS NOT NULL OR cm.chat_id = :chatId) AND cm.id > :messageId
    ');

    $statement->bindParam(":chatId", $chatId);
    $statement->bindParam(":messageId", $messageId);

    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    return $result;
}
?>