<?php
namespace Controllers;

// Include dependencies
require_once __DIR__ . '/../models/Account.php';
require_once __DIR__ . '/../config/Response.php';

use Models\Account;
use Config\Response;

class AccountController
{
    private $accountModel;

    public function __construct()
    {
        $this->accountModel = new Account();
    }

    public function getAllAccounts()
    {
        $accounts = $this->accountModel->getAllAccounts();

        if ($accounts !== false) {
            Response::success('Accounts retrieved successfully!', $accounts);
        } else {
            Response::error('Failed to retrieve accounts', 500);
        }
    }

    public function getAccountById($acc_id)
    {
        $account = $this->accountModel->getAccountById($acc_id);

        if ($account !== false) {
            Response::success('Account retrieved successfully!', $account);
        } else {
            Response::error('Account not found', 404);
        }
    }

    public function createAccount()
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

        $required = ['mode_id', 'acc_name', 'acc_reference_num', 'st_id'];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $acc_name = trim($input['acc_name']);
        
        // DUPLICATE CHECK
        $duplicate = $this->accountModel->accountExists($acc_name);

        if ($duplicate !== null) {
            Response::error('Account name already exists', 409);
            return;
        }

        $data = [
            'mode_id' => $input['mode_id'],
            'acc_name' => $acc_name,
            'acc_reference_num' => trim($input['acc_reference_num']),
            'st_id' => $input['st_id']
        ];

        $result = $this->accountModel->createAccount($data);

        if ($result) {
            Response::success('Account created successfully!', null, 201);
        } else {
            Response::error('Failed to create account', 500);
        }
    }

    public function updateAccount()
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

        $required = ['acc_id', 'mode_id', 'acc_name', 'acc_reference_num', 'st_id'];

        foreach ($required as $field) {
            if (empty($input[$field])) {
                Response::error("Missing field: {$field}", 400);
                return;
            }
        }

        $data = [
            'acc_id' => $input['acc_id'],
            'mode_id' => $input['mode_id'],
            'acc_name' => trim($input['acc_name']),
            'acc_reference_num' => trim($input['acc_reference_num']),
            'st_id' => $input['st_id']
        ];

        $result = $this->accountModel->updateAccount($data);

        if ($result) {
            Response::success('Account updated successfully!');
        } else {
            Response::error('Failed to update account', 500);
        }
    }

    public function deleteAccount($acc_id)
    {
        if (empty($acc_id)) {
            Response::error('Account ID is required', 400);
            return;
        }

        $result = $this->accountModel->deleteAccount($acc_id);

        if ($result) {
            Response::success('Account deleted successfully!');
        } else {
            Response::error('Failed to delete account', 500);
        }
    }
}
