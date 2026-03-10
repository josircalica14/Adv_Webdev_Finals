<?php
/**
 * Contact Form Handler
 * Processes contact form submissions with validation and email sending
 */

// Set JSON response header
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
    exit;
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Initialize response array
$response = [
    "success" => false,
    "message" => ""
];

try {
    // Get and sanitize form data
    $name = isset($_POST['contact-name']) ? sanitizeInput($_POST['contact-name']) : '';
    $email = isset($_POST['contact-email']) ? sanitizeInput($_POST['contact-email']) : '';
    $message = isset($_POST['contact-project']) ? sanitizeInput($_POST['contact-project']) : '';
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!validateEmail($email)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    // If validation errors exist, return them
    if (!empty($errors)) {
        $response["message"] = implode(", ", $errors);
        echo json_encode($response);
        exit;
    }
    
    // Prepare email
    $to = "your-email@example.com"; // TODO: Replace with actual email
    $subject = "Portfolio Contact Form: Message from " . $name;
    
    // Email body
    $emailBody = "You have received a new message from your portfolio contact form.\n\n";
    $emailBody .= "Name: " . $name . "\n";
    $emailBody .= "Email: " . $email . "\n\n";
    $emailBody .= "Message:\n" . $message . "\n";
    
    // Email headers
    $headers = "From: " . $email . "\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Send email
    if (mail($to, $subject, $emailBody, $headers)) {
        $response["success"] = true;
        $response["message"] = "Thank you for your message! I'll get back to you soon.";
    } else {
        $response["message"] = "Failed to send message. Please try again later.";
    }
    
} catch (Exception $e) {
    $response["message"] = "An error occurred. Please try again later.";
    error_log("Contact form error: " . $e->getMessage());
}

// Return JSON response
echo json_encode($response);
?>
