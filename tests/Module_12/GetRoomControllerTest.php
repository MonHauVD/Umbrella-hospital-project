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

class GetRoomControllerTest extends TestCase
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

    //role != admin
    public function test_should_return_error_if_not_admin()
    {
        $user = new FakeAuthUser(["role" => "doctor"]);
        $route = (object)[ "params" => (object) [ "id" => 1 ] ];

        $this->controller->setVariable("AuthUser", $user);
        $this->controller->setVariable("Route", $route);

        $res = $this->callPrivateMethod($this->controller, "getById");

        $this->assertEquals(0, $res["result"]);
        $this->assertEquals("You are not admin & you can't do this action !", $res["msg"]);
    }

    //Không truyền vào id phòng
    public function test_should_return_error_if_id_missing()
    {
        $user = new FakeAuthUser(["role" => "admin"]);
        $route = (object)[ "params" => (object) [] ];

        $this->controller->setVariable("AuthUser", $user);
        $this->controller->setVariable("Route", $route);

        $res = $this->callPrivateMethod($this->controller, "getById");

        $this->assertEquals(0, $res["result"]);
        $this->assertEquals("ID is required !", $res["msg"]);
    }

    // Phòng không tồn tại
    public function test_should_return_error_if_room_not_available()
    {
        $user = new FakeAuthUser(["role" => "admin"]);
        $route = (object)[ "params" => (object) [ "id" => 999999 ] ];

        $this->controller->setVariable("AuthUser", $user);
        $this->controller->setVariable("Route", $route);

        $res = $this->callPrivateMethod($this->controller, "getById");

        $this->assertEquals(0, $res["result"]);
        $this->assertEquals("__EXIT__", $res["msg"]);
    }


    // Lấy thôg tin phòng thành công
    public function test_should_return_success_if_room_exists_and_user_is_admin()
    {
        $user = new FakeAuthUser(["role" => "admin"]);
        $route = (object)[ "params" => (object) [ "id" => 1 ] ]; 

        $this->controller->setVariable("AuthUser", $user);
        $this->controller->setVariable("Route", $route);

        $res = $this->callPrivateMethod($this->controller, "getById");

        $this->assertEquals(1, $res["result"]);
        $this->assertEquals("Action successfully !", $res["msg"]);
        $this->assertArrayHasKey("data", $res);
        $this->assertArrayHasKey("id", $res["data"]);
        $this->assertArrayHasKey("name", $res["data"]);
        $this->assertArrayHasKey("location", $res["data"]);
    }



}
