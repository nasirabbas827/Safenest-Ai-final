<?php
session_start();
include('../config.php'); // adjust path if needed

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("location: admin_login.php");
    exit;
}

// Handle admin reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message']) && !empty($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    $message = trim($_POST['message']);

    if ($message !== '') {
        $stmt = mysqli_prepare($conn, 
            "INSERT INTO support_messages (user_id, is_from_user, message) 
             VALUES (?, 0, ?)");
        mysqli_stmt_bind_param($stmt, "is", $user_id, $message);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: support_messages.php");
    exit;
}

// Get users with messages
$users_query = "
    SELECT DISTINCT u.id, u.full_name, u.email,
           MAX(m.created_at) as last_msg_time,
           SUM(CASE WHEN m.is_from_user = 1 AND m.read_at IS NULL THEN 1 ELSE 0 END) as unread_count
    FROM support_messages m
    JOIN users u ON m.user_id = u.id
    GROUP BY u.id
    ORDER BY last_msg_time DESC";

$users_result = mysqli_query($conn, $users_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Messages - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container py-5">
    <h2>Support Messages</h2>

    <?php if (mysqli_num_rows($users_result) == 0): ?>
        <div class="alert alert-info mt-4">No messages from users yet.</div>
    <?php else: ?>
        <div class="table-responsive mt-4">
            <table class="table table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Last Message</th>
                        <th>Unread</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($u = mysqli_fetch_assoc($users_result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['full_name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= date('d M Y h:i A', strtotime($u['last_msg_time'])) ?></td>
                            <td>
                                <?php if ($u['unread_count'] > 0): ?>
                                    <span class="badge bg-danger"><?= $u['unread_count'] ?></span>
                                <?php else: ?>
                                    <span class="badge bg-success">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-primary btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#chatModal<?= $u['id'] ?>">
                                    View & Reply
                                </button>
                            </td>
                        </tr>

                        <!-- Modal -->
                        <div class="modal fade" id="chatModal<?= $u['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Chat with <?= htmlspecialchars($u['full_name']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body" style="max-height: 480px; overflow-y: auto;">
                                        <?php
                                        $chat_res = mysqli_query($conn, 
                                            "SELECT * FROM support_messages 
                                             WHERE user_id = {$u['id']} 
                                             ORDER BY created_at ASC");
                                        while ($msg = mysqli_fetch_assoc($chat_res)):
                                        ?>
                                            <div class="d-flex <?= $msg['is_from_user'] ? 'justify-content-end' : 'justify-content-start' ?> mb-3">
                                                <div class="p-3 rounded <?= $msg['is_from_user'] ? 'bg-primary text-white' : 'bg-light border' ?>" 
                                                     style="max-width: 70%;">
                                                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                                    <div class="small mt-1 opacity-75">
                                                        <?= date('d M Y • h:i A', strtotime($msg['created_at'])) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <form method="POST" class="w-100">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <div class="input-group">
                                                <textarea name="message" class="form-control" rows="2" 
                                                          placeholder="Type your reply..." required></textarea>
                                                <button type="submit" class="btn btn-success">Send</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>