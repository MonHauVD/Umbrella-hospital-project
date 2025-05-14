<?php
use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;
use TestHelper\Input as TestInput;

class UpdateRoomControllerTest extends TestCase
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

    // Role != "admin"
    public function test_should_return_error_if_not_admin()
    {
        $this->controller = new RoomHelperController();
        $this->controller->setVariable("AuthUser", new FakeAuthUser(["role" => "doctor"]));
        $this->controller->setVariable("Route", (object)["params" => (object)["id" => 1]]);

        $response = $this->callPrivateMethod($this->controller, "update");
        $this->assertEquals("You are not admin & you can't do this action !", $response["msg"]);
    }

    //Thiếu id room
    public function test_should_return_error_if_id_not_provided()
    {
        $this->controller = new RoomHelperController();
        $this->controller->setVariable("AuthUser", new FakeAuthUser(["role" => "admin"]));
        $this->controller->setVariable("Route", (object)["params" => (object)[]]);

        $response = $this->callPrivateMethod($this->controller, "update");
        $this->assertEquals("ID is required !", $response["msg"]);
    }

    // Thiếu tên phòng 
    public function test_should_return_error_if_name_missing()
    {
        InputTestHelper::setMethod("PUT"); 
        InputTestHelper::set("put", [
            "location" => "Floor 2"
        ]);

        $controller = new RoomHelperController();
        $controller->setVariable("AuthUser", new FakeAuthUser(["role" => "admin"]));
        $controller->setVariable("Route", (object)["params" => (object)["id" => 1]]);

        $response = $this->callPrivateMethod($controller, "update");

        $this->assertEquals("Missing field: name", $response["msg"]);
    }

    // Phòng đã tồn tại
    public function test_should_return_error_if_room_is_duplicated()
    {
        $existingRoom = Controller::model("Room");
        $existingRoom->set("name", "Room X")->set("location", "Floor 1")->save();

        $newRoom = Controller::model("Room");
        $newRoom->set("name", "Room Y")->set("location", "Floor 2")->save();

        InputTestHelper::setMethod("PUT"); 
        InputTestHelper::set("put", [
            "name" => "Room X",
            "location" => "Floor 1"
        ]);


        $controller = new RoomHelperController();
        $controller->setVariable("AuthUser", new FakeAuthUser(["role" => "admin"]));
        $controller->setVariable("Route", (object)["params" => (object)["id" => $newRoom->get("id")]]);

        $response = $this->callPrivateMethod($controller, "update");
        $this->assertStringContainsString("exists !", $response["msg"]);
    }

    // ID phòng không tồn tại
    public function test_should_return_error_if_room_not_available()
    {
        $this->controller->setVariable("AuthUser", new FakeAuthUser(["role" => "admin"]));
        $this->controller->setVariable("Route", (object)["params" => (object)["id" => -1]]);

        $response = $this->callPrivateMethod($this->controller, "update");
        $this->assertEquals("Room is not available", $response["msg"]);
    }


    // Cập nhật thành công
    public function test_should_update_room_successfully()
    {
        $room = Controller::model("Room");
        $room->set("name", "Old Name")->set("location", "Old Location")->save();

        $this->controller->setVariable("AuthUser", new FakeAuthUser(["role" => "admin"]));
        $this->controller->setVariable("Route", (object)["params" => (object)["id" => $room->get("id")]]);


        InputTestHelper::setMethod("PUT"); 
        InputTestHelper::set("put", [
            "name" => "Updated Name",
            "location" => "Updated Location"
        ]);

        $response = $this->callPrivateMethod($this->controller, "update");

        $this->assertEquals(1, $response["result"]);
        $this->assertEquals("Updated successfully", $response["msg"]);
        $this->assertEquals("Updated Name", $response["data"]["name"]);
        $this->assertEquals("Updated Location", $response["data"]["location"]);
    }
}
