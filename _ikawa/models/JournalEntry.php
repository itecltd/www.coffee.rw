<?php
namespace Models;

use PDO;
use PDOException;

class JournalEntry {
    private $conn;
    private $table_name = "tbl_journal_entries";
    private $log_file;

    public function __construct($db) {
        $this->conn = $db;
        $this->log_file = __DIR__ . '/../../logs/journal_entries.log';
    }
    
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;
        file_put_contents($this->log_file, $logMessage, FILE_APPEND);
    }

    /**
     * Create a new journal entry
     */
    public function createEntry($entry_date, $debit_account_id, $amount, $charges, $method_id, $reference_id, $description, $user_id, $action) {
        try {
            $this->log("=== CREATE ENTRY START ===");
            $this->log("Entry Date: $entry_date");
            $this->log("Debit Account ID: $debit_account_id");
            $this->log("Amount: $amount");
            $this->log("Charges: $charges");
            $this->log("Method ID: $method_id");
            $this->log("Reference ID: $reference_id");
            $this->log("Description: " . ($description ?? 'NULL'));
            $this->log("User ID: $user_id");
            $this->log("Action: $action");
            
            // Convert charges to integer as per database schema
            $charges_int = (int)$charges;
            
            $query = "INSERT INTO " . $this->table_name . " 
                      (entry_date, debit_account_id, credit_account_id, amount, charges, method_id, reference_id, description, user_id, action)
                      VALUES (:entry_date, :debit_account_id, NULL, :amount, :charges, :method_id, :reference_id, :description, :user_id, :action)";
            
            $this->log("Preparing query: $query");
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':entry_date', $entry_date, PDO::PARAM_STR);
            $stmt->bindParam(':debit_account_id', $debit_account_id, PDO::PARAM_INT);
            $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
            $stmt->bindParam(':charges', $charges_int, PDO::PARAM_INT);
            $stmt->bindParam(':method_id', $method_id, PDO::PARAM_INT);
            $stmt->bindParam(':reference_id', $reference_id, PDO::PARAM_INT);
            
            if ($description === null || $description === '') {
                $stmt->bindValue(':description', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            }
            
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':action', $action, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $lastId = $this->conn->lastInsertId();
                $this->log("Journal entry created successfully. ID: $lastId");
                return $lastId;
            } else {
                $this->log("Statement execute failed");
                $this->log("Error Info: " . print_r($stmt->errorInfo(), true));
            }
            
            return false;
        } catch (PDOException $e) {
            $this->log("PDOException in createEntry: " . $e->getMessage());
            $this->log("SQL State: " . $e->getCode());
            return false;
        }
    }

    /**
     * Create both expense and charges journal entries for a transaction
     */
    public function createExpenseTransaction($entry_date, $debit_account_id, $expense_amount, $charges_amount, $method_id, $reference_id, $description, $user_id) {
        try {
            $this->log("\n=== CREATE EXPENSE TRANSACTION START ===");
            $this->log("Expense Amount: $expense_amount, Charges Amount: $charges_amount");
            
            // Create expense entry first (amount = expense amount, charges = 0)
            $this->log("Creating expense entry...");
            $expenseEntryId = $this->createEntry(
                $entry_date,
                $debit_account_id,
                $expense_amount,
                0, // Charges = 0 for expense row
                $method_id,
                $reference_id,
                $description,
                $user_id,
                'expense'
            );
            
            if (!$expenseEntryId) {
                $this->log("FAILED to create expense entry");
                return false;
            }
            $this->log("Expense entry created with ID: $expenseEntryId");
            
            // Create charges entry second if charges amount > 0 (amount = 0, charges = charges amount)
            if ($charges_amount > 0) {
                $this->log("Creating charges entry...");
                $chargesEntryId = $this->createEntry(
                    $entry_date,
                    $debit_account_id,
                    0, // Amount = 0 for charges row
                    $charges_amount, // Charges field for charges entry
                    $method_id,
                    $reference_id,
                    $description,
                    $user_id,
                    'charges'
                );
                
                if (!$chargesEntryId) {
                    $this->log("FAILED to create charges entry");
                    return false;
                }
                $this->log("Charges entry created with ID: $chargesEntryId");
            }
            
            $this->log("=== CREATE EXPENSE TRANSACTION SUCCESS ===");
            return true;
            
        } catch (PDOException $e) {
            $this->log("PDOException in createExpenseTransaction: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all journal entries with account details
     */
    public function getAllEntries() {
        try {
            $query = "SELECT 
                        je.*,
                        da.acc_name as debit_account_name,
                        e.name as expense_name,
                        u.username
                    FROM " . $this->table_name . " je
                    LEFT JOIN tbl_accounts da ON je.debit_account_id = da.acc_id
                    LEFT JOIN tbl_expenses e ON je.reference_id = e.id
                    LEFT JOIN tbl_users u ON je.user_id = u.user_id
                    ORDER BY je.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->log("Error fetching journal entries: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get journal entries by reference ID (expense_id)
     */
    public function getEntriesByReference($reference_id) {
        try {
            $query = "SELECT 
                        je.*,
                        da.acc_name as debit_account_name,
                        e.name as expense_name,
                        u.username
                    FROM " . $this->table_name . " je
                    LEFT JOIN tbl_accounts da ON je.debit_account_id = da.acc_id
                    LEFT JOIN tbl_expenses e ON je.reference_id = e.id
                    LEFT JOIN tbl_users u ON je.user_id = u.user_id
                    WHERE je.reference_id = :reference_id
                    ORDER BY je.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':reference_id', $reference_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->log("Error fetching journal entries by reference: " . $e->getMessage());
            return [];
        }
    }
}
