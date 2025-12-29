<?php
namespace Controllers;

// Include dependencies
require_once __DIR__ . '/../models/ExpenseConsume.php';
require_once __DIR__ . '/../models/Account.php';
require_once __DIR__ . '/../models/JournalEntry.php';
require_once __DIR__ . '/../config/Response.php';

use Models\ExpenseConsume;
use Models\Account;
use Models\JournalEntry;
use Config\Response;

class ExpenseConsumeController
{
    private $expenseConsumeModel;
    private $accountModel;
    private $journalEntryModel;

    public function __construct()
    {
        $this->expenseConsumeModel = new ExpenseConsume();
        $this->accountModel = new Account();
        
        $db = (new \Config\Database())->getConnection();
        $this->journalEntryModel = new JournalEntry($db);
    }

    public function getAllExpenseConsumes()
    {
        $expenseConsumes = $this->expenseConsumeModel->getAllExpenseConsumes();

        if ($expenseConsumes !== false) {
            Response::success('Expense consumes retrieved successfully!', $expenseConsumes);
        } else {
            Response::error('Failed to retrieve expense consumes', 500);
        }
    }

    public function getExpenseConsumeById($con_id)
    {
        $expenseConsume = $this->expenseConsumeModel->getExpenseConsumeById($con_id);

        if ($expenseConsume !== false) {
            Response::success('Expense consume retrieved successfully!', $expenseConsume);
        } else {
            Response::error('Expense consume not found', 404);
        }
    }

    public function createExpenseConsume()
    {
        // POST METHOD ONLY
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        // Start session to get user_id and loc_id
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        if (!isset($_SESSION['loc_id'])) {
            Response::error('User location not set in session', 400);
            return;
        }

        $user_id = $_SESSION['user_id'];
        $station_id = $_SESSION['loc_id'];

        // READ JSON INPUT
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        $required = [
            'expense_id',
            'recorded_date',
            'payment_entries'
        ];

        foreach ($required as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        // Validate payment_entries is an array with at least one entry
        if (!is_array($input['payment_entries']) || count($input['payment_entries']) === 0) {
            error_log("Payment entries validation failed. Type: " . gettype($input['payment_entries']) . ", Count: " . (is_array($input['payment_entries']) ? count($input['payment_entries']) : 'N/A'));
            error_log("Full input data: " . json_encode($input));
            Response::error('At least one payment entry is required', 400);
            return;
        }

        // Validate each payment entry
        $total_expense_amount = 0;
        $total_charges_amount = 0;
        foreach ($input['payment_entries'] as $index => $entry) {
            if (!isset($entry['account_id']) || !isset($entry['amount']) || !isset($entry['charges'])) {
                error_log("Payment entry #{$index} validation failed: " . json_encode($entry));
                Response::error('Invalid payment entry: missing required fields (account_id, amount, or charges)', 400);
                return;
            }

            if (!is_numeric($entry['amount']) || $entry['amount'] <= 0) {
                error_log("Payment entry #{$index} amount validation failed: " . $entry['amount']);
                Response::error('Each payment entry amount must be a positive number', 400);
                return;
            }

            if (!is_numeric($entry['charges']) || $entry['charges'] < 0) {
                error_log("Payment entry #{$index} charges validation failed: " . $entry['charges']);
                Response::error('Each payment entry charges must be non-negative', 400);
                return;
            }

            $total_expense_amount += (float)$entry['amount'];
            $total_charges_amount += (float)$entry['charges'];
        }

        error_log("Validation passed. Processing " . count($input['payment_entries']) . " payment entries. Total amount: {$total_expense_amount}, Total charges: {$total_charges_amount}");

        // Generate single transaction ID for all entries
        $trans_id = $this->expenseConsumeModel->getNextTransId();

        // Get database connection for transaction
        $db = (new \Config\Database())->getConnection();

        try {
            // Begin database transaction
            $db->beginTransaction();

            // Process each payment entry
            foreach ($input['payment_entries'] as $entry) {
                $acc_id = (int)$entry['account_id'];
                $amount = (float)$entry['amount'];
                $charges = (float)$entry['charges'];
                $total_required = $amount + $charges;

                // Check account balance
                if (!$this->accountModel->checkBalance($acc_id, $total_required)) {
                    $db->rollBack();
                    $current_balance = $this->accountModel->getBalance($acc_id);
                    Response::error("Insufficient balance in account ID {$acc_id}. Required: {$total_required}, Available: {$current_balance}", 400);
                    return;
                }

                // Create expense consume record for this entry
                $data = [
                    'expense_id' => (int)$input['expense_id'],
                    'station_id' => (int)$station_id,
                    'amount' => $amount,
                    'pay_mode' => $acc_id,
                    'trans_id' => $trans_id,
                    'payer_name' => isset($input['payer_name']) ? trim($input['payer_name']) : null,
                    'description' => isset($input['description']) ? trim($input['description']) : null,
                    'recorded_date' => trim($input['recorded_date'])
                ];

                $con_id = $this->expenseConsumeModel->createExpenseConsume($data);
                
                if (!$con_id) {
                    $db->rollBack();
                    Response::error('Failed to record expense consume', 500);
                    return;
                }

                // Create journal entries (expense + charges) for this payment
                $journal_success = $this->journalEntryModel->createExpenseTransaction(
                    $data['recorded_date'],
                    $acc_id,
                    $amount,
                    $charges,
                    $acc_id,
                    $data['expense_id'],
                    $data['description'],
                    $user_id
                );

                if (!$journal_success) {
                    $db->rollBack();
                    Response::error('Failed to create journal entries', 500);
                    return;
                }

                // Update account balance (deduct total amount + charges)
                $balance_updated = $this->accountModel->updateBalance($acc_id, $total_required);

                if (!$balance_updated) {
                    $db->rollBack();
                    Response::error('Failed to update account balance', 500);
                    return;
                }
            }

            // Commit all changes
            $db->commit();

            Response::success('Expense consume recorded successfully', [
                'trans_id' => $trans_id,
                'total_amount' => $total_expense_amount,
                'total_charges' => $total_charges_amount,
                'grand_total' => $total_expense_amount + $total_charges_amount,
                'entries_count' => count($input['payment_entries'])
            ]);

        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Error in createExpenseConsume: " . $e->getMessage());
            Response::error('An error occurred while processing the transaction: ' . $e->getMessage(), 500);
        }
    }

    public function updateExpenseConsume()
    {
        // POST METHOD ONLY
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        // Start session to get loc_id
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['loc_id'])) {
            Response::error('User location not set in session', 400);
            return;
        }

        $station_id = $_SESSION['loc_id'];

        // READ JSON INPUT
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        $required = [
            'con_id',
            'expense_id',
            'amount',
            'pay_mode',
            'recorded_date'
        ];

        foreach ($required as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        // Validate amount is numeric and positive
        if (!is_numeric($input['amount']) || $input['amount'] <= 0) {
            Response::error('Amount must be a positive number', 400);
            return;
        }

        $data = [
            'con_id' => (int)$input['con_id'],
            'expense_id' => (int)$input['expense_id'],
            'station_id' => (int)$station_id,
            'amount' => (float)$input['amount'],
            'pay_mode' => (int)$input['pay_mode'],
            'payer_name' => isset($input['payer_name']) ? trim($input['payer_name']) : null,
            'description' => isset($input['description']) ? trim($input['description']) : null,
            'recorded_date' => trim($input['recorded_date'])
        ];

        if ($this->expenseConsumeModel->updateExpenseConsume($data)) {
            Response::success('Expense consume updated successfully', $data);
        } else {
            Response::error('Failed to update expense consume', 500);
        }
    }

    public function deleteExpenseConsume()
    {
        // POST METHOD ONLY
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        // READ JSON INPUT
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        if (empty($input['con_id'])) {
            Response::error('Missing con_id field', 400);
            return;
        }

        $con_id = (int)$input['con_id'];

        if ($this->expenseConsumeModel->deleteExpenseConsume($con_id)) {
            Response::success('Expense consume deleted successfully', ['con_id' => $con_id]);
        } else {
            Response::error('Failed to delete expense consume', 500);
        }
    }

    public function getExpensesByStation($station_id)
    {
        $expenses = $this->expenseConsumeModel->getExpensesByStation($station_id);

        if ($expenses !== false) {
            Response::success('Station expenses retrieved successfully!', $expenses);
        } else {
            Response::error('Failed to retrieve station expenses', 500);
        }
    }

    public function getTotalExpensesByPeriod()
    {
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-t');

        $total = $this->expenseConsumeModel->getTotalExpensesByPeriod($start_date, $end_date);

        if ($total !== false) {
            Response::success('Total expenses retrieved successfully!', ['total' => $total]);
        } else {
            Response::error('Failed to retrieve total expenses', 500);
        }
    }

    /**
     * Get accounts by payment mode ID
     */
    public function getAccountsByPaymentMode($mode_id)
    {
        if (empty($mode_id)) {
            Response::error('Payment mode ID is required', 400);
            return;
        }

        $accounts = $this->accountModel->getAccountsByPaymentMode($mode_id);

        if ($accounts !== false) {
            Response::success('Accounts retrieved successfully!', $accounts);
        } else {
            Response::error('Failed to retrieve accounts', 500);
        }
    }
}
