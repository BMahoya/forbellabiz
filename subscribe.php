<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = ""; // Add your MySQL password if any
$dbname = "forbella_journal";

$conn = new mysqli($servername, $username, $password, $dbname);

$response = array('status' => 'error', 'message' => 'An error occurred.');

if ($conn->connect_error) {
    $response['message'] = "Connection failed: " . $conn->connect_error;
    echo json_encode($response);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $plan = $_POST['plan'];
    $subscription_date = date('Y-m-d H:i:s'); // Capture the current date and time

    $sql = "INSERT INTO subscriptions (email, plan, subscription_date) VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $email, $plan, $subscription_date);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = "Subscription successful";
    } else {
        $response['message'] = "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>
