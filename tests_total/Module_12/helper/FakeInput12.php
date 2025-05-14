<?php
/**
 * InputTestHelper - Giả lập dữ liệu PUT (hoặc POST, PATCH...) cho unit test
 * KHÔNG SỬA FILE input.php GỐC, dùng file này để bổ sung hỗ trợ test
 */
class InputTestHelper
{
    /**
     * Gán dữ liệu cho phương thức HTTP giả lập (PUT, POST, PATCH...)
     *
     * @param string $method  (vd: put, post, patch...)
     * @param array $data     Dữ liệu muốn gán
     */
    public static function set(string $method, array $data): void
    {
        $method = "_" . strtoupper($method);
        $GLOBALS[$method] = $data;
    }

    /**
     * Gán lại phương thức HTTP hiện tại (ví dụ để Input::method() hiểu đang là PUT)
     *
     * @param string $method
     */
    public static function setMethod(string $method): void
    {
        $_SERVER['REQUEST_METHOD'] = strtoupper($method);
    }

    /**
     * Reset toàn bộ dữ liệu input trong $GLOBALS (để tránh ảnh hưởng test khác)
     */
    public static function reset(): void
    {
        foreach (["_GET", "_POST", "_REQUEST", "_PUT", "_DELETE", "_PATCH"] as $method) {
            unset($GLOBALS[$method]);
        }
    }
}
