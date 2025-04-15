<?php

use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

require_once __DIR__ . '/../../api/app/core/DataEntry.php';
require_once __DIR__ . '/../../api/app/models/DrugModel.php';
require_once __DIR__ . '/../../api/app/config/db.config.php';

class DrugModelTest extends TestCase
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

    //test_M02_DrugModel_select_01: Truy vấn theo id tồn tại ($uniquid = 1)
    public function test_M02_DrugModel_select_01(): void{
        $ids = 1;
        $drug = new DrugModel($ids);

        $this->assertEquals("1", $drug->get("id"));
        $this->assertEquals("Vitamin B1", $drug->get("name"));

        $dbDrug= DB::table(TABLE_PREFIX.TABLE_DRUGS)->where("id", "=", $ids)->get();
        $this->assertEquals(1, count($dbDrug));
        $this->assertEquals( $dbDrug[0]->id, $drug->get("id"));
        $this->assertEquals( $dbDrug[0]->name, $drug->get("name"));
    }

     //test_M02_DrugModel_select_02: Truy vấn với id = 0 (không tồn tại)
    public function test_M09_SpecialityModel_select_02(): void{
        $ids = 0;
        $drug = new DrugModel($ids);

        $this->assertFalse( $drug->isAvailable());
        $this->assertNull($drug->get("id"));
    }

    //test_M02_DrugModel_select_03: Truy vấn với ID là số nguyên dương không tồn tại (id = 9999)
     public function test_M09_SpecialityModel_select_03(): void{
        $ids = 9999;
        $drug = new DrugModel($ids);

        $this->assertFalse( $drug->isAvailable());
        $this->assertNull($drug->get("id"));
    }

    //test_M02_DrugModel_select_04: Truy vấn với ID là số nguyên âm (id = -1)
    public function test_M09_SpecialityModel_select_04(): void{
        $ids = -1;
        $drug = new DrugModel($ids);

        $this->assertFalse( $drug->isAvailable());
        $this->assertNull($drug->get("id"));
    }

    //test_M02_DrugModel_select_05: Truy vấn với ID là một chuỗi số (id = "123")
    public function test_M09_SpecialityModel_select_05(): void{
        $ids = "123";
        $drug = new DrugModel($ids);
        $drugDb = 
        $this->assertFalse( $drug->isAvailable());
        $this->assertNull($drug->get("id"));
    }

    //test_M02_DrugModel_select_06: Truy vấn với &uniqid là tên thuốc tồn tại
    public function test_M09_SpecialityModel_select_06(): void{
        $ids = "Insulin";
        $drug = new DrugModel($ids);

        $this->assertTrue( $drug->isAvailable());
        $this->assertNotNull($drug->get("id"));
        $this->assertEquals("Insulin", $drug->get("name"));
    }

    //test_M02_DrugModel_select_07: Truy vấn với &uniqid là tên thuốc không tồn tại
    public function test_M09_SpecialityModel_select_07(): void{
        $ids = "MedicineNotExist";
        $drug = new DrugModel($ids);

        $this->assertFalse( $drug->isAvailable());
        $this->assertNull($drug->get("id"));
        $this->assertNull($drug->get("name"));
    }


    // test hàm extendDefaults()
    // test_M02_DrugModel_extenDefaults_01
    // Tất cả field đều null
    public function test_M02_DrugModel_extenDefaults_01(): void{
        //Tất cả các field đều là null → sẽ được gán giá trị mặc định

        //Khởi tạo một đối tượng rỗng
        $drug = new DrugModel();
        $drug->set("id", null);
        $drug->set("name", null);
       
        // Gọi hàm để gán giá trị mặc định
        $drug->extendDefaults();
    
        // Kiểm tra các giá trị mặc định đã được gán đúng
        $this->assertEquals("", $drug->get("id"));         // Trường id phải là ""
        $this->assertEquals("", $drug->get("name"));  // Trường name phải là ""
    }
    

    // test_M02_DrugModel_extenDefaults_02: Một số field null (id = null, name = "Thuốc bổ")
    public function test_M02_DrugModel_extenDefaults_02(): void{

        $drug = new DrugModel();
        $drug->set("id", null);
        $drug->set("name", "Thuốc bổ");
    
        // Gọi hàm để mở rộng mặc định
        $drug->extendDefaults();
    
        $this->assertEquals("", $drug->get("id"));         
        $this->assertEquals("Thuốc bổ", $drug->get("name"));
    }
    

    // test_M02_DrugModel_extenDefaults_03: Không field nào null (id = 15, name = Thuốc nhỏ mắt)
    public function test_M02_DrugModel_extenDefaults_03(): void{
    
        $drug = new DrugModel();
        $drug->set("id", 15);
        $drug->set("name", "Thuốc nhỏ mắt");
    
        // Gọi hàm để mở rộng mặc định
        $drug->extendDefaults();
    
        $this->assertEquals("15", $drug->get("id"));        
        $this->assertEquals("Thuốc nhỏ mắt", $drug->get("name"));
    }


    // Test hàm insert
    // test_M02_DrugModel_insert_01: 
    //Đối tượng chưa tồn tại (isAvailable = False), thêm mới thuốc với đầy đủ trường dữ liệu 
    public function test_M02_DrugModel_insert_01()
    {
        // Set các giá trị để thêm mới
        $drug = new DrugModel();
        $drug->set("name", "Thuốc dị ứng");

        // Act
        $drug->insert();

        // Assert
        $this->assertTrue($drug->isAvailable());
        $this->assertNotNull($drug->get("id"));
        $this->assertEquals("Thuốc dị ứng", $drug->get("name"));

        // Verify in DB
        $dbDrug= DB::table(TABLE_PREFIX.TABLE_DRUGS)->where("id", "=", $drug->get("id"))->get();
        $this->assertEquals("Thuốc dị ứng", $dbDrug[0]->name);
    }

    // test_M02_DrugModel_insert_02: Đối tượng chưa tồn tại (isAvailable = False), thêm mới thuốc với trường name rỗng
    public function test_M02_DrugModel_insert_02(): void{
        $drug = new DrugModel();
        $drug->set("name", ""); // không phải string mô tả

        $drug->insert();
    
        $dbDrug= DB::table(TABLE_PREFIX.TABLE_DRUGS)->where("id", "=", $drug->get("id"))->get();
        $this->assertEquals("", $dbDrug[0]->name);
    }

    // test_M02_DrugModel_insert_03: Đối tượng chưa tồn tại (isAvailable = False), thêm mới thuốc với trường name là số
    public function test_M0test_M02_DrugModel_insert_03(): void {
        // Trường hợp: name là một số (không hợp lệ)
        $drug = new DrugModel();
        $drug->set("name", 12345); // không phải string mô tả

        $result = $drug->insert();
        
        // Check DB
        $dbDrug= DB::table(TABLE_PREFIX.TABLE_DRUGS)->where("id", "=", $drug->get("id"))->get();
        $this->assertNotNull($dbDrug[0]->id);
        $this->assertEquals(12345, $dbDrug[0]->name);
    }
    
    // test_M02_DrugModel_insert_04: Đối tượng chưa tồn tại (isAvailable = False), thêm mới thuốc với trường name là chuỗi ký tự bao gồm cả số, chữ, kí tự đặc biệt
    public function test_M02_DrugModel_insert_04(): void {
        $drug = new DrugModel();
        $drug->set("name", "1aA@!_"); 
    
        $result = $drug->insert();
    
        // Mong muốn insert bị từ chối
        $this->assertNotNull($drug->get("id"));
        $this->assertEquals("1aA@!_", $drug->get("name"));

        $dbDrug= DB::table(TABLE_PREFIX.TABLE_DRUGS)->where("id", "=", $drug->get("id"))->get();
        $this->assertNotNull($dbDrug[0]->id);
        $this->assertEquals("1aA@!_", $dbDrug[0]->name);
    }

    // test_M02_DrugModel_insert_05: khi isAvailable() trả về true, không thêm mới
    public function test_M02_DrugModel_insert_05()
    {
        $drug = new DrugModel();
        $drug->markAsAvailable(); // Đánh dấu là đã tồn tại

        // Gọi insert, mong đợi trả về false
        $result = $drug->insert();
        $this->assertFalse($result); // Không cho phép insert lần nữa
    }


    // Test hàm update()
    // test_M02_DrugModel_update_01
    // Cập nhật tất cả các trường khi isAvailable trả về TRUE (đối tượng đã tồn tại trong database)
    public function test_M02_DrugModel_update_01()
    {
        // Tạo một record mới trước để test update
        $drug = new DrugModel(8);
        
        $this->assertTrue($drug->isAvailable());
        $this->assertEquals("Ether", $drug->get("name"));
        
        // Cập nhật dữ liệu
        $drug->set("name", "Ether cập nhật");

        // Act
        $result = $drug->update();
        
        // Assert
        $this->assertSame($drug, $result);
        
        // Verify in DB
        $fetchDrug = DB::table(TABLE_PREFIX.TABLE_DRUGS)->where("id", "=", $drug->get("id"))->get();
        $this->assertEquals("Ether cập nhật", $fetchDrug[0]->name);
    }


    //test_M02_DrugModel_update_02: Không cập nhật khi isAvailable() trả về false (đối tượng k tồn tại trong DB)
    public function test_M02_DrugModel_update_02()
    {
        // Tạo đối tượng nhưng KHÔNG gọi select(), nên isAvailable = false
        $drug = new DrugModel(9999);
        
        //gọi update() trong khi isAvailable = false
        $result = $drug->update();

        // Assert
        $this->assertFalse($result); // Không được phép cập nhật
    }


    // test_M02_DrugModel_update_03: Cập nhật với tham số name rỗng
    public function test_M02_DrugModel_update_03()
    {
        $drug = new DrugModel(8);
        
        $this->assertTrue($drug->isAvailable());
        $this->assertEquals("Ether", $drug->get("name"));

        $drug->set("name", "");

        $result = $drug->update();

        $fetchDrug = DB::table(TABLE_PREFIX.TABLE_DRUGS)->where("id", "=", $drug->get("id"))->get();
        $this->assertEquals("", $fetchDrug[0]->name);
    }


    //test_M02_DrugModel_update_04: Cập nhật với tham số name là 1 số
    public function test_M02_DrugModel_update_04()
    {
        // Tạo đối tượng nhưng KHÔNG gọi select() hay save(), nên isAvailable = false
        $drug = new DrugModel(8);
        
        $this->assertTrue($drug->isAvailable());
        $this->assertEquals("Ether", $drug->get("name"));

        $drug->set("name", 9223);

        // Act - gọi update() trong khi isAvailable = false
        $result = $drug->update();
        $this->assertSame($drug, $result);

        $fetchDrug = DB::table(TABLE_PREFIX.TABLE_DRUGS)->where("id", "=", $drug->get("id"))->get();
        
        $this->assertEquals(9223, $fetchDrug[0]->name);
    }


    //test_M02_DrugModel_update_05: Cập nhật với tham số name là 1 chuỗi ký tự gồm số, chữ cái, kí tự đặc biệt và dấu cách
    public function test_M02_DrugModel_update_05()
    {
        // Tạo đối tượng nhưng KHÔNG gọi select() hay save(), nên isAvailable = false
        $drug = new DrugModel(8);
        
        $this->assertTrue($drug->isAvailable());
        $this->assertEquals("Ether", $drug->get("name"));

        $drug->set("name", "aA1@_ " );
        
        // Act - gọi update() trong khi isAvailable = false
        $result = $drug->update();

        $this->assertSame($drug, $result);

        $fetchDrug = DB::table(TABLE_PREFIX.TABLE_DRUGS)->where("id", "=", $drug->get("id"))->get();
        $this->assertEquals("aA1@_ " , $fetchDrug[0]->name);
    }


    // Test hàm delete()
    // test_Module_02_DrugModel_delete_01
    // Xoá thành công khi isAvailable() trả về true (đối tượng đã tồn tại trong DB)
    public function test_Module_02_DrugModel_delete_01()
    {
        // Arrange
        // Tạo một record mới trước để test delete
        $drug = new DrugModel(3);
        
        // Đảm bảo record đã được tạo
        $this->assertTrue($drug->isAvailable());
        $this->assertEquals("Vitamin B2", $drug->get("name"));
        
        // Act
        $result = $drug->delete();
        
        // Assert
        $this->assertTrue($result);
        
        // Verify in DB
        $fetchDrug = new DrugModel(3);
        $this->assertFalse($fetchDrug->isAvailable());
        
        // Kiểm tra trực tiếp từ DB
        $dbData = DB::table(TABLE_PREFIX.TABLE_DRUGS)->where("id", "=", 3)->get();
        $this->assertCount(0, $dbData);
    }
    

    // test_Module_02_DrugModel_delete_02: Không xoá khi isAvailable() trả về false (đối tượng không tồn tại trong DB)
    public function test_SpecialityModel_delete_02()
    {
        // Arrange
        $drug = new DrugModel(9999);
        
        // Act
        $result = $drug->delete();
        
        // Assert
        $this->assertFalse($result);
    }
}