<?php
require_once "DbConnector.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

if($_SERVER["REQUEST_METHOD"] === "GET"){
    $connection = new DbConnector("localhost", "root", "root", "chat_mock");
    $result = checkNewMessages($connection, $_GET["currentChat"], $_GET["currentUser"], $_GET["lastPoll"]);

    if($result){
        echo json_encode($result);
    }
    else{
        echo json_encode(["message" => "No resource found"]);
    }
}

function checkNewMessages($connection, $chatId, $recipientId, $pollInMilliseconds){
    $correctTimestampPoll = $pollInMilliseconds / 1000;
    
    /*$statement = $connection->prepare("
        SELECT DISTINCT user_id
        FROM chat_messages
        WHERE recipient_id = :recipientId and timestamp >= FROM_UNIXTIME(:lastPollTimestamp)
    ");*/
    $statement = $connection->prepare("
        SELECT DISTINCT user_id
        FROM chat_messages
        WHERE (:chatId IS NULL OR chat_id != :chatId) and recipient_id = :recipientId and timestamp >= FROM_UNIXTIME(:lastPollTimestamp)
    ");
    $statement->bindParam(":chatId", $chatId);
    
    $statement->bindParam(":recipientId", $recipientId);
    $statement->bindParam(":lastPollTimestamp", $correctTimestampPoll);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    if($result){
        return $result;
    }
    else{
        return false;
    }
}

?>