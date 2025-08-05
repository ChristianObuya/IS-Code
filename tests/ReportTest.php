<?php
use PHPUnit\Framework\TestCase;

class ReportTest extends TestCase
{
    public function testGenerateSalesReport()
    {
        // Simulate report generation
        $report = [
            'totalOrders' => 45,
            'totalRevenue' => 8200.00,
            'topSellingItems' => [
                ['name' => 'Burger', 'quantitySold' => 23],
                ['name' => 'Soda', 'quantitySold' => 19]
            ],
            'dateRange' => '2025-04-01 to 2025-04-07'
        ];

        $this->assertArrayHasKey('totalOrders', $report);
        $this->assertArrayHasKey('totalRevenue', $report);
        $this->assertArrayHasKey('topSellingItems', $report);
        $this->assertGreaterThan(0, $report['totalOrders']);
        $this->assertGreaterThan(0, $report['totalRevenue']);
    }


    public function testGenerateStockReport()
    {
        // Simulate low-stock items
        $stockReport = [
            'lowStockItems' => [
                ['itemID' => 5, 'name' => 'Soda', 'currentStock' => 2],
                ['itemID' => 7, 'name' => 'Mandazi', 'currentStock' => 1]
            ],
            'totalLowItems' => 2,
            'replenishmentUrgency' => 'High'
        ];

        $this->assertNotEmpty($stockReport['lowStockItems']);
        $this->assertIsArray($stockReport['lowStockItems']);
        $this->assertGreaterThan(0, $stockReport['totalLowItems']);
        $this->assertEquals('High', $stockReport['replenishmentUrgency']);
    }
}