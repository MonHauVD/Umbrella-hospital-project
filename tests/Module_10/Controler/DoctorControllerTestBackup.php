<?php
use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

require_once __DIR__ . '/../../../api/app/core/DataEntry.php';
require_once __DIR__ . '/../../../api/app/core/Controller.php';
require_once __DIR__ . '/../../../api/app/core/Input.php';
require_once __DIR__ . '/../../../api/app/controllers/DoctorController.php';
require_once __DIR__ . '/../../../api/app/models/DoctorModel.php';
require_once __DIR__ . '/../../../api/app/config/db.config.php';

class DoctorControllerTest extends TestCase
{
    protected static $db;
    protected static $qb;

    private $controller;
    private $routeMock;
    private $authMock;
    public static function setUpBeforeClass(): void
    {
        // Khởi tạo Pixie Connection
        $config = [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'doantotnghiep',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'options'   => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        ];
        self::$db = new Connection('mysql', $config, 'DB');
        self::$qb = self::$db->getQueryBuilder();
    }

    public function tearDown(){
        // Rollback transaction sau mỗi test case
        self::$db->getPdoInstance()->rollback();
    }

    public function setUp(): void
    {
        // Bắt đầu transaction trước mỗi test case
        self::$db->getPdoInstance()->beginTransaction();

         // Create an instance of the controller
        //  $this->controller = new DoctorController();
        $this->controller = $this->getMockBuilder(DoctorController::class)
                                  ->setMethods(['getVariable', 'jsonecho']) // Mocking getVariable
                                  ->getMock();


         // Create a mock for the Route class (assuming it's a class)
         $this->authMock = $this->createMock(stdClass::class);
         $this->routeMock = $this->createMock(stdClass::class);
 
         // Mock the getVariable method to return the routeMock
        //  $this->controller->method('getVariable')->willReturn($this->routeMock);
        $this->controller->method('getVariable')
                 ->will($this->returnCallback(function($name) {
                     if ($name === "Route") {
                         return $this->routeMock;
                     } elseif ($name === "AuthUser") {
                         return $this->authMock;
                     }
                     return null;
                 }));
        //Stop code when calling jsonecho method
        $this->controller->method('jsonecho')->will($this->returnCallback(function() {
            // return; // Ngừng thực thi phần còn lại của hàm
            throw new Exception("jsonecho method called");
        }));
    }

    // public function testGetByIdWithId()
    // {
    //     // Set up the Route mock to simulate missing ID
    //     $this->routeMock->params = (object) []; // No ID in params

    //     // Use reflection to call the private method
    //     $reflectionMethod = new ReflectionMethod(DoctorController::class, 'getById');
    //     $reflectionMethod->setAccessible(true); // Make the private method accessible

    //     // Call the private method under test
    //     $reflectionMethod->invoke($this->controller);

    //     // Use ReflectionProperty to access the protected $resp property
    //     $reflectionProperty = new ReflectionProperty(DoctorController::class, 'resp');
    //     $reflectionProperty->setAccessible(true); // Make the protected property accessible
        
    //     // Get the value of the $resp property
    //     $resp = $reflectionProperty->getValue($this->controller);
        
    //     fwrite(STDERR, "----------------------\n");
    //     fwrite(STDERR, var_export($resp, true));
    //     fwrite(STDERR, "\n----------------------\n");
    //     // Assert that the result is set to 0
    //     $this->assertEquals(0, $resp->result);

    //     // Assert that the error message is set
    //     $this->assertEquals("ID is required !", $resp->msg);
    // }

        public function testGetById1WithId()
    {
        // Set up the Route mock to simulate an ID being present
        $this->routeMock->params = (object) ['id' => 123]; // ID in params
        $this->authMock = "admin";
        // Use reflection to call the private method
        $reflectionMethod = new ReflectionMethod(DoctorController::class, 'getById1');
        $reflectionMethod->setAccessible(true); // Make the private method accessible

        // Call the private method under test
        try {
            $reflectionMethod->invoke($this->controller);
        } catch (Exception $e) {
            // Handle the exception thrown by jsonecho method
            // You can assert that the exception message is as expected if needed
            $this->assertEquals("jsonecho method called", $e->getMessage());
        }

        // Use ReflectionProperty to access the protected $resp property
        $reflectionProperty = new ReflectionProperty(DoctorController::class, 'resp');
        $reflectionProperty->setAccessible(true); // Make the protected property accessible

        // Get the value of the $resp property
        $resp = $reflectionProperty->getValue($this->controller);

        // Assert that the result is still 0 (no change when the ID is present)
        $this->assertEquals('ID is 123', $resp->msg);
        fwrite(STDERR, "----------------------\n");
        fwrite(STDERR, var_export($resp, true));
        fwrite(STDERR, "\n----------------------\n");
        // Since no message is set when ID is present, assert that msg is not set
        $this->assertObjectHasAttribute('msg', $resp);
    }
}
