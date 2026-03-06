<!-- navbar.php -->

<!-- Include Font Awesome for icons (add only once, preferably in <head> of your main layout or here if navbar is included early) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<nav class="navbar navbar-expand-lg navbar-dark bg-danger">
    <a class="navbar-brand" href="index.php">SafeNest Ai</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <?php
            if (isset($_SESSION["id"]) && !empty($_SESSION["id"])) {
                echo '<li class="nav-item"><a class="nav-link" href="dashboard.php">Home</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="view_cart.php">Cart</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="view_orders.php">My Orders</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="security_assessment.php">Security Assessment</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="support_chat.php">Customer Support</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="chatbot.php">AI Chatbot</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>';
            } else {
                echo '<li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="./admin/admin_login.php">Admin Login</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="chatbot.php">AI Chatbot</a></li>';

            }
            ?>
        </ul>
    </div>
</nav>

<!-- Floating Chatbot Icon (visible on all pages that include navbar) -->
<div id="chat-float" style="
    position: fixed;
    bottom: 25px;
    right: 25px;
    z-index: 9999;
">
    <a href="chatbot.php"  title="Chat with SafeNest AI Assistant">
        <div style="
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35);
            cursor: pointer;
            transition: all 0.3s ease;
        " onmouseover="this.style.transform='scale(1.12)';" onmouseout="this.style.transform='scale(1)';">
            <i class="fas fa-robot fa-2x"></i>
        </div>
    </a>
</div>

<!-- Optional: Small tooltip/hint on hover -->
<style>
    #chat-float a {
        text-decoration: none;
    }
    #chat-float a div:hover::after {
        content: "Ask AI anything!";
        position: absolute;
        bottom: 85px;
        right: 0;
        background: #333;
        color: white;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        white-space: nowrap;
        pointer-events: none;
    }
</style>