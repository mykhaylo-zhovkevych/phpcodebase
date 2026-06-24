<?php

session_start();

// Constants for database connection
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'example');
define('DB_PASSWORD', 'secret');
define('DATABASE_NAME', 'exampledb');

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
        $this->createAdminIfMissing('mz', '=y3&E)VM:JzqX');
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

        if (mysqli_stmt_num_rows($stmt) > 0) {
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

$books = getAllItems($db);
$selectedTopic = $_GET['topic'] ?? '';
$filteredBooks = fillterBookByTopic($books, $selectedTopic);

if ($is_admin && $requestPath === '/edit-book') {
    $id = (int) ($_GET['id'] ?? 0);

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
    $resourceUrl = $formData['resource_url_optional'] ?? '';

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


            if (!is_dir($uploadDir)) { 
                    mkdir($uploadDir, 0755, true); 
                }

            switch (move_uploaded_file($imageTmpPath, $uploadFilePath)) {
                case true:
                    $rating = (float) $rating;
                    $stmt = mysqli_prepare($db, "INSERT INTO books (title, author, topic, address_optional, rating, published_date, book_type, image_url, resource_url_optional) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmt, 'ssssdssss', $title, $author, $topic, $addressOptional, $rating, $publishedDate, $bookType, $uploadFilePath, $resourceUrl);
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

    if ($id > 0) {
        $stmt = mysqli_prepare($db, "DELETE FROM books WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: /admin");
    exit;
}

function loginAdmin(mysqli $db, array $formData): string
{
    $username = $formData['username'] ?? '';
    $password = $formData['password'] ?? '';

    if ($admin = authenticateAdmin($db, $username, $password)) {
        $_SESSION['is_admin'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: /admin");
        exit;
    } else {
        return "Invalid username or password.";
    }
}

function getAllItems(mysqli $db): array
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
    $resourceUrl = $formData['resource_url_optional'] ?? '';

    switch (true) {
        case $id <= 0 || empty($title) || empty($author) || empty($topic) || $rating === '' || !is_numeric($rating) || empty($publishedDate) || empty($bookType):
            return "All fields are required.";

        default:
            $rating = (float) $rating;
            $stmt = mysqli_prepare($db, "UPDATE books SET title = ?, author = ?, topic = ?, address_optional = ?, rating = ?, published_date = ?, book_type = ?, resource_url_optional = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'ssssdsssi', $title, $author, $topic, $addressOptional, $rating, $publishedDate, $bookType, $resourceUrl, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            header("Location: /admin");
            exit;
    }
}

function fillterBookByTopic(array $books, string $topic): array
{
    if (empty($topic)) {
        return $books;
    }

    return array_filter($books, function ($book) use ($topic) {
        return $book['topic'] === $topic;
    });
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Course Library</title>
</head>
<body class="antialiased bg-[#b4b4b4]">
    <?php if ($is_admin && $requestPath == '/admin') : ?>
        <section data-page="/admin" class="min-h-screen bg-[#dddddd] py-8 antialiased">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-950">Admin Panel</h1>
                        <p class="mt-1 text-sm text-slate-600">Manage titles, authors, topics, and book details.</p>
                    </div>

                    <a href="/add-new-book" class="inline-flex items-center justify-center rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                        Add New Book
                    </a>
                </div>

                <div class="overflow-hidden rounded-lg border border-slate-300 bg-[#e8e8e8] shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-[#e8e8e8]">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Title</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Author</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Topic</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Address Optional</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Rating</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Published Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Book Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Resource URL</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-300 bg-[#e8e8e8]">
                            <?php foreach ($books as $book): ?>
                                <tr class="transition hover:bg-[#f3f3f3]">
                                    <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-slate-950"><?= htmlspecialchars($book['title']) ?></td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-700"><?= htmlspecialchars($book['author']) ?></td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-700"><?= htmlspecialchars($book['topic']) ?></td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-500"><?= htmlspecialchars($book['address_optional'] ?? '') ?></td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-700"><?= htmlspecialchars($book['rating']) ?> / 5</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-700"><?= htmlspecialchars($book['published_date'] ?? '') ?></td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-700"><?= htmlspecialchars($book['book_type'] ?? '') ?></td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold <?= !empty($book['resource_url_optional']) ? 'text-green-600' : 'text-red-600' ?>">
                                        <?= !empty($book['resource_url_optional']) ? 'Yes' : 'No' ?>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm">
                                        <a href="/edit-book?id=<?= htmlspecialchars($book['id']) ?>" class="font-semibold text-blue-600 transition hover:text-blue-800">Edit</a>
                                        <form method="post" action="/delete-book" class="ml-3 inline">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($book['id']) ?>">
                                            <button type="submit" class="font-semibold cursor-pointer text-red-600 transition hover:text-red-800" onclick="return confirm('Are you sure you want to delete this book?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    <?php elseif ($is_admin && $requestPath == '/add-new-book') : ?>
        <section data-page="/add-new-book" class="min-h-screen bg-[#dddddd] py-12 antialiased">
            <div class="container mx-auto p-8">
                <h1 class="text-2xl font-bold mb-6">Add New Book</h1>

                <?php if (!empty($create_book_error)) : ?>
                    <div class="mb-4 border border-red-300 bg-red-100 px-3 py-2 text-red-700">
                        <?= htmlspecialchars($create_book_error) ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="/add-new-book" enctype="multipart/form-data" class="rounded bg-[#e8e8e8] p-6">
                    <div class="mb-4">
                        <label for="title" class="block text-gray-700">Title</label>
                        <input type="text" id="title" name="title" required class="w-full border border-black rounded px-3 py-2">
                    </div>

                    <div class="mb-4">
                        <label for="author" class="block text-gray-700">Author</label>
                        <input type="text" id="author" name="author" required class="w-full border border-black rounded px-3 py-2">
                    </div>

                    <div class="mb-4">
                        <label for="topic" class="block text-gray-700">Topic</label>
                        <input type="text" id="topic" name="topic" required class="w-full border border-black rounded px-3 py-2">
                    </div>

                    <div class="mb-4">
                        <label for="address_optional" class="block text-gray-700">Address Optional</label>
                        <input type="text" id="address_optional" name="address_optional" class="w-full border border-black rounded px-3 py-2">
                    </div>

                    <div class="mb-4">
                        <label for="rating" class="block text-gray-700">Rating</label>
                        <input type="number" id="rating" name="rating" min="0" max="5" step="0.1" required class="w-full border border-black rounded px-3 py-2">
                    </div>

                    <div class="mb-4">
                        <label for="published_date" class="block text-gray-700">Published Date</label>
                        <input type="text" id="published_date" name="published_date" required class="w-full border border-black rounded px-3 py-2">
                    </div>

                    <div class="mb-4">
                        <label for="book_type" class="block text-gray-700">Type</label>
                        <input type="text" id="book_type" name="book_type" required class="w-full border border-black rounded px-3 py-2">
                    </div>

                    <div class="mb-4">
                        <label for="image" class="block text-gray-700">Cover Image</label>
                        <input type="file" id="image" name="image" accept="image/*" required class="w-full border border-black rounded px-3 py-2">
                    </div>

                    <div class="mb-4">
                        <label for="resource_url_optional" class="block text-gray-700">Resource URL Optional</label>
                        <input type="url" id="resource_url_optional" name="resource_url_optional" class="w-full border border-black rounded px-3 py-2">
                    </div>

                    <button type="submit" name="create_book" value="1" class="inline-flex items-center justify-center rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">Add Book</button>
                </form>
            </div>
        </section>
        <?php elseif (!$is_admin && $requestPath == '/login') : ?>
            <section data-page="/login" class="min-h-screen bg-[#dddddd] py-12 antialiased">
                <div class="container mx-auto px-4">
                    <h1 class="text-2xl font-bold mb-6">Admin Login</h1>

                    <?php if (!empty($loginError)) : ?>
                        <div class="mb-4 border border-red-300 bg-red-100 px-3 py-2 text-red-700">
                            <?= htmlspecialchars($loginError) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="/login" class="rounded bg-[#e8e8e8] p-6">
                        <div class="mb-4">
                            <label for="username" class="block text-gray-700">Username</label>
                            <input type="text" id="username" name="username" required class="w-full border border-black rounded px-3 py-2">
                        </div>

                        <div class="mb-4">
                            <label for="password" class="block text-gray-700">Password</label>
                            <input type="password" id="password" name="password" required class="w-full border border-black rounded px-3 py-2">
                        </div>

                        <button type="submit" name="login" value="1" class="inline-flex items-center justify-center rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">Login</button>
                    </form>
                </div>
            </section>
            <?php elseif ($is_admin && $requestPath === '/edit-book'): ?>
                <section data-page="/edit-book" class="min-h-screen bg-[#dddddd] py-12 antialiased">
                    <div class="container mx-auto px-4">
                        <h1 class="text-2xl font-bold mb-6">Edit Book</h1>

                        <?php if (!empty($create_book_error)) : ?>
                            <div class="mb-4 border border-red-300 bg-red-100 px-3 py-2 text-red-700">
                                <?= htmlspecialchars($create_book_error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="/edit-book" enctype="multipart/form-data" class="rounded bg-[#e8e8e8] p-6">
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

                            <div class="mb-4">
                                <label for="resource_url_optional" class="block text-gray-700">Resource URL Optional</label>
                                <input type="url" id="resource_url_optional" name="resource_url_optional" value="<?= htmlspecialchars($book_detail['resource_url_optional'] ?? '') ?>" class="w-full border border-gray-300 rounded px-3 py-2">
                            </div>

                            <button type="submit" name="edit_book" value="1" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update Book</button>
                        </form>
                    </div>
                </section>
        <?php elseif ($requestPath === '/book-details'): ?>
            <section data-page="/book-details" class="min-h-screen bg-[#e8e8e8] py-12 antialiased">
                <div class="container mx-auto px-4">
                    <?php
                        $id = (int) ($_GET['id'] ?? 0);
                        $book_detail = findBookById($db, $id);

                        if (!$book_detail) {
                            echo "<p>Book not found.</p>";
                        } else {
                        ?>
                        <h1 class="text-2xl font-bold mb-6"><?= htmlspecialchars($book_detail['title']) ?></h1>
                        <p><strong>Author:</strong> <?= htmlspecialchars($book_detail['author']) ?></p>
                        <p><strong>Topic:</strong> <?= htmlspecialchars($book_detail['topic']) ?></p>
                        <p><strong>Address Optional:</strong> <?= htmlspecialchars($book_detail['address_optional'] ?? '') ?></p>
                        <p><strong>Rating:</strong> <?= htmlspecialchars($book_detail['rating']) ?></p>
                        <p><strong>Published Date:</strong> <?= htmlspecialchars($book_detail['published_date'] ?? '') ?></p>
                        <p><strong>Book Type:</strong> <?= htmlspecialchars($book_detail['book_type'] ?? '') ?></p>
                        <?php if (!empty($book_detail['image_url'])): ?>
                            <img src="<?= htmlspecialchars($book_detail['image_url']) ?>" alt="<?= htmlspecialchars($book_detail['title']) ?>" class="mt-4 w-full max-w-md">
                        <?php endif; ?>
                        <?php if (!empty($book_detail['resource_url_optional'])): ?>
                            <p class="mt-4"><a href="<?= htmlspecialchars($book_detail['resource_url_optional']) ?>" target="_blank" class="text-blue-600 hover:underline">Access Resource</a></p>
                        <?php else: ?>
                            <p class="mt-4 text-red-600">No resource available for this book.</p>
                        <?php endif; ?>
                    <button class="mt-4 inline-flex items-center justify-center rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800" onclick="window.location.href='/'">Go back to the list</button>
                    <?php } ?>
                </div>
            </section>
        <?php else: ?>
        <main>
            <section data-page="/" class="min-h-screen bg-[#f3f3f3] antialiased">
                <div class="container mx-auto p-8">
                <section class="mb-8">
                    <h1 class="text-2xl font-bold mb-4">Introduction to philosophy</h1>
                    <p>Welcome to the "Courses Library". Browse through collection of free courses.</p>
                </section>
                <section class="mb-8">
                    <div class="container mx-auto px-2">
                        <div class="-mx-2 rounded bg-[#e8e8e8] p-4 shadow md:w-1/2">
                            <?php $topics = array_unique(array_map(fn($book) => $book['topic'], $books)); ?>
                            <details class="group relative">
                                <summary class="flex w-full cursor-pointer list-none items-center justify-between rounded-md bg-[#f3f3f3] px-4 py-2 text-sm font-semibold text-black">
                                    <?= htmlspecialchars($selectedTopic ?: 'All Topics') ?>
                                    <span class="transition group-open:rotate-180">▼</span>
                                </summary>
                                <div class="absolute z-10 mt-1 w-full overflow-hidden rounded-md border border-slate-300 bg-white shadow-sm">
                                    <a href="/" class="block bg-white px-4 py-2 text-sm text-slate-950 transition hover:bg-[#e8e8e8]">
                                        All Topics
                                    </a>
                                    <?php foreach ($topics as $topic): ?>
                                        <a href="/?topic=<?= urlencode($topic) ?>" class="block bg-white px-4 p-2 text-sm text-slate-950 transition hover:bg-[#e8e8e8]">
                                            <?= htmlspecialchars($topic) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </details>
                        </div>
                    </div>
                </section>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($filteredBooks as $book): ?>
                    <div class="bg-[#e8e8e8] p-4 rounded shadow">
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
                                <button class="mt-4 inline-flex items-center justify-center rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800" onclick="window.location.href='/book-details?id=<?= htmlspecialchars($book['id']) ?>'">View Details</button>
                            </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>
    <?php endif; ?>
</body>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const currentPath = window.location.pathname;

    document.querySelectorAll('[data-page]').forEach((section) => {
            const pagePath = section.dataset.page;
            if (pagePath === currentPath) {
                section.classList.add('active');
            } else {
                section.classList.remove('active');
            }
         });
    }); 
</script>
</html>
