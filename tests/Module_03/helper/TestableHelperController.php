<?php
require_once APPPATH . "/controllers/LoginController.php";
require_once APPPATH . "/controllers/LoginWithGoogleController.php";
require_once APPPATH . "/controllers/PatientProfileController.php";

class LoginHelperController extends LoginController
{
    public $output;

    public function jsonecho($resp = null)
    {
        if (is_null($resp)) {
            $resp = $this->resp;
        }
        $this->output = json_encode($resp); 
        throw new \Exception("__EXIT__");
    }
}

class LoginWithGoogleHelperController extends LoginWithGoogleController
{
    public $output;

    public function jsonecho($resp = null)
    {
        if (is_null($resp)) {
            $resp = $this->resp;
        }
        $this->output = json_encode($resp); 
        throw new \Exception("__EXIT__");
    }
}


class TestableHelperController extends PatientProfileController
{
    public $output;
    public $mockAuthUser;
    public $mockRoute;

    public function getVariable($name)
    {
        if ($name === "AuthUser") {
            return $this->mockAuthUser;
        } elseif ($name === "Route") {
            return $this->mockRoute;
        }
        return null;
    }

    public function setVariable($name, $value)
    {
        if ($name === "AuthUser") {
            $this->mockAuthUser = $value;
        } elseif ($name === "Route") {
            $this->mockRoute = $value;
        }
    }

    public function jsonecho($resp = null)
    {
        if (is_null($resp)) {
            $resp = $this->resp;
        }
        $this->output = json_encode($resp);
        throw new \Exception("__EXIT__");
    }
}
