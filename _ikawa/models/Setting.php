<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use PDOException;

class Setting
 {
    private $conn;

    public function __construct()
 {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getRoles()
 {
        try {
            $query = 'SELECT * FROM  tbl_roles where role_id !=1 ';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute();
            return $stmt->fetchAll( PDO::FETCH_ASSOC );

        } catch ( PDOException $e ) {
            return false;
        }
    }

    public function getLocation()
 {
        try {
            $query = 'SELECT * FROM  tbl_location';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute();
            return $stmt->fetchAll( PDO::FETCH_ASSOC );

        } catch ( PDOException $e ) {
            return false;
        }
    }

    public function exists( string $role_name ): ?string
 {
        $sql = "
            SELECT 
                CASE
                    WHEN role_name = :role_name THEN 'role_name'
                END AS field
            FROM tbl_roles
            WHERE role_name = :role_name
            LIMIT 1
        ";

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute( [
            ':role_name' => $role_name
        ] );

        $row = $stmt->fetch( PDO::FETCH_ASSOC );

        return $row[ 'field' ] ?? null;
    }

    public function createRole( array $data ): bool
 {
        try {
            $sql = "
                INSERT INTO tbl_roles (
                    role_name,
                    description
                ) VALUES (
                    :role_name,
                    :description
                )
            ";

            $stmt = $this->conn->prepare( $sql );

            return $stmt->execute( [
                ':role_name' => $data[ 'role_name' ],
                ':description'  => $data[ 'description' ]
            ] );
        } catch ( PDOException $e ) {
            return false;
        }
    }

}
