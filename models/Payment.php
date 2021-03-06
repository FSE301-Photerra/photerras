<?php namespace Models\Payment;
$ROOT = __DIR__ . '/..';
require_once $ROOT.'/db.php';

class Payment {
    public $id;             // Unique id
    public $uid;            // assign payment to this user
    public $typeid;
    public $typeDesc;
    public $amount;
    public $token;
    public $createdOn;

    /**
     * Saves a new payment
     */
    function save() {
        $conn = \DB\getConnection();

        $query = sprintf("INSERT INTO Payments (uid, typeid, token, amount)
                          VALUES (%u, %u, '%s', %f)",
                         $this->uid, $this->typeid, $conn->escape_string($this->token), $this->amount);

    

        $result = $conn->query($query);

        // If this is a newly created user, then update the id with the
        // newly created one
        if (!isset($this->id)) {
            $this->id = $result->insert_id;
        }
    }
}

/**
 * Gets a payment type id by the code. If there is no type that matches the code
 * provided, a zero is returned
 * @param string code
 * @return int
 */
function getTypeIdByCode($code) {
    $id = 0;
    $conn = \DB\getConnection();

    // Run the query and get the user details
    $query = sprintf("SELECT id
                      FROM PaymentTypes
                      WHERE code = '%s'",
                     $conn->escape_string($code));

    $result = $conn->query($query);

    if ($result->num_rows) {
        $row = mysqli_fetch_assoc($result);
        $id = $row['id'];
    }

    return $id;
}

/**
 * Gets a payment type by the code. If there is no type that matches the code
 * provided, a zero is returned
 * @param string code
 * @return int
 */
function getTypeByCode($code) {
    $conn = \DB\getConnection();

    // Run the query and get the user details
    $query = sprintf("SELECT *
                      FROM PaymentTypes
                      WHERE code = '%s'",
                     $conn->escape_string($code));

    $result = $conn->query($query);

    return $result;
}

/**
 * Gets the payment history of the given user
 * @param int uid
 * @return mixed
 */
function getByUser($uid) {
    // Run the query and get the user details
    $query = sprintf("SELECT p.id,
                             p.uid,
                             p.typeid,
                             p.amount,
                             p.token,
                             p.createdOn,
                             pt.desc
                      FROM Payments p
                      INNER JOIN PaymentTypes pt ON pt.id = p.typeid
                      WHERE p.uid=%u
                      ORDER BY p.createdOn DESC",
                     $uid);

    $conn = \DB\getConnection();
    $result = $conn->query($query);

    return $result;
}

/**
 * Gets a count of the number of payments to increase upload limit
 * @param int uid
 * @return int
 */
function getCountByUser($uid) {
    $count = 0;

    // Run the query and get the user details
    $query = sprintf("SELECT COUNT(*) AS PaymentCount
                      FROM Payments
                      WHERE uid=%u
                        AND typeid = (
                            SELECT id
                            FROM PaymentTypes
                            WHERE code = 'il'
                        )",
                     $uid);

    $conn = \DB\getConnection();
    $result = $conn->query($query);
    $row = mysqli_fetch_assoc($result);
    $count = $row['PaymentCount'];

    return $count;
}

/**
 * Gets a count of the number of payments for premium photos
 * @param int uid
 * @return int
 */
function getPPCountByUser($uid) {
    $count = 0;

    // Run the query and get the user details
    $query = sprintf("SELECT COUNT(*) AS PaymentCount
                      FROM Payments
                      WHERE uid=%u
                        AND typeid = (
                            SELECT id
                            FROM PaymentTypes
                            WHERE code = 'pp'
                        )",
                     $uid);

    $conn = \DB\getConnection();
    $result = $conn->query($query);
    $row = mysqli_fetch_assoc($result);
    $count = $row['PaymentCount'];

    return $count;
}
