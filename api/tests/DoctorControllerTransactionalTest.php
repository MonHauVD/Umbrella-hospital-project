<?php
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\DB;

class DoctorControllerTransactionalTest extends TestCase
{
    protected function setUp(): void
    {
        // Bắt đầu Transaction trước mỗi test
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        // Rollback sau mỗi test
        DB::rollBack();
    }

    public function testCreateDoctorSuccessfully()
    {
        $doctorController = new DoctorController();

        $_POST['email'] = 'testdoctor@example.com';
        $_POST['name'] = 'Dr. Test';
        $_POST['phone'] = '1234567890';
        $_POST['role'] = 'admin';
        $_POST['speciality'] = 1;
        $_POST['room'] = 1;

        ob_start();
        $doctorController->create(); // Giả sử bạn có hàm create()
        $output = ob_get_clean();

        $this->assertStringContainsString('Success', $output);

        // Kiểm tra dữ liệu có tồn tại
        $doctor = DB::table('doctors')->where('email', 'testdoctor@example.com')->first();
        $this->assertNotNull($doctor);
    }
}
