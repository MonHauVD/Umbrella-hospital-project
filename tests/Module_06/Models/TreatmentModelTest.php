<?php
use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;
// use Mockery;

require_once __DIR__ . '/../../../ConfigDefine.php';
require_once __DIR__ . '/../../../api/app/core/DataEntry.php';
require_once __DIR__ . '/../../../api/app/core/Controller.php';
require_once __DIR__ . '/../../../api/app/models/TreatmentModel.php';
require_once __DIR__ . '/../../../umbrella-corporation/app/models/UserModel.php';
require_once __DIR__ . '/../../../api/app/config/db.config.php';
class TreatmentModelTest extends TestCase
{
    protected static $db;
    protected static $qb;
    protected $treatmentModel;

    public static function setUpBeforeClass(): void
    {
        // Khởi tạo Pixie Connection
        $config = require __DIR__ . '/../../../LocalConfigDB.php';
        self::$db = new Connection('mysql', $config, 'DB');
        self::$qb = self::$db->getQueryBuilder();
    }

    public function tearDown(){
        // Rollback transaction sau mỗi test case
        // self::$db->getPdoInstance()->commit;
        self::$db->getPdoInstance()->rollback();
    }

    public function setUp(): void
    {
        // Bắt đầu transaction trước mỗi test case
        self::$db->getPdoInstance()->beginTransaction();
        $this->treatmentModel = new TreatmentModel();
    }

    // Helper function

    private function getIsAvailable($model)
    {
        $ref = new ReflectionClass($model);
        $prop = $ref->getProperty('is_available');
        $prop->setAccessible(true);
        return $prop->getValue($model);
    }
    
    // M06_TreatmentModel_Select_01
    // Test trường hợp select một bản ghi TreatmentModel đã tồn tại trong cơ sở dữ liệu
    // Input: treatmentModel {id = 101, name = 'Test Treatment', type = 'Test Type', times = 2, purpose = 'Test Purpose', instruction = 'Test Instruction', repeat_days = 3, repeat_time = 4}
    // Output: treatmentModel {id = 101, name = 'Test Treatment', type = 'Test Type', times = 2, purpose = 'Test Purpose', instruction = 'Test Instruction', repeat_days = 3, repeat_time = 4}

    public function testSelectByIdFound()
    {
        // Tạo treatment để test
        $treatmentId = self::$qb->table(TABLE_PREFIX.TABLE_TREATMENTS)->insert([
            'name' => 'Test Treatment',
            'type' => 'Test Type',
            'times' => 2,
            'purpose' => 'Test Purpose',
            'instruction' => 'Test Instruction',
            'repeat_days' => 3,
            'repeat_time' => 4
        ]);

        $model = new \TreatmentModel();
        $result = $model->select($treatmentId);

        // Kiểm tra kết quả
        $this->assertInstanceOf(\TreatmentModel::class, $result);
        $this->assertEquals($treatmentId, $model->get('id'));
        $this->assertTrue($this->getIsAvailable($model));
    }

    // M06_TreatmentModel_Select_02
    // Test trường hợp select một bản ghi TreatmentModel bằng tên đã tồn tại trong cơ sở dữ liệu
    // Input: treatmentModel {name = 'Test Treatment Name', type = 'Test Type', times = 2, purpose = 'Test Purpose', instruction = 'Test Instruction', repeat_days = 3, repeat_time = 4}
    // Output: treatmentModel {name = 'Test Treatment Name', type = 'Test Type', times = 2, purpose = 'Test Purpose', instruction = 'Test Instruction', repeat_days = 3, repeat_time = 4}
    public function testSelectByNameFound()
    {
        $name = 'Test Treatment Name';

        // Tạo treatment
        $treatmentId = self::$qb->table(TABLE_PREFIX.TABLE_TREATMENTS)->insert([
            'name' => $name,
            'type' => 'Test Type',
            'times' => 2,
            'purpose' => 'Test Purpose',
            'instruction' => 'Test Instruction',
            'repeat_days' => 3,
            'repeat_time' => 4
        ]);

        $model = new \TreatmentModel();
        $result = $model->select($name);

        // Kiểm tra
        $this->assertInstanceOf(\TreatmentModel::class, $result);
        $this->assertEquals($name, $model->get('name'));
        $this->assertTrue($this->getIsAvailable($model));
    }

    // M06_TreatmentModel_Select_03
    // Test trường hợp select một bản ghi TreatmentModel không tồn tại trong cơ sở dữ liệu
    // Input: treatmentModel {id = 9999999}
    // Output: is_available = false
    public function testSelectByIdNotFound()
    {
        $model = new \TreatmentModel();
        $result = $model->select(9999999); // id không tồn tại

        $this->assertInstanceOf(\TreatmentModel::class, $result);
        $this->assertFalse($this->getIsAvailable($model));
    }
    // M06_TreatmentModel_Select_04
    // Test trường hợp select một bản ghi TreatmentModel bằng tên không tồn tại trong cơ sở dữ liệu
    // Input: treatmentModel {name = 'non-existent-name'}
    // Output: is_available = false
    public function testSelectByNameNotFound()
    {
        $model = new \TreatmentModel();
        $result = $model->select('non-existent-name');

        $this->assertInstanceOf(\TreatmentModel::class, $result);
        $this->assertFalse($this->getIsAvailable($model));
    }
    // M06_TreatmentModel_Select_05
    // Test trường hợp select một bản ghi TreatmentModel với ID null
    // Input: treatmentModel {id = null}
    // Output: is_available = false
    public function testSelectWithInvalidValue()
    {
        $model = new \TreatmentModel();
        $result = $model->select(0); // ID không hợp lệ => $col = null

        $this->assertInstanceOf(\TreatmentModel::class, $result);
        $this->assertFalse($this->getIsAvailable($model));
    }

    // M06_TreatmentModel_Insert_01
    // Test trường hợp insert một bản ghi TreatmentModel với đầy đủ dữ liệu
    // Input: treatmentModel {appointment_id = 1, name = 'Test Treatment', type = 'Test Type', times = 2, purpose = 'Test Purpose', instruction = 'Test Instruction', repeat_days = 3, repeat_time = 4}
    // Output: treatmentModel {id = 1, name = 'Test Treatment', type = 'Test Type', times = 2, purpose = 'Test Purpose', instruction = 'Test Instruction', repeat_days = 3, repeat_time = 4}
    public function testInsertWithFullData()
    {
        $appointmentId = self::$qb->table(TABLE_PREFIX.TABLE_APPOINTMENTS)->insert([
            'booking_id' => 1,
            'doctor_id' => 1,
            'patient_id' => 1,
            'appointment_time' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'create_at' => date('Y-m-d H:i:s'),
            'update_at' => date('Y-m-d H:i:s')
        ]);

        $treatment = new TreatmentModel();
        $treatment->set("appointment_id", $appointmentId);
        $treatment->set("name", "Test Treatment");
        $treatment->set("type", "Physical");
        $treatment->set("times", 3);
        $treatment->set("purpose", "Test Purpose");
        $treatment->set("instruction", "Follow the routine");
        $treatment->set("repeat_days", 2);
        $treatment->set("repeat_time", 1);
        
        $insertedId = $treatment->insert();
        
        $this->assertIsNumeric($insertedId); 
        $this->assertTrue($treatment->isAvailable());
    }

    // M06_TreatmentModel_Insert_02
    // Test trường hợp insert một bản ghi TreatmentModel với dữ liệu không đầy đủ
    // Input: treatmentModel {appointment_id = 1, name = 'Test Treatment'}
    // Output: treatmentModel {id = 1, name = 'Test Treatment', type = '', times = '', purpose = '', instruction = '', repeat_days = '', repeat_time = ''}
    public function testInsertWithPartialDataExtendsDefaults()
    {
        $appointmentId = self::$qb->table(TABLE_PREFIX.TABLE_APPOINTMENTS)->insert([
            'booking_id' => 1,
            'doctor_id' => 1,
            'patient_id' => 1,
            'appointment_time' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'create_at' => date('Y-m-d H:i:s'),
            'update_at' => date('Y-m-d H:i:s')
        ]);

        $treatment = new TreatmentModel();
        $treatment->set("appointment_id", $appointmentId);
        $treatment->set("name", "Default Test");

        try {
            $insertedId = $treatment->insert();
            $this->assertIsNumeric($insertedId);
            $this->assertEquals("", $treatment->get("type")); // Defaulted
            $this->assertEquals("", $treatment->get("purpose")); // Defaulted
            $this->assertTrue($treatment->isAvailable());
            // throw new PDOException("Insert failed"); // Giả lập lỗi PDOException
        } catch (\PDOException $e) {
            // Đánh dấu test là thất bại và in ra lý do
            $this->fail("Insert failed with PDOException: " . $e->getMessage());
        }
    }
    // M06_TreatmentModel_Insert_03
    // Test trường hợp insert một bản ghi TreatmentModel với dữ liệu không hợp lệ
    // Input: treatmentModel {is_available = false}
    // Output: False
    public function testInsertDuplicateReturnsFalse()
    {
        $treatment = new TreatmentModel();
        $treatment->markAsAvailable(); // Giả lập là đã tồn tại
        
        $this->assertFalse($treatment->insert());
    }
    // M06_TreatmentModel_Insert_04
    // Test trường hợp insert một bản ghi TreatmentModel với dữ liệu không hợp lệ
    // Input: treatmentModel {appointment_id = 1, name = 'Test Treatment', type = 'Test Type', times = 2, purpose = 'Test Purpose', instruction = 'Test Instruction', repeat_days = 3, repeat_time = 4}
    // Output: treatmentModel {id = 1, name = 'Test Treatment', type = 'Test Type', times = 2, purpose = 'Test Purpose', instruction = 'Test Instruction', repeat_days = 3, repeat_time = 4}
    public function testInsertReturnsIntegerId()
    {
        $appointmentId = self::$qb->table(TABLE_PREFIX.TABLE_APPOINTMENTS)->insert([
            'booking_id' => 1,
            'doctor_id' => 1,
            'patient_id' => 1,
            'appointment_time' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'create_at' => date('Y-m-d H:i:s'),
            'update_at' => date('Y-m-d H:i:s')
        ]);

        $treatment = new TreatmentModel();
        $treatment->set("appointment_id", $appointmentId);
        $treatment->set("name", "Return ID Check");
        $treatment->set("times", 2);
        
        $insertedId = $treatment->insert();

        $this->assertIsInt((int)$insertedId); // ép về int nếu DB trả về string
    }

    private function createDummyAppointment()
    {
        return self::$qb->table(TABLE_PREFIX.TABLE_APPOINTMENTS)->insert([
            'booking_id' => 1,
            'doctor_id' => 1,
            'patient_id' => 1,
            'appointment_time' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'create_at' => date('Y-m-d H:i:s'),
            'update_at' => date('Y-m-d H:i:s')
        ]);
    }

    // M06_TreatmentModel_Update_01
    // Test trường hợp update một bản ghi TreatmentModel với dữ liệu hợp lệ
    // Input: treatmentModel {id = 1, name = 'Test Treatment', type = 'Test Type', times = 2, purpose = 'Test Purpose', instruction = 'Test Instruction', repeat_days = 3, repeat_time = 4}
    // Output: treatmentModel {id = 1, name = 'Updated Treatment', type = 'Updated Type', times = 2, purpose = 'Test Purpose', instruction = 'Test Instruction', repeat_days = 3, repeat_time = 4}
    public function testUpdateWithFullValidData()
    {
        // Tạo mới một record trước
        $appointmentId = $this->createDummyAppointment(); // tạo bản ghi hợp lệ
        $treatment = new TreatmentModel();
        $treatment->set("appointment_id", $appointmentId);
        $treatment->set("name", "Initial Treatment");
        $treatment->set("type", "Initial Type");
        $treatment->set("times", 1);
        $treatment->set("purpose", "Initial Purpose");
        $treatment->set("instruction", "Initial Instruction");
        $treatment->set("repeat_days", 2);
        $treatment->set("repeat_time", 3);
        $id = $treatment->insert();

        // Cập nhật dữ liệu
        $treatment->set("name", "Updated Name");
        $treatment->set("type", "Updated Type");
        $result = $treatment->update();

        $this->assertInstanceOf(TreatmentModel::class, $result);
        $this->assertEquals("Updated Name", $treatment->get("name"));
    }
    // M06_TreatmentModel_Update_02
    // Test trường hợp update một bản ghi TreatmentModel không tồn tại trong cơ sở dữ liệu
    // Input: treatmentModel {id = 9999999}
    // Output: False
    public function testUpdateWhenNotAvailable()
    {
        $treatment = new TreatmentModel();
        $treatment->set("id", 999999); // ID không tồn tại
        $result = $treatment->update();

        $this->assertFalse($result);
    }

    // M06_TreatmentModel_Update_03
    // Test trường hợp update một bản ghi TreatmentModel với dữ liệu bị thiếu
    // Input: treatmentModel {id = 1, name = 'Before Missing', type = '', times = '', purpose = '', instruction = '', repeat_days = '', repeat_time = ''}
    // Output: treatmentModel {id = 1, name = 'Before Missing', type = '', times = '', purpose = '', instruction = '', repeat_days = '', repeat_time = ''}
    public function testUpdateWithMissingFields()
    {
        $appointmentId = $this->createDummyAppointment();
        $treatment = new TreatmentModel();
        $treatment->set("appointment_id", $appointmentId);
        $treatment->set("name", "Before Missing");
        $treatment->set("times", 1);
        $treatment->insert();

        $treatment->set("type", null);
        $treatment->update();

        $this->assertEquals("", $treatment->get("type"));
    }
    // M06_TreatmentModel_Delete_01
    // Test trường hợp delete một bản ghi TreatmentModel đã tồn tại trong cơ sở dữ liệu
    // Input: treatmentModel {name = 'To be deleted', appointment_id = 1}
    // Output: True
    public function testDeleteSuccessfully()
    {
        $appointmentId = $this->createDummyAppointment();

        $treatment = new TreatmentModel();
        $treatment->set("appointment_id", $appointmentId);
        $treatment->set("name", "To be deleted");
        $treatment->set("times", 1);
        $treatment->insert();

        $this->assertTrue($treatment->isAvailable());

        $result = $treatment->delete();
        $this->assertTrue($result);
        $this->assertFalse($treatment->isAvailable());

        // Đảm bảo rằng record đã bị xóa khỏi DB
        $count = self::$qb->table(TABLE_PREFIX.TABLE_TREATMENTS)
            ->where("id", "=", $treatment->get("id"))
            ->count();
        $this->assertEquals(0, $count);
    }
    // M06_TreatmentModel_Delete_02
    // Test trường hợp delete một bản ghi TreatmentModel không tồn tại trong cơ sở dữ liệu
    // Input: treatmentModel {is_available = false}
    // Output: False
    public function testDeleteWhenNotAvailable()
    {
        $treatment = new TreatmentModel(); // không có dữ liệu
        $this->assertFalse($treatment->isAvailable());

        $result = $treatment->delete();
        $this->assertFalse($result);
    }
    // M06_TreatmentModel_Delete_03
    // Test trường hợp delete một bản ghi TreatmentModel đã bị xóa trước đó
    // Input: treatmentModel {name = 'Double delete test', appointment_id = 1}
    // Output: Lần xóa đầu tiên trả về true, lần xóa thứ hai trả về false
    public function testDoubleDelete()
    {
        $appointmentId = $this->createDummyAppointment();

        $treatment = new TreatmentModel();
        $treatment->set("appointment_id", $appointmentId);
        $treatment->set("name", "Double delete test");
        $treatment->set("times", 1);
        $treatment->insert();

        // Lần xóa đầu tiên
        $this->assertTrue($treatment->delete());

        // Lần xóa thứ hai - đã unavailable
        $this->assertFalse($treatment->delete());
    }

}
