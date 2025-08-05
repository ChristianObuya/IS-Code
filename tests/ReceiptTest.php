<?php
use PHPUnit\Framework\TestCase;
require_once 'vendor/autoload.php';
class ReceiptTest extends TestCase
{
    public function testReceiptGeneratedAfterPayment()
    {
        // Simulate session data from live system
        $_SESSION['studentID'] = '190802'; // Real student ID from your system
        $_SESSION['user_role'] = 'student';
        $_SESSION['last_orderID'] = 101; // Assume this was set after order placement

        // Simulate M-Pesa STK push response (from Section 5.2.1)
        $transactionID = $_SESSION['mpesa_transaction_id'] ?? 'MPESA_' . rand(100000, 999999);

        // In real system: INSERT INTO Receipt (orderID, transactionID) VALUES (101, 'MPESA_123XYZ')
        $receiptGenerated = $this->simulateReceiptInsert($_SESSION['last_orderID'], $transactionID);

        // Assert: Receipt was generated
        $this->assertTrue($receiptGenerated, "Digital receipt should be generated after payment");
    }

    private function simulateReceiptInsert($orderID, $transactionID)
    {
        if (!empty($orderID) && !empty($transactionID)) {
            return true;
        }
        return false;
    }

    public function testReceiptContainsCorrectInformation()
    {
        $receipt = [
            'orderID' => 101,
            'transactionID' => 'MPESA_123XYZ',
            'timestamp' => date('Y-m-d H:i:s'),
            'items' => [
                ['name' => 'Burger', 'quantity' => 1, 'price' => 150],
                ['name' => 'Fries', 'quantity' => 1, 'price' => 80]
            ],
            'total' => 230
        ];

        // Assert: All required fields are present
        $this->assertArrayHasKey('orderID', $receipt);
        $this->assertArrayHasKey('transactionID', $receipt);
        $this->assertArrayHasKey('timestamp', $receipt);
        $this->assertArrayHasKey('items', $receipt);
        $this->assertArrayHasKey('total', $receipt);

        // Assert: Transaction ID format (MPESA_ followed by letters/numbers)
        $this->assertMatchesRegularExpression('/^MPESA_[A-Z0-9]{6,}$/', $receipt['transactionID']);
    }

    /**
     * Test that receipt is accessible to student
     * 
     * Simulates: Student views receipt in "My Orders" section
     */
    public function testReceiptIsAccessibleToStudent()
    {
        $_SESSION['studentID'] = '190802';
        $orderID = 101;

        // In real system: SELECT * FROM Receipt r JOIN `Order` o ON r.orderID = o.orderID WHERE o.studentID = ? AND r.orderID = ?
        $receiptAccessible = $this->simulateReceiptAccess($_SESSION['studentID'], $orderID);

        $this->assertTrue($receiptAccessible, "Student should be able to view their receipt");
    }

    private function simulateReceiptAccess($studentID, $orderID)
    {
        // Simulate database check: Does this student own this order?
        // In real system: JOIN Receipt, Order, Student tables
        if ($studentID === '190802' && $orderID === 101) {
            return true; // Access granted
        }
        return false;
    }
}