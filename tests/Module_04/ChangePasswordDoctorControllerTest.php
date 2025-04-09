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
if (!function_exists('__')) {
    function __($str) {
        return $str;
    }
}

require_once __DIR__ . '/../../api/app/core/DataEntry.php';
require_once __DIR__ . '/../../api/app/core/Controller.php';
require_once __DIR__ . '/../../api/app/core/Input.php';
require_once __DIR__ . '/../../api/app/config/db.config.php';
require_once __DIR__ . '/../../api/app/core/DataList.php';
require_once __DIR__ . '/../../api/app/helpers/common.helper.php';
require_once __DIR__ . '/../../tests/Module_04/helper/TestableHelperController.php';
require_once __DIR__ . '/../../tests/Module_04/helper/FakeAuthUser.php';

class ChangePasswordDoctorControllerTest extends TestCase
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


    // chưa đăng nhập
    public function test_should_return_error_if_not_logged_in()
    {
        $this->controller->setVariable("AuthUser", null);

        $resp = $this->callPrivateMethod($this->controller, "changePassword");

        $this->assertEquals(0, $resp["result"]);
        $this->assertEquals("You does not log in !", $resp["msg"]);
    }

    // current password không đúng
    public function test_should_return_error_if_current_password_incorrect()
    {
        $this->setPostFields([
            "currentPassword" => "sai_pass",
            "newPassword" => "12345678",
            "confirmPassword" => "12345678"
        ]);

        $fakeUser = new FakeAuthUser(["id" => 1, "active" => 1]);
        $this->controller->setVariable("AuthUser", $fakeUser);

        $resp = $this->callPrivateMethod($this->controller, "changePassword");

        $this->assertEquals(0, $resp["result"]);
        $this->assertEquals("Your current password is incorrect. Try again !", $resp["msg"]);
    }


    // password mới quá ngắn
    public function test_should_return_error_if_new_password_too_short()
    {
        $this->setPostFields([
            "currentPassword" => "Tdl@20102003",
            "newPassword" => "1234",
            "confirmPassword" => "1234"
        ]);

        $fakeUser = new FakeAuthUser(["id" => 10, "active" => 1]);
        $this->controller->setVariable("AuthUser", $fakeUser);

        $resp = $this->callPrivateMethod($this->controller, "changePassword");

        $this->assertEquals(0, $resp["result"]);
        $this->assertStringContainsString("Password must be at least 6 character length!", $resp["msg"]);
    }


    // password confirmation không đúng
    public function test_should_return_error_if_password_confirmation_mismatch()
    {
        $this->setPostFields([
            "currentPassword" => "Tdl@20102003",
            "newPassword" => "123456",
            "confirmPassword" => "654321"
        ]);

        $fakeUser = new FakeAuthUser(["id" => 10, "active" => 1]);
        $this->controller->setVariable("AuthUser", $fakeUser);

        $resp = $this->callPrivateMethod($this->controller, "changePassword");

        $this->assertEquals(0, $resp["result"]);
        $this->assertStringContainsString("Password confirmation does not equal to new password !", $resp["msg"]);
    }

    // thay đổi password thành công
    public function test_should_change_password_successfully()
    {
        $this->setPostFields([
            "currentPassword" => "Tdl@20102003",
            "newPassword" => "newpassword123",
            "confirmPassword" => "newpassword123"
        ]);

        $fakeUser = new FakeAuthUser(["id" => 10, "active" => 1]);
        $this->controller->setVariable("AuthUser", $fakeUser);

        $resp = $this->callPrivateMethod($this->controller, "changePassword");

        $this->assertEquals(1, $resp["result"]);
        $this->assertEquals("New password has been updated successfully. Don't forget to login again !", $resp["msg"]);
        $this->assertArrayHasKey("data", $resp);
    }
}
