<?php
    Class Secret 
    {
        Private $hash;
        Private $secretText;
        Private $createdAt;
        Private $expiresAt;
        Private $remainingViews;

        function __construct($hash, $secretText, $createdAt, $expiresAt, $remainingViews)
        {
            $this -> hash = $hash;
            $this -> secretText = $secretText;
            $this -> createdAt = $createdAt;
            $this -> expiresAt = $expiresAt;
            $this -> remainingViews = $remainingViews;
        }

        function getHash() 
        {
            return $this -> hash;
        }

        function getSecretText () 
        {
            return $this -> secretText;
        }

        function getCreatedAt() 
        {
            return $this -> createdAt;
        }

        function getExpiresAt () 
        {
            return $this -> expiresAt;
        }

        function getRemainingViews() 
        {
            return $this -> remainingViews;
        }
    }
?>