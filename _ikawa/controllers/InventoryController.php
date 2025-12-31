<?php
namespace Controllers;

// Include dependencies
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../config/Response.php';

use Models\Inventory;
use Config\Response;

class InventoryController
 {
    private $inventoryModel;

    public function __construct()
 {
        $this->inventoryModel = new Inventory();
    }

    public function createSupplier()
 {
        // POST METHOD ONLY
        if ( $_SERVER[ 'REQUEST_METHOD' ] !== 'POST' ) {
            Response::error( 'Invalid request method', 405 );
            return;
        }

        // READ JSON INPUT
        $input = json_decode( file_get_contents( 'php://input' ), true );

        if ( !is_array( $input ) ) {
            Response::error( 'Invalid JSON payload', 400 );
            return;
        }

        $required = [
            'phone', 'full_name'
        ];

        foreach ( $required as $field ) {
            if ( empty( $input[ $field ] ) ) {
                Response::error( "Missing field: {$field}", 400 );
                return;
            }
        }

        $full_name = trim( $input[ 'full_name' ] );
        $phone = trim( $input[ 'phone' ] );
        $email = trim( $input[ 'email' ] );
        //DUPLICATE CHECK
        $duplicate = $this->inventoryModel->exists( $phone, $email );

        if ( $duplicate !== null ) {
            Response::error(
                ucfirst( $duplicate ) . ' already exists',
                409
            );
            return;
        }

        $data = [
            'full_name' => $full_name,
            'email'  => $email,
            'phone'=>$phone,
            'address'=>trim( $input[ 'address' ] )
        ];

        if ( $this->inventoryModel->createSupp( $data ) ) {
            Response::success( 'Supplier created successfully', [
                'full_name' => $full_name,
                'phone'=>$phone
            ] );
        } else {
            Response::error( 'Failed to create user', 500 );
        }
    }

    public function SupplierList() {
        $suppliers = $this->inventoryModel->getSuppliers();

        if ( $suppliers !== false ) {
            Response::success( 'Suppliers retrieved successfully!', $suppliers );
        } else {
            Response::error( 'Failed to retrieve suppliers', 500 );
        }
    }

    public function UpdateSupplier()
 {
        // POST METHOD ONLY
        if ( $_SERVER[ 'REQUEST_METHOD' ] !== 'PUT' ) {
            Response::error( 'Invalid request method', 405 );
            return;
        }

        // READ JSON INPUT
        $input = json_decode( file_get_contents( 'php://input' ), true );

        if ( !is_array( $input ) ) {
            Response::error( 'Invalid JSON payload', 400 );
            return;
        }

        $required = [
            'full_name', 'sup_id', 'phone'
        ];

        foreach ( $required as $field ) {
            if ( empty( $input[ $field ] ) ) {
                Response::error( "Missing field: {$field}", 400 );
                return;
            }
        }

        $email = trim( $input[ 'email' ] );
        $phone   = trim( $input[ 'phone' ] );
        $sup_id   = trim( $input[ 'sup_id' ] );

        //DUPLICATE CHECK
        $duplicate = $this->inventoryModel->existsUpdate( $email, $phone, $sup_id );

        if ( $duplicate !== null ) {
            Response::error(
                ucfirst( $duplicate ) . ' already exists',
                409
            );
            return;
        }

        $data = [
            'full_name'=> trim( $input[ 'full_name' ] ),
            'email'=> $email,
            'phone'=>$phone,
            'address'=>trim( $input[ 'address' ] ),
            'sup_id' =>$sup_id
        ];

        if ( $this->inventoryModel->updateSuppl( $data ) ) {
            Response::success( 'Supplier updated successfully', [
                'full_name' => trim( $input[ 'full_name' ] ),
                'phone' => $phone
            ] );
        } else {
            Response::error( 'Failed to update role', 500 );
        }
    }

    public function deleteSupplier( $sup_id ) {
        if ( $this->inventoryModel->removeSupplier( $sup_id ) ) {
            Response::success( 'Supplier deleted successfully' );
        } else {
            Response::error( 'Failed to delete user', 500 );
        }
    }

}
