<?php
use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

if (!defined('APPPATH')) {
    define('APPPATH', realpath(__DIR__ . '/../../api/app'));
}
if (!defined('EC_SALT')) {
    define('EC_SALT', 'your_test_secret_key_here');
}

require_once __DIR__ . '/../../api/app/core/DataEntry.php';
require_once __DIR__ . '/../../api/app/core/Controller.php';
require_once __DIR__ . '/../../api/app/core/Input.php';
require_once __DIR__ . '/../../api/app/config/db.config.php';
require_once __DIR__ . '/../../api/app/core/DataList.php';
require_once __DIR__ . '/../../api/app/helpers/common.helper.php';
require_once __DIR__ . '/../../tests/Module_04/helper/TestableHelperController.php';
require_once __DIR__ . '/../../tests/Module_04/helper/FakeAuthUser.php';

class GetInforDoctorProfileControllerTest extends TestCase
{
    protected static $db;
    protected static $qb;

    private $controller;
    private $routeMock;

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

        $this->controller = new DoctorProfileHelperController();
        $this->routeMock = $this->createMock(stdClass::class);
        $this->controller->mockRoute = $this->routeMock;
    }

    private function callPrivateMethod($object, $methodName)
    {
        $ref = new ReflectionClass($object);
        $method = $ref->getMethod($methodName);
        $method->setAccessible(true);
        try {
            $method->invoke($object);
        } catch (Exception $e) {
            if ($e->getMessage() !== "__EXIT__") {
                throw $e;
            }
        }

        return json_decode($object->output, true);
    }


    //bác sĩ chưa đăng nhập
    public function test_should_return_error_if_not_logged_in()
    {
        $this->controller->setVariable("AuthUser", null);

        $resp = $this->callPrivateMethod($this->controller, "getInformation");

        $this->assertEquals(0, $resp["result"]);
        $this->assertEquals("You does not log in !", $resp["msg"]);
    }

    //tài khoản không tồn tại
    public function test_should_return_error_if_account_is_not_available()
    {
        $fakeUser = new FakeAuthUser([
            "id" => 9999999,
            "active" => 1
        ]);
        $this->controller->setVariable("AuthUser", $fakeUser);

        $resp = $this->callPrivateMethod($this->controller, "getInformation");

        $this->assertEquals(0, $resp["result"]);
        $this->assertEquals("This account is not available !", $resp["msg"]);
    }

    //
    public function test_should_return_error_if_account_is_deactivated()
    {
        // Giả sử có 1 doctor ID = 10, nhưng active = 0
        $fakeUser = new FakeAuthUser([
            "id" => 10,
            "active" => 0
        ]);
        $this->controller->setVariable("AuthUser", $fakeUser);

        $resp = $this->callPrivateMethod($this->controller, "getInformation");

        $this->assertEquals(0, $resp["result"]);
        $this->assertEquals("This account is deactivated !", $resp["msg"]);
    }

    public function test_should_return_doctor_information_successfully()
    {
        // Giả sử ID = 1 là một doctor tồn tại, active = 1
        $fakeUser = new FakeAuthUser([
            "id" => 1,
            "active" => 1
        ]);
        $this->controller->setVariable("AuthUser", $fakeUser);

        $resp = $this->callPrivateMethod($this->controller, "getInformation");

        $this->assertEquals(1, $resp["result"]);
        $this->assertEquals("Action successfully !", $resp["msg"]);
        $this->assertArrayHasKey("data", $resp);
        $this->assertArrayHasKey("name", $resp["data"]);
    }

}
