<?php
    ob_start();
    include "Database.php";    
    session_start();
?>

<!DOCTYPE html>
    <head>
        <title>
            Secret Server    
        </title>
        <base href="https://secret-server-api.000webhostapp.com/">
        <link rel="stylesheet" href="style.css">
    </head>

    <body>
        <?php
            //Check url 1
            if(isset($_GET["secret"]) && $_GET["secret"]==true) {
        ?>
                <form method="POST" action="" class="myForm">
                    Secret: <input type="text" name="secret" class="inputWidth" required>
                    Views: <input type="number" name="expireAfterViews" class="inputWidth" required>
                    Expire (in minutes): <input type="number" name="expireAfter" class="inputWidth" required>
                    <input type="submit" name="submit" value="Submit" class="myBtn">
                </form>

                <form method="POST" action="" class="myForm">
                    Get my Secret: <input type="text" name="secret_id" class="inputWidth" required>
                    Response content type: <select name="options" class="selectOption" required>
                                            <option value="json">application/json</option>
                                            <option value="xml">application/xml</option>
                    <input type="submit" name="getSecret" value="Confirm" class="myBtn">
                </form>
        <?php
            //Check url 2
            } else {
        ?>
                <a href="secret/" class="welcomeLink">Add secret</a>                
        <?php
            }
        ?>
    </body>
</html>

<?php
    if (isset($_POST["submit"])) {
        if (empty($_POST["secret"]) || empty($_POST["expireAfterViews"]) || empty($_POST["expireAfter"])) {
            echo '<p class="error">Missing data!</p>';
        } else {
            $secret = $_POST["secret"];
            $expireAfterViews = $_POST["expireAfterViews"];
            $expireAfter = $_POST["expireAfter"];

            $connection = new Database();
            $hash = $connection -> createSecret($secret, $expireAfterViews, $expireAfter);
            if ($hash) {
                echo '<p class="success">Secret created succesfully!</p>';
                echo '<p class="msg">The unique hash for the secret: '. $hash;
            } else {
                echo '<p class="error">Database/Query error</p>';
            }
        }
    } else if(isset($_POST["getSecret"])) {
        $connection = new Database();
        //Check if Secret is still active
        $checkSecretLife = $connection -> checkSecretLife($_POST["secret_id"]);
                
        if ($checkSecretLife == 1) {
            echo '<p class="error">This secret already expired!</p>';
        } else if ($checkSecretLife == 2) {
            echo '<p class="error">There are no more views!</p>';
        } else if ($checkSecretLife == -1 ) { 
            echo '<p class="error">Database/Query error</p>';
        } else {            
            $_SESSION["respondType"] = $_POST["options"]; 
            header('Location: '.$_POST["secret_id"]);
        }
    }
    
?>