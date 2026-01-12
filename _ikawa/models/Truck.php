<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use PDOException;

class Truck
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllTrucks()
    {
        try {
            $sql = "SELECT * FROM tbl_trucks WHERE status = 'active' ORDER BY plate_number";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching trucks: " . $e->getMessage());
            return false;
        }
    }

    public function exists(string $plate_number): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM tbl_trucks WHERE plate_number = :plate_number";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':plate_number' => $plate_number]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function existsUpdate(string $plate_number, int $truck_id): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM tbl_trucks WHERE plate_number = :plate_number AND truck_id != :truck_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':plate_number' => $plate_number, ':truck_id' => $truck_id]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function createTruck(array $data): bool
    {
        try {
            $sql = "INSERT INTO tbl_trucks (plate_number, driver_name, driver_phone, capacity) 
                    VALUES (:plate_number, :driver_name, :driver_phone, :capacity)";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':plate_number' => $data['plate_number'],
                ':driver_name' => $data['driver_name'],
                ':driver_phone' => $data['driver_phone'],
                ':capacity' => $data['capacity']
            ]);
        } catch (PDOException $e) {
            error_log("Error creating truck: " . $e->getMessage());
            return false;
        }
    }

    public function updateTruck(array $data): bool
    {
        try {
            $sql = "UPDATE tbl_trucks SET plate_number = :plate_number, driver_name = :driver_name, 
                    driver_phone = :driver_phone, capacity = :capacity, status = :status 
                    WHERE truck_id = :truck_id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':plate_number' => $data['plate_number'],
                ':driver_name' => $data['driver_name'],
                ':driver_phone' => $data['driver_phone'],
                ':capacity' => $data['capacity'],
                ':status' => $data['status'],
                ':truck_id' => $data['truck_id']
            ]);
        } catch (PDOException $e) {
            error_log("Error updating truck: " . $e->getMessage());
            return false;
        }
    }
}
