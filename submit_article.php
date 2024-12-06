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
    $author_name = $_POST['author-name'];
    $email = $_POST['email'];
    $article_title = $_POST['article-title'];
    $abstract = $_POST['abstract'];
    $submission_date = date('Y-m-d H:i:s'); // Capture the current date and time

    // Handle the file upload
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Sanitize file name
    $file_name = basename($_FILES["article-file"]["name"]);
    $file_name = preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $file_name);
    $target_file = $target_dir . $file_name;
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if ($fileType != "pdf") {
        $response['message'] = "Sorry, only PDF files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        $response['message'] = "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["article-file"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO submissions (author_name, email, article_title, abstract, article_file, submission_date)
                    VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $author_name, $email, $article_title, $abstract, $target_file, $submission_date);

            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = "New record created successfully";
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
