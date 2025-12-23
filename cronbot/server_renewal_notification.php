<?php
ini_set('error_log', 'error_log');
date_default_timezone_set('Asia/Tehran');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../botapi.php';
require_once __DIR__ . '/../function.php';
require_once __DIR__ . '/../jdf.php';

global $pdo;

// دریافت تمام هزینه‌های سرور که تاریخ پایان دارند
$sql = "SELECT * FROM expenses WHERE type = 'server' AND end_date IS NOT NULL AND end_date != '' ORDER BY end_date ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$server_expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($server_expenses) == 0) {
    exit;
}

// دریافت لیست ادمین‌ها
$admin_ids = select("admin", "id_admin", null, null, "FETCH_COLUMN");
if (!is_array($admin_ids)) {
    $admin_ids = [];
}

$today = date('Y-m-d');
$today_timestamp = strtotime($today);

foreach ($server_expenses as $expense) {
    $end_date = $expense['end_date'];
    $end_timestamp = strtotime($end_date);
    $days_remaining = intval(($end_timestamp - $today_timestamp) / 86400);
    
    // بررسی اینکه آیا قبلاً نوتیفیکیشن ارسال شده است
    $notification_sent = json_decode($expense['notification_sent'] ?? '[]', true);
    if (!is_array($notification_sent)) {
        $notification_sent = [];
    }
    
    // روزهای هشدار: 7 روز، 3 روز، 1 روز قبل از انقضا
    $warning_days = [7, 3, 1];
    
    foreach ($warning_days as $warning_day) {
        // اگر در بازه هشدار هستیم و هنوز نوتیفیکیشن ارسال نشده
        if ($days_remaining == $warning_day && !in_array($warning_day, $notification_sent)) {
            // آماده‌سازی پیام
            $end_jalali = jdate('Y/m/d', $end_timestamp);
            $start_jalali = !empty($expense['start_date']) ? jdate('Y/m/d', strtotime($expense['start_date'])) : 'نامشخص';
            $amount = number_format(floatval($expense['amount']), 0);
            $description = !empty($expense['description']) ? "\n📝 <b>توضیحات:</b> " . $expense['description'] : "";
            
            $message = "⚠️ <b>هشدار تمدید سرور</b>\n━━━━━━━━━━━━━━━━━━\n";
            $message .= "🖥️ <b>شناسه هزینه:</b> <code>{$expense['id']}</code>\n";
            $message .= "💰 <b>مبلغ:</b> $amount تومان\n";
            $message .= "📅 <b>تاریخ شروع:</b> $start_jalali\n";
            $message .= "📅 <b>تاریخ پایان:</b> $end_jalali\n";
            $message .= "⏰ <b>روزهای باقی‌مانده:</b> $days_remaining روز\n";
            $message .= "$description\n";
            $message .= "━━━━━━━━━━━━━━━━━━\n";
            $message .= "⚠️ لطفاً برای تمدید سرور اقدام کنید.";
            
            // ارسال پیام به تمام ادمین‌ها
            foreach ($admin_ids as $admin_id) {
                sendmessage($admin_id, $message, null, 'HTML');
            }
            
            // ثبت نوتیفیکیشن ارسال شده
            $notification_sent[] = $warning_day;
            $notification_json = json_encode($notification_sent);
            $stmt = $pdo->prepare("UPDATE expenses SET notification_sent = :notification_sent WHERE id = :id");
            $stmt->bindParam(':notification_sent', $notification_json);
            $stmt->bindParam(':id', $expense['id']);
            $stmt->execute();
            
            break; // فقط یک نوتیفیکیشن در هر اجرا
        }
    }
    
    // اگر سرور منقضی شده است (روز 0 یا منفی)
    if ($days_remaining <= 0 && !in_array('expired', $notification_sent)) {
        $end_jalali = jdate('Y/m/d', $end_timestamp);
        $start_jalali = !empty($expense['start_date']) ? jdate('Y/m/d', strtotime($expense['start_date'])) : 'نامشخص';
        $amount = number_format(floatval($expense['amount']), 0);
        $description = !empty($expense['description']) ? "\n📝 <b>توضیحات:</b> " . $expense['description'] : "";
        
        $message = "🔴 <b>سرور منقضی شده است!</b>\n━━━━━━━━━━━━━━━━━━\n";
        $message .= "🖥️ <b>شناسه هزینه:</b> <code>{$expense['id']}</code>\n";
        $message .= "💰 <b>مبلغ:</b> $amount تومان\n";
        $message .= "📅 <b>تاریخ شروع:</b> $start_jalali\n";
        $message .= "📅 <b>تاریخ پایان:</b> $end_jalali\n";
        $message .= "$description\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "🔴 این سرور منقضی شده است. لطفاً فوراً اقدام کنید.";
        
        // ارسال پیام به تمام ادمین‌ها
        foreach ($admin_ids as $admin_id) {
            sendmessage($admin_id, $message, null, 'HTML');
        }
        
        // ثبت نوتیفیکیشن ارسال شده
        $notification_sent[] = 'expired';
        $notification_json = json_encode($notification_sent);
        $stmt = $pdo->prepare("UPDATE expenses SET notification_sent = :notification_sent WHERE id = :id");
        $stmt->bindParam(':notification_sent', $notification_json);
        $stmt->bindParam(':id', $expense['id']);
        $stmt->execute();
    }
}

