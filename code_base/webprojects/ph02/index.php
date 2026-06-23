<?php

session_start();

// Constants for database connection
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'coffee');
define('DB_PASSWORD', 'secret');
define('DATABASE_NAME', 'coffeedb');

class AdminInitializer
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function initialize(): void
    {
        $this->createAdminTableIfMissing();
        $this->createAdminIfMissing('mz', '');
        $this->createAdminIfMissing('ad', '');
    }

    private function createAdminTableIfMissing(): void
    {
        $createTableQuery = "
            CREATE TABLE IF NOT EXISTS admins (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL
            )
        ";

        mysqli_query($this->db, $createTableQuery);
    }

    private function createAdminIfMissing(string $username, string $password): void
    {
        $stmt = mysqli_prepare($this->db, "SELECT id FROM admins WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        
        mysqli_stmt_store_result($stmt);

        switch (mysqli_stmt_num_rows($stmt) > 0) {
            case true:
                mysqli_stmt_close($stmt);
                return;
        }

        mysqli_stmt_close($stmt);

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($this->db, "INSERT INTO admins (username, password) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, 'ss', $username, $passwordHash);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($is_admin && $currentPath === '/login') {
    redirect('/admin');
}

$db = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DATABASE_NAME);
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}
$adminInitializer = new AdminInitializer($db);
$adminInitializer->initialize();

$create_book_error = '';
$loginError = '';
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_book'])) {
    $create_book_error = createBook($db, $_POST, $_FILES);
}

function redirect(string $path) : void
{
    header("Location: {$path}");
    exit;
}

if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && $requestPath === '/delete-book') {
    deleteBook($db, $_POST);
}

if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && $requestPath === '/edit-book') {
    $create_book_error = updateBook($db, $_POST);
}

if (!$is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && $requestPath === '/login') {
    $loginError = loginAdmin($db, $_POST);
}

$books = getBooks($db);

if ($is_admin && $requestPath === '/edit-book') {
    $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

    if ($id <= 0) {
        header('Location: /admin');
        exit;
    }

    $book_detail = findBookById($db, $id);

    if (!$book_detail) {
        header('Location: /admin');
        exit;
    }
}

function createBook(mysqli $db, array $formData, array $files): string
{
    $title = $formData['title'] ?? '';
    $author = $formData['author'] ?? '';
    $topic = $formData['topic'] ?? '';
    $addressOptional = $formData['address_optional'] ?? '';
    $rating = $formData['rating'] ?? '';
    $publishedDate = $formData['published_date'] ?? '';
    $bookType = $formData['book_type'] ?? '';

    switch (true) {
        case empty($title) || empty($author) || empty($topic) || $rating === '' || !is_numeric($rating) || empty($publishedDate) || empty($bookType):
            return "All fields are required.";

        case !isset($files['image']) || $files['image']['error'] !== UPLOAD_ERR_OK:
            return "No image uploaded or there was an upload error.";

        default:
            $imageTmpPath = $files['image']['tmp_name'];
            $imageName = basename($files['image']['name']);
            $uploadDir = 'uploads/';
            $uploadFilePath = $uploadDir . $imageName;

            switch (is_dir($uploadDir)) {
                case false:
                    mkdir($uploadDir, 0755, true);
                    break;
            }

            switch (move_uploaded_file($imageTmpPath, $uploadFilePath)) {
                case true:
                    $rating = (float) $rating;
                    $stmt = mysqli_prepare($db, "INSERT INTO books (title, author, topic, address_optional, rating, published_date, book_type, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmt, 'ssssdsss', $title, $author, $topic, $addressOptional, $rating, $publishedDate, $bookType, $uploadFilePath);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);

                    header("Location: /admin");
                    exit;

                default:
                    return "Error uploading the image.";
            }
    }
}

function deleteBook(mysqli $db, array $formData): void
{
    $id = (int) ($formData['id'] ?? 0);

    switch ($id > 0) {
        case true:
            $stmt = mysqli_prepare($db, "DELETE FROM books WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            break;
    }

    header("Location: /admin");
    exit;
}

function loginAdmin(mysqli $db, array $formData): string
{
    $username = $formData['username'] ?? '';
    $password = $formData['password'] ?? '';

    if($admin = authenticateAdmin($db, $username, $password)) {
        $_SESSION['is_admin'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: /admin");
        exit;
    } else {
        return "Invalid username or password.";
    }
}

function getBooks(mysqli $db): array
{
    return mysqli_fetch_all(mysqli_query($db, "SELECT * FROM books"), MYSQLI_ASSOC);
}

function findBookById(mysqli $db, int $id): array|false|null
{
    $stmt = mysqli_prepare($db, "SELECT * FROM books WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $book = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    return $book;
}

function authenticateAdmin(mysqli $db, string $username, string $password): array|false
{
    $stmt = mysqli_prepare($db, "SELECT id, username, password FROM admins WHERE username = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $admin = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($admin && password_verify($password, $admin['password'])) {
        return $admin;
    }

    return false;
}

function updateBook(mysqli $db, array $formData): string
{
    $id = (int) ($formData['id'] ?? 0);
    $title = $formData['title'] ?? '';
    $author = $formData['author'] ?? '';
    $topic = $formData['topic'] ?? '';
    $addressOptional = $formData['address_optional'] ?? '';
    $rating = $formData['rating'] ?? '';
    $publishedDate = $formData['published_date'] ?? '';
    $bookType = $formData['book_type'] ?? '';

    switch (true) {
        case $id <= 0 || empty($title) || empty($author) || empty($topic) || $rating === '' || !is_numeric($rating) || empty($publishedDate) || empty($bookType):
            return "All fields are required.";

        default:
            $rating = (float) $rating;
            $stmt = mysqli_prepare($db, "UPDATE books SET title = ?, author = ?, topic = ?, address_optional = ?, rating = ?, published_date = ?, book_type = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'ssssdssi', $title, $author, $topic, $addressOptional, $rating, $publishedDate, $bookType, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            header("Location: /admin");
            exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Book Library</title>
</head>
<body class="abtialiased bg-gray-100">
    <?php if ($is_admin && $requestPath == '/admin') : ?>
        <section>
            <div>
                <h1>Admin Page</h1>
                <p>Welcome to the admin page. Here you can manage the books.</p>

                <a href="/add-new-book" class="text-blue-500 hover:underline">Add New Book</a>

                <table class="min-w-full bg-white border border-gray-300">
                    <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Topic</th>
                        <th>Address Optional</th>
                        <th>Rating</th>
                        <th>Published Date</th>
                        <th>Book Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td><?= htmlspecialchars($book['topic']) ?></td>
                            <td><?= htmlspecialchars($book['address_optional'] ?? '') ?></td>
                            <td><?= htmlspecialchars($book['rating']) ?></td>
                            <td><?= htmlspecialchars($book['published_date'] ?? '') ?></td>
                            <td><?= htmlspecialchars($book['book_type'] ?? '') ?></td>
                            <td>
                                <a href="/edit-book?id=<?= htmlspecialchars($book['id']) ?>" class="text-blue-500 hover:underline">Edit</a>
                                <form method="post" action="/delete-book" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($book['id']) ?>">
                                    <button type="submit" class="text-red-500 hover:underline" onclick="return confirm('Are you sure you want to delete this book?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php elseif ($is_admin && $requestPath == '/add-new-book') : ?>
        <section class="py-12">
            <div class="container mx-auto px-4">
                <h1 class="text-2xl font-bold mb-6">Add New Book</h1>

                <?php if (!empty($create_book_error)) : ?>
                    <div class="mb-4 border border-red-300 bg-red-100 px-3 py-2 text-red-700">
                        <?= htmlspecialchars($create_book_error) ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="/add-new-book" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="title" class="block text-gray-700">Title</label>
                        <input type="text" id="title" name="title" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>

                    <div class="mb-4">
                        <label for="author" class="block text-gray-700">Author</label>
                        <input type="text" id="author" name="author" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>

                    <div class="mb-4">
                        <label for="topic" class="block text-gray-700">Topic</label>
                        <input type="text" id="topic" name="topic" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>

                    <div class="mb-4">
                        <label for="address_optional" class="block text-gray-700">Address Optional</label>
                        <input type="text" id="address_optional" name="address_optional" class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>

                    <div class="mb-4">
                        <label for="rating" class="block text-gray-700">Rating</label>
                        <input type="number" id="rating" name="rating" min="0" max="5" step="0.1" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>

                    <div class="mb-4">
                        <label for="published_date" class="block text-gray-700">Published Date</label>
                        <input type="text" id="published_date" name="published_date" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>

                    <div class="mb-4">
                        <label for="book_type" class="block text-gray-700">Book Type</label>
                        <input type="text" id="book_type" name="book_type" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>

                    <div class="mb-4">
                        <label for="image" class="block text-gray-700">Cover Image</label>
                        <input type="file" id="image" name="image" accept="image/*" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>

                    <button type="submit" name="create_book" value="1" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add Book</button>
                </form>
            </div>
        </section>
        <?php elseif (!$is_admin && $requestPath == '/login') : ?>
            <section class="py-12">
                <div class="container mx-auto px-4">
                    <h1 class="text-2xl font-bold mb-6">Admin Login</h1>

                    <?php if (!empty($loginError)) : ?>
                        <div class="mb-4 border border-red-300 bg-red-100 px-3 py-2 text-red-700">
                            <?= htmlspecialchars($loginError) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="/login">
                        <div class="mb-4">
                            <label for="username" class="block text-gray-700">Username</label>
                            <input type="text" id="username" name="username" required class="w-full border border-gray-300 rounded px-3 py-2">
                        </div>

                        <div class="mb-4">
                            <label for="password" class="block text-gray-700">Password</label>
                            <input type="password" id="password" name="password" required class="w-full border border-gray-300 rounded px-3 py-2">
                        </div>

                        <button type="submit" name="login" value="1" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Login</button>
                    </form>
                </div>
            </section>
            <?php elseif ($is_admin && $requestPath === '/edit-book'): ?>
                <section class="py-12">
                    <div class="container mx-auto px-4">
                        <h1 class="text-2xl font-bold mb-6">Edit Book</h1>

                        <?php if (!empty($create_book_error)) : ?>
                            <div class="mb-4 border border-red-300 bg-red-100 px-3 py-2 text-red-700">
                                <?= htmlspecialchars($create_book_error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="/edit-book" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($book_detail['id']) ?>">

                            <div class="mb-4">
                                <label for="title" class="block text-gray-700">Title</label>
                                <input type="text" id="title" name="title" value="<?= htmlspecialchars($book_detail['title'] ?? '') ?>" required class="w-full border border-gray-300 rounded px-3 py-2">
                            </div>

                            <div class="mb-4">
                                <label for="author" class="block text-gray-700">Author</label>
                                <input type="text" id="author" name="author" value="<?= htmlspecialchars($book_detail['author'] ?? '') ?>" required class="w-full border border-gray-300 rounded px-3 py-2">
                            </div>

                            <div class="mb-4">
                                <label for="topic" class="block text-gray-700">Topic</label>
                                <input type="text" id="topic" name="topic" value="<?= htmlspecialchars($book_detail['topic'] ?? '') ?>" required class="w-full border border-gray-300 rounded px-3 py-2">
                            </div>

                            <div class="mb-4">
                                <label for="address_optional" class="block text-gray-700">Address Optional</label>
                                <input type="text" id="address_optional" name="address_optional" value="<?= htmlspecialchars($book_detail['address_optional'] ?? '') ?>" class="w-full border border-gray-300 rounded px-3 py-2">
                            </div>

                            <div class="mb-4">
                                <label for="rating" class="block text-gray-700">Rating</label>
                                <input type="number" id="rating" name="rating" min="0" max="5" step="0.1" value="<?= htmlspecialchars($book_detail['rating'] ?? '') ?>" required class="w-full border border-gray-300 rounded px-3 py-2">
                            </div>

                            <div class="mb-4">
                                <label for="published_date" class="block text-gray-700">Published Date</label>
                                <input type="text" id="published_date" name="published_date" value="<?= htmlspecialchars($book_detail['published_date'] ?? '') ?>" required class="w-full border border-gray-300 rounded px-3 py-2">
                            </div>

                            <div class="mb-4">
                                <label for="book_type" class="block text-gray-700">Book Type</label>
                                <input type="text" id="book_type" name="book_type" value="<?= htmlspecialchars($book_detail['book_type'] ?? '') ?>" required class="w-full border border-gray-300 rounded px-3 py-2">
                            </div>

                            <button type="submit" name="edit_book" value="1" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update Book</button>
                        </form>
                    </div>
                </section>
        <?php else: ?>
        <main>
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($books as $book): ?>
                    <div class="bg-white p-4 rounded shadow">
                        <img src="<?= htmlspecialchars($book['image_url']) ?>" alt="<?= htmlspecialchars($book['title']) ?>" class="w-full h-48 object-cover rounded mb-4">
                            <div class="p-6">
                                <h2 class="text-xl font-bold"><?= htmlspecialchars($book['title']) ?></h2>
                                <p class="text-gray-600"><?= htmlspecialchars($book['author']) ?></p>
                                <p class="text-gray-600"><?= htmlspecialchars($book['topic']) ?></p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-800 font-semibold"><?= htmlspecialchars($book['rating']) ?> / 5</span>
                                        <span class="text-gray-600"><?= htmlspecialchars($book['published_date']) ?></span>
                                    </div>
                                    <p class="text-gray-600"><?= htmlspecialchars($book['book_type']) ?></p>
                                    <?php if (!empty($book['address_optional'])): ?>
                                        <p class="text-gray-600"><?= htmlspecialchars($book['address_optional']) ?></p>
                                    <?php endif; ?>
                            </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </main>
    <?php endif; ?>


</body>
</html>
