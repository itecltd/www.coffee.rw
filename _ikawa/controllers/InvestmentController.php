<?php
namespace Controllers;

require_once __DIR__ . '/../config/Response.php';
require_once __DIR__ . '/../models/Investment.php';

use Models\Investment;
use Config\Response;

class InvestmentController
{
    private $investmentModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->investmentModel = new Investment();
    }

    // POST /investments/create
    public function createInvestment()
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['loc_id'])) {
            Response::error('Unauthorized', 401);
            return;
        }

        $in_amount = isset($_POST['in_amount']) ? (int)$_POST['in_amount'] : 0;
        $account_id = isset($_POST['account_id']) ? (int)$_POST['account_id'] : 0;
        $description = $_POST['description'] ?? null;
        $reciept = $_POST['reciept'] ?? null;
        $action = $_POST['action'] ?? 'recharge';

        if ($in_amount <= 0 || $account_id <= 0) {
            Response::error('Invalid input: amount and account are required', 400);
            return;
        }

        $result = $this->investmentModel->createInvestment(
            $in_amount,
            $_SESSION['user_id'],
            $account_id,
            $_SESSION['loc_id'],
            $description,
            $reciept,
            $action
        );

        if ($result === true) {
            Response::success('Account recharged successfully');
        } else {
            Response::error('Failed to create investment: ' . $result, 500);
        }
    }

    // GET /investments/get-by-location
    public function getInvestmentsByLocation()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loc_id'])) {
            Response::error('Location not set', 401);
            return;
        }
        $data = $this->investmentModel->getInvestmentsByLocation($_SESSION['loc_id']);
        Response::success('Success', $data);
    }
}


