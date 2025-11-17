<?php
session_start();

// Handle clear chat
if (isset($_GET['clear'])) {
    $_SESSION["chat"] = [];
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

// Initialize chat session
if (!isset($_SESSION["chat"])) {
    $_SESSION["chat"] = [];
}

// Handle new message
if ($_POST && isset($_POST["msg"])) {
    $msg = trim($_POST["msg"]);
    if (!empty($msg)) {
        $_SESSION["chat"][] = ["user", $msg];
        
        // Send to Python API
        $data = json_encode(["message" => $msg, "use_rag" => true]);
        $ch = curl_init("http://localhost:8000/api/chat");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($resp && $httpCode == 200) {
            $resp_json = json_decode($resp, true);
            $bot = $resp_json["reply"] ?? "(no reply)";
        } else {
            $bot = "Error: Could not connect to API server. Please make sure the Python API is running on http://localhost:8000";
        }
        
        $_SESSION["chat"][] = ["bot", $bot];
        
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriBot Chat</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            background: #343541;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .chat-header {
            background: #202123;
            border-bottom: 1px solid #444654;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .chat-header h2 {
            color: #ececf1;
            font-size: 16px;
            font-weight: 600;
        }
        
        .new-chat-btn {
            background: transparent;
            border: 1px solid #565869;
            color: #ececf1;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        
        .new-chat-btn:hover {
            background: #40414f;
        }
        
        #chat-box {
            flex: 1;
            overflow-y: auto;
            scroll-behavior: smooth;
        }
        
        #chat-box::-webkit-scrollbar {
            width: 8px;
        }
        
        #chat-box::-webkit-scrollbar-track {
            background: transparent;
        }
        
        #chat-box::-webkit-scrollbar-thumb {
            background: #565869;
            border-radius: 10px;
        }
        
        .message-wrapper {
            width: 100%;
            padding: 24px 20px;
            border-bottom: 1px solid #444654;
        }
        
        .message-wrapper.user {
            background: #343541;
        }
        
        .message-wrapper.bot {
            background: #444654;
        }
        
        .message-content {
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            gap: 20px;
        }
        
        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        
        .avatar.user {
            background: #5436da;
        }
        
        .avatar.bot {
            background: #19c37d;
        }
        
        .message-text {
            color: #ececf1;
            line-height: 1.7;
            font-size: 16px;
            flex: 1;
            padding-top: 4px;
            word-wrap: break-word;
        }
        
        .chat-input-container {
            background: #343541;
            padding: 20px;
            border-top: 1px solid #444654;
        }
        
        .input-wrapper {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }
        
        form {
            position: relative;
        }
        
        textarea {
            width: 100%;
            padding: 14px 50px 14px 16px;
            border-radius: 12px;
            border: none;
            resize: none;
            font-size: 16px;
            font-family: inherit;
            background: #40414f;
            color: #ececf1;
            box-shadow: 0 0 0 1px rgba(255,255,255,0.1);
            transition: box-shadow 0.2s;
        }
        
        textarea:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(255,255,255,0.15);
        }
        
        textarea::placeholder {
            color: #8e8ea0;
        }
        
        .send-btn {
            position: absolute;
            right: 12px;
            bottom: 12px;
            width: 32px;
            height: 32px;
            background: #19c37d;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        
        .send-btn:hover:not(:disabled) {
            background: #1aa370;
        }
        
        .send-btn:disabled {
            background: #40414f;
            cursor: not-allowed;
            opacity: 0.5;
        }
        
        .send-btn svg {
            width: 18px;
            height: 18px;
            fill: white;
        }
        
        .empty-state {
            text-align: center;
            color: #8e8ea0;
            padding: 60px 20px;
            max-width: 600px;
            margin: auto;
        }
        
        .empty-state h3 {
            color: #ececf1;
            font-size: 24px;
            margin-bottom: 40px;
        }
        
        .example-prompts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 12px;
            margin-top: 20px;
        }
        
        .example-prompt {
            background: #40414f;
            padding: 16px;
            border-radius: 8px;
            font-size: 14px;
            color: #ececf1;
            cursor: pointer;
            transition: background 0.2s;
            border: 1px solid transparent;
        }
        
        .example-prompt:hover {
            background: #4a4b5a;
            border-color: #565869;
        }
        
        @media (max-width: 768px) {
            .message-content {
                gap: 12px;
            }
            
            .avatar {
                width: 28px;
                height: 28px;
                font-size: 16px;
            }
            
            .message-text {
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="chat-header">
        <h2>🌾 AgriBot</h2>
        <a href="?clear=1" class="new-chat-btn" onclick="return confirm('Start a new chat? Current conversation will be cleared.')">+ New chat</a>
    </div>
    
    <div id="chat-box">
        <?php
        // Render chat
        if (empty($_SESSION["chat"])) {
            echo '<div class="empty-state">';
            echo '<h3>How can I help you today?</h3>';
            echo '<div class="example-prompts">';
            echo '<div class="example-prompt">What are the best practices for rice cultivation?</div>';
            echo '<div class="example-prompt">How do I prevent pest attacks on crops?</div>';
            echo '<div class="example-prompt">When is the right time to plant corn?</div>';
            echo '</div>';
            echo '</div>';
        } else {
            foreach ($_SESSION["chat"] as $c) {
                $role = $c[0];
                $text = htmlspecialchars($c[1]);
                $wrapperClass = $role == "user" ? "user" : "bot";
                $avatarClass = $role == "user" ? "user" : "bot";
                $avatarIcon = $role == "user" ? "👤" : "🤖";
                
                echo "<div class='message-wrapper $wrapperClass'>";
                echo "<div class='message-content'>";
                echo "<div class='avatar $avatarClass'>$avatarIcon</div>";
                echo "<div class='message-text'>" . nl2br($text) . "</div>";
                echo "</div>";
                echo "</div>";
            }
        }
        ?>
    </div>
    
    <div class="chat-input-container">
        <div class="input-wrapper">
            <form method="POST" onsubmit="return validateForm()">
                <textarea name="msg" rows="1" placeholder="Send a message..." required oninput="autoResize(this)"></textarea>
                <button type="submit" class="send-btn">
                    <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path></svg>
                </button>
            </form>
        </div>
    </div>

    <script>
        // Auto-scroll to bottom on load
        window.addEventListener('load', function() {
            const chatBox = document.getElementById('chat-box');
            chatBox.scrollTop = chatBox.scrollHeight;
        });
        
        // Auto-resize textarea
        function autoResize(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 200) + 'px';
        }
        
        function validateForm() {
            const textarea = document.querySelector('textarea');
            if (textarea.value.trim() === '') {
                return false;
            }
            // Disable button to prevent double submit
            const btn = document.querySelector('.send-btn');
            btn.disabled = true;
            return true;
        }
    </script>
</body>
</html>