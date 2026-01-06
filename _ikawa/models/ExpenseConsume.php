<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use PDOException;

class ExpenseConsume
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Get next transaction ID (exptr1, exptr2, etc.)
     */
    public function getNextTransId()
    {
        try {
            $query = 'SELECT trans_id FROM tbl_expenseconsume 
                      WHERE trans_id IS NOT NULL 
                      ORDER BY con_id DESC LIMIT 1';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['trans_id']) {
                // Extract number from exptr1, exptr2, etc.
                $lastNumber = intval(str_replace('exptr', '', $result['trans_id']));
                return 'exptr' . ($lastNumber + 1);
            }
            
            return 'exptr1';
        } catch (PDOException $e) {
            error_log("Error getting next trans_id: " . $e->getMessage());
            return 'exptr1';
        }
    }

    public function getAllExpenseConsumes()
    {
        try {
            $query = 'SELECT ec.*, 
                      e.expense_name,
                      l.location_name as st_name,
                      l.description as st_location,
                      a.acc_name as payment_mode_name
                      FROM tbl_expenseconsume ec
                      LEFT JOIN tbl_expenses e ON ec.expense_id = e.expense_id
                      LEFT JOIN tbl_location l ON ec.station_id = l.loc_id
                      LEFT JOIN tbl_accounts a ON ec.pay_mode = a.acc_id
                      WHERE ec.status = 1 
                      ORDER BY ec.con_id DESC';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching expense consumes: " . $e->getMessage());
            return false;
        }
    }

    public function getExpenseConsumeById($con_id)
    {
        try {
            $query = 'SELECT ec.*, 
                      e.expense_name,
                      l.location_name as st_name,
                      l.description as st_location,
                      a.acc_name as payment_mode_name
                      FROM tbl_expenseconsume ec
                      LEFT JOIN tbl_expenses e ON ec.expense_id = e.expense_id
                      LEFT JOIN tbl_location l ON ec.station_id = l.loc_id
                      LEFT JOIN tbl_accounts a ON ec.pay_mode = a.acc_id
                      WHERE ec.con_id = :con_id';
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['con_id' => $con_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching expense consume by ID: " . $e->getMessage());
            return false;
        }
    }

    public function createExpenseConsume($data)
    {
        try {
            $query = 'INSERT INTO tbl_expenseconsume 
                      (expense_id, co_id, station_id, amount, pay_mode, trans_id, payer_name, description, recorded_date, status) 
                      VALUES 
                      (:expense_id, :co_id, :station_id, :amount, :pay_mode, :trans_id, :payer_name, :description, :recorded_date, 1)';
            
            $stmt = $this->conn->prepare($query);
            $success = $stmt->execute([
                'expense_id' => $data['expense_id'],
                'co_id' => 1, // Always 1 as per requirement
                'station_id' => $data['station_id'],
                'amount' => $data['amount'],
                'pay_mode' => $data['pay_mode'],
                'trans_id' => $data['trans_id'] ?? null,
                'payer_name' => $data['payer_name'] ?? null,
                'description' => $data['description'] ?? null,
                'recorded_date' => $data['recorded_date']
            ]);

            return $success;
        } catch (PDOException $e) {
            error_log("Error creating expense consume: " . $e->getMessage());
            return false;
        }
    }

    public function updateExpenseConsume($data)
    {
        try {
            $query = 'UPDATE tbl_expenseconsume 
                      SET expense_id = :expense_id,
                          station_id = :station_id,
                          amount = :amount,
                          pay_mode = :pay_mode,
                          payer_name = :payer_name,
                          description = :description,
                          recorded_date = :recorded_date
                      WHERE con_id = :con_id';
            
            $stmt = $this->conn->prepare($query);
            $success = $stmt->execute([
                'expense_id' => $data['expense_id'],
                'station_id' => $data['station_id'],
                'amount' => $data['amount'],
                'pay_mode' => $data['pay_mode'],
                'payer_name' => $data['payer_name'] ?? null,
                'description' => $data['description'] ?? null,
                'recorded_date' => $data['recorded_date'],
                'con_id' => $data['con_id']
            ]);

            return $success;
        } catch (PDOException $e) {
            error_log("Error updating expense consume: " . $e->getMessage());
            return false;
        }
    }

    public function deleteExpenseConsume($con_id)
    {
        try {
            // Soft delete - set status to 0
            $query = 'UPDATE tbl_expenseconsume SET status = 0 WHERE con_id = :con_id';
            $stmt = $this->conn->prepare($query);
            return $stmt->execute(['con_id' => $con_id]);
        } catch (PDOException $e) {
            error_log("Error deleting expense consume: " . $e->getMessage());
            return false;
        }
    }

    public function getTotalExpensesByPeriod($start_date, $end_date)
    {
        try {
            $query = 'SELECT SUM(amount) as total 
                      FROM tbl_expenseconsume 
                      WHERE status = 1 
                      AND recorded_date BETWEEN :start_date AND :end_date';
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error getting total expenses: " . $e->getMessage());
            return false;
        }
    }

    public function getExpensesByStation($station_id)
    {
        try {
            $query = 'SELECT ec.*, e.expense_name
                      FROM tbl_expenseconsume ec
                      LEFT JOIN tbl_expenses e ON ec.expense_id = e.expense_id
                      WHERE ec.station_id = :station_id AND ec.status = 1
                      ORDER BY ec.recorded_date DESC';
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['station_id' => $station_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching expenses by station: " . $e->getMessage());
            return false;
        }
    }
}
