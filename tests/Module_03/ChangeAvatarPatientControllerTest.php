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
if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', __DIR__ . '/../../tests/Module_03');
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
require_once __DIR__ . '/../../tests/Module_03/helper/TestableHelperController.php';
require_once __DIR__ . '/../../tests/Module_03/helper/FakeAuthUser.php';

class ChangeAvatarPatientControllerTest extends TestCase
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

        $this->controller = new TestableHelperController();
        $this->routeMock = $this->createMock(stdClass::class);
        $this->controller->mockRoute = $this->routeMock;
    }

    private function callChangeAvatar($controller)
    {
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('changeAvatar');
        $method->setAccessible(true);
        try {
            $method->invoke($controller);
        } catch (\Exception $e) {
            if ($e->getMessage() !== '__EXIT__') throw $e;
        }
    }

    public function createTestUser()
    {
        $phone = '0911222333';

        self::$qb->table('tn_patients')->where('phone', $phone)->delete();

        self::$qb->table('tn_patients')->insert([
            'phone' => $phone,
            'password' => password_hash('realpass', PASSWORD_DEFAULT),
            'name' => 'Test User',
            'gender' => 1,
            'create_at' => date("Y-m-d H:i:s"),
            'update_at' => date("Y-m-d H:i:s"),
        ]);

        return self::$qb->table('tn_patients')->where('phone', $phone)->first();
    }

    //Không gửi file nào lên
    public function test_should_return_error_if_no_file_uploaded()
    {
        $fakeUser = new FakeAuthUser(1);
        $this->controller->setVariable("AuthUser", $fakeUser);

        $_FILES = []; 

        $this->callChangeAvatar($this->controller);
        $response = json_decode($this->controller->output, true);

        $this->assertEquals(0, $response['result']);
        $this->assertEquals("Photo is not received !", $response['msg']);
    }


    //File sai định dạng
    public function test_should_return_error_if_file_extension_is_invalid()
    {
        $fakeUser = new FakeAuthUser(1);
        $this->controller->setVariable("AuthUser", $fakeUser);

        $_FILES["file"] = [
            "name" => "testfile.exe",
            "type" => "application/octet-stream",
            "tmp_name" => "/tmp/php12345",
            "error" => 0,
            "size" => 1234,
        ];

        $this->callChangeAvatar($this->controller);
        $response = json_decode($this->controller->output, true);

        $this->assertEquals(0, $response['result']);
        $this->assertStringContainsString("Only jpeg,jpg,png files are allowed", $response['msg']);
    }


    //upload file thành công
    public function test_should_upload_valid_image_successfully()
    {
        // Arrange
        $controller = new TestableHelperController();

    
        $user = $this->createTestUser(); 
        $fakeAuthUser = new FakeAuthUser($user); 
        $controller->setVariable("AuthUser", $fakeAuthUser);

        // Tạo file upload giả
        $_FILES['file'] = [
            'name' => 'test_avatar.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => __DIR__ . '/test_avatar.jpg', 
            'error' => 0,
            'size' => filesize(__DIR__ . '/test_avatar.jpg'),
        ];

     
        try {
            $ref = new \ReflectionClass($controller);
            $method = $ref->getMethod("changeAvatar");
            $method->setAccessible(true);
            $method->invoke($controller);
        } catch (\Exception $e) {
            if ($e->getMessage() !== "__EXIT__") {
                throw $e; 
            }
        }

        $response = json_decode($controller->output, true);
        $this->assertEquals(1, $response['result']);
        $this->assertEquals("Avatar has been updated successfully !", $response['msg']);
    }
}
