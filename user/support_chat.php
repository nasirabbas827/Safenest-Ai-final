<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'user') {
    header("location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $message = trim($_POST['message']);
    if ($message !== '') {
        $stmt = mysqli_prepare($conn, 
            "INSERT INTO support_messages (user_id, is_from_user, message) 
             VALUES (?, 1, ?)");
        mysqli_stmt_bind_param($stmt, "is", $user_id, $message);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: support_chat.php");
    exit;
}

// Get all messages for this user
$messages = [];
$result = mysqli_query($conn, 
    "SELECT * FROM support_messages 
     WHERE user_id = $user_id 
     ORDER BY created_at ASC");

while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
}

// Mark admin messages as read
mysqli_query($conn, 
    "UPDATE support_messages 
     SET read_at = NOW() 
     WHERE user_id = $user_id 
       AND is_from_user = 0 
       AND read_at IS NULL");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Chat - SafeNest</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .chat-window {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            background: #f8f9fa;
        }
        .msg {
            margin-bottom: 12px;
            padding: 10px 14px;
            border-radius: 18px;
            max-width: 78%;
            word-wrap: break-word;
        }
        .msg-user {
            background: #0f0f0e;
            color: white;
            margin-left: auto;
        }
        .msg-admin {
            background: #e9ecef;
            margin-right: auto;
        }
        .msg-time {
            font-size: 0.78rem;
            color: #6c757d;
            margin-top: 4px;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container py-5">
    <h2 class="mb-4">Support / Help Chat</h2>
    <p class="lead text-muted mb-4">Ask us anything — we'll reply as soon as possible.</p>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="chat-window mb-4" id="chatWindow">
                <?php if (empty($messages)): ?>
                    <div class="text-center text-muted py-5">
                        No messages yet.<br>Start by sending your first message!
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $m): ?>
                        <div class="d-flex <?= $m['is_from_user'] ? 'justify-content-end' : 'justify-content-start' ?>">
                            <div class="msg <?= $m['is_from_user'] ? 'msg-user' : 'msg-admin' ?>">
                                <?= nl2br(htmlspecialchars($m['message'])) ?>
                                <div class="msg-time">
                                    <?= date('d M Y • h:i A', strtotime($m['created_at'])) ?>
                                    <?php if ($m['read_at'] && !$m['is_from_user']): ?>
                                        <span class="ms-2 text-danger">✓✓</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form method="POST">
                <div class="input-group">
                    <textarea name="message" class="form-control" rows="2"
                              placeholder="Type your message here..." required></textarea>
                    <button type="submit" class="btn btn-primary">Send</button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-4">
        <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto scroll to bottom
    const chat = document.getElementById('chatWindow');
    if (chat) chat.scrollTop = chat.scrollHeight;
</script>
</body>
</html>