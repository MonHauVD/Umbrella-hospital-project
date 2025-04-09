<?php
use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

if (!defined('APPPATH')) {
    define('APPPATH', realpath(__DIR__ . '/../app'));
}
if (!defined('EC_SALT')) {
    define('EC_SALT', 'your_test_secret_key_here'); 
}

require_once __DIR__ . '/../app/core/DataEntry.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Input.php';
require_once __DIR__ . '/../app/config/db.config.php';
require_once __DIR__ . '/../app/core/DataList.php';
require_once __DIR__ . '/../app/controllers/PatientProfileController.php';
require_once __DIR__ . '/../tests/helper/TestableHelperController.php';
require_once __DIR__ . '/../tests/helper/FakeAuthUser.php';


class GetInformationPatientProfileControllerTest extends TestCase
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
            'username'  => 'mysql',
            'password'  => '12345',
            'charset'   => 'utf8',
            'options'   => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        ];
        self::$db = new Connection('mysql', $config, 'DB');
        self::$qb = self::$db->getQueryBuilder();
    }

    public function tearDown(): void{
        // Rollback transaction sau mỗi test case
        self::$db->getPdoInstance()->rollback();
    }

    public function setUp(): void
    {
        // Bắt đầu transaction trước mỗi test case
        self::$db->getPdoInstance()->beginTransaction();

         // Create an instance of the controller
        $this->controller = $this->getMockBuilder(PatientProfileController::class)
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
        // $this->controller->method('jsonecho')->will($this->returnCallback(function() {
        //     return; // Ngừng thực thi phần còn lại của hàm
        // }));
        // Cho phép test đọc được resp thông qua Reflection
        $controller = $this->controller;
        $this->controller->method('jsonecho')->will($this->returnCallback(function () use ($controller) {
            $reflection = new ReflectionClass($controller);
            $prop = $reflection->getProperty('resp');
            $prop->setAccessible(true);
            echo json_encode($prop->getValue($controller));
        }));
    }
    //PatientProfileController
    

    //đã đăng nhập
    public function testGetInformationWithAuthUser()
    {
        require_once APPPATH . "/models/PatientsModel.php";
        $patients = new PatientsModel();
        $patients->where("id", ">", 0)->orderBy("id", "asc")->fetchData();

        $data = $patients->getData();

        if (count($data) === 0) {
            $this->fail("No patient available in database to run test.");
        }

        $patient = $data[0];

        // Bắt đầu lưu output thủ công (để không bị PHPUnit nuốt)
        ob_start();
        print_r($patient);
        $outputDebug = ob_get_clean();

        // In ra console trong quá trình test
        fwrite(STDOUT, "\nPatient data:\n" . $outputDebug . "\n");

        // Wrap patient stdClass thành đối tượng có get()
        $authUser = new FakeAuthUser($patient);
        $this->authMock = $authUser;
        $this->controller->method('getVariable')
        ->will($this->returnCallback(function($name) {
            if ($name === "Route") {
                return $this->routeMock;
            } elseif ($name === "AuthUser") {
                return $this->authMock;
            }
            return null;
        }));

        // Gọi getInformation() qua Reflection
        try {
            $reflection = new ReflectionClass($this->controller);
            $method = $reflection->getMethod("getInformation");
            $method->setAccessible(true);
            $method->invoke($this->controller);
        } catch (\Exception $e) {
            if ($e->getMessage() !== "__EXIT__") {
                throw $e;
            }
        }

        $output = json_decode($this->getActualOutputForAssertion(), true);

        $this->assertEquals(1, $output["result"]);
        $this->assertEquals("Action successfully !", $output["msg"]);
        $this->assertArrayHasKey("id", $output["data"]);
        $this->assertEquals($patient->id, $output["data"]["id"]);
    }

    //chưa đăng nhập
    public function testGetInformationWithoutAuthUser()
    {
        // Tạo mock controller riêng 
        $controller = $this->getMockBuilder(PatientProfileController::class)
            ->setMethods(['getVariable', 'jsonecho'])
            ->getMock();

        // Mock getVariable trả về null cho "AuthUser"
        $controller->method('getVariable')
            ->willReturnCallback(function($name) {
                if ($name === "AuthUser") return null;
                return null;
            });

        // Mock jsonecho để in ra response
        $controller->method('jsonecho')
        ->willReturnCallback(function () use ($controller) {
            $reflection = new ReflectionClass($controller);
            $prop = $reflection->getProperty('resp');
            $prop->setAccessible(true);
            echo json_encode($prop->getValue($controller));
            throw new \Exception("__EXIT__"); // Dừng hàm tại đây
        });
        // Gọi hàm qua Reflection
        ob_start();
        try {
            $reflection = new ReflectionClass($controller);
            $method = $reflection->getMethod("getInformation");
            $method->setAccessible(true);
            $method->invoke($controller);
        } catch (\Exception $e) {
            if ($e->getMessage() !== "__EXIT__") {
                throw $e;
            }
        }
        $output = json_decode(ob_get_clean(), true);

        // Kiểm tra kết quả
        $this->assertEquals(0, $output["result"]);
        $this->assertEquals("There is no authenticated user !", $output["msg"]);
        $this->assertArrayNotHasKey("data", $output);
    }

}
