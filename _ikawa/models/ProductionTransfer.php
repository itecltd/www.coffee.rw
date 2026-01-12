<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use PDOException;

class ProductionTransfer
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function generateReferenceNo(): string
    {
        $prefix = 'TRF';
        $date = date('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return $prefix . $date . $random;
    }

    public function getAvailableStock(int $loc_id)
    {
        try {
            $sql = "
                SELECT 
                    ss.stock_summary_id,
                    ss.type_id,
                    ss.sup_id,
                    ss.total_quantity,
                    ct.type_name,
                    s.full_name as supplier_name,
                    s.type as supplier_type
                FROM tbl_stock_summary ss
                INNER JOIN tbl_category_types ct ON ss.type_id = ct.type_id
                INNER JOIN tbl_suppliers s ON ss.sup_id = s.sup_id
                WHERE ss.loc_id = :loc_id AND ss.total_quantity > 0
                ORDER BY ct.type_name, s.full_name
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':loc_id' => $loc_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching available stock: " . $e->getMessage());
            return false;
        }
    }

    public function getUnitByTypeAndSupplier(int $type_id, int $sup_id, int $loc_id)
    {
        try {
            $sql = "
                SELECT DISTINCT sd.unit_id, u.unit_name
                FROM tbl_stock_details sd
                LEFT JOIN tbl_units u ON sd.unit_id = u.unit_id
                WHERE sd.type_id = :type_id AND sd.sup_id = :sup_id AND sd.loc_id = :loc_id
                LIMIT 1
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':type_id' => $type_id, ':sup_id' => $sup_id, ':loc_id' => $loc_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching unit: " . $e->getMessage());
            return null;
        }
    }

    public function createTransferBatch(array $data): array
    {
        try {
            $this->conn->beginTransaction();

            $reference_no = $this->generateReferenceNo();
            $total_items = count($data['items']);
            $total_quantity = 0;

            // Calculate total quantity
            foreach ($data['items'] as $item) {
                $total_quantity += floatval($item['quantity']);
            }

            // Insert tracking record
            $trackingSql = "
                INSERT INTO tbl_transfer_tracking 
                (reference_no, from_loc_id, to_loc_id, transfer_date, total_items, total_quantity, notes, user_id, status) 
                VALUES 
                (:reference_no, :from_loc_id, :to_loc_id, :transfer_date, :total_items, :total_quantity, :notes, :user_id, 'pending')
            ";
            $trackingStmt = $this->conn->prepare($trackingSql);
            $trackingStmt->execute([
                ':reference_no' => $reference_no,
                ':from_loc_id' => $data['from_loc_id'],
                ':to_loc_id' => $data['to_loc_id'],
                ':transfer_date' => $data['transfer_date'],
                ':total_items' => $total_items,
                ':total_quantity' => $total_quantity,
                ':notes' => $data['notes'],
                ':user_id' => $data['user_id']
            ]);

            $tracking_id = $this->conn->lastInsertId();

            // Insert each item and deduct from stock
            foreach ($data['items'] as $item) {
                // Get unit_id from stock details
                $unit = $this->getUnitByTypeAndSupplier($item['type_id'], $item['sup_id'], $data['from_loc_id']);
                $unit_id = $unit ? $unit['unit_id'] : 0;

                // Check available stock
                $checkSql = "
                    SELECT total_quantity FROM tbl_stock_summary 
                    WHERE loc_id = :loc_id AND type_id = :type_id AND sup_id = :sup_id
                ";
                $checkStmt = $this->conn->prepare($checkSql);
                $checkStmt->execute([
                    ':loc_id' => $data['from_loc_id'],
                    ':type_id' => $item['type_id'],
                    ':sup_id' => $item['sup_id']
                ]);
                $stock = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if (!$stock || $stock['total_quantity'] < $item['quantity']) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Insufficient stock for one or more items'];
                }

                // Insert transfer detail
                $detailSql = "
                    INSERT INTO tbl_transfer_details 
                    (tracking_id, type_id, unit_id, sup_id, quantity) 
                    VALUES 
                    (:tracking_id, :type_id, :unit_id, :sup_id, :quantity)
                ";
                $detailStmt = $this->conn->prepare($detailSql);
                $detailStmt->execute([
                    ':tracking_id' => $tracking_id,
                    ':type_id' => $item['type_id'],
                    ':unit_id' => $unit_id,
                    ':sup_id' => $item['sup_id'],
                    ':quantity' => $item['quantity']
                ]);

                // Deduct from stock summary
                $deductSql = "
                    UPDATE tbl_stock_summary 
                    SET total_quantity = total_quantity - :quantity, updated_at = CURRENT_TIMESTAMP
                    WHERE loc_id = :loc_id AND type_id = :type_id AND sup_id = :sup_id
                ";
                $deductStmt = $this->conn->prepare($deductSql);
                $deductStmt->execute([
                    ':quantity' => $item['quantity'],
                    ':loc_id' => $data['from_loc_id'],
                    ':type_id' => $item['type_id'],
                    ':sup_id' => $item['sup_id']
                ]);
            }

            $this->conn->commit();
            return ['success' => true, 'reference_no' => $reference_no, 'tracking_id' => $tracking_id];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creating transfer batch: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }

    public function getTransfersByLocation(int $loc_id)
    {
        try {
            $sql = "
                SELECT 
                    tt.*,
                    fl.location_name as from_location,
                    tl.location_name as to_location,
                    u.username as created_by
                FROM tbl_transfer_tracking tt
                LEFT JOIN tbl_location fl ON tt.from_loc_id = fl.loc_id
                LEFT JOIN tbl_location tl ON tt.to_loc_id = tl.loc_id
                LEFT JOIN tbl_users u ON tt.user_id = u.user_id
                WHERE tt.from_loc_id = :loc_id
                ORDER BY tt.created_at DESC
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':loc_id' => $loc_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching transfers: " . $e->getMessage());
            return false;
        }
    }

    public function getTransferDetails(int $tracking_id)
    {
        try {
            $sql = "
                SELECT 
                    td.*,
                    ct.type_name,
                    u.unit_name,
                    s.full_name as supplier_name,
                    s.type as supplier_type
                FROM tbl_transfer_details td
                LEFT JOIN tbl_category_types ct ON td.type_id = ct.type_id
                LEFT JOIN tbl_units u ON td.unit_id = u.unit_id
                LEFT JOIN tbl_suppliers s ON td.sup_id = s.sup_id
                WHERE td.tracking_id = :tracking_id
                ORDER BY ct.type_name
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':tracking_id' => $tracking_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching transfer details: " . $e->getMessage());
            return false;
        }
    }

    public function updateTransferStatus(int $tracking_id, string $status): bool
    {
        try {
            $sql = "UPDATE tbl_transfer_tracking SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE tracking_id = :tracking_id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([':status' => $status, ':tracking_id' => $tracking_id]);
        } catch (PDOException $e) {
            error_log("Error updating transfer status: " . $e->getMessage());
            return false;
        }
    }
}
