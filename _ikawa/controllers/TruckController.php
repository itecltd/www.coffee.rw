<?php
namespace Controllers;

require_once __DIR__ . '/../models/Truck.php';
require_once __DIR__ . '/../config/Response.php';

use Models\Truck;
use Config\Response;

class TruckController
{
    private $truckModel;

    public function __construct()
    {
        $this->truckModel = new Truck();
    }

    public function getAllTrucks()
    {
        $trucks = $this->truckModel->getAllTrucks();

        if ($trucks !== false) {
            Response::success('Trucks retrieved successfully', $trucks);
        } else {
            Response::error('Failed to retrieve trucks', 500);
        }
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Invalid request method', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input) || empty($input['plate_number'])) {
            Response::error('Plate number is required', 400);
            return;
        }

        $plate_number = strtoupper(trim($input['plate_number']));

        if ($this->truckModel->exists($plate_number)) {
            Response::error('Plate number already exists', 409);
            return;
        }

        $data = [
            'plate_number' => $plate_number,
            'driver_name' => trim($input['driver_name'] ?? ''),
            'driver_phone' => trim($input['driver_phone'] ?? ''),
            'capacity' => floatval($input['capacity'] ?? 0)
        ];

        if ($this->truckModel->createTruck($data)) {
            Response::success('Truck created successfully');
        } else {
            Response::error('Failed to create truck', 500);
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::error('Invalid request method', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input) || empty($input['truck_id']) || empty($input['plate_number'])) {
            Response::error('Truck ID and plate number are required', 400);
            return;
        }

        $plate_number = strtoupper(trim($input['plate_number']));
        $truck_id = intval($input['truck_id']);

        if ($this->truckModel->existsUpdate($plate_number, $truck_id)) {
            Response::error('Plate number already exists', 409);
            return;
        }

        $data = [
            'truck_id' => $truck_id,
            'plate_number' => $plate_number,
            'driver_name' => trim($input['driver_name'] ?? ''),
            'driver_phone' => trim($input['driver_phone'] ?? ''),
            'capacity' => floatval($input['capacity'] ?? 0),
            'status' => $input['status'] ?? 'active'
        ];

        if ($this->truckModel->updateTruck($data)) {
            Response::success('Truck updated successfully');
        } else {
            Response::error('Failed to update truck', 500);
        }
    }
}
