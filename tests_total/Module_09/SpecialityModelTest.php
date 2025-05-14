<?php

use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;


class SpecialityModelTest extends TestCase
{
    protected static $db;
    protected static $qb;

    public static function setUpBeforeClass(): void
    {
        // Khởi tạo Pixie Connection
        $config =  require __DIR__ . '/../../LocalConfigDB.php';
        self::$db = new Connection('mysql', $config, 'DB');
        self::$qb = self::$db->getQueryBuilder();
    }
    public function setUp(): void
    {
        // Bắt đầu transaction trước mỗi test case
        self::$db->getPdoInstance()->beginTransaction();
    }

    public function tearDown(){
        // Rollback transaction sau mỗi test case
        self::$db->getPdoInstance()->rollback();
    }


    // test hàm select
    // M09_SpecialityModel_select_01: Truy vấn theo ID tồn tại
    public function test_M09_SpecialityModel_select_01(): void{
        $ids = 8;
        $speciality = new SpecialityModel($ids);

        $this->assertEquals("8", $speciality->get("id"));
        $this->assertEquals("Nhãn khoa", $speciality->get("name"));
        $this->assertEquals("Chuyên khoa răng - hàm - mặt", $speciality->get("description"));
        $this->assertEquals("speciality_8_1668691572.jpg", $speciality->get("image"));

        $dbSpeciality= DB::table(TABLE_PREFIX.TABLE_SPECIALITIES)->where("id", "=", $ids)->get();
        $this->assertEquals(1, count($dbSpeciality));
        $this->assertEquals( $dbSpeciality[0]->id, $speciality->get("id"));
        $this->assertEquals( $dbSpeciality[0]->name, $speciality->get("name"));
        $this->assertEquals( $dbSpeciality[0]->description, $speciality->get("description"));
        $this->assertEquals( $dbSpeciality[0]->image, $speciality->get("image"));
    }

    // M09_SpecialityModel_select_02: Truy vấn với ID = 0 (không tồn tại)
    public function test_M09_SpecialityModel_select_02(): void{
        $ids = 0;
        $speciality = new SpecialityModel($ids);

        $this->assertFalse( $speciality->isAvailable());
        $this->assertNull($speciality->get("id"));
    }

    // M09_SpecialityModel_select_03: Truy vấn với chuỗi số "0"
    public function test_M09_SpecialityModel_select_03(): void{
        $ids = "0";
        $speciality = new SpecialityModel($ids);

        $this->assertFalse( $speciality->isAvailable());
        $this->assertNull($speciality->get("id"));
    }


    // M09_SpecialityModel_select_04: Truy vấn với tên "abc" tồn tại
    public function test_M09_SpecialityModel_select_04(): void{
        $ids = "Tiêu hoá";
        $speciality = new SpecialityModel($ids);

        $this->assertTrue( $speciality->isAvailable());
        $this->assertNotNull($speciality->get("id"));
    }


    // M09_SpecialityModel_select_05: Truy vấn với tên "xyz" không tồn tại
    public function test_M09_SpecialityModel_select_05(): void{
        $ids = "Nha Khoa";
        $speciality = new SpecialityModel($ids);

        $this->assertFalse( $speciality->isAvailable());
        $this->assertNull($speciality->get("id"));
    }


    // test hàm extendDefaults()
    // M09_SpecialityModel_extendDefaults_01: Tất cả field đều null
    public function test_M10_SpecialityModel_extendDefaults_01(): void{
        //Tất cả các field đều là null → sẽ được gán giá trị mặc định
        //Khởi tạo một đối tượng rỗng
        $speciality = new SpecialityModel();
        $speciality->set("name", null);
        $speciality->set("description", null);
        $speciality->set("image", null);
    
        // Gọi hàm để gán giá trị mặc định
        $speciality->extendDefaults();
    
        // Kiểm tra các giá trị mặc định đã được gán đúng
        $this->assertEquals("", $speciality->get("name"));         // Trường name phải là ""
        $this->assertEquals("", $speciality->get("description"));  // Trường description phải là ""
        $this->assertEquals("", $speciality->get("image"));        // Trường image phải là ""
    }
    

    // M09_SpecialityModel_extendDefaults_02: Một số field null (name = Khoa dạ dày, description = null, image = null)
    public function test_M09_SpecialityModel_extendDefaults_02(): void{

        $speciality = new SpecialityModel();
        $speciality->set("name", "Khoa dạ dày");
        $speciality->set("description", null);
        $speciality->set("image", null);
    
        // Gọi hàm để mở rộng mặc định
        $speciality->extendDefaults();
    
        // Các giá trị ban đầu vẫn giữ nguyên
        $this->assertEquals("Khoa dạ dày", $speciality->get("name"));
        $this->assertEquals("", $speciality->get("description"));
        $this->assertEquals("", $speciality->get("image"));
    }
    

    // M09_SpecialityModel_extendDefaults_03: Không field nào null (name = Khoa não, description = Chữa các bệnh về não, image = anh.jpg)
    public function test_M09_SpecialityModel_extendDefaults_03(): void{
    
        $speciality = new SpecialityModel();
        $speciality->set("name", "Khoa não");
        $speciality->set("description", "Chữa các bệnh về não");
        $speciality->set("image", "anh.jpg");
    
        // Gọi hàm để mở rộng mặc định
        $speciality->extendDefaults();
    
        // Các giá trị ban đầu vẫn giữ nguyên
        $this->assertEquals("Khoa não", $speciality->get("name"));
        $this->assertEquals("Chữa các bệnh về não", $speciality->get("description"));
        $this->assertEquals("anh.jpg", $speciality->get("image"));
    }


    
    // test_M09_SpecialityModel_insert_01: Đối tượng chưa tồn tại (isAvailable = False), thêm mới chuyên khoa với đầy đủ trường dữ liệu 
    public function test_M09_SpecialityModel_insert_01()
    {
        
        // Set các giá trị để thêm mới
        $speciality = new SpecialityModel();
        $speciality->set("name", "Khoa Ngoại");
        $speciality->set("description", "Chữa bệnh ngoại khoa");
        $speciality->set("image", "ngoại khoa.jpg");
        
        // Act
        $speciality->insert();  // trả ve id

        // Assert
        $this->assertTrue($speciality->isAvailable());
        $this->assertNotNull($speciality->get("id"));
        $this->assertEquals("Khoa Ngoại", $speciality->get("name"));
        $this->assertEquals("Chữa bệnh ngoại khoa", $speciality->get("description"));
        $this->assertEquals("ngoại khoa.jpg", $speciality->get("image"));
    
        // Verify in DB
        $fetchSpeciality = DB::table(TABLE_PREFIX.TABLE_SPECIALITIES)->where("id", "=", $speciality->get("id"))->get();
        $this->assertEquals("Khoa Ngoại", $fetchSpeciality[0]->name);
        $this->assertEquals("Chữa bệnh ngoại khoa", $fetchSpeciality[0]->description);
        $this->assertEquals("ngoại khoa.jpg", $fetchSpeciality[0]->image);
    }

    // test_M09_SpecialityModel_insert_02: Đối tượng chưa tồn tại (isAvailable = False), thêm mới chuyên khoa với trường description rỗng
    public function test_M09_SpecialityModel_insert_02(): void{
        $speciality = new SpecialityModel();
        $speciality->set("name", "Khoa xương khớp");
        $speciality->set("image", "xuongkhop.jpg");

        $speciality->insert();  // tra ve id

        // Kiểm tra các trường đã được gán mặc định
        $this->assertTrue($speciality->isAvailable()); // Sau insert, phải available
        $this->assertNotNull($speciality->get("id")); // ID phải được tạo
        $this->assertEquals("Khoa xương khớp", $speciality->get("name"));
        $this->assertEquals("", $speciality->get("description"));
        $this->assertEquals("xuongkhop.jpg", $speciality->get("image"));
   
       // Verify in DB
       $fetchSpeciality = DB::table(TABLE_PREFIX.TABLE_SPECIALITIES)->where("id", "=", $speciality->get("id"))->get();
       $this->assertEquals($speciality->get("name"), $fetchSpeciality[0]->name);
       $this->assertEquals($speciality->get("description"), $fetchSpeciality[0]->description);
       $this->assertEquals($speciality->get("image"), $fetchSpeciality[0]->image);
    }


    // test_M09_SpecialityModel_insert_03: Đối tượng chưa tồn tại (isAvailable = False), thêm mới chuyên khoa với trường image rỗng
    public function test_M09_SpecialityModel_insert_03(): void{
        $speciality = new SpecialityModel();
        $speciality->set("name", "Khoa xương khớp");
        $speciality->set("description", "Chữa các bệnh về xương khớp");

        $speciality->insert();

        // Kiểm tra các trường đã được gán mặc định
        $this->assertTrue($speciality->isAvailable()); // Sau insert, phải available
        $this->assertNotNull($speciality->get("id")); // ID phải được tạo
        $this->assertEquals("Khoa xương khớp", $speciality->get("name"));
        $this->assertEquals("Chữa các bệnh về xương khớp", $speciality->get("description"));
        $this->assertEquals("", $speciality->get("image"));

        // Verify in DB (optional)
        $fetchSpeciality = DB::table(TABLE_PREFIX.TABLE_SPECIALITIES)->where("id", "=", $speciality->get("id"))->get();
        $this->assertEquals($speciality->get("name"), $fetchSpeciality[0]->name);
        $this->assertEquals("Chữa các bệnh về xương khớp", $fetchSpeciality[0]->description);
        $this->assertEquals("", $fetchSpeciality[0]->image);
    }

    // test_M09_SpecialityModel_insert_04: Đối tượng chưa tồn tại (isAvailable = False), thêm mới chuyên khoa với trường name rỗng
    public function test_M09_SpecialityModel_insert_04(): void{
        $speciality = new SpecialityModel();
        $speciality->set("image", "xuongkhop.png");
        $speciality->set("description", "Chữa các bệnh về xương khớp");

        $speciality->insert();

        $this->assertTrue($speciality->isAvailable()); // Sau insert, phải available
        $this->assertNotNull($speciality->get("id")); // ID phải được tạo
      
        // Kiểm tra các trường đã được gán mặc định
        $this->assertEquals("", $speciality->get("name"));
        $this->assertEquals("Chữa các bệnh về xương khớp", $speciality->get("description")); // do extendDefaults() gán
        $this->assertEquals("xuongkhop.png", $speciality->get("image"));

        // Kiem tra DB
        $fetchSpeciality = DB::table(TABLE_PREFIX.TABLE_SPECIALITIES)->where("id", "=", $speciality->get("id"))->get();
        $this->assertEquals($speciality->get("name"), $fetchSpeciality[0]->name);
        $this->assertEquals($speciality->get("description"), $fetchSpeciality[0]->description);
        $this->assertEquals($speciality->get("image"), $fetchSpeciality[0]->image);
    }


    // test_M09_SpecialityModel_insert_05: Đối tượng chưa tồn tại (isAvailable = False), thêm mới chuyên khoa với trường name là số
    public function test_M09_SpecialityModel_insert_05(): void {
        // Trường hợp: name là một số (không hợp lệ)
        $speciality = new SpecialityModel();
        $speciality->set("name", 12345); // không phải string mô tả
        $speciality->set("description", "Khoa đặc biệt cho bệnh nhân cao tuổi");
        $speciality->set("image", "cao_tuoi.png");
    
        $speciality->insert();

        // kiểm tra
        $this->assertTrue($speciality->isAvailable()); // Sau insert, phải available
        $this->assertNotNull($speciality->get("id")); // ID phải được tạo

        // kiểm tra lại trong db
        $fetchSpeciality = DB::table(TABLE_PREFIX.TABLE_SPECIALITIES)->where("id", "=", $speciality->get("id"))->get();
        $this->assertEquals($speciality->get("name"), $fetchSpeciality[0]->name);
        $this->assertEquals($speciality->get("description"), $fetchSpeciality[0]->description);
        $this->assertEquals($speciality->get("image"), $fetchSpeciality[0]->image);
    }
    
    // test_M09_SpecialityModel_insert_06: Đối tượng chưa tồn tại (isAvailable = False), thêm mới chuyên khoa với trường name là chuỗi ký tự bao gồm cả số, chữ, kí tự đặc biệt
    
    public function test_M09_SpecialityModel_insert_06(): void {
        // Trường hợp: name là một số (không hợp lệ)
        $speciality = new SpecialityModel();
        $speciality->set("name", "1aA@!_"); 
        $speciality->set("description", "Khoa đặc biệt cho người khuyết tật");
        $speciality->set("image", "kt.png");
    
        $speciality->insert();
        
          // kiểm tra
          $this->assertTrue($speciality->isAvailable()); // Sau insert, phải available
          $this->assertNotNull($speciality->get("id")); // ID phải được tạo
  
          // kiểm tra lại trong db
          $fetchSpeciality = DB::table(TABLE_PREFIX.TABLE_SPECIALITIES)->where("id", "=", $speciality->get("id"))->get();
          $this->assertEquals($speciality->get("name"), $fetchSpeciality[0]->name);
          $this->assertEquals($speciality->get("description"), $fetchSpeciality[0]->description);
          $this->assertEquals($speciality->get("image"), $fetchSpeciality[0]->image);
    }

    // test_M09_SpecialityModel_insert_07: khi isAvailable() trả về true, không thêm mới
    public function test_M09_SpecialityModel_insert_07()
    {
        $speciality = new SpecialityModel();
        $speciality->markAsAvailable(); // Đánh dấu là đã tồn tại

        // Gọi insert, mong đợi trả về false
        $result = $speciality->insert();
        $this->assertFalse($result); // Không cho phép insert lần nữa
    }


    // Test hàm update()
    // test_M09_SpecialityModel_update_01: Cập nhật tất cả các trường khi isAvailable trả về TRUE (đối tượng đã tồn tại trong database)
    public function test_SpecialityModel_update_01()
    {
        // Tạo một record mới trước để test update
        $speciality = new SpecialityModel(8);
        
        $this->assertTrue($speciality->isAvailable());
        $this->assertEquals("Nhãn khoa", $speciality->get("name"));
        
        // Cập nhật dữ liệu
        $speciality->set("name", "Nội khoa cập nhật");
        $speciality->set("description", "Mô tả mới");
        $speciality->set("image", "new_image.jpg");
        
        // Act
        $result = $speciality->update();
        
        // Assert
        $this->assertSame($speciality, $result);
        
        // Verify in DB
        $fetchSpeciality = DB::table(TABLE_PREFIX.TABLE_SPECIALITIES)->where("id", "=", $speciality->get("id"))->get();
        $this->assertEquals($speciality->get("name"), $fetchSpeciality[0]->name);
        $this->assertEquals($speciality->get("description"), $fetchSpeciality[0]->description);
        $this->assertEquals($speciality->get("image"), $fetchSpeciality[0]->image);
    }

    // test_M09_SpecialityModel_update_02: Cập nhật một trường khi isAvailable trả về TRUE (đối tượng đã tồn tại trong database)
    public function test_SpecialityModel_update_02()
    {
        // Tạo một record mới trước để test update
        $speciality = new SpecialityModel(8);
        
        $this->assertTrue($speciality->isAvailable());
        $this->assertEquals("Nhãn khoa", $speciality->get("name"));
        
        // Cập nhật dữ liệu
        $speciality->set("name", "Nhãn khoa cập nhật");
        $speciality->set("description", "Chuyên khoa răng - hàm - mặt");
        $speciality->set("image", "speciality_8_1668691572.jpg");
        
        // Act
        $result = $speciality->update();
        
        // Assert
        $this->assertSame($speciality, $result);
        
        // Check DB
        $fetchSpeciality = DB::table(TABLE_PREFIX.TABLE_SPECIALITIES)->where("id", "=", $speciality->get("id"))->get();
        $this->assertEquals($speciality->get("name"), $fetchSpeciality[0]->name);
        $this->assertEquals($speciality->get("description"), $fetchSpeciality[0]->description);
        $this->assertEquals($speciality->get("image"), $fetchSpeciality[0]->image);
    }


    //test_M09_SpecialityModel_update_03: Không cập nhật khi isAvailable() trả về false (đối tượng k tồn tại trong DB)
    public function test_SpecialityModel_update_03()
    {
        // Tạo đối tượng nhưng KHÔNG gọi select() hay save(), nên isAvailable = false
        $speciality = new SpecialityModel(9999);
        
        //gọi update() trong khi isAvailable = false
        $result = $speciality->update();

        // Assert
        $this->assertFalse($result); // Không được phép cập nhật
    }


    // test_M09_SpecialityModel_update_04: Cập nhật với tham số name rỗng
    public function test_SpecialityModel_update_04()
    {
        $speciality = new SpecialityModel(8);
        
        $this->assertTrue($speciality->isAvailable());
        $this->assertEquals("Nhãn khoa", $speciality->get("name"));

        $speciality->set("name", "");

        // gọi update() trong khi isAvailable = true
        $result = $speciality->update();

        $this->assertSame($speciality, $result);

        //Check DB
        $fetchSpeciality = DB::table(TABLE_PREFIX.TABLE_SPECIALITIES)->where("id", "=", $speciality->get("id"))->get();
        $this->assertEquals($speciality->get("name"), $fetchSpeciality[0]->name);
        $this->assertEquals($speciality->get("description"), $fetchSpeciality[0]->description);
        $this->assertEquals($speciality->get("image"), $fetchSpeciality[0]->image);
    }


    //test_M09_SpecialityModel_update_05: Cập nhật với tham số name là 1 số
    public function test_SpecialityModel_update_05()
    {
        // Tạo đối tượng nhưng KHÔNG gọi select() hay save(), nên isAvailable = false
        $speciality = new SpecialityModel(8);
        
        $this->assertTrue($speciality->isAvailable());
        $this->assertEquals("Nhãn khoa", $speciality->get("name"));

        $speciality->set("name", 9223);
        // Act - gọi update() trong khi isAvailable = false
        $result = $speciality->update();

        $this->assertSame($speciality, $result);

         //Check DB
        $fetchSpeciality = DB::table(TABLE_PREFIX.TABLE_SPECIALITIES)->where("id", "=", $speciality->get("id"))->get();
        $this->assertEquals($speciality->get("name"), $fetchSpeciality[0]->name);
        $this->assertEquals($speciality->get("description"), $fetchSpeciality[0]->description);
        $this->assertEquals($speciality->get("image"), $fetchSpeciality[0]->image);
    }

    //test_M09_SpecialityModel_update_06: Cập nhật với tham số name là 1 chuỗi ký tự gồm số, chữ cái, kí tự đặc biệt và dấu cách
    public function test_SpecialityModel_update_06()
    {
        // Tạo đối tượng nhưng KHÔNG gọi select() hay save(), nên isAvailable = false
        $speciality = new SpecialityModel(8);
        
        $this->assertTrue($speciality->isAvailable());
        $this->assertEquals("Nhãn khoa", $speciality->get("name"));

        $speciality->set("name", "aA1@_ " );
        
        // Act - gọi update() trong khi isAvailable = false
        $result = $speciality->update();

        $this->assertSame($speciality, $result);

        //Check DB
        $fetchSpeciality = DB::table(TABLE_PREFIX.TABLE_SPECIALITIES)->where("id", "=", $speciality->get("id"))->get();
        $this->assertEquals($speciality->get("name"), $fetchSpeciality[0]->name);
        $this->assertEquals($speciality->get("description"), $fetchSpeciality[0]->description);
        $this->assertEquals($speciality->get("image"), $fetchSpeciality[0]->image);
    }

    //test_M09_SpecialityModel_update_07: Cập nhật với trường description rỗng
    public function test_SpecialityModel_update_07()
    {
        // Tạo đối tượng nhưng KHÔNG gọi select() hay save(), nên isAvailable trả về false
        $speciality = new SpecialityModel(8);
        
        $this->assertTrue($speciality->isAvailable());
        $this->assertEquals("Chuyên khoa răng - hàm - mặt", $speciality->get("description"));

        $speciality->set("description", "");
        
        // Act - gọi update() trong khi isAvailable = false
        $result = $speciality->update();

        $this->assertSame($speciality, $result);

        // check DB
        $fetchSpeciality = DB::table(TABLE_PREFIX.TABLE_SPECIALITIES)->where("id", "=", $speciality->get("id"))->get();
        $this->assertEquals($speciality->get("name"), $fetchSpeciality[0]->name);
        $this->assertEquals($speciality->get("description"), $fetchSpeciality[0]->description);
        $this->assertEquals($speciality->get("image"), $fetchSpeciality[0]->image);
    }


    //test_M09_SpecialityModel_update_08: Cập nhật với trường image rỗng
    public function test_SpecialityModel_update_08()
    {
        // Tạo đối tượng nhưng KHÔNG gọi select() hay save(), nên isAvailable() trả về false
        $speciality = new SpecialityModel(8);
        
        $this->assertTrue($speciality->isAvailable());
        $this->assertEquals("speciality_8_1668691572.jpg", $speciality->get("image"));

        $speciality->set("image", "");
        
        // Act - gọi update() trong khi isAvailable = false
        $result = $speciality->update();

        $this->assertSame($speciality, $result);

        
        // check DB
        $fetchSpeciality = DB::table(TABLE_PREFIX.TABLE_SPECIALITIES)->where("id", "=", $speciality->get("id"))->get();
        $this->assertEquals($speciality->get("name"), $fetchSpeciality[0]->name);
        $this->assertEquals($speciality->get("description"), $fetchSpeciality[0]->description);
        $this->assertEquals($speciality->get("image"), $fetchSpeciality[0]->image);
    }


    // Test hàm delete()
    //test_M09_SpecialityModel_delete_01: Xoá thành công khi isAvailable() trả về true (đối tượng đã tồn tại trong DB)
    public function test_SpecialityModel_delete_01()
    {
        // Arrange
        // Tạo một record mới trước để test delete
        $speciality = new SpecialityModel(3);
        
        // Đảm bảo record đã được tạo
        $this->assertTrue($speciality->isAvailable());
        $this->assertEquals("Nhi khoa", $speciality->get("name"));
        
        // Act
        $result = $speciality->delete();
        
        // Assert
        $this->assertTrue($result);
        
        // Kiểm tra trực tiếp từ DB
        $dbData = DB::table(TABLE_PREFIX.TABLE_SPECIALITIES)->where("id", "=", 3)->first();
        $this->assertNull($dbData);
    }

    //test_M09_SpecialityModel_delete_02: Không xoá khi isAvailable() trả về false (đối tượng không tồn tại trong DB)
    public function test_SpecialityModel_delete_02()
    {
        // Arrange
        $speciality = new SpecialityModel(9999);
        
        // Act
        $result = $speciality->delete();
        
        // Assert
        $this->assertFalse($result);
    }
}