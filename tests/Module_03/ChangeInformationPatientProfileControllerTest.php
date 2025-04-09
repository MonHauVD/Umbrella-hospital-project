<?php
use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

if (!defined('APPPATH')) {
    define('APPPATH', realpath(__DIR__ . '/../app'));
}

if (!defined('EC_SALT')) {
    define('EC_SALT', 'your_test_secret_key_here');
}

// Load core và controller
require_once __DIR__ . '/../app/core/DataEntry.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Input.php';
require_once __DIR__ . '/../app/config/db.config.php';
require_once __DIR__ . '/../app/core/DataList.php';
require_once __DIR__ . '/../app/helpers/common.helper.php';
require_once __DIR__ . '/../app/controllers/PatientProfileController.php';
require_once __DIR__ . '/../tests/helper/TestableHelperController.php';
require_once __DIR__ . '/../tests/helper/FakeAuthUser.php';

class ChangeInformationPatientProfileControllerTest extends TestCase
{
    protected static $db;
    protected static $qb;

    private $controller;
    private $routeMock;

    public static function setUpBeforeClass(): void
    {
        $config = [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'doantotnghiep',
            'username'  => 'mysql',
            'password'  => '12345',
            'charset'   => 'utf8',
            'options'   => [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        ];
        self::$db = new Connection('mysql', $config, 'DB');
        self::$qb = self::$db->getQueryBuilder();
    }

    public function setUp(): void
    {
        self::$db->getPdoInstance()->beginTransaction();

        // override
        $this->controller = new TestableHelperController();

        // Tạo mock cho route
        $this->routeMock = $this->createMock(stdClass::class);
        $this->controller->mockRoute = $this->routeMock;
    }

    public function tearDown(): void
    {
        self::$db->getPdoInstance()->rollback();
    }

    public function testChangeInformationSuccess()
    {
        require_once APPPATH . "/models/PatientsModel.php";
        $patients = new PatientsModel();
        $patients->where("id", ">", 0)->orderBy("id", "asc")->fetchData();
        $data = $patients->getData();
        $this->assertNotEmpty($data);

        // Fake AuthUser từ database
        $patient = new FakeAuthUser($data[0]);
        $this->controller->mockAuthUser = $patient;

        ob_start();
        print_r($patient);
        $outputDebug = ob_get_clean();
        fwrite(STDOUT, "\nPatient data:\n" . $outputDebug . "\n");

        // Gán POST giả lập
        $_POST["name"] = "Nguyễn Văn A";
        $_POST["birthday"] = "01-01-2000";
        $_POST["address"] = "123 đường ABC";
        $_POST["gender"] = "1";

        try {
            $reflection = new ReflectionClass($this->controller);
            $method = $reflection->getMethod("changeInformation");
            $method->setAccessible(true);
            $method->invoke($this->controller);
        } catch (\Exception $e) {
            if ($e->getMessage() !== "__EXIT__") {
                throw $e;
            }
        }
        //check thong tin 
        echo "Raw JSON output: \n" . $this->controller->output . "\n\n";
        $output = json_decode($this->controller->output, true);
        print_r($output);

        $this->assertEquals(1, $output["result"]);
        $this->assertEquals("Your personal information has been updated successfully !", $output["msg"]);
        $this->assertEquals("Nguyễn Văn A", $output["data"]["name"]);

        unset($_POST["name"], $_POST["birthday"], $_POST["address"], $_POST["gender"]);
    }


    //Tên không hợp lệ
    public function testChangeInformationWithInvalidName()
    {
        // Lấy dữ liệu thật từ database
        require_once APPPATH . "/models/PatientsModel.php";
        $patients = new PatientsModel();
        $patients->where("id", ">", 0)->orderBy("id", "asc")->fetchData();
        $data = $patients->getData();
        $this->assertNotEmpty($data, "Không có dữ liệu bệnh nhân trong database");

        // Tạo user giả
        $patient = new FakeAuthUser($data[0]);

        // Gán AuthUser và Route giả lập
        $this->controller->setVariable("AuthUser", $patient);
        $this->controller->setVariable("Route", $this->routeMock);

        // Gán POST sai tên (có số)
        $_POST["name"] = "Nguyễn Văn 123";
        $_POST["birthday"] = "01-01-2000";
        $_POST["address"] = "123 đường ABC";
        $_POST["gender"] = "1";

        // Gọi hàm changeInformation() bằng Reflection và bắt output
        ob_start();
        try {
            $method = (new ReflectionClass($this->controller))->getMethod("changeInformation");
            $method->setAccessible(true);
            $method->invoke($this->controller);
        } catch (\Exception $e) {
            if ($e->getMessage() !== "__EXIT__") {
                throw $e;
            }
        }
        ob_end_clean(); // dọn sạch buffer để tránh rác
        $output = json_decode($this->controller->output, true);

        // Kiểm tra kết quả trả về
        $this->assertIsArray($output);
        $this->assertArrayHasKey("msg", $output);
        $this->assertEquals("Vietnamese name only has letters and space", $output["msg"]);
        $this->assertEquals(0, $output["result"]);

        // Dọn dẹp biến POST
        unset($_POST["name"], $_POST["birthday"], $_POST["address"], $_POST["gender"]);
    }

    //Thiếu trường yêu cầu 
    public function testChangeInformationWithMissingName()
    {
        require_once APPPATH . "/models/PatientsModel.php";
        $patients = new PatientsModel();
        $patients->where("id", ">", 0)->orderBy("id", "asc")->fetchData();
        $data = $patients->getData();
        $this->assertNotEmpty($data);

        $patient = new FakeAuthUser($data[0]);
        $this->controller->setVariable("AuthUser", $patient);
        $this->controller->setVariable("Route", $this->routeMock);

        $_POST["birthday"] = "01-01-2000";
        $_POST["address"] = "123 đường ABC";

        ob_start();
        try {
            $method = (new ReflectionClass($this->controller))->getMethod("changeInformation");
            $method->setAccessible(true);
            $method->invoke($this->controller);
        } catch (\Exception $e) {
            if ($e->getMessage() !== "__EXIT__") throw $e;
        }
        ob_end_clean();

        $output = json_decode($this->controller->output, true);
        $this->assertEquals("Missing field: name", $output["msg"]);
        $this->assertEquals(0, $output["result"]);
    }

    //Giới tính không hợp lệ
    public function testChangeInformationWithInvalidGender()
    {
        require_once APPPATH . "/models/PatientsModel.php";
        $patients = new PatientsModel();
        $patients->where("id", ">", 0)->orderBy("id", "asc")->fetchData();
        $data = $patients->getData();
        $this->assertNotEmpty($data);

        $patient = new FakeAuthUser($data[0]);
        $this->controller->setVariable("AuthUser", $patient);
        $this->controller->setVariable("Route", $this->routeMock);

        $_POST["name"] = "Nguyễn Văn A";
        $_POST["birthday"] = "01-01-2000";
        $_POST["address"] = "123 đường ABC";
        $_POST["gender"] = "5"; // không hợp lệ

        ob_start();
        try {
            $method = (new ReflectionClass($this->controller))->getMethod("changeInformation");
            $method->setAccessible(true);
            $method->invoke($this->controller);
        } catch (\Exception $e) {
            if ($e->getMessage() !== "__EXIT__") throw $e;
        }
        ob_end_clean();

        $output = json_decode($this->controller->output, true);
        $this->assertEquals("Gender value is not correct. There are 2 values: 0 is female & 1 is man", $output["msg"]);
        $this->assertEquals(0, $output["result"]);
    }

    //Ngày sinh không hợp lệ
    public function testChangeInformationWithInvalidBirthday()
    {
        require_once APPPATH . "/models/PatientsModel.php";
        $patients = new PatientsModel();
        $patients->where("id", ">", 0)->orderBy("id", "asc")->fetchData();
        $data = $patients->getData();
        $this->assertNotEmpty($data);

        $patient = new FakeAuthUser($data[0]);
        $this->controller->setVariable("AuthUser", $patient);
        $this->controller->setVariable("Route", $this->routeMock);

        $_POST["name"] = "Nguyễn Văn A";
        $_POST["birthday"] = "31-02-2020"; // sai ngày
        $_POST["address"] = "123 đường ABC";
        $_POST["gender"] = "1";

        ob_start();
        try {
            $method = (new ReflectionClass($this->controller))->getMethod("changeInformation");
            $method->setAccessible(true);
            $method->invoke($this->controller);
        } catch (\Exception $e) {
            if ($e->getMessage() !== "__EXIT__") throw $e;
        }
        ob_end_clean();

        $output = json_decode($this->controller->output, true);
        $this->assertStringContainsString("does not exist", $output["msg"]);
        $this->assertEquals(0, $output["result"]);
    }

    //Sinh nhật ở tương lai (sai logic năm)
    public function testChangeInformationWithFutureBirthday()
    {
        require_once APPPATH . "/models/PatientsModel.php";
        $patients = new PatientsModel();
        $patients->where("id", ">", 0)->orderBy("id", "asc")->fetchData();
        $data = $patients->getData();
        $this->assertNotEmpty($data);

        $patient = new FakeAuthUser($data[0]);
        $this->controller->setVariable("AuthUser", $patient);
        $this->controller->setVariable("Route", $this->routeMock);

        $nextYear = (int)date("Y") + 1;
        $_POST["name"] = "Nguyễn Văn A";
        $_POST["birthday"] = "01-01-$nextYear";
        $_POST["address"] = "123 đường ABC";
        $_POST["gender"] = "1";

        ob_start();
        try {
            $method = (new ReflectionClass($this->controller))->getMethod("changeInformation");
            $method->setAccessible(true);
            $method->invoke($this->controller);
        } catch (\Exception $e) {
            if ($e->getMessage() !== "__EXIT__") throw $e;
        }
        ob_end_clean();

        $output = json_decode($this->controller->output, true);
        $this->assertStringContainsString("birthday is not valid", $output["msg"]);
        $this->assertEquals(0, $output["result"]);
    }

    //Địa chỉ không hợp lệ
    public function testChangeInformationWithInvalidAddress()
    {
        require_once APPPATH . "/models/PatientsModel.php";
        $patients = new PatientsModel();
        $patients->where("id", ">", 0)->orderBy("id", "asc")->fetchData();
        $data = $patients->getData();
        $this->assertNotEmpty($data);

        $patient = new FakeAuthUser($data[0]);
        $this->controller->setVariable("AuthUser", $patient);
        $this->controller->setVariable("Route", $this->routeMock);

        $_POST["name"] = "Nguyễn Văn A";
        $_POST["birthday"] = "01-01-2000";
        $_POST["address"] = "123 đường ABC!@#"; // Ký tự đặc biệt
        $_POST["gender"] = "1";

        ob_start();
        try {
            $method = (new ReflectionClass($this->controller))->getMethod("changeInformation");
            $method->setAccessible(true);
            $method->invoke($this->controller);
        } catch (\Exception $e) {
            if ($e->getMessage() !== "__EXIT__") throw $e;
        }
        ob_end_clean();

        $output = json_decode($this->controller->output, true);
        $this->assertEquals("Address only accepts letters, space & number", $output["msg"]);
        $this->assertEquals(0, $output["result"]);
    }


}