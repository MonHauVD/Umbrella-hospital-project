<?php
use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;


class GetAllRoomControllerTest extends TestCase
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

        $this->controller = new RoomsHelperController();
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
    public function test_should_fail_if_not_admin()
    {
        $controller = new RoomsHelperController();
        $controller->setVariable("AuthUser", new FakeAuthUser(["role" => "doctor"]));
        
        $result = $this->callPrivateMethod($controller, "getAll");

        $this->assertEquals(0, $result["result"]);
        $this->assertEquals("You are not admin & you can't do this action !", $result["msg"]);
    }


    //Lấy tất cả các phòng
    public function test_should_return_room_list_with_default_paging()
    {
        $controller = new RoomsHelperController();
        $controller->setVariable("AuthUser", new FakeAuthUser(["role" => "admin"]));

        $result = $this->callPrivateMethod($controller, "getAll");

        $this->assertEquals(1, $result["result"]);
        $this->assertArrayHasKey("data", $result);
        $this->assertIsArray($result["data"]);
        $this->assertLessThanOrEqual(5, count($result["data"])); 
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
