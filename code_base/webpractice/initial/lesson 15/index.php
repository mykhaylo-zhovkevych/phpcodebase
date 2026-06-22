<?php

require_once __DIR__ . '/SignUpAccount.php';
require_once __DIR__ . '/SignUpData.php';
require_once __DIR__ . '/SignUpHandler.php';

$result = null;
$error = null;

$messageData = [
    'messages' => [],
    'totalItems' => 0,
];

// This parameter can accept any type of value
// ENT_SUBSTITUTE replace invalid char characters with a Unicode replacement character
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function renderMessages(array $messageData): void
{
    ?>
    <p>Total messages: <?= e($messageData['totalItems']) ?></p>

    <?php if ($messageData['messages'] === []): ?>
        <p>No emails received yet.</p>
    <?php else: ?>
        <?php foreach ($messageData['messages'] as $message): ?>
            <article>
                <h3>Subject</h3>
                <?= e($message['subject'] ?? '(No subject)') ?>

                <p><strong>From:</strong> 
                <?= e($message['from']['name'] ?? '') ?>

                    <?= e($message['from']['address'] ?? '') ?>;
                </p>

                <p>
                    <strong>Has attachments:</strong>
                    <?= !empty($message['hasAttachments']) ? 'yes' : 'no' ?>
                </p>

                <p>
                    <strong>Created at:</strong>
                    <?= e($message['createdAt'] ?? 'No date available') ?>
                </p>
            </article>

            <hr>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action']) === 'fetch_messages') {
    try {
        $accountService = new SignUpAccount();
        $messageData = $accountService->fetchMessages($_POST['token'] ?? '');

        renderMessages($messageData);
    } catch (Throwable $exception) {
        ?>
            <p>Error: <?= e($exception->getMessage()) ?></p>
        <?php
    }

    exit;
}

// is an array containing information such as headers, paths, and script locations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action']) === 'signup') {
    $accountService = null;

    try {
        $accountService = new SignUpAccount();
        $handler = new SignUpHandler($accountService);

        $result = $handler->handle($_POST);
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$usernameValue = $_POST['username'] ?? 'Not Provided';
$displayNameValue = $_POST['display_name'] ?? 'Not Provided';
$descriptionValue = $_POST['description'] ?? 'Not Provided';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <title>Mail.tm Signup</title>
</head>
<body>

    <?php if ($result === null): ?>
        <h1>Create temporary mail.tm account</h1>

        <form method="post" action="">
            <input type="hidden" name="action" value="signup">

            <div>
                <label for="username">Username</label>
                <br>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?= e($usernameValue) ?>"
                    required
                >
            </div>

            <br>

            <div>
                <label for="password">Password</label>
                <br>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                >
            </div>

            <br>

            <div>
                <label for="display_name">Display name</label>
                <br>
                <input
                    type="text"
                    id="display_name"
                    name="display_name"
                    value="<?= e($displayNameValue) ?>"
                >
            </div>

            <br>

            <div>
                <label for="description">Description</label>
                <br>
                <textarea
                    id="description"
                    name="description"
                ><?= e($descriptionValue) ?></textarea>
            </div>

            <br>

            <button type="submit">Create account</button>
        </form>
    <?php endif; ?>

    <hr>

    <?php if ($error !== null): ?>
        <h2>Error</h2>
        <p><?= e($error) ?></p>
    <?php endif; ?>

    <?php if ($result !== null): ?>
        <h2>Created account</h2>

        <pre>
            <?php
            printf("Username as string: %s\n", e($result->username));
            printf("Address as string: %s\n", e($result->address));
            printf("Display name as string: %s\n", e($result->displayName));
            printf("Description as string: %s\n", e($result->description));
            printf("Token exists: %s\n", $result->token !== null ? 'yes' : 'no');

            printf("Account ID as string: %s\n", e($result->accountId));
            printf("Used storage as integer: %d\n", $result->used);
            printf("Quota as integer: %d\n", $result->quota);

            $usedInMb = ($result->used / 1024 / 1024);
            $quotaInMb = ($result->quota / 1024 / 1024);

            printf("Used storage as float: %f MB\n", $usedInMb);
            printf("Quota as float with 2 decimals: %.2f MB\n", $quotaInMb);
            ?>
        </pre>
    <?php endif; ?>

<?php if ($result !== null): ?>
    <h2>Inbox</h2>

    <p>
        Inbox refreshes every 5 seconds.
    </p>

    <form method="get">
        <button type="submit" name="reset" value="1">
            Create another account
        </button>
    </form>

    <div id="messages-section">
        <?php renderMessages($messageData); ?>
    </div>

    <?php if ($result->token !== null): ?>
        <script>
            async function refreshMessages() {
                const formData = new FormData();
                formData.append('action', 'fetch_messages');
                formData.append('token', '<?= e($result->token) ?>');

                const response = await fetch('', {
                    method: 'POST',
                    body: formData,
                });

                document.getElementById('messages-section').innerHTML = await response.text();
            }

            setInterval(refreshMessages, 5000);
        </script>
    <?php endif; ?>
<?php endif; ?>

</body>
</html>
