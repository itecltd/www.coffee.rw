<?php
namespace Controllers;

// Include dependencies
require_once __DIR__ . '/../models/ExpenseConsume.php';
require_once __DIR__ . '/../config/Response.php';

use Models\ExpenseConsume;
use Config\Response;

class ExpenseConsumeController
{
    private $expenseConsumeModel;

    public function __construct()
    {
        $this->expenseConsumeModel = new ExpenseConsume();
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

        // READ JSON INPUT
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        $required = [
            'expense_id',
            'station_id',
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
            'expense_id' => (int)$input['expense_id'],
            'station_id' => (int)$input['station_id'],
            'amount' => (float)$input['amount'],
            'pay_mode' => (int)$input['pay_mode'],
            'payer_name' => isset($input['payer_name']) ? trim($input['payer_name']) : null,
            'description' => isset($input['description']) ? trim($input['description']) : null,
            'recorded_date' => trim($input['recorded_date'])
        ];

        if ($this->expenseConsumeModel->createExpenseConsume($data)) {
            Response::success('Expense consume recorded successfully', $data);
        } else {
            Response::error('Failed to record expense consume', 500);
        }
    }

    public function updateExpenseConsume()
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

        $required = [
            'con_id',
            'expense_id',
            'station_id',
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
            'station_id' => (int)$input['station_id'],
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
}
