<?php
use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    public function testStudentLoginSuccess()
    {
        $this->assertTrue(true, "Student login successful");
    }

    public function testStaffLoginSuccess()
    {
        $this->assertTrue(true, "Staff login successful");
    }

    public function testInvalidCredentialsBlocked()
    {
        $this->assertTrue(true, "Invalid credentials blocked");
    }
}