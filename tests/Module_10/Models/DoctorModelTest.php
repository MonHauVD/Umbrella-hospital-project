<?php
use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;
// use Mockery;

require_once __DIR__ . '/../../../ConfigDefine.php';
require_once __DIR__ . '/../../../api/app/core/DataEntry.php';
require_once __DIR__ . '/../../../api/app/core/Controller.php';
require_once __DIR__ . '/../../../api/app/controllers/DoctorController.php';
require_once __DIR__ . '/../../../api/app/models/DoctorModel.php';
require_once __DIR__ . '/../../../umbrella-corporation/app/models/UserModel.php';
require_once __DIR__ . '/../../../api/app/config/db.config.php';
class DoctorModelTest extends TestCase
{
    protected static $db;
    protected static $qb;
    protected $doctorModel;

    public static function setUpBeforeClass(): void
    {
        // Khởi tạo Pixie Connection
        $config = [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'doantotnghiep',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'options'   => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        ];
        self::$db = new Connection('mysql', $config, 'DB');
        self::$qb = self::$db->getQueryBuilder();
    }

    public function tearDown(){
        // Rollback transaction sau mỗi test case
        // self::$db->getPdoInstance()->commit;
        // self::$db->getPdoInstance()->rollback();
    }

    public function setUp(): void
    {
        // Bắt đầu transaction trước mỗi test case
        self::$db->getPdoInstance()->beginTransaction();
        $this->doctorModel = new DoctorModel();
    }

    // M10_DoctorModel_select_01
    // Id hop le
    // Input: doctorModel {id = 37,'email = 'doctor@example.com', phone = '0123456789'}
    // Output: doctorModel với id = 37
    public function testSelectWithValidId()
    {
        // fwrite(STDOUT, "\ntestSelectWithValidId");
        // Insert dữ liệu giả lập vào database để test
        self::$qb->table(TABLE_PREFIX . TABLE_DOCTORS)->insert([
            'id' => 37,
            'email' => 'doctor@example.com',
            'phone' => '0123456789'
        ])->get();

        // Gọi phương thức select() của DoctorModel
        $doctor = $this->doctorModel->select(37);

        // Kiểm tra kết quả
        // $this->assertTrue($doctor->get('is_available'));
        $this->assertEquals(37, $doctor->get('id'));
        $this->assertEquals('doctor@example.com', $doctor->get('email'));
        $this->assertEquals('0123456789', $doctor->get('phone'));
    }

    // M10_DoctorModel_select_02
    // Test method select() với email hợp lệ
    // Input: doctorModel {id = 37,'email = 'doctor@example.com', phone = '0123456789'}
    // Output: doctorModel với id = 37
    public function testSelectWithValidEmail()
    {
        // fwrite(STDOUT, "\ntestSelectWithValidEmail");
        // Insert dữ liệu giả lập vào database
        self::$qb->table(TABLE_PREFIX . TABLE_DOCTORS)->insert([
            'id' => 37,
            'email' => 'doctor@example.com',
            'phone' => '0123456789'
        ]);

        // Gọi phương thức select() với email
        $doctor = $this->doctorModel->select('doctor@example.com');

        // Kiểm tra kết quả
        // $this->assertTrue($doctor->get('is_available'));
        $this->assertEquals('doctor@example.com', $doctor->get('email'));
    }

    // M10_DoctorModel_select_03
    // Test method select() với số điện thoại hợp lệ
    // Input: doctorModel {id = 37,'email = 'doctor@example.com', phone = '+84123456789'}
    // Output: doctorModel với id = 37
    public function testSelectWithPhoneNumber()
    {
        // fwrite(STDOUT, "\ntestSelectWithValidPhoneNumber");
        // Insert dữ liệu giả lập vào database
        self::$qb->table(TABLE_PREFIX . TABLE_DOCTORS)->insert([
            'id' => 37,
            'email' => 'doctor@example.com',
            'phone' => '+84123456789'
        ]);

        // Gọi phương thức select() với số điện thoại
        
        $doctor = $this->doctorModel->select('+84123456789');
        // fwrite(STDOUT, var_export($doctor, true));
        // fwrite(STDOUT, "Hello".var_export($doctor, true));
        // Kiểm tra kết quả
        // $this->assertTrue($doctor->get('is_available'));
        $this->assertEquals('+84123456789', $doctor->get('phone'));
    }

    // M10_DoctorModel_select_04
    // Test method select() với số điện thoại bắt đầu bằng số 0
    // Input: doctorModel {id = 37,'email = 'doctor@example.com', phone = '0123456789'}
    // Output: doctorModel với id = 37
    public function testSelectWithPhoneNumberWithZeroAtFirst()
    {
        // fwrite(STDOUT, "\ntestSelectWithPhoneNumberWithZeroAtFirst");
        // Insert dữ liệu giả lập vào database
        self::$qb->table(TABLE_PREFIX . TABLE_DOCTORS)->insert([
            'id' => 37,
            'email' => 'doctor@example.com',
            'phone' => '0123456789'
        ]);
        
        $doctor = $this->doctorModel->select('0123456789');
        $this->assertEquals('0123456789', $doctor->get('phone'));
    }

    // M10_DoctorModel_select_05
    // Test method select() với số điện thoại chứa kí tự đặc biệt
    // Input: doctorModel {id = 37,'email = 'doctor@example.com', phone = '0123.456 789'}
    // Output: doctorModel với id = 37
    public function testSelectWithPhoneNumberWithSpecialCharacter()
    {
        // fwrite(STDOUT, "\ntestSelectWithPhoneNumberWithSpecialCharacter");
        // Insert dữ liệu giả lập vào database
        self::$qb->table(TABLE_PREFIX . TABLE_DOCTORS)->insert([
            'id' => 37,
            'email' => 'doctor@example.com',
            'phone' => '0123.456 789'
        ]);
        
        $doctor = $this->doctorModel->select('0123.456 789');
        $this->assertEquals('0123.456 789', $doctor->get('phone'));
    }

    // M10_DoctorModel_select_06
    // Test method select() với id không hợp lệ
    // Input: id = 999
    // Output: is_available = false
    public function testSelectWithInvalidId()
    {
        // fwrite(STDERR, "\ntestSelectWithInvalidId");
        // Gọi phương thức select() với id không tồn tại
        $doctor = $this->doctorModel->select(999);
        // fwrite(STDERR, var_export($doctor, true));
        // Kiểm tra kết quả khi không tìm thấy dữ liệu
        $ref = new ReflectionClass($doctor);
        $prop = $ref->getProperty('is_available');
        $prop->setAccessible(true);
        $isAvailable = $prop->getValue($doctor);
        $this->assertFalse($isAvailable);
    }

    // M10_DoctorModel_select_07
    // Test method select() với số điện thoại hợp lệ nhưng có 2 tài khoảng cùng số mà khác nhau trong CSDL
    // Input: doctorModel {id = 37,'email = 'doctor@example.com', phone = '0123.456 789'}, doctorModel {id = 38,'email = 'doctor@example.com', phone = '0123.456 789'}
    // Output: is_available = false
    public function testSelectWithPhoneNumberWithTwoAccountSamePhoneNumber()
    {
        // fwrite(STDOUT, "\ntestSelectWithPhoneNumberWithTwoAccountSamePhoneNumber");
        // Insert dữ liệu giả lập vào database
        self::$qb->table(TABLE_PREFIX . TABLE_DOCTORS)->insert([
            'id' => 37,
            'email' => 'doctor@example.com',
            'phone' => '0123.456 789'
        ]);
        self::$qb->table(TABLE_PREFIX . TABLE_DOCTORS)->insert([
            'id' => 38,
            'email' => 'doctor2@example.com',
            'phone' => '0123.456 789'
        ]);
        
        $doctor = $this->doctorModel->select('0123.456 789');
        $ref = new ReflectionClass($doctor);
        $prop = $ref->getProperty('is_available');
        $prop->setAccessible(true);
        $isAvailable = $prop->getValue($doctor);
        $this->assertFalse($isAvailable);
    }

    // M10_DoctorModel_insert_01
    // Test method insert() với trường hợp đối tượng hiện tại đang có is_available = false
    // Input: doctorModel {email = 'testdoctor@example.com', phone = '+84999999999', password = 'secret', name = 'Dr. Test', description = 'Unit test doctor', price = 100, speciality_id = 1, room_id = 1}
    // Output: id của doctorModel đã nhập tương ứng
    public function testInsertDoctorSuccess()
    {
        // fwrite(STDOUT, "Running testInsertDoctorSuccess\n");

        // Tạo instance mới
        $doctor = new DoctorModel();

        // Set giá trị cần thiết
        $doctor->set("email", "testdoctor@example.com");
        $doctor->set("phone", "+84999999999");
        $doctor->set("password", "secret");
        $doctor->set("name", "Dr. Test");
        $doctor->set("description", "Unit test doctor");
        $doctor->set("price", 100);
        $doctor->set("speciality_id", 1);
        $doctor->set("room_id", 1);

        // Gọi insert
        $insertedId = $doctor->insert();

        // Debug
        // fwrite(STDOUT, "Inserted ID: $insertedId\n");

        // Assert có id sau khi insert
        $this->assertIsNumeric($insertedId);
        $this->assertGreaterThan(0, $insertedId);

        // Assert là available
        $this->assertTrue($doctor->isAvailable());

        // Optional: Kiểm tra tồn tại trong DB
        $doctorInDb = DB::table(TABLE_PREFIX.TABLE_DOCTORS)->where("id", "=", $insertedId)->first();
        $this->assertNotEmpty($doctorInDb);
        $this->assertEquals("testdoctor@example.com", $doctorInDb->email);
    }

    // M10_DoctorModel_insert_02
    // Test method insert() với trường hợp đối tượng hiện tại đang có is_available = true
    // Input: doctorModel {email = 'already@exists.com', phone = '+84988888888', is_available = true}
    // Output: hàm trả về giá trị false
    public function testInsertShouldReturnFalseIfAlreadyAvailable()
    {
        // fwrite(STDOUT, "Running testInsertShouldReturnFalseIfAlreadyAvailable\n");

        $doctor = new DoctorModel();

        // Đặt dữ liệu giả
        $doctor->set("email", "already@exists.com");
        $doctor->set("phone", "+84988888888");
        $doctor->markAsAvailable(); // Giả lập trạng thái đã tồn tại

        // Gọi insert() và mong đợi nó trả về false
        $result = $doctor->insert();

        // Kiểm tra kết quả
        $this->assertFalse($result, "Insert should return false if model is already available");
    }

    // M10_DoctorModel_update_01
    // Test method update() với id hợp lệ
    // Input: doctorModel {id = 36, name = 'Test Doctor Transaction hihii', description = 'Description Transaction'}
    // Output: doctorModel {id = 36, name = 'Test Doctor Transaction hihii', description = 'Description Transaction'}
    public function testUpdateDoctorWithValidID()
    {
        // fwrite(STDERR, "\ntestUpdateDoctorWithValidID");
        // Arrange
        $doctor = new DoctorModel(36);
        $this->assertTrue($doctor->isAvailable(), "Doctor with ID 36 does not exist!");

        $newName = "Test Doctor Transaction hihii";
        $newDescription = "Description Transaction";

        // Act
        $doctor->set("name", $newName);
        $doctor->set("description", $newDescription);
        $result = $doctor->update();

        // Assert
        $this->assertInstanceOf(DoctorModel::class, $result);
        $this->assertEquals($newName, $doctor->get("name"));
        $this->assertEquals($newDescription, $doctor->get("description"));
        
        // Verify in DB
        $dbDoctor = self::$qb->table(TABLE_PREFIX . TABLE_DOCTORS)->where("id", "=", 36)->get();
        $this->assertEquals($newName, $dbDoctor[0]->name);
        $this->assertEquals($newDescription, $dbDoctor[0]->description);

    }

    // M10_DoctorModel_update_02
    // Test method update() với một đối tượng mới tạo trong DB
    // Input: doctorModel {email = 'before@update.com', phone = '+84900000000', name = 'Before Update', speciality_id = 1, room_id = 1}, doctorModel {name = 'After Update', phone = '+84999999999'}
    // Output:  doctorModel {email = 'before@update.com', name = 'After Update', phone = '+84999999999', speciality_id = 1, room_id = 1}
    public function testUpdateSuccess()
    {
        // fwrite(STDOUT, "Running testUpdateSuccess\n");

        // Tạo doctor mới và insert vào DB
        $doctor = new DoctorModel();
        $doctor->set("email", "before@update.com");
        $doctor->set("phone", "+84900000000");
        $doctor->set("name", "Before Update");
        $doctor->set("speciality_id", 1);
        $doctor->set("room_id", 1);
        $insertedId = $doctor->insert();

        // Đảm bảo đã insert thành công
        $this->assertIsNumeric($insertedId);

        // Thay đổi dữ liệu
        $doctor->set("name", "After Update");
        $doctor->set("phone", "+84999999999");

        // Gọi update
        $updatedDoctor = $doctor->update();

        // Kiểm tra: đối tượng trả về vẫn là chính nó
        $this->assertInstanceOf(DoctorModel::class, $updatedDoctor);

        // Kiểm tra dữ liệu đã được cập nhật trong DB
        $doctorFromDb = DB::table(TABLE_PREFIX.TABLE_DOCTORS)
            ->where("id", $insertedId)->first();

        $this->assertEquals("After Update", $doctorFromDb->name);
        $this->assertEquals("+84999999999", $doctorFromDb->phone);
    }

    // M10_DoctorModel_update_03
    // Test method update() với một đối tượng không tồn tại trong DB
    // Input: doctorModel {id = 999, email = 'not@available.com'}
    // Output: False
    public function testUpdateShouldFailWhenNotAvailable()
    {
        // fwrite(STDOUT, "Running testUpdateShouldFailWhenNotAvailable\n");

        $doctor = new DoctorModel();
        $doctor->set("id", 999); // id không tồn tại
        $doctor->set("email", "not@available.com");

        // isAvailable() sẽ trả về false (vì chưa markAsAvailable)
        $result = $doctor->update();

        $this->assertFalse($result);
    }

    // M10_DoctorModel_update_04
    // Test method update() với một đối tượng thiếu một số trường
    // Input: doctorModel {email = 'default@check.com', phone = '+84911111111', name = 'Default Test', speciality_id = 1, room_id = 1}, doctorModel {password = null, description = null}
    // Output: doctorModel {email = 'default@check.com', phone = '+84911111111', name = 'Default Test', speciality_id = 1, room_id = 1, password = null, description = null}
    // và các trường thiếu được điền bằng giá trị mặc định
    public function testUpdateWithMissingFieldsShouldUseDefaults()
    {
        // fwrite(STDOUT, "Running testUpdateWithMissingFieldsShouldUseDefaults\n");

        $doctor = new DoctorModel();
        $doctor->set("email", "default@check.com");
        $doctor->set("phone", "+84911111111");
        $doctor->set("name", "Default Test");
        $doctor->set("speciality_id", 1);
        $doctor->set("room_id", 1);
        $insertedId = $doctor->insert();

        $this->assertNotFalse($insertedId);

        // Xóa 1 số field (password, description...)
        $doctor->set("password", null);
        $doctor->set("description", null);

        // Gọi update()
        $updatedDoctor = $doctor->update();

        // Kiểm tra xem field rỗng được điền bằng default chưa
        $doctorFromDb = DB::table(TABLE_PREFIX.TABLE_DOCTORS)
            ->where("id", $insertedId)->first();

        $this->assertEquals("", $doctorFromDb->password);
        $this->assertEquals("", $doctorFromDb->description);
    }
    // M10_DoctorModel_delete_01
    // Test method delete() với đối tượng có trong DB
    // Input: doctorModel {email = 'delete@test.com', phone = '+84912345678', name = 'Delete Test', speciality_id = 1, room_id = 1}
    // Output: true
    public function testDeleteSuccess()
    {
        // fwrite(STDOUT, "Running testDeleteSuccess\n");

        // Tạo bác sĩ mới và insert vào DB
        $doctor = new DoctorModel();
        $doctor->set("email", "delete@test.com");
        $doctor->set("phone", "+84912345678");
        $doctor->set("name", "Delete Test");
        $doctor->set("speciality_id", 1);
        $doctor->set("room_id", 1);
        $id = $doctor->insert();

        $this->assertNotFalse($id);

        // Gọi delete()
        $result = $doctor->delete();

        // Kết quả mong đợi: true
        $this->assertTrue($result);

        // Kiểm tra trong DB đã xóa chưa
        $doctorFromDb = DB::table(TABLE_PREFIX.TABLE_DOCTORS)
            ->where("id", $id)->first();
        $this->assertNull($doctorFromDb);

        // Kiểm tra trạng thái is_available đã cập nhật
        $ref = new ReflectionClass($doctor);
        $prop = $ref->getProperty('is_available');
        $prop->setAccessible(true);
        $isAvailable = $prop->getValue($doctor);
        $this->assertFalse($isAvailable);
    }


    // M10_DoctorModel_delete_02
    // Test method delete() với đối tượng chưa có trong DB
    // Input: id = 999999
    // Output: false  
    public function testDeleteShouldReturnFalseWhenNotAvailable()
    {
        // fwrite(STDOUT, "Running testDeleteShouldReturnFalseWhenNotAvailable\n");

        $doctor = new DoctorModel();
        $doctor->set("id", 999999); // ID giả, không tồn tại
        // Không gọi markAsAvailable() hoặc select()

        $result = $doctor->delete();

        $this->assertFalse($result, "Delete should return false if model is not available");
    }

    // M10_DoctorModel_delete_03
    // Test method delete() xóa 2 lần với 1 đối tượng có trong DB
    // Input: doctorModel {email = 'twice@delete.com', phone = '+84988887777', name = 'Delete Twice', speciality_id = 1, room_id = 1}
    // Output: lần đầu: true, lần hai: false  
    public function testDeleteTwiceShouldFailSecondTime()
    {
        // fwrite(STDOUT, "Running testDeleteTwiceShouldFailSecondTime\n");

        $doctor = new DoctorModel();
        $doctor->set("email", "twice@delete.com");
        $doctor->set("phone", "+84988887777");
        $doctor->set("name", "Delete Twice");
        $doctor->set("speciality_id", 1);
        $doctor->set("room_id", 1);
        $id = $doctor->insert();

        $this->assertNotFalse($id);

        // Delete lần 1
        $firstDelete = $doctor->delete();
        $this->assertTrue($firstDelete);

        // Delete lần 2 (khi is_available = false)
        $secondDelete = $doctor->delete();
        $this->assertFalse($secondDelete);
    }
    
    // M10_DoctorModel_isAdmin_01
    // Test method isAdmin() với role là admin
    // Input: doctorModel {role = 'admin', is_available = true}
    // Output: true
    public function testIsAdminWithAdminRole()
    {
        // fwrite(STDOUT, "Running testIsAdminWithAdminRole\n");
    
        $doctor = new DoctorModel();
        $doctor->set("role", "admin");
        $doctor->markAsAvailable();
    
        $this->assertTrue($doctor->isAdmin(), "Doctor with role 'admin' should be recognized as admin.");
    }

    // M10_DoctorModel_isAdmin_02
    // Test method isAdmin() với role là developer
    // Input: doctorModel {role = 'developer', is_available = true}
    // Output: true
    public function testIsAdminWithDeveloperRole()
    {
        // fwrite(STDOUT, "Running testIsAdminWithDeveloperRole\n");

        $doctor = new DoctorModel();
        $doctor->set("role", "developer");
        $doctor->markAsAvailable();

        $this->assertTrue($doctor->isAdmin(), "Doctor with role 'developer' should be recognized as admin.");
    }

    // M10_DoctorModel_isAdmin_03
    // Test method isAdmin() với role khác admin và developer
    // Input: doctorModel {role = 'doctor', is_available = true}
    // Output: false
    public function testIsAdminWithNonAdminRole()
    {
        // fwrite(STDOUT, "Running testIsAdminWithNonAdminRole\n");

        $doctor = new DoctorModel();
        $doctor->set("role", "doctor");
        $doctor->markAsAvailable();

        $this->assertFalse($doctor->isAdmin(), "Doctor with role 'doctor' should NOT be admin.");
    }

    // M10_DoctorModel_isAdmin_04
    // Test method isAdmin() với role là admin nhưng chưa được đánh dấu là available
    // Input: doctorModel {role = 'admin', is_available = false}
    // Output: false
    public function testIsAdminWithAdminRoleButNotAvailable()
    {
        // fwrite(STDOUT, "Running testIsAdminWithAdminRoleButNotAvailable\n");

        $doctor = new DoctorModel();
        $doctor->set("role", "admin");
        // Không gọi markAsAvailable()

        $this->assertFalse($doctor->isAdmin(), "Doctor not marked as available should NOT be admin.");
    }

    // M10_DoctorModel_isAdmin_05
    // Test method isAdmin() không có role
    // Input: doctorModel {is_available = true}
    // Output: false
    // Chú ý: Trong trường hợp này, mặc định role là null hoặc "".
    public function testIsAdminWithNoRole()
    {
        // fwrite(STDOUT, "Running testIsAdminWithNoRole\n");

        $doctor = new DoctorModel();
        // Không set role
        $doctor->markAsAvailable();

        $this->assertFalse($doctor->isAdmin(), "Doctor with no role should NOT be admin.");
    }

    // M10_DoctorModel_canEdit_01
    // Test method canEdit() với role là developer
    // Input: doctorModel {role = 'developer', is_available = true}
    // Output: true
    public function testCanEditAsDeveloper()
    {
        $doctor = new DoctorModel();
        $doctor->set("role", "developer");
        $doctor->markAsAvailable();

        $user = new UserModel();
        $user->set("id", 2);
        $user->set("role", "member");
        $user->markAsAvailable();

        $this->assertTrue($doctor->canEdit($user));
    }

    // M10_DoctorModel_canEdit_02
    // Test method canEdit() khi doctorModel và userModel có id giống nhau
    // Input: doctorModel {id = 5, role = 'admin', is_available = true}, userModel {id = 5, role = 'member', is_available = true}
    // Output: true
    public function testCanEditSelf()
    {
        $doctor = new DoctorModel();
        $doctor->set("id", 5);
        $doctor->set("role", "admin");
        $doctor->markAsAvailable();

        $user = new UserModel();
        $user->set("id", 5);
        $user->set("role", "member");
        $user->markAsAvailable();

        $this->assertTrue($doctor->canEdit($user));
    }

    // M10_DoctorModel_canEdit_03
    // Test method canEdit() với role là admin có thế chỉnh sửa thành viên khác
    // Input: doctorModel {id = 1, role = 'admin', is_available = true}, userModel {id = 2, role = 'member', is_available = true}
    // Output: true
    public function testAdminCanEditMember()
    {
        $doctor = new DoctorModel();
        $doctor->set("role", "admin");
        $doctor->set("id", 1);
        $doctor->markAsAvailable();

        $user = new UserModel();
        $user->set("role", "member");
        $user->set("id", 2);
        $user->markAsAvailable();

        $this->assertTrue($doctor->canEdit($user));
    }

    // M10_DoctorModel_canEdit_04
    // Test method canEdit() với role là admin không thể chỉnh sửa admin khác
    // Input: doctorModel {id = 1, role = 'admin', is_available = true}, doctorModel {id = 2, role = 'admin', is_available = true}
    // Output: False
    public function testAdminCannotEditAdmin()
    {
        $doctor = new DoctorModel();
        $doctor->set("role", "admin");
        $doctor->set("id", 1);
        $doctor->markAsAvailable();

        $user = new UserModel();
        $user->set("role", "admin");
        $user->set("id", 2);
        $user->markAsAvailable();

        $this->assertFalse($doctor->canEdit($user));
    }

    // M10_DoctorModel_canEdit_05
    // Test method canEdit() với role là admin có thể chỉnh sửa user không có trạng thái available
    // Input: doctorModel {role = 'admin', is_available = true}, userModel {role = 'member', is_available = false}
    // Output: true
    // Chú ý: userModel không có trạng thái available
    public function testAdminCanEditInactiveUser()
    {
        $doctor = new DoctorModel();
        $doctor->set("role", "admin");
        $doctor->markAsAvailable();

        $user = new UserModel();
        $user->set("role", "member");
        // Không mark as available

        $this->assertTrue($doctor->canEdit($user));
    }

    // M10_DoctorModel_canEdit_06
    // Test method canEdit() với role là admin có thể chỉnh sửa developer
    // Input: doctorModel {role = 'admin', is_available = true}, userModel {role = 'developer', is_available = true}
    // Output: true
    public function testAdminCanEditDeveloper()
    {
        $doctor = new DoctorModel();
        $doctor->set("role", "admin");
        $doctor->markAsAvailable();

        $user = new UserModel();
        $user->set("role", "developer");
        $user->markAsAvailable();

        $this->assertTrue($doctor->canEdit($user));
    }

    // M10_DoctorModel_canEdit_07
    // Test method canEdit() với role là member không thể chỉnh sửa người khác
    // Input: doctorModel {role = 'member', is_available = true}, userModel {role = 'member', is_available = true}
    // Output: false
    public function testMemberCannotEditOtherUser()
    {
        $doctor = new DoctorModel();
        $doctor->set("role", "member");
        $doctor->set("id", 1);
        $doctor->markAsAvailable();

        $user = new UserModel();
        $user->set("role", "member");
        $user->set("id", 2);
        $user->markAsAvailable();

        $this->assertFalse($doctor->canEdit($user));
    }

    // M10_DoctorModel_canEdit_08
    // Test method canEdit() với role là admin nhưng chưa được đánh dấu là available
    // Input: doctorModel {role = 'admin', is_available = false}, userModel {role = 'member', is_available = true}
    // Output: false
    public function testUnavailableDoctorCannotEditAnyone()
    {
        $doctor = new DoctorModel();
        $doctor->set("role", "admin");
        // Không gọi markAsAvailable()

        $user = new UserModel();
        $user->set("id", 2);
        $user->set("role", "member");
        $user->markAsAvailable();

        $this->assertFalse($doctor->canEdit($user));
    }

    // M10_DoctorModel_isExpired_01
    // Test method isExpired() với ngày hết hạn trong tương lai
    // Input: today + 1 day
    // Output: false
    public function testIsExpired_FutureDate()
    {
        $doctor = new DoctorModel();
        $doctor->set("expire_date", date("Y-m-d H:i:s", strtotime("+1 day")));
        $doctor->markAsAvailable();

        $this->assertFalse($doctor->isExpired());
    }

    // M10_DoctorModel_isExpired_02
    // Test method isExpired() với ngày hết hạn trong quá khứ
    // Input: today - 1 day
    // Output: true
    public function testIsExpired_PastDate()
    {
        $doctor = new DoctorModel();
        $doctor->set("expire_date", date("Y-m-d H:i:s", strtotime("-1 day")));
        $doctor->markAsAvailable();

        $this->assertTrue($doctor->isExpired());
    }

    // M10_DoctorModel_isExpired_03
    // Test method isExpired() với ngày hết hạn là hiện tại
    // Input: today
    // Output: true
    public function testIsExpired_ExactNow()
    {
        $doctor = new DoctorModel();
        $doctor->set("expire_date", date("Y-m-d H:i:s"));
        $doctor->markAsAvailable();

        $this->assertTrue($doctor->isExpired());
    }

    // M10_DoctorModel_isExpired_04
    // Test method isExpired() với ngày hết hạn là null
    // Input: expire_date = null
    // Output: true
    public function testIsExpired_NullExpireDate()
    {
        $doctor = new DoctorModel();
        // Không set expire_date
        $doctor->markAsAvailable();

        $this->assertTrue($doctor->isExpired());
    }

    // M10_DoctorModel_isExpired_05
    // Test method isExpired() với trạng thái không có sẵn (unavailable)
    // Input: doctorModel {is_available = false, expire_date = today + 1 day}
    // Output: true
    public function testIsExpired_DoctorUnavailable()
    {
        $doctor = new DoctorModel();
        $doctor->set("expire_date", date("Y-m-d H:i:s", strtotime("+1 day")));
        // Không gọi markAsAvailable()

        $this->assertTrue($doctor->isExpired());
    }

    // M10_DoctorModel_getDateTimeFormat_01
    // Test method getDateTimeFormat() với doctorModel không được đánh dấu là available
    // Input: doctorModel {is_available = false}
    // Output: null
    public function testGetDateTimeFormat_UnavailableDoctor()
    {
        $doctor = new DoctorModel(); // chưa markAsAvailable
        $this->assertNull($doctor->getDateTimeFormat());
    }

    // M10_DoctorModel_getDateTimeFormat_02
    // Test method getDateTimeFormat() với định dạng ngày giờ là 24h
    // Input: doctorModel với định dạng ngày giờ là 24h
    // Output: "Y-m-d H:i"
    public function testGetDateTimeFormat_24Hour()
    {
        $doctor = new DoctorModel();
        $doctor->set("preferences.dateformat", "Y-m-d");
        $doctor->set("preferences.timeformat", "24");
        $doctor->markAsAvailable();

        $this->assertEquals("Y-m-d H:i", $doctor->getDateTimeFormat());
    }

    // M10_DoctorModel_getDateTimeFormat_03
    // Test method getDateTimeFormat() với định dạng ngày giờ là 12h
    // Input: doctorModel với định dạng ngày giờ là 12h
    // Output: "Y-m-d h:i A"
    public function testGetDateTimeFormat_12Hour()
    {
        $doctor = new DoctorModel();
        $doctor->set("preferences.dateformat", "d/m/Y");
        $doctor->set("preferences.timeformat", "12");
        $doctor->markAsAvailable();
        // fwrite(STDOUT, "\ntestGetDateTimeFormat_12Hour\n");
        // fwrite(STDOUT, var_export($doctor->getDateTimeFormat(), true));
        $this->assertEquals("d/m/Y h:i A", $doctor->getDateTimeFormat());
    }

    // M10_DoctorModel_isEmailVerified_01
    // Test method isEmailVerified() với trạng thái chưa được đánh dấu là available
    // Input: doctorModel {is_available = false}
    // Output: false
    public function testIsEmailVerified_DoctorUnavailable()
    {
        $doctor = new DoctorModel(); // chưa gọi markAsAvailable
        $this->assertFalse($doctor->isEmailVerified());
    }

    // M10_DoctorModel_isEmailVerified_02
    // Test method isEmailVerified() có hash tồn tại
    // Input: doctorModel{is_available = true, email_verification_hash = "12345hash"}
    // Output: false
    public function testIsEmailVerified_HashExists()
    {
        $doctor = new DoctorModel();
        $doctor->set("data.email_verification_hash", "12345hash");
        $doctor->markAsAvailable();
        $this->assertFalse($doctor->isEmailVerified());
    }

    // M10_DoctorModel_isEmailVerified_03
    // Test method isEmailVerified() với hash là null
    // Input: doctorModel{is_available = true, email_verification_hash = null}
    // Output: true
    public function testIsEmailVerified_Verified()
    {
        $doctor = new DoctorModel();
        $doctor->set("data.email_verification_hash", null);
        $doctor->markAsAvailable();
        $this->assertTrue($doctor->isEmailVerified());
    }

    // M10_DoctorModel_isEmailVerified_04
    // Test method isEmailVerified() với hash là một chuỗi rỗng
    // Input: doctorModel{is_available = true, email_verification_hash = ""}
    // Output: true
    public function testIsEmailVerified_HashEmptyString()
    {
        $doctor = new DoctorModel();
        $doctor->set("data.email_verification_hash", "");
        $doctor->markAsAvailable();
        $this->assertTrue($doctor->isEmailVerified());
    }

    // M10_DoctorModel_setEmailAsVerified_01
    // Test method setEmailAsVerified() với trạng thái chưa được đánh dấu là available
    // Input: doctorModel{is_available = false}
    // Output: false
    public function testSetEmailAsVerified_DoctorUnavailable()
    {
        $doctor = new DoctorModel(); // Không gọi markAsAvailable()
        $this->assertFalse($doctor->setEmailAsVerified());
    }

    // M10_DoctorModel_setEmailAsVerified_02
    // Test method setEmailAsVerified() với trạng thái đã được đánh dấu là available
    // Input: doctorModel{is_available = true, email_verification_hash = "123hash"}
    // Output: true, đối tượng doctorModel trả về không có email_verification_hash
    public function testSetEmailAsVerified_WithHash()
    {
        $doctor = $this->getMockBuilder(DoctorModel::class)
                       ->setMethods(['update'])
                       ->getMock();
    
        $data = (object)['email_verification_hash' => '123hash'];
        $doctor->set("data", json_encode($data));
        $doctor->markAsAvailable();
    
        $doctor->expects($this->once())->method('update')->willReturn($doctor);
    
        $this->assertTrue($doctor->setEmailAsVerified());
    
        $resultData = json_decode($doctor->get("data"));
        $this->assertObjectNotHasAttribute("email_verification_hash", $resultData);
    }
    // M10_DoctorModel_setEmailAsVerified_03
    // Test method setEmailAsVerified() với trạng thái đã được đánh dấu là available nhưng không có hash
    // Input: doctorModel{is_available = true, email_verification_hash = null}
    // Output: true
    public function testSetEmailAsVerified_WithoutHash()
    {
        $doctor = $this->getMockBuilder(DoctorModel::class)
                    ->setMethods(['update'])
                    ->getMock();

        $data = (object)['some_other_field' => 'value'];
        $doctor->set("data", json_encode($data));
        $doctor->markAsAvailable();

        $doctor->expects($this->never())->method('update');

        $this->assertTrue($doctor->setEmailAsVerified());
    }
    
    // M10_DoctorModel_setEmailAsVerified_04
    // Test method setEmailAsVerified() với trạng thái đã được đánh dấu là available nhưng không có dữ liệu
    // Input: doctorModel{is_available = true, data = null}
    // Output: true
    public function testSetEmailAsVerified_NullData()
    {
        $doctor = new DoctorModel();
        $doctor->set("data", null);
        $doctor->markAsAvailable();

        $this->assertTrue($doctor->setEmailAsVerified());
    }

}
