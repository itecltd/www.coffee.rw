<?php
namespace Controllers;
// Include dependencies
require_once __DIR__ . '/../models/Inadvance.php';
require_once __DIR__ . '/../config/Response.php';

use Models\Inadvance;
use Config\Response;

class InadvanceController {
    private $inadvanceModel;

    public function __construct() {
        $this->inadvanceModel = new Inadvance();
    }

    public function getSupplier() {
        if ( $_SERVER[ 'REQUEST_METHOD' ] !== 'GET' ) {
            Response::error( 'Invalid request method', 405 );
            return;
        }

        // Get data from GET parameters, not JSON input
        $requestType = $_GET[ 'request_type' ] ?? '';

        if ( empty( $requestType ) ) {
            Response::error( 'Missing field: request_type', 400 );
            return;
        }

        $data = [ 'request_type' => $requestType ];
        $suppliers = $this->inadvanceModel->getAllSuppliers( $data );

        if ( $suppliers !== false ) {
            Response::success( 'Suppliers retrieved successfully!', $suppliers );
        } else {
            Response::error( 'Failed to retrieve suppliers', 500 );
        }
    }

    public function createInAdvance() {
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
            'destination_id', 'amount', 'reason', 'created_by', 'station_id'
        ];

        foreach ( $required as $field ) {
            if ( empty( $input[ $field ] ) ) {
                Response::error( "Missing field: {$field}", 400 );
                return;
            }
        }

        $destination_id = trim( $input[ 'destination_id' ] );
        $n_days = trim( $input[ 'n_days' ] );
        $payment_on = date( 'Y-m-d', strtotime( "+$n_days days" ) );
        $created_at = date( 'Y-m-d' );
        //check Active Loan
        $activeloan = $this->inadvanceModel->existsActiveAdvance( $destination_id );

        if ( $activeloan !== null ) {
            Response::error(
                ucfirst( $activeloan ),
                409
            );
            return;
        }

        $data = [
            'destination_id' => $destination_id,
            'amount'=>trim( $input[ 'amount' ] ),
            'payment_on'=>$payment_on,
            'created_at'=>$created_at,
            'reason'=>trim( $input[ 'reason' ] ),
            'station_id'=>trim( $input[ 'station_id' ] ),
            'created_by'=>trim( $input[ 'created_by' ] ),
            'status'=>'pending'
        ];

        if ( $this->inadvanceModel->createInAdvance( $data ) ) {
            Response::success( 'Advance created successfully', [
                'destination_id' => $destination_id,
                'amount'=>trim( $input[ 'amount' ] )
            ] );
        } else {
            Response::error( 'Failed to create user', 500 );
        }
    }

    public function getAllAdvances() {
        $advancelists = $this->inadvanceModel->getAllAdvanceLists();

        if ( $advancelists !== false ) {
            Response::success( 'Advances retrieved successfully!', $advancelists );
        } else {
            Response::error( 'Failed to retrieve Advances', 500 );
        }
    }

    public function getAllAdvancesPending() {
        $advancelistspending = $this->inadvanceModel->getAllAdvanceListsPending();

        if ( $advancelistspending !== false ) {
            Response::success( 'Advances pending retrieved successfully!', $advancelistspending );
        } else {
            Response::error( 'Failed to retrieve Advances', 500 );
        }
    }

    public function rejectAdvancePending() {
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
            'adv_id', 'approved_by', 'rejected_reason'
        ];

        foreach ( $required as $field ) {
            if ( empty( $input[ $field ] ) ) {
                Response::error( "Missing field: {$field}", 400 );
                return;
            }
        }

        $data = [
            'adv_id' =>trim( $input[ 'adv_id' ] ),
            'approved_by'=>trim( $input[ 'approved_by' ] ),
            'rejected_reason'=>trim( $input[ 'rejected_reason' ] ),
            'status'=>'rejected'
        ];

        if ( $this->inadvanceModel->rejectInAdvance( $data ) ) {
            Response::success( 'Advance rejected successfully', [
                'adv_id'=>trim( $input[ 'adv_id' ] ),
                'rejected_reason'=>trim( $input[ 'rejected_reason' ] )
            ] );
        } else {
            Response::error( 'Failed to create user', 500 );
        }
    }

    public function approveinadvancerequest( $adv_id ) {
        if ( $this->inadvanceModel->updateRequestAdvance( $adv_id ) ) {
            Response::success( 'Advance approved successfully' );
        } else {
            Response::error( 'Failed to delete user', 500 );
        }
    }

}
