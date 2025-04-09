<?php
use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;
use Mockery as m;

// require_once __DIR__ . '/../../../ConfigDefine.php';
require_once __DIR__ . '/../../../api/app/core/DataEntry.php';
require_once __DIR__ . '/../../../api/app/core/Controller.php';
require_once __DIR__ . '/../../../api/app/models/AppointmentRecordModel.php';
require_once __DIR__ . '/../../../umbrella-corporation/app/models/UserModel.php';
require_once __DIR__ . '/../../../api/app/config/db.config.php';
class AppointmentRecordModelTest extends TestCase
{
    protected static $db;
    protected static $qb;

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
        // m::close();
    }

    public function setUp(): void
    {
        // Bắt đầu transaction trước mỗi test case
        self::$db->getPdoInstance()->beginTransaction();
    }

    // M06_AppointmentRecordModel_select_01
    // Lấy thông tin appointment record với Cuộc hẹn đã có trong CSDL
    // Input: appointmentModel {id = $appointmentId, doctor_id = 1, patient_id = 1, appointment_time = '2025-04-09 14:30:00', status = 'pending', create_at = '2025-04-09 14:30:00', update_at = '2025-04-09 14:30:00'}
    //          appointmentRecordModel {id = $appointmentRecordID, appointment_id = $appointmentId, reason = 'Reason by appointment_id', description = 'Desc by appointment_id', status_before = 'approved', status_after = 'completed', create_at = '2025-04-09 14:30:00', update_at = '2025-04-09 14:30:00'}
    // Output: appointmentRecordModel {id = $appointmentRecordID, appointment_id = $appointmentId, reason = 'Reason by appointment_id', description = 'Desc by appointment_id', status_before = 'approved', status_after = 'completed', create_at = '2025-04-09 14:30:00', update_at = '2025-04-09 14:30:00'}
    public function testSelectWithValidAppointmentId()
    {
        $model = new AppointmentRecordModel();
    
        $appointmentId = self::$qb->table(TABLE_PREFIX.TABLE_APPOINTMENTS)->insert([
            'doctor_id' => 1, // hoặc một giá trị giả định hợp lệ
            'patient_id' => 1,
            'appointment_time' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'create_at' => date('Y-m-d H:i:s'),
            'update_at' => date('Y-m-d H:i:s')
        ]);
    
        // Chèn bản ghi
        $appointmentRecordID = self::$qb->table(TABLE_PREFIX.TABLE_APPOINTMENT_RECORDS)->insert([
            'appointment_id' => $appointmentId,
            'reason' => 'Reason by appointment_id',
            'description' => 'Desc by appointment_id',
            'status_before' => 'approved',
            'status_after' => 'completed',
            'create_at' => date("Y-m-d H:i:s"),
            'update_at' => date("Y-m-d H:i:s")
        ]);
    
        $result = $model->select($appointmentRecordID);
    
        $ref = new ReflectionClass($result);
        $prop = $ref->getProperty('is_available');
        $prop->setAccessible(true);
        $isAvailable = $prop->getValue($result);
        $this->assertTrue($isAvailable);
        $this->assertEquals($appointmentRecordID, $result->get("id"));
        $this->assertEquals("Reason by appointment_id", $result->get("reason"));
    }   

    // M06_AppointmentRecordModel_select_02
    // Lấy thông tin appointment record với Cuộc hẹn không tồn tại
    // Input: appointmentRecordModel {id = 999999}
    // Output: is_available = false, data = []
    public function testSelectWithInvalidId()
    {
        $model = new AppointmentRecordModel();

        $nonExistentId = 999999;

        $result = $model->select($nonExistentId);

        $ref = new ReflectionClass($result);
        $prop = $ref->getProperty('is_available');
        $prop->setAccessible(true);
        $isAvailable = $prop->getValue($result);
        $this->assertFalse($isAvailable);
        $prop2 = $ref->getProperty('data');
        $prop2->setAccessible(true);
        $data2 = $prop2->getValue($result);
        $this->assertEmpty($data2);
    }
    // M06_AppointmentRecordModel_select_03
    // Lấy thông tin appointment record với Cuộc hẹn có id không hợp lệ
    // Input: appointmentRecordModel {id = "not_exist_appt"}
    // Output: is_available = false, data = []
    public function testSelectWithInvalidAppointmentId()
    {
        $model = new AppointmentRecordModel();

        $fakeAppointmentId = "not_exist_appt";

        $result = $model->select($fakeAppointmentId);
        $ref = new ReflectionClass($result);
        $prop = $ref->getProperty('is_available');
        $prop->setAccessible(true);
        $isAvailable = $prop->getValue($result);
        $this->assertFalse($isAvailable);
        $prop2 = $ref->getProperty('data');
        $prop2->setAccessible(true);
        $data2 = $prop2->getValue($result);
        $this->assertEmpty($data2);
    }

    // M06_AppointmentRecordModel_update_01
    // Cập nhật thông tin appointment record với Cuộc hẹn đã có trong CSDL
    // Input: appointmentRecordModel {appointment_id = [appointmentId], reason = 'Initial reason', description = 'Initial description', status_before = 'pending', status_after = 'approved', create_at = '2025-04-09 14:30:00', update_at = '2025-04-09 14:30:00'}, appointmentRecordModel {reason = 'Updated reason', description = 'Updated description', status_before = 'approved', status_after = 'completed', update_at = '2025-04-09 14:30:00'}
    // Output: {appointment_id = [appointmentId], reason = 'Updated reason', description = 'Updated description', status_before = 'approved', status_after = 'completed', update_at = '2025-04-09 14:30:00'}

    public function testUpdateSuccessfullyUpdatesAppointmentRecord()
    {
        $appointmentId = self::$qb->table(TABLE_PREFIX.TABLE_APPOINTMENTS)->insert([
            'doctor_id' => 1, // hoặc một giá trị giả định hợp lệ
            'patient_id' => 1,
            'appointment_time' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'create_at' => date('Y-m-d H:i:s'),
            'update_at' => date('Y-m-d H:i:s')
        ]);
        // Step 1: Tạo một bản ghi appointment record mẫu
        $insertId = self::$qb->table(TABLE_PREFIX.TABLE_APPOINTMENT_RECORDS)->insert([
            'appointment_id' => $appointmentId,
            'reason' => 'Initial reason',
            'description' => 'Initial description',
            'status_before' => 'pending',
            'status_after' => 'approved',
            'create_at' => date("Y-m-d H:i:s"),
            'update_at' => date("Y-m-d H:i:s")
        ]);

        // Step 2: Tạo đối tượng AppointmentRecordModel
        $record = new AppointmentRecordModel($insertId);
        
        // Step 3: Giả lập thay đổi dữ liệu
        $record->set("reason", "Updated reason");
        $record->set("description", "Updated description");
        $record->set("status_before", "approved");
        $record->set("status_after", "completed");
        $record->set("update_at", date("Y-m-d H:i:s"));

        // Step 4: Gọi hàm update
        $result = $record->update();

        // Step 5: Lấy lại dữ liệu từ DB để so sánh
        $updated = self::$qb->table(TABLE_PREFIX.TABLE_APPOINTMENT_RECORDS)->where("id", "=", $insertId)->first();

        // Step 6: Assertions
        $this->assertInstanceOf(AppointmentRecordModel::class, $result);
        $this->assertEquals("Updated reason", $updated->reason);
        $this->assertEquals("Updated description", $updated->description);
        $this->assertEquals("approved", $updated->status_before);
        $this->assertEquals("completed", $updated->status_after);
    }

    // M06_AppointmentRecordModel_update_02
    // Cập nhật thông tin appointment record với Cuộc hẹn không tồn tại
    // Input: appointmentRecordModel {is_available = false}
    // Output: False

    public function testUpdateReturnsFalseIfNotAvailable()
    {
        // Tạo một mock đối tượng không available
        $mock = $this->getMockBuilder(AppointmentRecordModel::class)
                    ->setMethods(['isAvailable'])
                    ->disableOriginalConstructor()
                    ->getMock();

        $mock->method('isAvailable')->willReturn(false);

        $this->assertFalse($mock->update());
    }

    // M06_AppointmentRecordModel_insert_01
    // Thêm mới appointment record với Cuộc hẹn đã có trong CSDL
    // Input: appointmentRecordModel {appointment_id = 75, reason = 'Test Reason', description = 'Test Description', status_before = 'pending', status_after = 'approved'}
    // Output: appointmentRecordModel {appointment_id = 75, reason = 'Test Reason', description = 'Test Description', status_before = 'pending', status_after = 'approved'}

    public function testInsertSuccessWithFullData()
    {
        $appointmentId = self::$qb->table(TABLE_PREFIX.TABLE_APPOINTMENTS)->insert([
            'doctor_id' => 1,
            'patient_id' => 1,
            'appointment_time' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'create_at' => date('Y-m-d H:i:s'),
            'update_at' => date('Y-m-d H:i:s')
        ]);

        $record = new AppointmentRecordModel();
        $record->set("appointment_id", $appointmentId);
        $record->set("reason", "Test Reason");
        $record->set("description", "Test Description");
        $record->set("status_before", "pending");
        $record->set("status_after", "approved");
        $record->set("create_at", date("Y-m-d H:i:s"));
        $record->set("update_at", date("Y-m-d H:i:s"));

        $insertedId = $record->insert();

        $this->assertNotFalse($insertedId);
        $this->assertTrue(is_numeric($insertedId));

        $ref = new ReflectionClass($record);
        $prop = $ref->getProperty('is_available');
        $prop->setAccessible(true);
        $isAvailable = $prop->getValue($record);
        $this->assertTrue($isAvailable);
    }

    // M06_AppointmentRecordModel_insert_02
    // Thêm mới appointment record với Cuộc hẹn bằng giá trị mặc định
    // Input: appointmentRecordModel {id = null}
    // Output: is_available = false, data = []
    public function testInsertWithPartialDataExtendsDefaults()
    {
        $record = new AppointmentRecordModel();
        $appointmentId = self::$qb->table(TABLE_PREFIX.TABLE_APPOINTMENTS)->insert([
            'doctor_id' => 1,
            'patient_id' => 1,
            'appointment_time' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'create_at' => date('Y-m-d H:i:s'),
            'update_at' => date('Y-m-d H:i:s')
        ]);
        $record->set("appointment_id", $appointmentId);
        // Không set gì cả
        $insertedId = $record->insert();

        $this->assertNotFalse($insertedId);
        $this->assertTrue(is_numeric($insertedId));
        
        $ref = new ReflectionClass($record);
        $prop = $ref->getProperty('is_available');
        $prop->setAccessible(true);
        $isAvailable = $prop->getValue($record);
        $this->assertTrue($isAvailable);

        // Kiểm tra các giá trị mặc định
        $this->assertEquals("", $record->get("reason"));
    }

    // M06_AppointmentRecordModel_insert_03
    // Thêm mới appointment record với Cuộc hẹn đã Available
    // Input: appointmentRecordModel {appointment_id = "fake_id", reason = "should not insert again", is_available = true}
    // Output: is_available = false, data = []
    public function testInsertFailsIfAlreadyAvailable()
    {
        $record = new AppointmentRecordModel();
        $record->set("appointment_id", "fake_id");
        $record->set("reason", "should not insert again");
        $record->markAsAvailable(); // giả lập đã tồn tại

        $result = $record->insert();

        $this->assertFalse($result);
    }

    // M06_AppointmentRecordModel_delete_01
    // Xóa appointment record với Cuộc hẹn đã có trong CSDL
    // Input: appointmentRecordModel {id = 1, appointment_id = 75, reason = 'Test Reason', description = 'Test Description', status_before = 'pending', status_after = 'approved'}
    // Output: appointmentRecordModel {is_available = false, data = []}
    public function testDeleteAppointmentRecord()
    {
        // STEP 1: Tạo dữ liệu phụ thuộc
        $appointmentId = self::$qb->table(TABLE_PREFIX.TABLE_APPOINTMENTS)->insert([
            'doctor_id' => 1,
            'patient_id' => 1,
            'appointment_time' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'create_at' => date('Y-m-d H:i:s'),
            'update_at' => date('Y-m-d H:i:s')
        ]);

        // STEP 2: Tạo bản ghi appointment_record
        $recordId = self::$qb->table(TABLE_PREFIX.TABLE_APPOINTMENT_RECORDS)->insert([
            'appointment_id' => $appointmentId,
            'reason' => 'Need removal',
            'description' => 'Will be deleted',
            'status_before' => 'pending',
            'status_after' => 'canceled',
            'create_at' => date('Y-m-d H:i:s'),
            'update_at' => date('Y-m-d H:i:s')
        ]);

        // STEP 3: Gán model và gọi delete()
        $model = new \AppointmentRecordModel($recordId);
        $result = $model->delete();

        // STEP 4: Kiểm tra kết quả
        $this->assertTrue($result); // delete() return true
        $this->assertFalse($model->isAvailable()); // Model không còn khả dụng
        $deletedRecord = self::$qb->table(TABLE_PREFIX.TABLE_APPOINTMENT_RECORDS)->where('id', $recordId)->first();
        $this->assertNull($deletedRecord); // Không còn trong DB
    }

    // M06_AppointmentRecordModel_delete_02
    // Xóa appointment record với Cuộc hẹn không tồn tại
    // Input: appointmentRecordModel {id = 999999}
    // Output: is_available = false, data = []
    public function testDeleteWhenRecordDoesNotExist()
    {
        $model = new \AppointmentRecordModel(999999); // ID không tồn tại
        $this->assertFalse($model->delete());
    }

}
