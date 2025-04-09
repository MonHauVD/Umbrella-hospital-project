<?php

require_once APPPATH . "/controllers/DoctorProfileController.php";


class DoctorProfileHelperController extends DoctorProfileController
{
    public $output;
    public $mockAuthUser;
    public $mockRoute;

    public function getVariable($name)
    {
        if ($name === "AuthUser") return $this->mockAuthUser;
        if ($name === "Route") return $this->mockRoute;
        return null;
    }

    public function setVariable($name, $value)
    {
        if ($name === "AuthUser") $this->mockAuthUser = $value;
        if ($name === "Route") $this->mockRoute = $value;
    }

    public function jsonecho($resp = null)
    {
        if (is_null($resp)) $resp = $this->resp;
        $this->output = json_encode($resp);
        throw new \Exception("__EXIT__"); 
    }
}

