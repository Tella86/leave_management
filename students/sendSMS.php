<?php
require '../admin/africastalking/src/AfricasTalking.php';
require '../admin/africastalking/vendor/autoload.php';

use AfricasTalking\SDK\AfricasTalking;

function sendSMS($recipientPhone, $message, $saveToDatabase = false, $actionType = 'checkout', $title = null, $senderPhoneNumber = null) {
    // AfricasTalking API credentials
    $username = 'ezems';
    $apiKey = '39fafb4f99370b33f2ce8a89fb49de56c6db75d19219d49db45c0522931be77e';
    
    // Initialize AfricasTalking
    $AT = new AfricasTalking($username, $apiKey);
    $sms = $AT->sms();

    // Database connection (only if saving to DB)
    include('../includes/db.php');

    try {
        // Send the message
        $result = $sms->send([
            'to' => $recipientPhone,
            'message' => $message
        ]);

        // Save the message details in the appropriate database table if required
        if ($saveToDatabase) {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if ($actionType === 'checkout') {
                // Insert into check_out_messages
                $stmt = $pdo->prepare("INSERT INTO check_out_messages (phone_number, sender_phone_number, message, sent_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$recipientPhone, $senderPhoneNumber, $message]);
            } elseif ($actionType === 'checkin') {
                // Insert into check_in_messages
                $stmt = $pdo->prepare("INSERT INTO check_in_messages (title, message, sent_at) VALUES (?, ?, NOW())");
                $stmt->execute([$title, $message]);
            }
        }

        return "Message sent successfully!";
    } catch (PDOException $e) {
        return "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        return "Error sending message: " . $e->getMessage();
    }
}
