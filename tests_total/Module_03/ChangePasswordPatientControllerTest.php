<?php
use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;


class ChangePasswordPatientControllerTest extends TestCase
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

    private function callChangePassword($controller)
    {
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('changePassword');
        $method->setAccessible(true);
        try {
            $method->invoke($controller);
        } catch (\Exception $e) {
            if ($e->getMessage() !== '__EXIT__') throw $e;
        }
    }


    //Thiếu currentPassword, newPassword, hoặc confirmPassword
    public function test_should_return_error_when_missing_fields()
    {
        $this->controller->setVariable("AuthUser", new FakeAuthUser(1));

        $_POST = []; // thiếu hết

        $this->callChangePassword($this->controller);
        $response = json_decode($this->controller->output, true);

        $this->assertEquals(0, $response['result']);
        $this->assertStringContainsString("Missing field", $response['msg']);
    }

    //currentPassword không đúng
    public function test_should_return_error_if_current_password_incorrect()
    {
        self::$qb->table('tn_patients')->where('phone', '0911222333')->delete();
        self::$qb->table('tn_patients')->insert([
            'phone' => '0911222333',
            'password' => password_hash('realpass', PASSWORD_DEFAULT),
            'name' => 'Test User',
            'gender' => 1,
            'create_at' => date("Y-m-d H:i:s"),
            'update_at' => date("Y-m-d H:i:s"),
        ]);

        $user = self::$qb->table('tn_patients')->where('phone', '0911222333')->first();
        $id = $user->id;

        // Giả lập đăng nhập
        $fakeUser = new FakeAuthUser([
            "id" => $id,
            "phone" => "0911222333",
            "name" => "Test User",
            "gender" => 1,
        ]);
        $this->controller->setVariable("AuthUser", $fakeUser);

        $_POST['currentPassword'] = 'wrongpass';
        $_POST['newPassword'] = 'newpass123';
        $_POST['confirmPassword'] = 'newpass123';

        $this->callChangePassword($this->controller);

        $response = json_decode($this->controller->output, true);

        $this->assertEquals(0, $response['result']);
        $this->assertEquals('Your current password is incorrect. Try again !', $response['msg']);
    }


    //password quá ngắn
    public function test_should_return_error_when_new_password_is_too_short()
    {
        self::$qb->table('tn_patients')->where('phone', '0911222333')->delete();
        self::$qb->table('tn_patients')->insert([
            'phone' => '0911222333',
            'password' => password_hash('realpass', PASSWORD_DEFAULT),
            'name' => 'Test User',
            'gender' => 1,
            'create_at' => date("Y-m-d H:i:s"),
            'update_at' => date("Y-m-d H:i:s"),
        ]);

        $user = self::$qb->table('tn_patients')->where('phone', '0911222333')->first();
        $id = $user->id;

        // Giả lập đăng nhập
        $fakeUser = new FakeAuthUser([
            "id" => $id,
            "phone" => "0911222333",
            "name" => "Test User",
            "gender" => 1,
        ]);
        $this->controller->setVariable("AuthUser", $fakeUser);

        $_POST['currentPassword'] = 'realpass';
        $_POST['newPassword'] = '123';
        $_POST['confirmPassword'] = '123';

        $this->callChangePassword($this->controller);
        $response = json_decode($this->controller->output, true);

        $this->assertEquals(0, $response['result']);
        $this->assertEquals("Password must be at least 6 character length!", $response['msg']);
    }
    

    //newpassword và confirmpassword không giống nhau
    public function test_should_return_error_when_password_confirmation_does_not_match()
    {
        self::$qb->table('tn_patients')->where('phone', '0911222333')->delete();
        self::$qb->table('tn_patients')->insert([
            'phone' => '0911222333',
            'password' => password_hash('realpass', PASSWORD_DEFAULT),
            'name' => 'Test User',
            'gender' => 1,
            'create_at' => date("Y-m-d H:i:s"),
            'update_at' => date("Y-m-d H:i:s"),
        ]);

        $user = self::$qb->table('tn_patients')->where('phone', '0911222333')->first();
        $id = $user->id;

        // Giả lập đăng nhập
        $fakeUser = new FakeAuthUser([
            "id" => $id,
            "phone" => "0911222333",
            "name" => "Test User",
            "gender" => 1,
        ]);
        $this->controller->setVariable("AuthUser", $fakeUser);  

        $_POST['currentPassword'] = 'realpass';
        $_POST['newPassword'] = 'abcdef';
        $_POST['confirmPassword'] = 'xyz123';

        $this->callChangePassword($this->controller);
        $response = json_decode($this->controller->output, true);

        $this->assertEquals(0, $response['result']);
        $this->assertEquals("Password confirmation does not equal to new password !", $response['msg']);
    }

    //đổi mật khẩu thành công
    public function test_should_change_password_successfully()
    {
        self::$qb->table('tn_patients')->where('phone', '0911222333')->delete();
        self::$qb->table('tn_patients')->insert([
            'phone' => '0911222333',
            'password' => password_hash('realpass', PASSWORD_DEFAULT),
            'name' => 'Test User',
            'gender' => 1,
            'create_at' => date("Y-m-d H:i:s"),
            'update_at' => date("Y-m-d H:i:s"),
        ]);

        $user = self::$qb->table('tn_patients')->where('phone', '0911222333')->first();
        $id = $user->id;

        // Giả lập đăng nhập
        $fakeUser = new FakeAuthUser([
            "id" => $id,
            "phone" => "0911222333",
            "name" => "Test User",
            "gender" => 1,
        ]);
        $this->controller->setVariable("AuthUser", $fakeUser);  

        $_POST['currentPassword'] = 'realpass';
        $_POST['newPassword'] = 'abcdef';
        $_POST['confirmPassword'] = 'abcdef';

        $this->callChangePassword($this->controller);
        $response = json_decode($this->controller->output, true);

        $this->assertEquals(1, $response['result']);
        $this->assertEquals("New password has been updated successfully. Don't forget to login again !", $response['msg']);
        $this->assertEquals($id, $response['data']['id']);
    }


    //Mật khẩu cũ trùng với mật khẩu mới
    public function test_should_return_error_if_new_password_is_same_as_current()
    {
        // Xóa trước để test sạch
        self::$qb->table('tn_patients')->where('phone', '0911222333')->delete();

        // Thêm user với mật khẩu là 'samepass'
        self::$qb->table('tn_patients')->insert([
            'phone' => '0911222333',
            'password' => password_hash('samepass', PASSWORD_DEFAULT),
            'name' => 'Test User',
            'gender' => 1,
            'create_at' => date("Y-m-d H:i:s"),
            'update_at' => date("Y-m-d H:i:s"),
        ]);

        $user = self::$qb->table('tn_patients')->where('phone', '0911222333')->first();
        $id = $user->id;

        $fakeUser = new FakeAuthUser([
            "id" => $id,
            "phone" => "0911222333",
            "name" => "Test User",
            "gender" => 1,
        ]);
        $this->controller->setVariable("AuthUser", $fakeUser);

        $_POST = [
            'currentPassword' => 'samepass',
            'newPassword' => 'samepass',
            'confirmPassword' => 'samepass'
        ];

        $this->callChangePassword($this->controller);
        $response = json_decode($this->controller->output, true);

        $this->assertEquals(0, $response['result']);
        $this->assertEquals("New password must be different from current password!", $response['msg']);
    }
}
