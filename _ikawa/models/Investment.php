<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;

class Investment
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Create a recharge record and update account balance atomically
     * @return true|string  true on success, error message on failure
     */
    public function createInvestment($in_amount, $done_by, $account_id, $loc_id, $description = null, $reciept = null, $action = 'recharge')
    {
        try {
            $this->conn->beginTransaction();

            // Ensure account exists and lock row
            $check = $this->conn->prepare('SELECT acc_id FROM tbl_accounts WHERE acc_id = ? FOR UPDATE');
            $check->execute([$account_id]);
            $acc = $check->fetch(PDO::FETCH_ASSOC);
            if (!$acc) {
                $this->conn->rollBack();
                return 'Account not found';
            }

            // Insert into tbl_investiments
            $sql = "INSERT INTO tbl_investiments (in_amount, done_by, account_id, loc_id, description, reciept, action) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                (int)$in_amount,
                (int)$done_by,
                (int)$account_id,
                (int)$loc_id,
                $description,
                $reciept,
                $action
            ]);

            // Update account balance
            $sql2 = "UPDATE tbl_accounts SET balance = balance + ? WHERE acc_id = ?";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->execute([(int)$in_amount, (int)$account_id]);

            $this->conn->commit();
            return true;
        } catch (\Exception $e) {
            try { $this->conn->rollBack(); } catch (\Exception $_) {}
            return $e->getMessage();
        }
    }

    /**
     * Simple fetch by location for UI listing (optional)
     */
    public function getInvestmentsByLocation($loc_id)
    {
        $sql = "SELECT i.*, a.acc_name, u.username, l.location_name FROM tbl_investiments i LEFT JOIN tbl_accounts a ON i.account_id = a.acc_id LEFT JOIN tbl_users u ON i.done_by = u.user_id LEFT JOIN tbl_location l ON i.loc_id = l.loc_id WHERE i.loc_id = ? ORDER BY i.done_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$loc_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
