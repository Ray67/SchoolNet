<?php

namespace RayTools\Application;

class Config
{
    public 
        $hostname,
        $username,
        $password,
        $dbname;
    
    public function get($filename)
    {
        $json_data = json_decode(file_get_contents($filename));
        $this->hostname = $json_data->hostname;
        $this->username = $json_data->username;
        $this->password = $json_data->password;
        $this->dbname = $json_data->dbname;
    }
    
    public function put($filename)
    {
        file_put_contents($filename, json_encode($this));
    }
}