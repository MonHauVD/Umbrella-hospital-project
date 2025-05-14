<?php
use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;


class GetInformationPatientProfileControllerTest extends TestCase
{
    protected static $db;
    protected static $qb;

    private $controller;
    private $routeMock;
    private $authMock;

    public static function setUpBeforeClass(): void
    {
        $config = require __DIR__ . '/../../LocalConfigDB.php';
        self::$db = new Connection('mysql', $config, 'DB');
        self::$qb = self::$db->getQueryBuilder();
    }

    public function tearDown(): void
    {
        self::$db->getPdoInstance()->rollback();
    }

    public function setUp(): void
    {
        self::$db->getPdoInstance()->beginTransaction();

        $this->controller = new TestableHelperController();

        $this->routeMock = $this->createMock(stdClass::class);
        $this->controller->mockRoute = $this->routeMock;
    }

    // Trường hợp đã đăng nhập
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

        // In thông tin bệnh nhân ra log để debug
        fwrite(STDOUT, "\nPatient data:\n" . print_r($patient, true) . "\n");

        $authUser = new FakeAuthUser($patient);
        $this->authMock = $authUser;

        // Set biến giả lập vào controller
        $this->controller->setVariable("AuthUser", $this->authMock);
        $this->controller->setVariable("Route", $this->routeMock);

        // Gọi hàm getInformation
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

        // Lấy output từ jsonecho override
        $output = json_decode($this->controller->output, true);

        // Kiểm tra kết quả
        $this->assertEquals(1, $output["result"]);
        $this->assertEquals("Action successfully !", $output["msg"]);
        $this->assertArrayHasKey("id", $output["data"]);
        $this->assertEquals($patient->id, $output["data"]["id"]);
    }

    // Trường hợp chưa đăng nhập
    public function testGetInformationWithoutAuthUser()
    {
        $controller = $this->getMockBuilder(PatientProfileController::class)
            ->setMethods(['getVariable', 'jsonecho'])
            ->getMock();

        $controller->method('getVariable')
            ->willReturnCallback(function ($name) {
                if ($name === "AuthUser") return null;
                return null;
            });

        $controller->method('jsonecho')
            ->willReturnCallback(function () use ($controller) {
                $reflection = new ReflectionClass($controller);
                $prop = $reflection->getProperty('resp');
                $prop->setAccessible(true);
                echo json_encode($prop->getValue($controller));
                throw new \Exception("__EXIT__");
            });

        // Gọi hàm getInformation
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
