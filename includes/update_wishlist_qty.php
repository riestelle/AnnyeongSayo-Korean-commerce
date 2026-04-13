<?php
// Include database connection
include('db_connection.php');

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get variables from POST
    $wishlist_id = $_POST['wishlist_id'];
    $delta = $_POST['delta'];

    // Prepare the SQL statement to update the quantity
    $stmt = $conn->prepare('UPDATE wishlist SET quantity = quantity + ? WHERE id = ?');
    $stmt->bind_param('ii', $delta, $wishlist_id);
    $stmt->execute();

    // Check if the update was successful
    if ($stmt->affected_rows > 0) {
        // Retrieve the updated quantity
        $stmt = $conn->prepare('SELECT quantity FROM wishlist WHERE id = ?');
        $stmt->bind_param('i', $wishlist_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $updated_quantity = $row['quantity'];

        // Retrieve total quantity and price (assuming total_price is in the wishlist table)
        $stmt = $conn->prepare('SELECT SUM(quantity) as total_qty, SUM(price) as total_price FROM wishlist');
        $stmt->execute();
        $result = $stmt->get_result();
        $totals = $result->fetch_assoc();

        // Create response data
        $response = [
            'updated_quantity' => $updated_quantity,
            'total_quantity' => $totals['total_qty'],
            'total_price' => $totals['total_price']
        ];

        // Send response in JSON format
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        // Handle error
        http_response_code(500);
        echo json_encode(['message' => 'Failed to update quantity.']);
    }
} else {
    // Invalid request method
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
}
?>