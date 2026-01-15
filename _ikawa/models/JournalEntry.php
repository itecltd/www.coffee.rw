<?php
namespace Models;

use PDO;
use PDOException;

class JournalEntry {
    private $conn;
    private $table_name = "tbl_journal_entries";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Update existing journal entries for a given reference_id.
     * Will update the 'expense' row (amount) and the 'charges' row (charges).
     * If a charges row does not exist and $charges_amount > 0, it will insert one.
     */
    public function updateEntriesByReferenceId($reference_id, $entry_date, $debit_account_id, $expense_amount, $charges_amount, $method_id, $description, $user_id) {
        try {
            // Update expense entry (action='expense')
            $updateExpense = "UPDATE " . $this->table_name . " 
                              SET entry_date = :entry_date,
                                  debit_account_id = :debit_account_id,
                                  amount = :amount,
                                  method_id = :method_id,
                                  description = :description,
                                  user_id = :user_id
                              WHERE reference_id = :reference_id AND action = 'expense'";

            $stmtExp = $this->conn->prepare($updateExpense);
            $stmtExp->execute([
                'entry_date' => $entry_date,
                'debit_account_id' => $debit_account_id,
                'amount' => $expense_amount,
                'method_id' => $method_id,
                'description' => $description,
                'user_id' => $user_id,
                'reference_id' => $reference_id
            ]);

            // Check if a charges row exists
            $queryChargesExist = "SELECT entry_id FROM " . $this->table_name . " WHERE reference_id = :reference_id AND action = 'charges' LIMIT 1";
            $stmtCheck = $this->conn->prepare($queryChargesExist);
            $stmtCheck->execute(['reference_id' => $reference_id]);
            $chargesRow = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($charges_amount > 0) {
                if ($chargesRow) {
                    // Update existing charges row - cast charges to int
                    $updateCharges = "UPDATE " . $this->table_name . " 
                                      SET entry_date = :entry_date,
                                          debit_account_id = :debit_account_id,
                                          charges = :charges,
                                          method_id = :method_id,
                                          description = :description,
                                          user_id = :user_id
                                      WHERE reference_id = :reference_id AND action = 'charges'";

                    $stmtCh = $this->conn->prepare($updateCharges);
                    $stmtCh->execute([
                        'entry_date' => $entry_date,
                        'debit_account_id' => $debit_account_id,
                        'charges' => (int)$charges_amount,
                        'method_id' => $method_id,
                        'description' => $description,
                        'user_id' => $user_id,
                        'reference_id' => $reference_id
                    ]);
                } else {
                    // Insert new charges row - cast charges to int
                    $insertCharges = "INSERT INTO " . $this->table_name . " (entry_date, debit_account_id, credit_account_id, amount, charges, method_id, reference_id, description, user_id, action)
                                      VALUES (:entry_date, :debit_account_id, NULL, 0, :charges, :method_id, :reference_id, :description, :user_id, 'charges')";
                    $stmtIns = $this->conn->prepare($insertCharges);
                    $stmtIns->execute([
                        'entry_date' => $entry_date,
                        'debit_account_id' => $debit_account_id,
                        'charges' => (int)$charges_amount,
                        'method_id' => $method_id,
                        'reference_id' => $reference_id,
                        'description' => $description,
                        'user_id' => $user_id
                    ]);
                }
            } else {
                // charges_amount == 0; if a charges row exists, set charges to 0
                if ($chargesRow) {
                    $stmtZero = $this->conn->prepare("UPDATE " . $this->table_name . " SET charges = 0, entry_date = :entry_date, debit_account_id = :debit_account_id, method_id = :method_id, description = :description, user_id = :user_id WHERE reference_id = :reference_id AND action = 'charges'");
                    $stmtZero->execute([
                        'entry_date' => $entry_date,
                        'debit_account_id' => $debit_account_id,
                        'method_id' => $method_id,
                        'description' => $description,
                        'user_id' => $user_id,
                        'reference_id' => $reference_id
                    ]);
                }
            }

            return true;
        } catch (PDOException $e) {
            error_log('Error updating journal entries: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new journal entry
     */
    public function createEntry($entry_date, $debit_account_id, $amount, $charges, $method_id, $reference_id, $description, $user_id, $action) {
        try {
            // Convert charges to integer as per database schema
            $charges_int = (int)$charges;
            
            $query = "INSERT INTO " . $this->table_name . " 
                      (entry_date, debit_account_id, credit_account_id, amount, charges, method_id, reference_id, description, user_id, action)
                      VALUES (:entry_date, :debit_account_id, NULL, :amount, :charges, :method_id, :reference_id, :description, :user_id, :action)";
            
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
                return $lastId;
            }
            
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Create both expense and charges journal entries for a transaction
     */
    public function createExpenseTransaction($entry_date, $debit_account_id, $expense_amount, $charges_amount, $method_id, $reference_id, $description, $user_id) {
        try {
            // Create expense entry first (amount = expense amount, charges = 0)
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
                return false;
            }
            
            // Create charges entry second if charges amount > 0 (amount = 0, charges = charges amount)
            if ($charges_amount > 0) {
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
                    return false;
                }
            }
            
            return true;
            
        } catch (PDOException $e) {
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
            return [];
        }
    }

    /**
     * Get journal entries by reference ID (con_id from expenseconsume)
     */
    public function getEntriesByReferenceId($reference_id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " 
                      WHERE reference_id = :reference_id 
                      ORDER BY entry_id ASC";
            
            $stmt = $this->conn->prepare($query);
            $success = $stmt->execute(['reference_id' => $reference_id]);
            
            if (!$success) {
                error_log("Failed to execute query for reference_id: {$reference_id}");
                return [];
            }
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        } catch (PDOException $e) {
            error_log("Error getting journal entries by reference ID {$reference_id}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cancel journal entries by reference ID (set action to 'canceled')
     */
    public function cancelEntriesByReferenceId($reference_id, $user_id) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET action = 'canceled'
                      WHERE reference_id = :reference_id";
            
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute(['reference_id' => $reference_id]);
            
            return $result;
        } catch (PDOException $e) {
            return false;
        }
    }
}
