<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use PDOException;

class Account
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllAccounts()
    {
        try {
            $query = 'SELECT a.*, pm.Mode_names, s.st_name, s.st_location 
                      FROM tbl_accounts a 
                      JOIN tbl_paymentmodes pm ON a.mode_id = pm.Mode_id 
                      LEFT JOIN tbl_station s ON a.st_id = s.st_id 
                      WHERE a.status = 1 
                      ORDER BY a.acc_id DESC';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAccountById($acc_id)
    {
        try {
            $query = 'SELECT a.*, pm.Mode_names, s.st_name 
                      FROM tbl_accounts a 
                      JOIN tbl_paymentmodes pm ON a.mode_id = pm.Mode_id 
                      LEFT JOIN tbl_station s ON a.st_id = s.st_id 
                      WHERE a.acc_id = :acc_id';
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['acc_id' => $acc_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function accountExists($acc_name)
    {
        try {
            $query = 'SELECT acc_name FROM tbl_accounts WHERE acc_name = :acc_name LIMIT 1';
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['acc_name' => $acc_name]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['acc_name'] : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function createAccount($data)
    {
        try {
            $query = 'INSERT INTO tbl_accounts (mode_id, acc_name, acc_reference_num, st_id, balance, status) 
                      VALUES (:mode_id, :acc_name, :acc_reference_num, :st_id, 0, 1)';
            $stmt = $this->conn->prepare($query);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateAccount($data)
    {
        try {
            $query = 'UPDATE tbl_accounts 
                      SET mode_id = :mode_id, acc_name = :acc_name, acc_reference_num = :acc_reference_num, st_id = :st_id 
                      WHERE acc_id = :acc_id';
            $stmt = $this->conn->prepare($query);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteAccount($acc_id)
    {
        try {
            $query = 'UPDATE tbl_accounts SET status = 0 WHERE acc_id = :acc_id';
            $stmt = $this->conn->prepare($query);
            return $stmt->execute(['acc_id' => $acc_id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
