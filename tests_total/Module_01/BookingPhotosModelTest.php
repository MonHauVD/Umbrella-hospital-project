<?php
use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;


class BookingPhotosModelTest extends TestCase
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

    public function test_M09_SpecialitiesModel_getAll_01()
    {
       
        // Lấy tổng số bản ghi trong DB
        $dbCount = DB::table(TABLE_PREFIX.TABLE_BOOKING_PHOTOS)->count();

        // Gọi model để fetch data
        $specialities = new BookingPhotosModel();
        $specialities->fetchData();

        // So sánh số bản ghi model fetch được với DB
        $this->assertEquals($dbCount, $specialities->getTotalCount());
 
    }
}