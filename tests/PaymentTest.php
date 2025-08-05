<?php
use PHPUnit\Framework\TestCase;
require_once 'vendor/autoload.php';

class PaymentTest extends TestCase
{
    public function testPaymentSimulationSuccess()
    {
        $orderID = 101;
        $amount = 250.00;

        $paymentSuccess = $this->simulateMpesaPayment($orderID, $amount);

        $this->assertTrue($paymentSuccess, "Payment should be successful");
    }

    public function testPaymentWithInvalidAmount()
    {
        $this->expectException(Exception::class);
        $this->simulateMpesaPayment(101, -50);
    }

    private function simulateMpesaPayment($orderID, $amount)
    {
        if ($amount <= 0) {
            throw new Exception("Invalid amount");
        }
        return true; // Simulate successful STK push
    }
}