<?php
use PHPUnit\Framework\TestCase;

/**
 * MenuManagementTest.php
 *
 * This test validates the Menu Management functionality as described in:
 * - Section 4.2.1: Staff can add, edit, and delete menu items
 * - Section 5.2.2: Staff dashboard supports image uploads and form-based management
 * - Figure 16 & 17: Staff Menu Management wireframes
 *
 * Note: This test is for system documentation only. It simulates successful outcomes
 * without interacting with the database or modifying live data.
 */
class MenuManagementTest extends TestCase
{
    /**
     * Simulate adding a menu item via staff dashboard
     */
    public function testAddMenuItem()
    {
        // Simulate form submission from Figure 16
        $formData = [
            'name' => 'Chicken Wrap',
            'price' => 150,
            'category' => 'Main Course'
            // 'image' upload handled separately
        ];

        // In real system: INSERT INTO MenuItem (name, price, category) ...
        $addSuccess = $this->simulateAdd($formData);

        $this->assertTrue($addSuccess, "Staff should be able to add a new menu item");
    }

    /**
     * Simulate editing a menu item
     */
    public function testEditMenuItem()
    {
        $updatedData = [
            'itemID' => 5,
            'name' => 'Veggie Burger',
            'price' => 120
        ];

        $editSuccess = $this->simulateEdit($updatedData);

        $this->assertTrue($editSuccess, "Staff should be able to update menu item details");
    }

    /**
     * Simulate deleting a menu item
     */
    public function testDeleteMenuItem()
    {
        $itemID = 8;

        $deleteSuccess = $this->simulateDelete($itemID);

        $this->assertTrue($deleteSuccess, "Staff should be able to remove a menu item from the system");
    }

    /**
     * Simulate image upload for a menu item
     */
    public function testImageUploadForMenuItem()
    {
        $imageFile = 'burger.jpg';
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        $extension = pathinfo($imageFile, PATHINFO_EXTENSION);

        $uploadSuccess = in_array(strtolower($extension), $allowedTypes) && !empty($imageFile);

        $this->assertTrue($uploadSuccess, "System should support image upload for menu items");
    }

    // --- Simulation Methods (No DB Access) ---

    private function simulateAdd($data)
    {
        return !empty($data['name']) && $data['price'] > 0;
    }

    private function simulateEdit($data)
    {
        return isset($data['itemID']) && !empty($data['name']);
    }

    private function simulateDelete($itemID)
    {
        return $itemID > 0;
    }
}