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
require_once __DIR__ . '/../../tests/Module_12/helper/FakeInput.php';

class UpdateServiceControllerTest extends TestCase
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


    //role != admin
    public function test_should_return_error_if_not_admin()
    {
        $controller = new RoomHelperController();
        $controller->setVariable("AuthUser", new FakeAuthUser(["role" => "patient"]));
        $controller->setVariable("Route", (object)["params" => (object)["id" => 2]]);

        $result = $this->callPrivateMethod($controller, "delete");

        $this->assertEquals(0, $result["result"]);
        $this->assertEquals("You are not admin & you can't do this action !", $result["msg"]);
    }

    //Thiếu trường id
    public function test_should_return_error_if_id_missing()
    {
        $this->controller->setVariable("AuthUser", new FakeAuthUser(["role" => "admin"]));
        $this->controller->setVariable("Route", (object)["params" => (object)[]]);

        $result = $this->callPrivateMethod($this->controller, "update");

        $this->assertEquals(0, $result["result"]);
        $this->assertEquals("ID is required !", $result["msg"]);
    }


    //ID dịch vụ không tồn tại
    public function test_should_return_error_if_service_not_found()
    {
        $this->controller->setVariable("AuthUser", new FakeAuthUser(["role" => "admin"]));
        $this->controller->setVariable("Route", (object)["params" => (object)["id" => 999999]]); // id không tồn tại

        $result = $this->callPrivateMethod($this->controller, "delete");

        $this->assertEquals(0, $result["result"]);
        $this->assertEquals("Service is not available", $result["msg"]);
    }


    //xoá dịch vụ thành công
    public function test_should_delete_service_successfully()
    {
        $service = Controller::model("Service");
        $service->set("name", "Service Test Xóa")
                ->set("image", "")
                ->set("description", "Test cho unit test xoá service")
                ->save();
        $serviceId = $service->get("id");

        $this->controller->setVariable("AuthUser", new FakeAuthUser(["role" => "admin"]));
        $this->controller->setVariable("Route", (object)["params" => (object)["id" => $serviceId]]);

        $result = $this->callPrivateMethod($this->controller, "delete");
        $this->assertEquals(1, $result["result"]);
        $this->assertEquals("Service is deleted successfully !", $result["msg"]);

        $deletedService = Controller::model("Service", $serviceId);
        $this->assertFalse($deletedService->isAvailable());
    }

    //Xoá dịch vụ mà dịch vụ đấy đang gán với bác sĩ
    public function test_should_not_delete_service_assigned_to_doctor()
    {
        $service = Controller::model("Service");
        $service->set("name", "Service gán Doctor")->set("description", "Test")->save();
        $serviceId = $service->get("id");

        self::$qb->table(TABLE_PREFIX . TABLE_DOCTOR_AND_SERVICE)->insert([
            "doctor_id" => 1,
            "service_id" => $serviceId
        ]);
        $this->controller = new ServiceHelperController();
        $this->controller->setVariable("AuthUser", new FakeAuthUser(["role" => "admin"]));
        $this->controller->setVariable("Route", (object)["params" => (object)["id" => $serviceId]]);

        $result = $this->callPrivateMethod($this->controller, "delete");

        $this->assertEquals(0, $result["result"]);
        $this->assertStringContainsString("assigned with doctors", $result["msg"]);
    }
    
}
