<?php
namespace Controllers;

// Include dependencies
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../config/Response.php';

use Models\Setting;
use Config\Response;

class SettingController
 {
    private $settingModel;

    public function __construct()
 {
        $this->settingModel = new Setting();
    }

    public function getAllRoles()
 {
        $setting = $this->settingModel->getRoles();

        if ( $setting !== false ) {
            Response::success( 'Roles retrieved successfully!', $setting );
        } else {
            Response::error( 'Failed to retrieve users', 500 );
        }
    }

    public function getAllUnits()
 {
        $units = $this->settingModel->getUnits();

        if ( $units !== false ) {
            Response::success( 'Units retrieved successfully!', $units );
        } else {
            Response::error( 'Failed to retrieve users', 500 );
        }
    }

    public function createHeadQuater()
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
            'location_name'
        ];

        foreach ( $required as $field ) {
            if ( empty( $input[ $field ] ) ) {
                Response::error( "Missing field: {$field}", 400 );
                return;
            }
        }

        $location_name = trim( $input[ 'location_name' ] );
        //DUPLICATE CHECK
        $duplicate = $this->settingModel->existsHeadQuarter( $location_name );

        if ( $duplicate !== null ) {
            Response::error(
                ucfirst( $duplicate ) . ' already exists',
                409
            );
            return;
        }

        $data = [
            'location_name' => trim( $input[ 'location_name' ] ),
            'description'  => trim( $input[ 'description' ] ),
            'type'=>trim( $input[ 'type' ] )

        ];

        if ( $this->settingModel->createHeadQuater( $data ) ) {
            Response::success( 'Station created successfully', [
                'location_name' => $location_name
            ] );
        } else {
            Response::error( 'Failed to create user', 500 );
        }
    }

    public function UpdateLocation()
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
            'location_name'
        ];

        foreach ( $required as $field ) {
            if ( empty( $input[ $field ] ) ) {
                Response::error( "Missing field: {$field}", 400 );
                return;
            }
        }

        $location_name = trim( $input[ 'location_name' ] );
        $loc_id   = trim( $input[ 'loc_id' ] );

        //DUPLICATE CHECK
        $duplicate = $this->settingModel->existsStation( $location_name, $loc_id );

        if ( $duplicate !== null ) {
            Response::error(
                ucfirst( $duplicate ) . ' already exists',
                409
            );
            return;
        }

        $data = [
            'location_name' =>$location_name,
            'description'  => trim( $input[ 'description' ] ),
            'type'  => trim( $input[ 'type' ] ),
            'loc_id'      => $loc_id
        ];

        if ( $this->settingModel->updateLocat( $data ) ) {
            Response::success( 'Station updated successfully', [
                'location_name' => $location_name,
                'loc_id' => $loc_id
            ] );
        } else {
            Response::error( 'Failed to update role', 500 );
        }
    }

    public function getAllLocation() {
        $setting = $this->settingModel->getLocation();

        if ( $setting !== false ) {
            Response::success( 'Location retrieved successfully!', $setting );
        } else {
            Response::error( 'Failed to retrieve users', 500 );
        }
    }

    public function CreateRole()
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
            'role_name'
        ];

        foreach ( $required as $field ) {
            if ( empty( $input[ $field ] ) ) {
                Response::error( "Missing field: {$field}", 400 );
                return;
            }
        }

        $role_name = trim( $input[ 'role_name' ] );
        //DUPLICATE CHECK
        $duplicate = $this->settingModel->exists( $role_name );

        if ( $duplicate !== null ) {
            Response::error(
                ucfirst( $role_name ) . ' already exists',
                409
            );
            return;
        }

        $data = [
            'role_name' => trim( $input[ 'role_name' ] ),
            'description'  => trim( $input[ 'description' ] )
        ];

        if ( $this->settingModel->createRole( $data ) ) {
            Response::success( 'Role created successfully', [
                'role_name' => $role_name
            ] );
        } else {
            Response::error( 'Failed to create user', 500 );
        }
    }
}
