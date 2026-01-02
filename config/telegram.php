<?php
/*
 * Telegram Bot Configuration
 * Setup instructions:
 * 1. Create a bot via @BotFather on Telegram
 * 2. Get your bot token
 * 3. Get your chat ID (use @userinfobot or send message to your bot and check updates)
 */

// Telegram Bot Token (get from @BotFather)
define('TELEGRAM_BOT_TOKEN', '7896650858:AAENiMiSWgv8EX0vRy_YNftTAhYo3Nwo2fA');

// Telegram Chat ID (your personal chat ID or group chat ID)
define('TELEGRAM_CHAT_ID', '1223813974');

/*
 * HOW TO GET YOUR TELEGRAM BOT TOKEN:
 * 1. Open Telegram and search for @BotFather
 * 2. Send /newbot command
 * 3. Follow instructions to create your bot
 * 4. Copy the bot token provided
 * 5. Replace YOUR_BOT_TOKEN_HERE above
 * 
 * Example: 1234567890:ABCdefGHIjklMNOpqrsTUVwxyz1234567890
 */

/*
 * HOW TO GET YOUR CHAT ID:
 * Method 1 - Using @userinfobot:
 * 1. Search for @userinfobot on Telegram
 * 2. Start chat and send any message
 * 3. Bot will reply with your user ID
 * 
 * Method 2 - Using getUpdates API:
 * 1. Send a message to your bot
 * 2. Visit: https://api.telegram.org/bot{YOUR_BOT_TOKEN}/getUpdates
 * 3. Look for "chat":{"id": YOUR_CHAT_ID in the response
 * 
 * Method 3 - For Groups:
 * 1. Add your bot to the group
 * 2. Send a message in the group
 * 3. Visit: https://api.telegram.org/bot{YOUR_BOT_TOKEN}/getUpdates
 * 4. Look for chat id (will be negative for groups, e.g., -1234567890)
 * 
 * Example Chat IDs:
 * - Personal chat: 123456789
 * - Group chat: -987654321
 */

/**
 * Send message to Telegram
 * 
 * @param string $message The message to send
 * @return bool True on success, false on failure
 */
function sendTelegramNotification($message) {
    $bot_token = TELEGRAM_BOT_TOKEN;
    $chat_id = TELEGRAM_CHAT_ID;
    
    // Check if token and chat ID are configured
    if ($bot_token === 'YOUR_BOT_TOKEN_HERE' || $chat_id === 'YOUR_CHAT_ID_HERE') {
        error_log("Telegram not configured. Please set TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID in config/telegram.php");
        return false;
    }
    
    // Telegram API URL
    $url = "https://api.telegram.org/bot" . $bot_token . "/sendMessage";
    
    // Prepare data
    $data = array(
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML'
    );
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    // Execute request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Check for errors
    if (curl_errno($ch)) {
        error_log("Telegram cURL Error: " . curl_error($ch));
        curl_close($ch);
        return false;
    }
    
    curl_close($ch);
    
    // Check HTTP response code
    if ($http_code == 200) {
        return true;
    } else {
        error_log("Telegram API Error (HTTP " . $http_code . "): " . $response);
        return false;
    }
}

/**
 * Format order details for Telegram message
 * 
 * @param int $order_id Order ID
 * @param array $order_data Order information
 * @param array $cart_items Cart items
 * @param float $total Total amount
 * @return string Formatted message
 */
function formatOrderMessage($order_id, $order_data, $cart_items, $total) {
    $message = "🛒 <b>NEW ORDER RECEIVED!</b>\n";
    $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // Order Information
    $message .= "📋 <b>Order #" . str_pad($order_id, 6, '0', STR_PAD_LEFT) . "</b>\n";
    $message .= "📅 Date: " . date('M j, Y - g:i A') . "\n\n";
    
    // Customer Information
    $message .= "👤 <b>Customer Details:</b>\n";
    $message .= "Name: " . htmlspecialchars($order_data['name']) . "\n";
    $message .= "Email: " . htmlspecialchars($order_data['email']) . "\n";
    $message .= "Phone: " . htmlspecialchars($order_data['phone']) . "\n";
    $message .= "Address: " . htmlspecialchars($order_data['address']) . "\n\n";
    
    // Order Items
    $message .= "🛍️ <b>Items Ordered:</b>\n";
    foreach ($cart_items as $item) {
        $message .= "• " . htmlspecialchars($item['name']) . "\n";
        $message .= "  Qty: " . $item['quantity'] . " × $" . number_format($item['price'], 2);
        $message .= " = $" . number_format($item['subtotal'], 2) . "\n";
    }
    
    $message .= "\n━━━━━━━━━━━━━━━━━━━━\n";
    
    // Total Amount
    $message .= "💰 <b>Total Amount: $" . number_format($total, 2) . "</b>\n";
    
    $message .= "━━━━━━━━━━━━━━━━━━━━\n";
    $message .= "✅ Order has been saved to database\n";
    $message .= "📦 Ready for processing";
    
    return $message;
}
?>