<?php
session_start();
include('config.php');

$user_id = $_SESSION["id"] ?? null;

// Replace with your actual key if needed, or keep in config.php
$GROQ_API_KEY = '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';
$MODEL = 'llama-3.3-70b-versatile';

function call_groq_chat($messages) {
    global $GROQ_API_KEY, $MODEL;
    $url = 'https://api.groq.com/openai/v1/chat/completions';

    $payload = [
        'model' => $MODEL,
        'messages' => $messages,
        'temperature' => 0.7,
        'max_tokens' => 1024,
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $GROQ_API_KEY,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) return ['error' => true, 'message' => "Service unavailable (Code: $http_code)"];

    $data = json_decode($response, true);
    return ['error' => false, 'content' => $data['choices'][0]['message']['content'] ?? 'No response'];
}

$bot_reply = '';
$user_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $user_msg = trim($_POST['message']);
    
    // User Context
    $name = 'Guest';
    $city = 'Pakistan';
    $order_text = 'No orders yet.';

    if ($user_id) {
        $stmt = mysqli_prepare($conn, "SELECT full_name, city FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $u_info = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        $name = $u_info['full_name'] ?? $name;
        $city = $u_info['city'] ?? $city;

        $o_stmt = mysqli_prepare($conn, "SELECT id, order_status, total_amount FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        mysqli_stmt_bind_param($o_stmt, "i", $user_id);
        mysqli_stmt_execute($o_stmt);
        $l_order = mysqli_fetch_assoc(mysqli_stmt_get_result($o_stmt));
        if ($l_order) $order_text = "Order #{$l_order['id']} is {$l_order['order_status']} (PKR " . number_format($l_order['total_amount']) . ")";
    }

    $system_prompt = "You are 'Nestor', the AI assistant for SafeNest. Location: Karachi/Pakistan. User: $name from $city. Last Order: $order_text. 
    Style: Professional but friendly, use words like 'Assalam-o-Alaikum', 'Bilkul', 'Zaroor'. Focus on home security advice and store help. 
    Keep responses concise. If they ask about prices, tell them to check the product details page.";

    $messages = [['role' => 'system', 'content' => $system_prompt], ['role' => 'user', 'content' => $user_msg]];
    $result = call_groq_chat($messages);
    $bot_reply = $result['error'] ? "Sorry, I'm having trouble connecting." : $result['content'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SafeNest AI Assistant</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        :root { --primary: #2c3e50; --accent: #3498db; }
        .chat-container { height: 75vh; background: white; border-radius: 20px; overflow: hidden; display: flex; flex-direction: column; }
        .chat-header { background: var(--primary); color: white; padding: 20px; display: flex; align-items: center; }
        .chat-body { flex: 1; overflow-y: auto; padding: 25px; background: #fdfdfd; }
        .msg-bubble { padding: 12px 18px; border-radius: 20px; margin-bottom: 15px; max-width: 80%; position: relative; line-height: 1.5; }
        .msg-user { background: var(--accent); color: white; align-self: flex-end; border-bottom-right-radius: 2px; margin-left: auto; }
        .msg-bot { background: #f1f3f5; color: #333; align-self: flex-start; border-bottom-left-radius: 2px; }
        .chat-footer { padding: 20px; background: white; border-top: 1px solid #eee; }
        .avatar { width: 40px; height: 40px; border-radius: 50%; background: #ddd; margin: 0 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .suggestion-chip { border: 1px solid var(--accent); color: var(--accent); padding: 5px 15px; border-radius: 20px; cursor: pointer; margin-right: 10px; font-size: 14px; transition: 0.3s; display: inline-block; margin-bottom: 10px; }
        .suggestion-chip:hover { background: var(--accent); color: white; }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm rounded-lg p-4 mb-4">
                    <p class="text-muted small">I'm Nestor, your SafeNest security expert. Ask me about cameras, alarms, or your current order status.</p>
                    <hr>
                    <h6 class="font-weight-bold small text-uppercase mb-3">Try asking:</h6>
                    <div id="suggestions">
                        <span class="suggestion-chip" onclick="fillMsg('How do I secure my apartment?')">Apartment Safety</span>
                        <span class="suggestion-chip" onclick="fillMsg('Which camera is best for night vision?')">Night Vision Cameras</span>
                        <span class="suggestion-chip" onclick="fillMsg('Where is my last order?')">Track Order</span>
                        <span class="suggestion-chip" onclick="fillMsg('Do you have JazzCash payment?')">Payment Methods</span>
                    </div>
                </div>
                <?php if(!$user_id): ?>
                <div class="alert alert-warning rounded-lg border-0 shadow-sm">
                    <i class="fa fa-info-circle mr-2"></i> <strong>Guest Mode:</strong> Login to see your personal order history!
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-8">
                <div class="chat-container shadow-lg">
                    <div class="chat-header">
                        <div class="avatar"><i class="fa fa-robot text-dark"></i></div>
                        <div>
                            <h6 class="mb-0 font-weight-bold">SafeNest AI Assistant</h6>
                            <small class="text-light-50"><i class="fa fa-circle text-success mr-1"></i> Nestor is online</small>
                        </div>
                    </div>

                    <div class="chat-body d-flex flex-column" id="chatBody">
                        <?php if (!$user_msg): ?>
                            <div class="text-center my-auto p-5">
                                <i class="fa fa-comment-dots fa-3x text-light mb-3"></i>
                                <h5 class="text-secondary">Assalam-o-Alaikum!</h5>
                                <p class="text-muted">How can I help make your home safer today?</p>
                            </div>
                        <?php else: ?>
                            <div class="msg-bubble msg-user shadow-sm">
                                <?= htmlspecialchars($user_msg) ?>
                            </div>
                            <div class="d-flex align-items-start mb-3">
                                <div class="avatar bg-primary text-white" style="width: 30px; height: 30px; font-size: 14px;"><i class="fa fa-shield-alt"></i></div>
                                <div class="msg-bubble msg-bot shadow-sm">
                                    <?= nl2br(htmlspecialchars($bot_reply)) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="chat-footer">
                        <form method="POST" id="chatForm">
                            <div class="input-group">
                                <input type="text" name="message" id="msgInput" class="form-control form-control-lg border-0 bg-light rounded-pill px-4" placeholder="Type your security query..." required autocomplete="off">
                                <div class="input-group-append ml-2">
                                    <button type="submit" class="btn btn-primary rounded-circle shadow-sm" style="width: 50px; height: 50px;">
                                        <i class="fa fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script>
        function fillMsg(text) {
            document.getElementById('msgInput').value = text;
            document.getElementById('chatForm').submit();
        }
        const body = document.getElementById('chatBody');
        body.scrollTop = body.scrollHeight;
    </script>
</body>
</html>