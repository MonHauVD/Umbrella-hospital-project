<?php

use PHPUnit\Framework\TestCase;
use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

require_once __DIR__ . '/../../../api/app/core/DataEntry.php';
require_once __DIR__ . '/../../../api/app/models/DoctorModel.php';
require_once __DIR__ . '/../../../api/app/config/db.config.php';

class DoctorModelTest3 extends TestCase
{
    protected static $db;
    protected static $qb;

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

    public function testUpdateDoctor()
    {
        DB::transaction(function ($qb) {
            // Arrange
            $doctor = new DoctorModel(36);
            $this->assertTrue($doctor->isAvailable(), "Doctor with ID 36 does not exist!");

            $newName = "Test Doctor Transaction huhu";
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
            $dbDoctor = $qb->table(TABLE_PREFIX . TABLE_DOCTORS)->where("id", "=", 36)->get();
            $this->assertEquals($newName, $dbDoctor[0]->name);
            $this->assertEquals($newDescription, $dbDoctor[0]->description);

            $qb->rollback();
        });
    }
}
