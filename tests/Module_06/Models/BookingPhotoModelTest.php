<?php
use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;
// use Mockery;

require_once __DIR__ . '/../../../ConfigDefine.php';
require_once __DIR__ . '/../../../api/app/core/DataEntry.php';
require_once __DIR__ . '/../../../api/app/core/Controller.php';
require_once __DIR__ . '/../../../api/app/models/BookingPhotoModel.php';
require_once __DIR__ . '/../../../umbrella-corporation/app/models/UserModel.php';
require_once __DIR__ . '/../../../api/app/config/db.config.php';
class BookingPhotoModelTest extends TestCase
{
    protected static $db;
    protected static $qb;
    protected $bookingPhotoModel;

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
        $this->bookingPhotoModel = new BookingPhotoModel();
    }

    // M06_BookingPhotoModel_Select_01
    // Test trường hợp select một bản ghi BookingPhotoModel đã tồn tại trong cơ sở dữ liệu
    // Input: bookingPhotoModel{url: 'photos/test1.jpg'}
    // Output: bookingPhotoModel{url: 'photos/test1.jpg'}
    public function testSelectExistingBookingPhoto()
    {
        
        // STEP 1: Tạo bản ghi mẫu
        $id = self::$qb->table(TABLE_PREFIX.TABLE_BOOKING_PHOTOS)->insert([
            // 'booking_id' => $bookingId,
            'url' => 'photos/test1.jpg'
        ]);
    
        // STEP 2: Tạo model và gọi select
        $model = new \BookingPhotoModel();
        $model->select($id);
    
        // STEP 3: Kiểm tra dữ liệu được gán và is_available = true
        $this->assertTrue($model->isAvailable());
        $this->assertEquals($id, $model->get('id'));
        $this->assertEquals('photos/test1.jpg', $model->get('url'));
    }

    // M06_BookingPhotoModel_Select_02
    // Test trường hợp select một bản ghi BookingPhotoModel không tồn tại trong cơ sở dữ liệu
    // Input: bookingPhotoModel{id: 999999}
    // Output: is_available = false
    public function testSelectNonExistingBookingPhoto()
    {
        $model = new \BookingPhotoModel();
        $model->select(999999); // ID không tồn tại

        $this->assertFalse($model->isAvailable());
    }

    // M06_BookingPhotoModel_Select_03
    // Test trường hợp select một bản ghi BookingPhotoModel với ID null
    // Input: bookingPhotoModel{id: null}
    // Output: is_available = false
    public function testSelectNullId()
    {
        $model = new \BookingPhotoModel();
        $model->select(null); // truyền null

        $this->assertFalse($model->isAvailable());
    }

    // M06_BookingPhotoModel_Update_01
    // Test trường hợp update một bản ghi BookingPhotoModel đã tồn tại trong cơ sở dữ liệu
    // Input: bookingPhotoModel{id: 1, url: 'photos/test1.jpg'}
    // Output: bookingPhotoModel{id: 1, url: 'photos/updated.jpg'}
    public function testUpdateBookingPhoto()
    {
        $bookingId = self::$qb->table(TABLE_PREFIX.TABLE_BOOKINGS)->insert([
            'patient_id' => 1, // Chỉnh theo schema thực tế
            'doctor_id' => 1,
            'status' => 'pending',
            'appointment_time' => date('H:i'),
            'create_at' => date('Y-m-d H:i:s'),
            'update_at' => date('Y-m-d H:i:s')
        ]);

        $photoId = self::$qb->table(TABLE_PREFIX.TABLE_BOOKING_PHOTOS)->insert([
            'booking_id' => $bookingId,
            'url' => 'photos/old.jpg'
        ]);

        // 3. Khởi tạo model và nạp dữ liệu hiện tại
        $model = new \BookingPhotoModel();
        $model->set("id", $photoId);
        $model->set("booking_id", $bookingId);
        $model->set("url", "photos/updated.jpg");

        // Đánh dấu là available (giả lập hành vi đã gọi select trước)
        $ref = new ReflectionClass($model);
        $prop = $ref->getProperty('is_available');
        $prop->setAccessible(true);
        $prop->setValue($model, true);

        // 4. Gọi hàm update
        $result = $model->update();

        // 5. Kiểm tra kết quả trả về
        $this->assertInstanceOf(\BookingPhotoModel::class, $result);

        // 6. Kiểm tra dữ liệu đã được cập nhật chưa trong DB
        $updatedRecord = self::$qb->table(TABLE_PREFIX.TABLE_BOOKING_PHOTOS)->where("id", $photoId)->first();
        $this->assertEquals("photos/updated.jpg", $updatedRecord->url);
    }

    // M06_BookingPhotoModel_Update_02
    // Test trường hợp update một bản ghi BookingPhotoModel không tồn tại trong cơ sở dữ liệu
    // Input: bookingPhotoModel{id: 999}
    // Output: is_available = false
    public function testUpdateShouldReturnFalseWhenNotAvailable()
    {
        $model = new \BookingPhotoModel();
        $model->set("id", 999); // ID không tồn tại

        // Không đánh dấu is_available = true
        // => phải trả về false
        $result = $model->update();

        $this->assertFalse($result);
    }

    // M06_BookingPhotoModel_Delete_01
    // Test trường hợp xóa một bản ghi BookingPhotoModel đã tồn tại trong cơ sở dữ liệu
    // Input: bookingPhotoModel{id: 1}
    // Output: is_available = false
    public function testDeleteBookingPhotoSuccess()
    {
        // 1. Tạo booking cần thiết để thỏa mãn khóa ngoại
        $bookingId = self::$qb->table(TABLE_PREFIX.TABLE_BOOKINGS)->insert([
            'patient_id' => 1, // Chỉnh theo schema thực tế
            'doctor_id' => 1,
            'status' => 'pending',
            'appointment_time' => date('H:i'),
            'create_at' => date('Y-m-d H:i:s'),
            'update_at' => date('Y-m-d H:i:s')
        ]);

        // 2. Tạo booking_photo để test
        $photoId = self::$qb->table(TABLE_PREFIX.TABLE_BOOKING_PHOTOS)->insert([
            'booking_id' => $bookingId,
            'url' => 'test.jpg'
        ]);

        // 3. Khởi tạo model và gán giá trị
        $model = new \BookingPhotoModel();
        $model->set('id', $photoId);

        // Đánh dấu là isAvailable
        $ref = new ReflectionClass($model);
        $prop = $ref->getProperty('is_available');
        $prop->setAccessible(true);
        $prop->setValue($model, true);

        // 4. Gọi delete
        $result = $model->delete();

        // 5. Kiểm tra kết quả
        $this->assertTrue($result);
        $this->assertFalse($prop->getValue($model)); // Kiểm tra is_available = false

        $deleted = self::$qb->table(TABLE_PREFIX.TABLE_BOOKING_PHOTOS)->where('id', $photoId)->first();
        $this->assertNull($deleted); // Kiểm tra đã xóa khỏi DB
    }

    // M06_BookingPhotoModel_Delete_02
    // Test trường hợp xóa một bản ghi BookingPhotoModel không tồn tại trong cơ sở dữ liệu
    // Input: bookingPhotoModel{id: 999}
    // Output: is_available = false
    public function testDeleteBookingPhotoNotAvailable()
    {
        $model = new \BookingPhotoModel();
        $model->set('id', 999); // ID không tồn tại

        // Không đánh dấu is_available
        $ref = new ReflectionClass($model);
        $prop = $ref->getProperty('is_available');
        $prop->setAccessible(true);
        $prop->setValue($model, false);

        // Gọi delete
        $result = $model->delete();

        // Kiểm tra
        $this->assertFalse($result);
    }

}
