<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use PDOException;

class Stock
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function createStock(array $data): bool
    {
        try {
            $this->conn->beginTransaction();

            // Insert into detailed stock table
            $sql = "
                INSERT INTO tbl_stock_details 
                (type_id, unit_id, sup_id, quantity, unit_price, total_price, loc_id, user_id) 
                VALUES 
                (:type_id, :unit_id, :sup_id, :quantity, :unit_price, :total_price, :loc_id, :user_id)
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':type_id' => $data['type_id'],
                ':unit_id' => $data['unit_id'],
                ':sup_id' => $data['sup_id'],
                ':quantity' => $data['quantity'],
                ':unit_price' => $data['unit_price'],
                ':total_price' => $data['total_price'],
                ':loc_id' => $data['loc_id'],
                ':user_id' => $data['user_id']
            ]);

            // Update or insert into summary table (grouped by loc_id, type_id, sup_id)
            $checkSql = "
                SELECT stock_summary_id, total_quantity 
                FROM tbl_stock_summary 
                WHERE loc_id = :loc_id AND type_id = :type_id AND sup_id = :sup_id
            ";
            $checkStmt = $this->conn->prepare($checkSql);
            $checkStmt->execute([
                ':loc_id' => $data['loc_id'],
                ':type_id' => $data['type_id'],
                ':sup_id' => $data['sup_id']
            ]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $updateSql = "
                    UPDATE tbl_stock_summary 
                    SET total_quantity = total_quantity + :quantity,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE stock_summary_id = :stock_summary_id
                ";
                $updateStmt = $this->conn->prepare($updateSql);
                $updateStmt->execute([
                    ':quantity' => $data['quantity'],
                    ':stock_summary_id' => $existing['stock_summary_id']
                ]);
            } else {
                $insertSql = "
                    INSERT INTO tbl_stock_summary 
                    (loc_id, type_id, sup_id, total_quantity) 
                    VALUES 
                    (:loc_id, :type_id, :sup_id, :quantity)
                ";
                $insertStmt = $this->conn->prepare($insertSql);
                $insertStmt->execute([
                    ':loc_id' => $data['loc_id'],
                    ':type_id' => $data['type_id'],
                    ':sup_id' => $data['sup_id'],
                    ':quantity' => $data['quantity']
                ]);
            }

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creating stock: " . $e->getMessage());
            return false;
        }
    }

    public function getUnitsByType(string $type_id)
    {
        try {
            $sql = "
                SELECT u.unit_id, u.unit_name 
                FROM tbl_units u
                INNER JOIN tbl_category_type_units ctu ON u.unit_id = ctu.unit_id
                WHERE ctu.type_id = :type_id AND ctu.status = 'active'
                ORDER BY u.unit_name
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':type_id' => $type_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching units: " . $e->getMessage());
            return false;
        }
    }

    public function getDetailedStock(int $loc_id)
    {
        try {
            $sql = "
                SELECT 
                    sd.*,
                    ct.type_name,
                    u.unit_name,
                    s.full_name as supplier_name
                FROM tbl_stock_details sd
                LEFT JOIN tbl_category_types ct ON sd.type_id = ct.type_id
                LEFT JOIN tbl_units u ON sd.unit_id = u.unit_id
                LEFT JOIN tbl_suppliers s ON sd.sup_id = s.sup_id
                WHERE sd.loc_id = :loc_id
                ORDER BY sd.created_at DESC
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':loc_id' => $loc_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching detailed stock: " . $e->getMessage());
            return false;
        }
    }

    public function getSummaryStock(int $loc_id)
    {
        try {
            $sql = "
                SELECT 
                    ss.stock_summary_id,
                    ss.loc_id,
                    ss.type_id,
                    ss.sup_id,
                    ss.total_quantity,
                    ss.created_at,
                    ss.updated_at,
                    l.location_name as station_name,
                    ct.type_name,
                    s.full_name as supplier_name,
                    s.type as supplier_type
                FROM tbl_stock_summary ss
                LEFT JOIN tbl_location l ON ss.loc_id = l.loc_id
                LEFT JOIN tbl_category_types ct ON ss.type_id = ct.type_id
                LEFT JOIN tbl_suppliers s ON ss.sup_id = s.sup_id
                WHERE ss.loc_id = :loc_id
                ORDER BY ct.type_name, s.full_name
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':loc_id' => $loc_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching summary stock: " . $e->getMessage());
            return false;
        }
    }
}
