<?php
require_once "DbConnector.php";
require_once "TokenManager.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type,authorization");

if($_SERVER['REQUEST_METHOD'] === "POST"){
    $post = json_decode(file_get_contents("php://input"), true);
    $hasError = false;
    $tokenManager = new TokenManager();
    $token = $tokenManager->getBearerToken();
    
    if(!$tokenManager->validate($token)){
        $hasError = true;
        echo json_encode(["code" => "401 Unauthorized"]);
    }

    if(!$hasError){
        $connection = new DbConnector("localhost", "root", "root","chat_mock");

        if(saveMessageToDb($connection, $post["chat"], $post["user"],$post["recipient"], $post["message"])){
            echo json_encode(["Code" => "200 OK"]);
        }
        else{
            echo json_encode(["Code" => "400 Bad Request"]);
        }
    }
}

function saveMessageToDb($connection, $chatId, $userId, $recipientId, $message){
    $statement = $connection->prepare("
        INSERT INTO chat_messages(user_id, chat_id, recipient_id, message) VALUES(:userId, :chatId, :recipient, :message)
    ");
    $statement->bindParam(":userId", $userId);
    $statement->bindParam(":chatId", $chatId);
    $statement->bindParam(":recipient", $recipientId);
    $statement->bindParam(":message", $message);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    if($result > 0){
        return true;
    }
    else{
        return false;
    }
}
?>