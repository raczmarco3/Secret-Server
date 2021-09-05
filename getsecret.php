<?php
    session_start();
    include "Database.php";

    $connection = new Database();
    
    if ($_SESSION["respondType"] == "xml") {
        $connection -> generateXml($_GET["secret_id"]);
    } else {
        $connection -> generateJson($_GET["secret_id"]);
    }
?>