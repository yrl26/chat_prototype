<?php
require_once "DbConnector.php";
require_once "TokenManager.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: authorization,userId");

if($_SERVER["REQUEST_METHOD"] === "GET"){
    $hasError = false;
    $tokenManager = new TokenManager();

    $token = $tokenManager->getBearerToken();

    if(!$tokenManager->validate($token)){
        $hasError = true;
        echo json_encode(["code" => "401 Unauthorized"]);
    }

    if(!$hasError){
        $connection = new DbConnector("localhost", "root", "root", "chat_mock");
        $mostRecentMessage = getMostRecentMessage($connection, $_GET["userId"]);

        if($mostRecentMessage){
            echo json_encode($mostRecentMessage);
        }
        else{
            echo json_encode(["error" => "400 Bad Request"]);
        }

    }

}

function getMostRecentMessage($connection, $userId){
    $statement = $connection->prepare('
        SELECT id
        FROM chat_messages
        WHERE recipient_id = :userId
        ORDER BY id DESC
        LIMIT 1
    ');
    $statement->bindParam(":userId", $userId);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    if($result){
        return $result;
    }
    else{
        return false;
    }

}