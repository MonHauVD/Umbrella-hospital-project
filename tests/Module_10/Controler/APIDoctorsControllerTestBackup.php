<?php
use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

require_once __DIR__ . '/../../../api/app/core/DataEntry.php';
require_once __DIR__ . '/../../../api/app/core/Controller.php';
require_once __DIR__ . '/../../../api/app/core/Input.php';
require_once __DIR__ . '/../../../api/app/controllers/DoctorsController.php';
require_once __DIR__ . '/../../../api/app/models/DoctorModel.php';
require_once __DIR__ . '/../../../api/app/config/db.config.php';



class APIDoctorsControllerTest extends TestCase
{
    public function setUp(): void
    {
        $app_url = "http://localhost:8080/api";
        define("APPURL", $app_url);
    }
    public function testProcessWhenUserIsNotAuthenticated()
    {
        // Mock the controller and its dependencies
        $controller = $this->getMockBuilder(DoctorsController::class)
                           ->setMethods(['getVariable', 'getAll', 'save'])
                           ->getMock();

        // Simulate that AuthUser does not exist (user not authenticated)
        $controller->method('getVariable')
                   ->willReturn(null); // No authenticated user

        // Expect the header function to be called for redirection
        $controller->expects($this->once())
                   ->method('getVariable')
                   ->willReturn(null); // AuthUser is not set

        // Run the process method, expecting a redirect to login
        $controller->process();
    }

    public function testProcessWhenUserIsAuthenticatedWithGETRequest()
    {
        // Mock the controller and its dependencies
        $controller = $this->getMockBuilder(DoctorsController::class)
                           ->setMethods(['getVariable', 'getAll', 'save', 'header'])
                           ->getMock();

        // Simulate AuthUser exists (user authenticated)
        $controller->method('getVariable')
                   ->willReturn(true); // Authenticated user

        // Mock Input::method to return 'GET'
        $controller->method('Input::method')
                   ->willReturn('GET');

        // Expect getAll() to be called for GET request
        $controller->expects($this->once())
                   ->method('getAll');

        // Run the process method
        $controller->process();
    }

    public function testProcessWhenUserIsAuthenticatedWithPOSTRequest()
    {
        // Mock the controller and its dependencies
        $controller = $this->getMockBuilder(DoctorsController::class)
                           ->setMethods(['getVariable', 'getAll', 'save', 'header'])
                           ->getMock();

        // Simulate AuthUser exists (user authenticated)
        $controller->method('getVariable')
                   ->willReturn(true); // Authenticated user

        // Mock Input::method to return 'POST'
        $controller->method('Input::method')
                   ->willReturn('POST');

        // Expect save() to be called for POST request
        $controller->expects($this->once())
                   ->method('save');

        // Run the process method
        $controller->process();
    }
}
