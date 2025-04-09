<?php
class FakeAuthUser {
    private $data;

    public function __construct($data = []) {
        $this->data = (array)$data;
    }

    public function get($key) {
        return $this->data[$key] ?? null;
    }

    public function set($key, $value) {
        $this->data[$key] = $value;
        return $this;
    }

    public function save() {
        return true;
    }
    public function isAvailable() {
        return true;
    }
}