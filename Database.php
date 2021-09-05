<?php
    ob_start();
    include "Secret.php";

    //Connection details
    define('SERVER', "localhost");
    define('USERNAME', "");
    define('PASSWORD', "");
    define('DATABASE', "");

    Class Database 
    {        
        private $connection;

        //Build connection
        function __construct() 
        {
            $this -> connection = new mysqli(SERVER, USERNAME, PASSWORD, DATABASE);
            if (!$this -> connection) {
                die('<p class="error"> Nem sikerült kapcsolódni az adatbázishoz!</p>');
            } 
        }

        //Check if generated hash is unique
        function uniqueHash($generatedHash) 
        {
            $query = "SELECT hash FROM `secret` WHERE hash = '$generatedHash';";
            $result = $this -> connection -> query($query);
            if ($result -> num_rows > 0) {                
                return False;
            }
            return True;
        }

        function getSecret($hash) 
        {
            $query = "SELECT * FROM `secret` WHERE hash = '$hash';";
            $result = $this -> connection -> query($query);
            if ($result) {
                $data = $result -> fetch_assoc();                 
                $secret = new Secret($data["hash"], $data["secretText"], $data["createdAt"], $data["expiresAt"], $data["remainingViews"]);
                //If there are views left we reduce the remaining views
                if ($data["remainingViews"] > 0) {
                    if ($this -> decreaseViewCount($hash)) {
                        return $secret;
                    } else {
                        return False;
                    } 
                } else {
                    return $secret;
                }                               
            }
            return False;            
        }

        function decreaseViewCount($hash) {
            $query = "UPDATE `secret` SET remainingViews = remainingViews - 1 WHERE hash = '$hash';";
            if ($this -> connection -> query($query)) {
                return True;
            }
            return False;
        }

        function createSecret($secret, $expireAfterViews, $expireAfter) 
        {
            $currentDate = date('Y-m-d H:i:s', time());
            $expireDate = strtotime($currentDate);
            $expireDate = $expireDate + (60*$expireAfter);
            $expireDate = date("Y-m-d H:i:s", $expireDate); 
            $hash = hash("sha256", $currentDate);
            //+1 for fixing the bug which occurs when only 1 view remains
            $expireAfterViews = $expireAfterViews + 1;

            //Prevent 2 identical hash
            while(!$this -> uniqueHash($hash))
            {
                $currentDate = date('Y-m-d H:i:s', time());
                $expireDate = strtotime($currentDate);
                $expireDate = $expireDate + (60*$expireAfter);
                $expireDate = date("Y-m-d H:i:s", $expireDate);
                $hash = hash("sha256", $currentDate);
            }
            $query = "INSERT INTO `secret` SET 
                                                hash = '$hash', secretText = '$secret', createdAt = '$currentDate', 
                                                expiresAt = '$expireDate', remainingViews = $expireAfterViews;";
            $result = $this -> connection -> query($query);
            if ($result) {
                return $hash;
            }
            return False;
        }

        function checkSecretLife($hash) 
        {
            //Check if Secret is still active
            $query = "SELECT * FROM `secret` WHERE hash = '$hash';";
            //Get secret
            $result = $this -> connection -> query($query);
            if ($result) {
                $data = $result -> fetch_assoc(); 
                //Convert to secret                
                $secret = new Secret($data["hash"], $data["secretText"], $data["createdAt"], $data["expiresAt"], $data["remainingViews"]);
                if ($secret) {
                    $expireDate = $secret -> getExpiresAt();
                    $remainingViews = $secret -> getRemainingViews();
                    $currentDate = date('Y-m-d H:i:s', time());
                    if ($currentDate > $expireDate) {
                        return 1;
                    } else if ($remainingViews == 0) {
                        return 2;
                    }
                    return 0;
                }
            }                            
            return -1;
        }
        //Generate xml
        function generateXml($secret) 
        {   
            $secret = $this -> getSecret($secret);
            if ($secret) {
                //Check if Secret is still active
                $checkSecretLife = $this -> checkSecretLife($secret -> getHash());
                if ($checkSecretLife != 0) {
                    header("HTTP/1.1 404 Not Found");
                } else {
                    //Create XML
                    Header('Content-type: text/xml');
                    $secretXml = new SimpleXMLElement("<Secret></Secret>");
                    $secretXml->addChild('hash', $secret -> getHash());
                    $secretXml->addChild('secretText', $secret -> getSecretText());
                    $secretXml->addChild('createdAt', $secret -> getCreatedAt());
                    $secretXml->addChild('expiresAt', $secret -> getExpiresAt());
                    $secretXml->addChild('remainingViews', $secret -> getRemainingViews());                    
                    echo $secretXml->asXML();
                }
            } else {
                header("HTTP/1.1 404 Not Found");
            }            
        }

        //Generate json
        function generateJson($secret) 
        {   
            $secret = $this -> getSecret($secret);
            if ($secret) {
                //Check if Secret is still active
                $checkSecretLife = $this -> checkSecretLife($secret -> getHash());
                if ($checkSecretLife != 0) {
                    header("HTTP/1.1 404 Not Found");
                } else {
                    //Create JSON
                    Header('Content-Type: application/json');
                    $data = array(
                            'hash' => $secret -> getHash(),
                            'secretText' => $secret -> getSecretText(),
                            'createdAt' => $secret -> getCreatedAt(),
                            'expiresAt' => $secret -> getExpiresAt(),
                            'remainingViews' => $secret -> getRemainingViews(),
                        );
                    $json = json_encode($data);
                    echo $json;
                }
            } else {
                header("HTTP/1.1 404 Not Found");
            }            
        }
    }
?>