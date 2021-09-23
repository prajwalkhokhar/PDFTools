<?php

class config
{
    public function __construct()
    {
        $this->set_env();
    }

    public function set_env()
    {
        $env_variables = array(
            "SITE_NAME" => "PDF Tools"
        );

        foreach($env_variables as $key=>$val)
        {
            putenv("$key=$val");
        }
    }
}

$obj = new config();

?>