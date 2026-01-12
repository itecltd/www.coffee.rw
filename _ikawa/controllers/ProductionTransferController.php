<?php
namespace Controllers;

require_once __DIR__ . '/../models/ProductionTransfer.php';
require_once __DIR__ . '/../config/Response.php';

use Models\ProductionTransfer;
use Config\Response;

class ProductionTransferController
{
    private $transferModel;

    public function __construct()
    {
        $this->transferModel = new ProductionTransfer();
    }

    public function getAvailableStock()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $loc_id = intval($_SESSION['loc_id']);
        $stock = $this->transferModel->getAvailableStock($loc_id);

        if ($stock !== false) {
            Response::success('Available stock retrieved', $stock);
        } else {
            Response::error('Failed to retrieve stock', 500);
        }
    }

    public function createMultiple()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input) || !isset($input['items']) || !is_array($input['items']) || count($input['items']) === 0) {
            Response::error('No items to transfer', 400);
            return;
        }

        $transfer_date = $input['transfer_date'] ?? date('Y-m-d');
        $notes = trim($input['notes'] ?? '');

        // Use user's loc_id as both from and to location
        $loc_id = $_SESSION['loc_id'];

        $data = [
            'from_loc_id' => $loc_id,
            'to_loc_id' => $loc_id,  // Same as from_loc_id (user's station)
            'transfer_date' => $transfer_date,
            'notes' => $notes,
            'user_id' => $_SESSION['user_id'],
            'items' => $input['items']
        ];

        $result = $this->transferModel->createTransferBatch($data);

        if ($result['success']) {
            Response::success('Transfer created successfully', ['reference_no' => $result['reference_no']]);
        } else {
            Response::error($result['message'] ?? 'Failed to create transfer', 500);
        }
    }

    public function getTransfers()
    {
        session_start();
        if (!isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $transfers = $this->transferModel->getTransfersByLocation($_SESSION['loc_id']);

        if ($transfers !== false) {
            Response::success('Transfers retrieved successfully', $transfers);
        } else {
            Response::error('Failed to retrieve transfers', 500);
        }
    }

    public function getTransferDetails($tracking_id)
    {
        session_start();
        if (!isset($_SESSION['loc_id'])) {
            Response::error('User not authenticated', 401);
            return;
        }

        $details = $this->transferModel->getTransferDetails($tracking_id);

        if ($details !== false) {
            Response::success('Transfer details retrieved successfully', $details);
        } else {
            Response::error('Failed to retrieve transfer details', 500);
        }
    }
}
