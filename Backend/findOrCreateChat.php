<?php
require_once "DbConnector.php";
require_once "TokenManager.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type,authorization");

$chatId = null;

if($_SERVER["REQUEST_METHOD"] === "POST"){
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
        $chatId = findChat($connection, $post["currentUser"], $post["chatMemberId"]);
        if(!$chatId){
            createNewChat($connection, $post["currentUser"], $post["chatMemberId"]);
            $chatId = findChat($connection, $post["currentUser"], $post["chatMemberId"]);
        }
        
        echo json_encode($chatId);
    }
}


function findChat($connection, $currentUser, $chatMemberId){
    $statement = $connection->prepare("
        SELECT chat_id
        FROM chat_members
        WHERE user_id IN (:currentUser, :chatMemberId)
        GROUP BY chat_id
        HAVING COUNT(user_id) = 2  
    ");
    $statement->bindParam(":currentUser", $currentUser);
    $statement->bindParam(":chatMemberId", $chatMemberId);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    if($result){
        return $result;
    }
    else{
        return false;
    }
}

function createNewChat($connection,$currentUser, $chatMemberId){
    $statement = $connection->prepare('
        INSERT INTO chat() VALUES();

        SET @chat_id = LAST_INSERT_ID();

        INSERT INTO chat_members(chat_id, user_id)
        VALUES(@chat_id, :currentUser),(@chat_id, :chatMemberId);
    ');
    $statement->bindParam(":currentUser", $currentUser);
    $statement->bindParam(":chatMemberId", $chatMemberId);
    $statement->execute();
}
?>