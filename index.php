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

// Calculate total count for pagination
$totalCountQuery = "SELECT COUNT(*) FROM books";

if (!empty($whereClause)) {
    $totalCountQuery .= " WHERE " . $whereClause;
}

$totalCountStmt = $pdo->prepare($totalCountQuery);

foreach ($params as $param => $value) {
    $totalCountStmt->bindValue($param, $value);
}

$totalCountStmt->execute();
$totalCount = $totalCountStmt->fetchColumn();
$totalPages = ceil($totalCount / $itemsPerPage);

// Display book list


?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Management System - Book List</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid black;
            padding: 8px;
        }
    </style>
</head>
<body>
    <h1>Book Management System - Book List</h1>

    <form action="index.php" method="GET">
        <label for="keyword">Keyword:</label>
        <input type="text" name="keyword" id="keyword" value="<?php echo $keyword; ?>">

        <label for="age">Age:</label>
        <select name="age" id="age">
            <option value="child">Child</option>
            <option value="teen">Teen</option>
            <option value="adult
        <option value="adult">Adult</option>
        </select><br><br>
    <label for="book_types">Book Types:</label><br>
    <input type="checkbox" name="book_types[]" value="fiction" <?php if (in_array('fiction', $bookTypes)) echo 'checked'; ?>> Fiction<br>
    <input type="checkbox" name="book_types[]" value="non-fiction" <?php if (in_array('non-fiction', $bookTypes)) echo 'checked'; ?>> Non-Fiction<br>
    <input type="checkbox" name="book_types[]" value="biography" <?php if (in_array('biography', $bookTypes)) echo 'checked'; ?>> Biography<br>

    <input type="submit" value="Search">
</form>

<br>

<table>
    <tr>
        <th>Serial Number</th>
        <th>Title of Book</th>
        <th>Name of Publisher</th>
        <th>Date of Application</th>
    </tr>
    <?php foreach ($books as $book): ?>
    <tr>
        <td><?php echo $book['id']; ?></td>
        <td><?php echo $book['title']; ?></td>
        <td><?php echo $book['publisher']; ?></td>
        <td><?php echo $book['application_date']; ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<br>

<!-- Pagination links -->
<?php if ($totalPages > 1): ?>
<div>
    <?php if ($page > 1): ?>
        <a href="index.php?page=<?php echo $page - 1; ?>&keyword=<?php echo $keyword; ?>&age=<?php echo $age; ?>&book_types=<?php echo implode(',', $bookTypes); ?>">Previous</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($page == $i): ?>
            <span><?php echo $i; ?></span>
        <?php else: ?>
            <a href="index.php?page=<?php echo $i; ?>&keyword=<?php echo $keyword; ?>&age=<?php echo $age; ?>&book_types=<?php echo implode(',', $bookTypes); ?>"><?php echo $i; ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="index.php?page=<?php echo $page + 1; ?>&keyword=<?php echo $keyword; ?>&age=<?php echo $age; ?>&book_types=<?php echo implode(',', $bookTypes); ?>">Next</a>
    <?php endif; ?>
</div>
<?php endif; ?>
</body>
</html>