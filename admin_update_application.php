<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}
require_once 'csrf.php';
require_once 'db_connect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/Exception.php';
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_csrf_token();
    $app_id = intval($_POST['app_id']);
    $status = $_POST['status']; // 'Accepted' or 'Rejected'

    // update status
    $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $app_id);
    if ($stmt->execute()) {

        // --- NOTIFICATION & EMAIL LOGIC IF ACCEPTED ---
        // Only run if the status is Accepted
        if ($status === 'Accepted') {
            // Get application and hackathon details
            $q = $conn->prepare("SELECT a.user_id, a.team_id, h.title, h.location, h.date_start, h.date_end FROM applications a JOIN hackathons h ON a.hackathon_id = h.id WHERE a.id = ?");
            $q->bind_param("i", $app_id);
            $q->execute();
            $app_res = $q->get_result();
            if ($app = $app_res->fetch_assoc()) {

                // Identify target users (either entire team or just individual)
                $target_users = [];
                if ($app['team_id']) {
                    // Fetch Leader
                    $lq = $conn->query("SELECT u.id, u.email FROM users u JOIN teams t ON t.leader_id = u.id WHERE t.id = " . intval($app['team_id']));
                    if ($r = $lq->fetch_assoc())
                        $target_users[$r['id']] = $r['email'];

                    // Fetch accepted team members
                    $mq = $conn->query("SELECT u.id, u.email FROM users u JOIN team_members tm ON tm.user_id = u.id WHERE tm.team_id = " . intval($app['team_id']) . " AND tm.status = 'Accepted'");
                    while ($r = $mq->fetch_assoc()) {
                        $target_users[$r['id']] = $r['email'];
                    }
                } else {
                    // Fetch individual applicant
                    $uq = $conn->query("SELECT email FROM users WHERE id = " . intval($app['user_id']));
                    if ($r = $uq->fetch_assoc()) {
                        $target_users[$app['user_id']] = $r['email'];
                    }
                }

                // Construct Message template
                $subject = "✅ DevSprint: You've been accepted to {$app['title']}";
                $body_plain = "Congratulations! Your application has been approved by the Admin team.\n\n" .
                    "Hackathon: {$app['title']}\n" .
                    "Dates: {$app['date_start']} to {$app['date_end']}\n" .
                    "Location: {$app['location']}\n\n" .
                    "Instructions: Please arrive at the venue on time. Be sure to check with your team or review the hackathon guidelines on the DevSprint platform to finalize preparations. See you there!";

                $body_html = "
                <html>
                <body style='background-color:#02020f; padding:30px; margin:0;'>
                    <div style='max-width:600px; margin:0 auto; background-color:#00000a; border:1px solid #00e5ff33; border-radius:12px; overflow:hidden; font-family:Arial, sans-serif;'>
                        <div style='background:linear-gradient(90deg, #00e5ff, #7c4dff); padding:20px; text-align:center;'>
                            <h2 style='color:#00000a; margin:0; font-family:monospace; letter-spacing:2px;'>DEVSPRINT MISSION CONTROL</h2>
                        </div>
                        <div style='padding:35px;'>
                            <h3 style='color:#00e5ff; margin-top:0; font-size:22px;'>Application Approved! 🚀</h3>
                            <p style='color:#e8f0ff; font-size:16px; line-height:1.6;'>Congratulations Commander! Your application has been successfully verified and approved by the administrative team.</p>
                            
                            <div style='background-color:#10101a; padding:20px; border-left:4px solid #7c4dff; border-radius:6px; margin:25px 0; color:#e8f0ff;'>
                                <p style='margin:0 0 10px 0;'><strong>🏆 Hackathon:</strong> {$app['title']}</p>
                                <p style='margin:0 0 10px 0;'><strong>📅 Dates:</strong> {$app['date_start']} to {$app['date_end']}</p>
                                <p style='margin:0;'><strong>📍 Location:</strong> {$app['location']}</p>
                            </div>
                            
                            <h4 style='color:#e8f0ff; margin-bottom:10px; font-size:18px;'>Next Steps</h4>
                            <p style='color:#7b8eb0; font-size:14px; line-height:1.6;'>Please arrive at the venue on time. Coordinate securely with your team and ensure you review the final competition guidelines on your platform dashboard.</p>
                            
                            <div style='text-align:center; margin-top:35px; margin-bottom:10px;'>
                                <a href='http://localhost/WP-class/profile.php' style='background-color:#7c4dff; color:#ffffff; text-decoration:none; padding:14px 28px; border-radius:8px; font-weight:bold; font-family:monospace; display:inline-block;'>View Mission Log</a>
                            </div>
                        </div>
                        <div style='text-align:center; padding:20px; border-top:1px solid #00e5ff1a; color:#7b8eb0; font-size:12px; background-color:#02020f;'>
                            © 2026 DevSprint · Build faster. Compete. Conquer.
                        </div>
                    </div>
                </body>
                </html>
                ";

                // Prepare PHPMailer instance
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->CharSet = 'UTF-8'; // Fixes Emojis
                    $mail->isHTML(true); // Enables HTML template

                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    // TODO: The DevSprint administrator must set these credentials
                    $mail->Username = 'youremail@gmail.com';
                    $mail->Password = 'yourpassword'; // 16-dight security key
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->setFrom('no-reply@devsprint.local', 'DevSprint Admin');

                    // Dispatch to all found members
                    $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, 'hackathons.php')");

                    foreach ($target_users as $uid => $email) {
                        // 1. Send Internal Notification
                        $notif_stmt->bind_param("iss", $uid, $subject, $body_plain);
                        $notif_stmt->execute();

                        // 2. Add email target
                        $mail->clearAddresses();
                        $mail->addAddress($email);
                        $mail->Subject = $subject;
                        $mail->Body = $body_html;
                        $mail->AltBody = $body_plain;

                        // NOTE: If credentials aren't set, this generic send() will fail and throw exception.
                        // For safety, we suppress failures so it doesn't break the application accept process!
                        try {
                            $mail->send();
                            $_SESSION['profile_success'] = "Application Accepted & Email successfully sent!";
                        } catch (Exception $e) {
                            // Catch physical SMTP auth locks and return them to the session
                            $_SESSION['profile_error'] = "Status Updated, but Email Failed: " . $mail->ErrorInfo;
                            error_log("PHPMailer failed to send to $email: {$mail->ErrorInfo}");
                        }
                    }
                    $notif_stmt->close();
                } catch (Exception $e) {
                    $_SESSION['profile_error'] = "Mailer configuration error: " . $mail->ErrorInfo;
                    error_log("Mailer configuration error: {$mail->ErrorInfo}");
                }
            }
            $q->close();
        }
        // --- END NOTIFICATION LOGIC ---

        header("Location: admin_dashboard.php");
    } else {
        echo "<script>alert('Error updating status'); window.history.back();</script>";
    }
    $stmt->close();
}
$conn->close();
?>