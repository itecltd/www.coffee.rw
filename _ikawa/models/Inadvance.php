<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use PDOException;

class Inadvance {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Model fix

    public function getAllSuppliers( $data ) {
        try {
            $query = 'SELECT * FROM tbl_suppliers where status="active" and type=:type';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute( [ ':type' => $data[ 'request_type' ] ] );
            return $stmt->fetchAll( PDO::FETCH_ASSOC );
        } catch ( PDOException $e ) {
            return false;
        }
    }

    public function existsActiveAdvance( string $destination_id ): ?string {
        $sql = "
        SELECT 
            CASE
                WHEN status IN ('pending', 'approved', 'outstanding', 'partially_cleared') 
                THEN 'There is an active advance that needs to be settled first'
            END AS field
        FROM tbl_advances
        WHERE destination_id = :destination_id 
        AND status IN ('pending', 'approved', 'outstanding', 'partially_cleared')
        LIMIT 1
    ";

        $stmt = $this->conn->prepare( $sql );
        $stmt->execute( [ ':destination_id' => $destination_id ] );
        $row = $stmt->fetch( PDO::FETCH_ASSOC );

        return $row[ 'field' ] ?? null;
    }

    public function createInAdvance( array $data ): bool {
        try {
            $sql = "
                INSERT INTO tbl_advances (
                    destination_id,
                    station_id,
                    created_by,
                    amount,
                    created_at,
                    payment_on,
                    reason,
                    status
                ) VALUES (
                    :destination_id,
                    :station_id,
                    :created_by,
                    :amount,
                    :created_at,
                    :payment_on,
                    :reason,
                    :status
                )
            ";

            $stmt = $this->conn->prepare( $sql );

            return $stmt->execute( [
                ':destination_id' => $data[ 'destination_id' ],
                ':station_id'  => $data[ 'station_id' ],
                ':created_by'  => $data[ 'created_by' ],
                ':amount'  => $data[ 'amount' ],
                ':created_at'=>$data[ 'created_at' ],
                ':payment_on'=>$data[ 'payment_on' ],
                ':reason'=>$data[ 'reason' ],
                ':status'=>$data[ 'status' ]

            ] );
        } catch ( PDOException $e ) {
            return false;
        }
    }

    public function getAllAdvanceLists() {
        try {
            $query = 'SELECT sp.*,adv.* FROM tbl_advances adv inner join tbl_suppliers sp
            on sp.sup_id=adv.destination_id  order by adv.adv_id DESC';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute();
            return $stmt->fetchAll( PDO::FETCH_ASSOC );

        } catch ( PDOException $e ) {
            return false;
        }
    }

    public function getAllAdvanceListsPending() {
        try {
            $query = 'SELECT sp.*,adv.*,user.first_name,user.last_name FROM tbl_advances adv inner join tbl_suppliers sp
            on sp.sup_id=adv.destination_id
            inner join tbl_users user on user.user_id=adv.created_by where adv.status in ("pending")';
            $stmt = $this->conn->prepare( $query );
            $stmt->execute();
            return $stmt->fetchAll( PDO::FETCH_ASSOC );

        } catch ( PDOException $e ) {
            return false;
        }
    }

    public function rejectInAdvance( array $data ): bool {

        try {
            $sql = "UPDATE tbl_advances SET approved_by=:approved_by,status=:status,rejected_reason=:rejected_reason
            where adv_id=:adv_id";

            $stmt = $this->conn->prepare( $sql );

            return $stmt->execute( [
                ':approved_by' => $data[ 'approved_by' ],
                ':status'  => $data[ 'status' ],
                ':rejected_reason'  => $data[ 'rejected_reason' ],
                ':adv_id'  => $data[ 'adv_id' ]
            ] );
        } catch ( PDOException $e ) {
            return false;
        }
    }

    public function updateRequestAdvance( $adv_id ) {
        $sql = 'UPDATE  tbl_advances SET status = "approved" WHERE adv_id = :adv_id';
        $stmt = $this->conn->prepare( $sql );
        return $stmt->execute( [ ':adv_id' => $adv_id ] );
    }

}
