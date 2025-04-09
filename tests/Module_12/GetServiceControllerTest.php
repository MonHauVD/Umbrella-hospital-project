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
require_once __DIR__ . '/../../tests/Module_12/helper/TestableHelperController.php';
require_once __DIR__ . '/../../tests/Module_12/helper/FakeAuthUser.php';

class GetServiceControllerTest extends TestCase
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

        $this->controller = new ServiceHelperController();
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


    //Thiếu ID service
    public function test_should_fail_if_id_missing()
    {
        $controller = new ServiceHelperController();
        $controller->setVariable("AuthUser", new FakeAuthUser(["role" => "admin"]));
        $controller->setVariable("Route", (object)["params" => (object)[]]);

        $result = $this->callPrivateMethod($controller, "getById");

        $this->assertEquals(0, $result["result"]);
        $this->assertEquals("ID is required !", $result["msg"]);
    }

    //ID service không tồn tại
    public function test_should_fail_if_service_not_found()
    {
        $controller = new ServiceHelperController();
        $controller->setVariable("AuthUser", new FakeAuthUser(["role" => "admin"]));
        $controller->setVariable("Route", (object)["params" => (object)["id" => 999999]]); 

        $result = $this->callPrivateMethod($controller, "getById");

        $this->assertEquals(0, $result["result"]);
        $this->assertEquals("__EXIT__", $result["msg"]);
    }

    //Lấy service thành công
    public function test_should_return_service_by_id_successfully()
    {
        $existingService = DB::table("tn_services")->first(); 

        $controller = new ServiceHelperController();
        $controller->setVariable("AuthUser", new FakeAuthUser(["role" => "admin"]));
        $controller->setVariable("Route", (object)["params" => (object)["id" => $existingService->id]]);

        $result = $this->callPrivateMethod($controller, "getById");

        $this->assertEquals(1, $result["result"]);
        $this->assertEquals("Action successfully !", $result["msg"]);
        $this->assertEquals($existingService->name, $result["data"]["name"]);
    }

}
