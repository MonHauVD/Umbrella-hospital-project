<?php

// namespace App\Tests\Models;
// use App\Models\DoctorModel; 
// use App\Core\DataEntry;

use PHPUnit\Framework\TestCase;
// use PDO;

require_once __DIR__ . '/../../api/app/core/DataEntry.php';
require_once __DIR__ . '/../../api/app/models/DoctorModel.php';
use Illuminate\Database\Capsule\Manager as DB;



class DoctorModelTest extends TestCase
{
    protected static $pdo;
    protected $transactionStarted = false;

    
    public static function setUpBeforeClass(): void
    {
        // Kết nối tới DB test
        self::$pdo = new PDO('mysql:host=localhost;dbname=doantotnghiep', 'root', '');
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    protected function setUp(): void
    {
        // Bắt đầu transaction
        self::$pdo->beginTransaction();
        $this->transactionStarted = true;
    }

    protected function tearDown(): void
    {
        // Rollback để giữ nguyên dữ liệu
        if ($this->transactionStarted) {
            self::$pdo->rollBack();
        }
    }

    public function testInsertDoctor()
    {
        $doctor = new DoctorModel();
        $doctor->set("email", "unit_test@example.com");
        $doctor->set("phone", "0123456789");
        $doctor->set("name", "Unit Test");
        $doctor->set("password", "123456");
        $doctor->set("description", "Unit test description");
        $doctor->set("price", 500);
        $id = $doctor->insert();

        // Kiểm tra output
        $this->assertIsNumeric($id);
        $this->assertGreaterThan(0, $id);

        // Kiểm tra DB
        $stmt = self::$pdo->prepare("SELECT * FROM " . TABLE_PREFIX . TABLE_DOCTORS . " WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($result);
        $this->assertEquals("unit_test@example.com", $result["email"]);
        $this->assertEquals("Unit Test", $result["name"]);
    }

    public function testUpdateDoctor()
    {
        // Tạo trước doctor
        $doctor = new DoctorModel();
        $doctor->set("email", "update_test@example.com");
        $doctor->set("phone", "0111222333");
        $doctor->set("name", "Update Test");
        $doctor->set("password", "123456");
        $doctor->insert();

        // Thực hiện update
        $doctor->set("name", "Updated Name");
        $doctor->update();

        // Kiểm tra DB
        $stmt = self::$pdo->prepare("SELECT name FROM " . TABLE_PREFIX . TABLE_DOCTORS . " WHERE id = ?");
        $stmt->execute([$doctor->get("id")]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals("Updated Name", $result["name"]);
    }

    public function testDeleteDoctor()
    {
        // Tạo trước doctor
        $doctor = new DoctorModel();
        $doctor->set("email", "delete_test@example.com");
        $doctor->set("phone", "0999888777");
        $doctor->set("name", "Delete Test");
        $doctor->set("password", "123456");
        $doctor->insert();

        // Xóa doctor
        $result = $doctor->delete();
        $this->assertTrue($result);

        // Kiểm tra DB
        $stmt = self::$pdo->prepare("SELECT * FROM " . TABLE_PREFIX . TABLE_DOCTORS . " WHERE id = ?");
        $stmt->execute([$doctor->get("id")]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertFalse($result);
    }

    public function testSelectDoctorById()
    {
        // Insert trước doctor
        $doctor = new DoctorModel();
        $doctor->set("email", "select_test@example.com");
        $doctor->set("phone", "0777888999");
        $doctor->set("name", "Select Test");
        $doctor->set("password", "123456");
        $id = $doctor->insert();

        // Gọi select
        $selectedDoctor = new DoctorModel($id);

        // Kiểm tra output
        $this->assertTrue($selectedDoctor->isAvailable());
        $this->assertEquals("Select Test", $selectedDoctor->get("name"));
    }

    public function testSelectDoctorByEmail()
    {
        // Insert trước doctor
        $doctor = new DoctorModel();
        $doctor->set("email", "email_test@example.com");
        $doctor->set("phone", "0555666777");
        $doctor->set("name", "Email Test");
        $doctor->set("password", "123456");
        $doctor->insert();

        // Gọi select
        $selectedDoctor = new DoctorModel("email_test@example.com");

        // Kiểm tra output
        $this->assertTrue($selectedDoctor->isAvailable());
        $this->assertEquals("Email Test", $selectedDoctor->get("name"));
    }
}

?>
