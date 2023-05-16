<?php
require_once "config.php";

// Set the number of items per page
$itemsPerPage = 10;

// Get the current page number from the URL query string
$page = isset($_GET['page']) ? $_GET['page'] : 1;

// Retrieve search parameters from the URL query string
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$age = isset($_GET['age']) ? $_GET['age'] : '';
$bookTypes = isset($_GET['book_types']) ? $_GET['book_types'] : [];

// Prepare the WHERE clause for the search query
$whereClause = '';
$params = [];

if (!empty($keyword)) {
    $whereClause .= "title LIKE :keyword OR publisher LIKE :keyword";
    $params['keyword'] = '%' . $keyword . '%';
}

if (!empty($age)) {
    $whereClause .= (!empty($whereClause) ? ' AND ' : '') . "age = :age";
    $params['age'] = $age;
}

if (!empty($bookTypes)) {
    $bookTypeParams = [];
    foreach ($bookTypes as $index => $bookType) {
        $bookTypeParams[] = ':book_type' . $index;
        $params['book_type' . $index] = $bookType;
    }

    $whereClause .= (!empty($whereClause) ? ' AND ' : '') . "book_type IN (" . implode(',', $bookTypeParams) . ")";
}

// Calculate the offset for pagination
$offset = ($page - 1) * $itemsPerPage;

// Build the search query with pagination
$query = "SELECT * FROM books";

if (!empty($whereClause)) {
    $query .= " WHERE " . $whereClause;
}

$query .= " ORDER BY id LIMIT :offset, :itemsPerPage";

// Retrieve books from the database based on the search parameters
$stmt = $pdo->prepare($query);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);

foreach ($params as $param => $value) {
    $stmt->bindValue($param, $value);
}

$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create a JSON response
$response = [
    'total_pages' => ceil(count($books) / $itemsPerPage),
    'books' => $books
];

// Set the Content-Type header to JSON
header('Content-Type: application/json');

// Output the JSON response
echo json_encode($response);
?>
