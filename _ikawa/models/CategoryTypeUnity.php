<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use PDOException;

class CategoryTypeUnity
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllAssignments()
    {
        try {
            $query = '
                SELECT 
                    ctu.assignment_id,
                    ctu.type_id,
                    ctu.unit_id,
                    ctu.status,
                    c.category_name,
                    ct.type_name,
                    u.unit_name,
                    ctu.created_at
                FROM tbl_category_type_units ctu
                LEFT JOIN tbl_category_types ct ON ctu.type_id = ct.type_id
                LEFT JOIN tbl_categories c ON ct.category_id = c.category_id
                LEFT JOIN tbl_units u ON ctu.unit_id = u.unit_id
                ORDER BY ctu.created_at DESC
            ';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching assignments: " . $e->getMessage());
            return false;
        }
    }

    public function exists(string $type_id, string $unit_id): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM tbl_category_type_units WHERE type_id = :type_id AND unit_id = :unit_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':type_id' => $type_id,
                ':unit_id' => $unit_id
            ]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking assignment existence: " . $e->getMessage());
            return false;
        }
    }

    public function existsUpdate(string $type_id, string $unit_id, string $assignment_id): bool
    {
        try {
            $sql = "
                SELECT COUNT(*) 
                FROM tbl_category_type_units
                WHERE type_id = :type_id 
                AND unit_id = :unit_id
                AND assignment_id != :assignment_id
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':type_id' => $type_id,
                ':unit_id' => $unit_id,
                ':assignment_id' => $assignment_id
            ]);

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking assignment update: " . $e->getMessage());
            return false;
        }
    }

    public function createAssignment(array $data): bool
    {
        try {
            $sql = "
                INSERT INTO tbl_category_type_units (type_id, unit_id, status) 
                VALUES (:type_id, :unit_id, 'active')
            ";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':type_id' => $data['type_id'],
                ':unit_id' => $data['unit_id']
            ]);
        } catch (PDOException $e) {
            error_log("Error creating assignment: " . $e->getMessage());
            return false;
        }
    }

    public function updateAssignment(array $data): bool
    {
        try {
            $sql = "
                UPDATE tbl_category_type_units 
                SET type_id = :type_id,
                    unit_id = :unit_id,
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP
                WHERE assignment_id = :assignment_id
            ";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':type_id' => $data['type_id'],
                ':unit_id' => $data['unit_id'],
                ':status' => $data['status'],
                ':assignment_id' => $data['assignment_id']
            ]);
        } catch (PDOException $e) {
            error_log("Error updating assignment: " . $e->getMessage());
            return false;
        }
    }

    public function deleteAssignment(string $assignment_id): bool
    {
        try {
            $sql = "DELETE FROM tbl_category_type_units WHERE assignment_id = :assignment_id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([':assignment_id' => $assignment_id]);
        } catch (PDOException $e) {
            error_log("Error deleting assignment: " . $e->getMessage());
            return false;
        }
    }
}
