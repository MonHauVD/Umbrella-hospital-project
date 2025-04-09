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
require_once __DIR__ . '/../../api/app/helpers/common.helper.php';
require_once __DIR__ . '/../../api/app/controllers/LoginWithGoogleController.php';
require_once __DIR__ . '/../../tests/Module_03/helper/TestableHelperController.php';

class LoginWithGoogleControllerTest extends TestCase
{
    protected static $db;
    protected static $qb;

    private $controller;
    private $routeMock;
    private $authMock;
    public static function setUpBeforeClass(): void
    {
        // Khởi tạo Pixie Connection
        $config = require __DIR__ . '/../../LocalConfigDB.php';
        self::$db = new Connection('mysql', $config, 'DB');
        self::$qb = self::$db->getQueryBuilder();
    }

    public function tearDown(): void{
        // Rollback transaction sau mỗi test case
        self::$db->getPdoInstance()->rollback();
    }

    public function setUp(): void
    {
        // Bắt đầu transaction trước mỗi test case
        self::$db->getPdoInstance()->beginTransaction();

         // Create an instance of the controller
        $this->controller = $this->getMockBuilder(LoginWithGoogleController::class)
                                  ->setMethods(['getVariable', 'jsonecho']) // Mocking getVariable
                                  ->getMock();


         // Create a mock for the Route class (assuming it's a class)
         $this->authMock = $this->createMock(stdClass::class);
         $this->routeMock = $this->createMock(stdClass::class);
 
         // Mock the getVariable method to return the routeMock
        //  $this->controller->method('getVariable')->willReturn($this->routeMock);
        $this->controller->method('getVariable')
                 ->will($this->returnCallback(function($name) {
                     if ($name === "Route") {
                         return $this->routeMock;
                     } elseif ($name === "AuthUser") {
                         return $this->authMock;
                     }
                     return null;
                 }));
                 
        //Stop code when calling jsonecho method
        // $this->controller->method('jsonecho')->will($this->returnCallback(function() {
        //     return; // Ngừng thực thi phần còn lại của hàm
        // }));
        // Cho phép test đọc được resp thông qua Reflection
        $controller = $this->controller;
        $this->controller->method('jsonecho')->will($this->returnCallback(function () use ($controller) {
            $reflection = new ReflectionClass($controller);
            $prop = $reflection->getProperty('resp');
            $prop->setAccessible(true);
            echo json_encode($prop->getValue($controller));
        }));
    }


    //Thiếu input(type)
    public function test_missing_type_should_return_error()
    {
        $controller = new LoginWithGoogleHelperController();

        $_POST = [
            'email' => 'test@example.com',
            'password' => '123456'
        ];

        try {
            $controller->process();
        } catch (\Exception $e) {
            if ($e->getMessage() !== '__EXIT__') throw $e;
        }

        $response = json_decode($controller->output, true);

        $this->assertEquals(0, $response['result']);
        $this->assertEquals('Missing field type', $response['msg']);
    }   

    //Type không hợp lệ
    public function test_invalid_type_should_return_error()
    {
        $controller = new LoginWithGoogleHelperController();

        $_POST = [
            'type' => 'admin',
            'email' => 'test@example.com',
            'password' => '123456'
        ];

        try {
            $controller->process();
        } catch (\Exception $e) {
            if ($e->getMessage() !== '__EXIT__') throw $e;
        }

        $response = json_decode($controller->output, true);

        $this->assertEquals(0, $response['result']);
        $this->assertEquals("Your request's type is admin & it's not valid !", $response['msg']);
    }

    //Tạo tài khoản mới nếu chưa tồn tại
    public function test_create_new_account_if_not_exist()
    {
        // Giả lập Patient model không tồn tại -> tạo mới
        $mockQueryBuilder = $this->createMock(QueryBuilderHandler::class);

        // giả lập kết quả khi gọi where()
        $mockQueryBuilder->method('where')
                        ->with('email', '=', 'test@example.com')
                        ->willReturn($mockQueryBuilder); 

        $mockQueryBuilder->method('first')
                 ->willReturn(null);

        $controller = new LoginWithGoogleHelperController();

        $_POST = [
            'type' => 'patient',
            'email' => 'newpatient@example.com',
            'password' => '123456'
        ];

        try {
            $controller->process();
        } catch (\Exception $e) {
            if ($e->getMessage() !== '__EXIT__') throw $e;
        }

        $response = json_decode($controller->output, true);

        $this->assertEquals(1, $response['result']);
        $this->assertEquals('Patient account has been CREATE successfully', $response['msg']);
        $this->assertArrayHasKey('accessToken', $response);
    }

    //Tài khoản đã tồn tại
    public function test_login_success_with_existing_account()
    {
        $email = 'existing_user@example.com';
        $password = '123456';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
        // Chèn dữ liệu test vào bảng patients
        self::$qb->table('tn_patients')->insert([
            'email' => $email,
            'password' => $hashedPassword,
            'phone' => '',
            'name' => 'Test User',
            'gender' => 1,
            'birthday' => '',
            'address' => '',
            'avatar' => 'default_avatar.jpg',
            'create_at' => date('Y-m-d H:i:s'),
            'update_at' => date('Y-m-d H:i:s')
        ]);
    
        $controller = new LoginWithGoogleHelperController();
    
        $_POST = [
            'type' => 'patient',
            'email' => $email,
            'password' => $password
        ];
    
        try {
            $controller->process();
        } catch (\Exception $e) {
            if ($e->getMessage() !== '__EXIT__') throw $e;
        }
    
        $response = json_decode($controller->output, true);
    
        $this->assertEquals(1, $response['result']);
        $this->assertEquals('Patient has been LOGGED IN successfully !', $response['msg']);
        $this->assertArrayHasKey('accessToken', $response);
    }

    // Đăng nhập thất bại do sai mật khẩu
    public function test_login_fail_with_wrong_password()
    {
        $controller = new LoginWithGoogleHelperController();

        $email = 'wrongpass@example.com';
        $password = 'correct_password';
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Chèn dữ liệu test vào bảng patients
        $patient = Controller::model("Patient", $email);
        if (!$patient->isAvailable()) {
            $now = date("Y-m-d H:i:s");
            $patient->set("email", $email)
                    ->set("password", $hashed)
                    ->set("phone", "")
                    ->set("name", $email)
                    ->set("gender", 1)
                    ->set("birthday", "")
                    ->set("address", "")
                    ->set("avatar", "default_avatar.jpg")
                    ->set("create_at", $now)
                    ->set("update_at", $now)
                    ->save();
        }

        $_POST = [
            'type' => 'patient',
            'email' => $email,
            'password' => 'wrong_password' // mật khẩu sai
        ];

        try {
            $controller->process();
        } catch (\Exception $e) {
            if ($e->getMessage() !== '__EXIT__') throw $e;
        }

        $response = json_decode($controller->output, true);

        $this->assertEquals(0, $response['result']);
        $this->assertEquals("Your email or password is incorrect. Try again !", $response['msg']);
    }


}
