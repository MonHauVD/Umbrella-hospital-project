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

class DeleteRoomControllerTest extends TestCase
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

        $this->controller = new RoomHelperController();
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

    //Role != admin
    public function test_should_return_error_if_not_admin()
    {
        $controller = new RoomHelperController();
        $controller->setVariable("AuthUser", new FakeAuthUser(["role" => "patient"]));
        $controller->setVariable("Route", (object)["params" => (object)["id" => 2]]);

        $result = $this->callPrivateMethod($controller, "delete");

        $this->assertEquals(0, $result["result"]);
        $this->assertEquals("You are not admin & you can't do this action !", $result["msg"]);
    }

    //Không có id phòng
    public function test_should_return_error_if_id_is_missing()
    {
        $controller = new RoomHelperController();
        $controller->setVariable("AuthUser", new FakeAuthUser(["role" => "admin"]));
        $controller->setVariable("Route", (object)["params" => (object)[]]);

        $result = $this->callPrivateMethod($controller, "delete");

        $this->assertEquals(0, $result["result"]);
        $this->assertEquals("ID is required !", $result["msg"]);
    }

    //ID phòng không tồn tại
    public function test_should_return_error_if_room_not_available()
    {
        $controller = new RoomHelperController();
        $controller->setVariable("AuthUser", new FakeAuthUser(["role" => "admin"]));
        $controller->setVariable("Route", (object)["params" => (object)["id" => 99999]]); // ID không tồn tại

        $result = $this->callPrivateMethod($controller, "delete");

        $this->assertEquals(0, $result["result"]);
        $this->assertEquals("Room is not available", $result["msg"]);
    }

    //Xóa phòng thành công
    public function test_should_delete_room_successfully()
    {
        $room = Controller::model("Room");
        $room->set("name", "Phòng Test Xóa")
            ->set("location", "Khu A")
            ->save();
        $roomId = $room->get("id");

        $controller = new RoomHelperController();
        $controller->setVariable("AuthUser", new FakeAuthUser(["role" => "admin"]));
        $controller->setVariable("Route", (object)["params" => (object)["id" => $roomId]]);

        $result = $this->callPrivateMethod($controller, "delete");

        $this->assertEquals(1, $result["result"]);
        $this->assertEquals("Room is deleted successfully !", $result["msg"]);
    }

}
