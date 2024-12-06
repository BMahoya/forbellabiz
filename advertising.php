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
    $company_name = $_POST['company-name'];
    $contact_name = $_POST['contact-name'];
    $contact_email = $_POST['contact-email'];
    $ad_description = $_POST['ad-description'];
    $ad_duration = $_POST['ad-duration'];
    $card_number = $_POST['card-number'];
    $expiry_date = $_POST['expiry-date'];
    $cvv = $_POST['cvv'];
    $submission_date = date('Y-m-d H:i:s'); // Capture the current date and time

    // Handle the file upload
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Sanitize file name
    $file_name = basename($_FILES["ad-file"]["name"]);
    $file_name = preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $file_name);
    $target_file = $target_dir . $file_name;
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $allowedTypes = array("pdf", "jpg", "jpeg", "png");
    if (!in_array($fileType, $allowedTypes)) {
        $response['message'] = "Sorry, only PDF, JPG, JPEG, and PNG files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        $response['message'] = "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["ad-file"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO advertising_requests (company_name, contact_name, contact_email, ad_description, ad_duration, card_number, expiry_date, cvv, ad_file, submission_date)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssss", $company_name, $contact_name, $contact_email, $ad_description, $ad_duration, $card_number, $expiry_date, $cvv, $target_file, $submission_date);

            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = "Advertising request submitted successfully";
            } else {
                $response['message'] = "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $response['message'] = "Sorry, there was an error uploading your file.";
        }
    }
}

$conn->close();

echo json_encode($response);
?>
