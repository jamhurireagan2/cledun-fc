<?php
/**
 * CLEDUN FC - Email + SMS Notification Helper
 * Uses Gmail SMTP via cURL (No PHPMailer needed)
 */

/**
 * Send an email using Gmail SMTP via cURL
 */
function sendEmailNotification($to, $toName, $subject, $bodyHtml) {
    $smtpHost = 'ssl://smtp.gmail.com';
    $smtpPort = 465;
    $username = 'cledunfc@gmail.com';        // ← YOUR GMAIL
    $password = '@cledunfc254';        // ← YOUR 16-CHAR APP PASSWORD

    $from     = 'cledunfc@gmail.com';
    $fromName = 'CLEDUN FC';

    // Build email headers
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: $fromName <$from>\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "Subject: $subject\r\n";

    $data = "To: $toName <$to>\r\n" . $headers . "\r\n" . $bodyHtml;

    try {
        $fp = @stream_socket_client(
            $smtpHost . ':' . $smtpPort,
            $errno, $errstr, 15,
            STREAM_CLIENT_CONNECT,
            stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]])
        );

        if (!$fp) {
            error_log("SMTP connect failed: $errstr ($errno)");
            return false;
        }

        // Read greeting
        fgets($fp, 515);

        // EHLO
        fwrite($fp, "EHLO " . gethostname() . "\r\n");
        while ($line = fgets($fp, 515)) { if (substr($line, 3, 1) === ' ') break; }

        // AUTH LOGIN
        fwrite($fp, "AUTH LOGIN\r\n"); fgets($fp, 515);
        fwrite($fp, base64_encode($username) . "\r\n"); fgets($fp, 515);
        fwrite($fp, base64_encode($password) . "\r\n");
        $authResp = fgets($fp, 515);
        if (substr($authResp, 0, 3) !== '235') {
            error_log("SMTP auth failed: $authResp");
            fclose($fp);
            return false;
        }

        // MAIL FROM
        fwrite($fp, "MAIL FROM: <$from>\r\n"); fgets($fp, 515);

        // RCPT TO
        fwrite($fp, "RCPT TO: <$to>\r\n"); fgets($fp, 515);

        // DATA
        fwrite($fp, "DATA\r\n"); fgets($fp, 515);

        // Body
        $message = "To: $toName <$to>\r\n";
        $message .= "From: $fromName <$from>\r\n";
        $message .= "Subject: $subject\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "\r\n" . $bodyHtml . "\r\n.";
        fwrite($fp, $message . "\r\n");

        $resp = fgets($fp, 515);
        fwrite($fp, "QUIT\r\n");
        fclose($fp);

        return substr($resp, 0, 3) === '250';
    } catch (Exception $e) {
        error_log("Email error: " . $e->getMessage());
        return false;
    }
}

/**
 * Send SMS via Africa's Talking
 */
function sendSMSNotification($phone, $message) {
    // === SANDBOX MODE ===
    $username = 'sandbox';
    $apiKey   = 'atsk_192a726fee0501be94d0502586bba4d6ab4a168b71a53d5b6172cbee3346751f80363f81';

    // Clean the phone number
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    if (substr($phone, 0, 1) === '0') {
        $phone = '+254' . substr($phone, 1);
    } elseif (substr($phone, 0, 4) !== '+254') {
        $phone = '+254' . ltrim($phone, '+');
    }

    // SANDBOX endpoint
    $url = 'https://api.sandbox.africastalking.com/version1/messaging';

    $data = [
        'username' => $username,
        'to'       => $phone,
        'message'  => $message
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apiKey: ' . $apiKey,
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Log for debugging
    error_log("SMS Response: HTTP $httpCode | $response");

    return $httpCode >= 200 && $httpCode < 300;
}

/**
 * Notify approved registration
 */
function notifyRegistrationApproved($registration, $categoryName) {
    $fullName = $registration['first_name'] . ' ' . $registration['last_name'];
    $ref = str_pad($registration['id'], 6, '0', STR_PAD_LEFT);

    // EMAIL
    $emailSent = false;
    if (!empty($registration['email'])) {
        $subject = "🎉 CLEDUN FC Registration Approved - {$fullName}";
        $body = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:auto;'>
                <div style='background:#1a2a6c;color:#fff;padding:25px;text-align:center;border-radius:10px 10px 0 0;'>
                    <h1 style='margin:0;color:#fbbf24;'>⚽ CLEDUN FC</h1>
                    <p style='margin:5px 0 0;'>Building Champions Since 2026</p>
                </div>
                <div style='background:#fff;padding:30px;border:1px solid #e5e7eb;border-top:none;'>
                    <h2 style='color:#1a2a6c;'>Congratulations, {$fullName}! 🎉</h2>
                    <p>We are delighted to inform you that your registration for <strong>{$categoryName}</strong> has been <span style='color:#10b981;font-weight:bold;'>APPROVED</span>.</p>
                    <p><strong>Application Reference:</strong> #{$ref}</p>
                    <p><strong>Category:</strong> {$categoryName}</p>
                    <p>Welcome to the CLEDUN FC family! Our team will contact you shortly with the next steps.</p>
                    <p style='margin-top:25px;'>For any inquiries:</p>
                    <p>📧 <a href='mailto:cledunsports@gmail.com'>cledunsports@gmail.com</a><br>
                       📱 WhatsApp: <a href='https://wa.me/254710339213'>+254 710 339 213</a></p>
                    <p style='margin-top:25px;color:#6b7280;font-size:0.9rem;'>— CLEDUN FC Team</p>
                </div>
                <div style='background:#0d1b3e;color:#fff;padding:15px;text-align:center;border-radius:0 0 10px 10px;font-size:0.85rem;'>
                    © " . date('Y') . " CLEDUN FC. All rights reserved.
                </div>
            </div>
        ";
        $emailSent = sendEmailNotification($registration['email'], $fullName, $subject, $body);
    }

    // SMS
    $smsSent = false;
    if (!empty($registration['phone'])) {
        $sms = "CLEDUN FC: Congratulations {$fullName}! Your registration for {$categoryName} (Ref #{$ref}) has been APPROVED. Welcome to the team!";
        $smsSent = sendSMSNotification($registration['phone'], $sms);
    }

    return ['email' => $emailSent, 'sms' => $smsSent];
}

/**
 * Notify rejected registration
 */
function notifyRegistrationRejected($registration, $reason) {
    $fullName = $registration['first_name'] . ' ' . $registration['last_name'];
    $ref = str_pad($registration['id'], 6, '0', STR_PAD_LEFT);

    $emailSent = false;
    if (!empty($registration['email'])) {
        $subject = "CLEDUN FC Registration Update - {$fullName}";
        $body = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:auto;'>
                <div style='background:#1a2a6c;color:#fff;padding:25px;text-align:center;border-radius:10px 10px 0 0;'>
                    <h1 style='margin:0;color:#fbbf24;'>⚽ CLEDUN FC</h1>
                </div>
                <div style='background:#fff;padding:30px;border:1px solid #e5e7eb;border-top:none;'>
                    <h2 style='color:#1a2a6c;'>Hi {$fullName},</h2>
                    <p>Thank you for your interest in CLEDUN FC.</p>
                    <p>After reviewing your application <strong>#{$ref}</strong>, we regret to inform you that it has not been approved at this time.</p>
                    <p><strong>Reason:</strong> " . htmlspecialchars($reason) . "</p>
                    <p>You are welcome to reapply in the future. For questions, contact us at:</p>
                    <p>📧 cledunsports@gmail.com | 📱 WhatsApp: +254 710 339 213</p>
                    <p style='margin-top:25px;color:#6b7280;font-size:0.9rem;'>— CLEDUN FC Team</p>
                </div>
            </div>
        ";
        $emailSent = sendEmailNotification($registration['email'], $fullName, $subject, $body);
    }

    $smsSent = false;
    if (!empty($registration['phone'])) {
        $sms = "CLEDUN FC: Hi {$fullName}, your application #{$ref} was not approved. Reason: {$reason}. Contact us for more info.";
        $smsSent = sendSMSNotification($registration['phone'], $sms);
    }

    return ['email' => $emailSent, 'sms' => $smsSent];
}