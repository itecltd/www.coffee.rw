<?php
namespace Controllers;

require_once __DIR__ . '/../models/Account.php';
require_once __DIR__ . '/../models/JournalEntry.php';
require_once __DIR__ . '/../models/Investment.php';
require_once __DIR__ . '/../config/Response.php';

use Models\Account;
use Models\JournalEntry;
use Models\Investment;
use Config\Response;

class ReportsController {
    private $accountModel;
    private $journalModel;
    private $investmentModel;
    private $db;

    public function __construct() {
        $this->accountModel = new Account();
        $this->db = (new \Config\Database())->getConnection();
        $this->journalModel = new JournalEntry($this->db);
        $this->investmentModel = new Investment();
    }

    // POST: returns account operations statement
    public function accountOperationsStatement() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        $acc_id = isset($input['account_id']) ? (int)$input['account_id'] : null;
        $date_from = $input['date_from'] ?? date('Y-m-01');
        $date_to = $input['date_to'] ?? date('Y-m-t');
        $display = $input['display'] ?? 'both'; // values: both, expense, recharge

        if (!$acc_id) {
            Response::error('Account ID is required', 400);
            return;
        }

        try {
            // Query journal entries grouped by reference_id (expenses + charges)
            $sql = "SELECT reference_id, MIN(entry_date) AS entry_date,
                           SUM(CASE WHEN action = 'expense' THEN amount ELSE 0 END) AS expense_amount,
                           SUM(CASE WHEN action = 'charges' THEN charges ELSE 0 END) AS charges_amount
                    FROM tbl_journal_entries
                    WHERE debit_account_id = :acc_id
                      AND entry_date BETWEEN :date_from AND :date_to
                      AND action IN ('expense','charges')
                    GROUP BY reference_id
                    ORDER BY entry_date ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':acc_id' => $acc_id,
                ':date_from' => $date_from . ' 00:00:00',
                ':date_to' => $date_to . ' 23:59:59'
            ]);

            $expenses = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $rows = [];

            if ($display === 'both' || $display === 'expense') {
                foreach ($expenses as $r) {
                    $total = (float)$r['expense_amount'] + (float)$r['charges_amount'];
                    $rows[] = [
                        'date' => $r['entry_date'],
                        'reference_id' => $r['reference_id'],
                        'type' => 'expense',
                        'expense_amount' => (float)$r['expense_amount'],
                        'charges' => (float)$r['charges_amount'],
                        'total' => $total
                    ];
                }
            }

            if ($display === 'both' || $display === 'recharge') {
                // get recharges from investments
                $sql2 = "SELECT id as reference_id, in_amount as amount, done_at FROM tbl_investiments WHERE account_id = :acc_id AND done_at BETWEEN :date_from AND :date_to ORDER BY done_at ASC";
                $stmt2 = $this->db->prepare($sql2);
                $stmt2->execute([
                    ':acc_id' => $acc_id,
                    ':date_from' => $date_from . ' 00:00:00',
                    ':date_to' => $date_to . ' 23:59:59'
                ]);
                $recharges = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($recharges as $rc) {
                    $rows[] = [
                        'date' => $rc['done_at'],
                        'reference_id' => $rc['reference_id'],
                        'type' => 'recharge',
                        'expense_amount' => 0,
                        'charges' => 0,
                        'total' => (float)$rc['amount']
                    ];
                }
            }

            // Sort rows by date ascending
            usort($rows, function($a, $b){
                return strtotime($a['date']) <=> strtotime($b['date']);
            });

            // Totals
            $totals = ['expense' => 0.0, 'charges' => 0.0, 'recharge' => 0.0];
            foreach ($rows as $r) {
                if ($r['type'] === 'expense') {
                    $totals['expense'] += $r['expense_amount'];
                    $totals['charges'] += $r['charges'];
                } elseif ($r['type'] === 'recharge') {
                    $totals['recharge'] += $r['total'];
                }
            }

            $current_balance = $this->accountModel->getBalance($acc_id);

            Response::success('Account operations statement retrieved', [
                'account_id' => $acc_id,
                'balance' => $current_balance,
                'rows' => $rows,
                'totals' => $totals
            ]);

        } catch (\Exception $e) {
            error_log('Error generating account operations statement: ' . $e->getMessage());
            Response::error('Failed to generate statement', 500);
        }
    }
}

?>
