<?php
namespace Controllers;

require_once __DIR__ . '/../models/Stock.php';
require_once __DIR__ . '/../config/Response.php';

use Models\Stock;
use Config\Response;

class StockController
{
    private $stockModel;

    public function __construct()
    {
        $this->stockModel = new Stock();
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        session_start();
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        $required = ['type_id', 'unit_id', 'sup_id', 'quantity', 'unit_price', 'total_price'];

        foreach ($required as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $data = [
            'type_id' => trim($input['type_id']),
            'unit_id' => trim($input['unit_id']),
            'sup_id' => trim($input['sup_id']),
            'quantity' => floatval($input['quantity']),
            'unit_price' => floatval($input['unit_price']),
            'total_price' => floatval($input['total_price']),
            'loc_id' => $_SESSION['loc_id'],
            'user_id' => $_SESSION['user_id']
        ];

        if ($this->stockModel->createStock($data)) {
            Response::success('Stock added successfully');
        } else {
            Response::error('Failed to add stock', 500);
        }
    }

    public function getDetailedStock()
    {
        session_start();
        if (!isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $stock = $this->stockModel->getDetailedStock($_SESSION['loc_id']);

        if ($stock !== false) {
            Response::success('Stock retrieved successfully', $stock);
        } else {
            Response::error('Failed to retrieve stock', 500);
        }
    }

    public function getSummaryStock()
    {
        session_start();
        if (!isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $summary = $this->stockModel->getSummaryStock($_SESSION['loc_id']);

        if ($summary !== false) {
            Response::success('Summary retrieved successfully', $summary);
        } else {
            Response::error('Failed to retrieve summary', 500);
        }
    }

    public function createMultiple()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        session_start();
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input) || !isset($input['items']) || !is_array($input['items'])) {
            Response::error('Invalid JSON payload', 400);
            return;
        }

        $items = $input['items'];
        
        if (count($items) === 0) {
            Response::error('No items to save', 400);
            return;
        }

        $required = ['type_id', 'unit_id', 'sup_id', 'quantity', 'unit_price', 'total_price'];
        
        foreach ($items as $index => $item) {
            foreach ($required as $field) {
                if (!isset($item[$field]) || $item[$field] === '') {
                    Response::error("Missing field: {$field} in row " . ($index + 1), 400);
                    return;
                }
            }
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($items as $item) {
            $data = [
                'type_id' => trim($item['type_id']),
                'unit_id' => trim($item['unit_id']),
                'sup_id' => trim($item['sup_id']),
                'quantity' => floatval($item['quantity']),
                'unit_price' => floatval($item['unit_price']),
                'total_price' => floatval($item['total_price']),
                'loc_id' => $_SESSION['loc_id'],
                'user_id' => $_SESSION['user_id']
            ];

            if ($this->stockModel->createStock($data)) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        if ($errorCount === 0) {
            Response::success("Successfully added {$successCount} stock item(s)");
        } else if ($successCount > 0) {
            Response::success("Added {$successCount} item(s), {$errorCount} failed");
        } else {
            Response::error('Failed to add stock items', 500);
        }
    }
}
