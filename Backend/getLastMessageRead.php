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
        $lastMessageRead = getLastMessageRead($connection, $_GET["userId"]);

        if($lastMessageRead){
            echo json_encode($lastMessageRead);
        }
        else{
            echo json_encode(["error" => "400 Bad Request"]);
        }

    }

}

function getLastMessageRead($connection, $userId){
    $statement = $connection->prepare('
        SELECT last_message_read_id as "lastMessageReadId"
        FROM users
        WHERE id = :userId
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



?>