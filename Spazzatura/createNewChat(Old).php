<?php
require_once "DbConnector.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if($_SERVER["REQUEST_METHOD"] === "POST"){
    $post = json_decode(file_get_contents("php://input"), true);
    $connection = new DbConnector("localhost", "root", "root","chat_mock");

    if(createNewChat($connection,$post["currentUser"], $post["user"])){
        echo json_encode(["Code" => "200 OK"]);
    }
    else{
        echo json_encode(["Code" => "400 Bad Request"]);
    }
}

/*
To make this more scalable, it is necessary to create an array of the participants' id and somehow create the whole Values string
*/
function createNewChat($connection,$currentUser, $userId){
    $statement = $connection->prepare("
        INSERT INTO chat() VALUES();

        SET @chat_id = LAST_INSERT_ID();

        INSERT INTO chat_members(chat_id, user_id)
        VALUES(@chat_id, :currentUser),(@chat_id, :userId);
    ");
    $statement->bindParam(":currentUser", $currentUser);
    $statement->bindParam(":userId", $userId);

    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    if($result > 0){
        return $result;
    }
    else{
        return false;
    }
}
?>