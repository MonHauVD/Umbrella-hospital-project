<?php
use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;


class ChangeInformationDoctorControllerTest extends TestCase
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

    private function setPostFields(array $fields)
    {
        $_POST = $fields;
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

    //Chưa đăng nhập
    public function test_should_return_error_if_not_logged_in()
    {
        $resp = $this->callPrivateMethod($this->controller, "changeInformation");

        $this->assertEquals(0, $resp["result"]);
        $this->assertEquals("You does not log in !", $resp["msg"]);
    }

    //Tài khoản đã bị khóa
    public function test_should_return_error_if_account_is_deactivated()
    {
        $fakeUser = new FakeAuthUser(["id" => 8, "active" => 0]);
        $this->controller->setVariable("AuthUser", $fakeUser);

        $resp = $this->callPrivateMethod($this->controller, "changeInformation");

        $this->assertEquals(0, $resp["result"]);
        $this->assertEquals("You does not log in !", $resp["msg"]);
    }


    //Thiếu tên người dùng
    public function test_should_return_error_if_missing_name()
    {
        $_POST["phone"] = "0123456789";
        $fakeUser = new FakeAuthUser(["id" => 1, "active" => 1]);
        $this->controller->setVariable("AuthUser", $fakeUser);

        $resp = $this->callPrivateMethod($this->controller, "changeInformation");

        $this->assertEquals(0, $resp["result"]);
        $this->assertEquals("Missing field: name", $resp["msg"]);
    }


    //Tên không hợp lệ
    public function test_should_return_error_if_name_is_invalid()
    {
        $_POST["phone"] = "0912345678";
        $_POST["name"] = "John@@##$%^^^Doe";
        $fakeUser = new FakeAuthUser(["id" => 1, "active" => 1]);
        $this->controller->setVariable("AuthUser", $fakeUser);

        $resp = $this->callPrivateMethod($this->controller, "changeInformation");

        $this->assertEquals(0, $resp["result"]);
        $this->assertEquals("Vietnamese name only has letters and space", $resp["msg"]);
    }


    //Số điện thoại < 10 số
    public function test_should_return_error_if_phone_too_short()
    {
        $_POST["phone"] = "0912";
        $_POST["name"] = "Nguyen Van A";
        $fakeUser = new FakeAuthUser(["id" => 1, "active" => 1]);
        $this->controller->setVariable("AuthUser", $fakeUser);

        $resp = $this->callPrivateMethod($this->controller, "changeInformation");

        $this->assertEquals(0, $resp["result"]);
        $this->assertEquals("Phone number has at least 10 number !", $resp["msg"]);
    }

    //Số điện thoại không hợp lệ 
    public function test_should_return_error_if_phone_is_not_valid()
    {
        $_POST["phone"] = "abc123xyzx";
        $_POST["name"] = "Nguyen Van A";
        $fakeUser = new FakeAuthUser(["id" => 1, "active" => 1]);
        $this->controller->setVariable("AuthUser", $fakeUser);

        $resp = $this->callPrivateMethod($this->controller, "changeInformation");

        $this->assertEquals(0, $resp["result"]);
        $this->assertEquals("This is not a valid phone number. Please, try again !", $resp["msg"]);
    }

    //Thay đổi thông tin thành công
    public function test_should_change_information_successfully()
    {
        $_POST["phone"] = "0912345678";
        $_POST["name"] = "Nguyen Van A";
        $fakeUser = new FakeAuthUser(["id" => 1, "active" => 1]);
        $this->controller->setVariable("AuthUser", $fakeUser);

        $resp = $this->callPrivateMethod($this->controller, "changeInformation");

        $this->assertEquals(1, $resp["result"]);
        $this->assertEquals("Your personal information has been updated successfully !", $resp["msg"]);
        $this->assertEquals("Nguyen Van A", $resp["data"]["name"]);
        $this->assertEquals("0912345678", $resp["data"]["phone"]);
    }

}
