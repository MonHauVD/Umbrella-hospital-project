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

class GetAllServiceControllerTest extends TestCase
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

        $this->controller = new ServicesHelperController();
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



    //Lấy tất cả các phòng
    public function test_should_return_room_list_with_default_paging()
    {
        $controller = new ServicesHelperController();
        $controller->setVariable("AuthUser", new FakeAuthUser(["role" => "admin"]));

        $result = $this->callPrivateMethod($controller, "getAll");

        $this->assertEquals(1, $result["result"]);
        $this->assertArrayHasKey("data", $result);
        $this->assertIsArray($result["data"]);
        $this->assertLessThanOrEqual(10, count($result["data"])); 
    }

    //Tìm kiếm phòng
    // public function test_should_filter_by_search_name()
    // {
    //     $_GET["search"] = "Phòng A"; // 

    //     $controller = new RoomsHelperController();
    //     $controller->setVariable("AuthUser", new FakeAuthUser(["role" => "admin"]));

    //     $result = $this->callPrivateMethod($controller, "getAll");

    //     $this->assertEquals(1, $result["result"]);
    //     foreach ($result["data"] as $row) {
    //         $this->assertStringStartsWith("Phòng A", $row["name"]);
    //     }
    // }
}
