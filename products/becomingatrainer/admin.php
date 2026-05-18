<?php
require_once __DIR__ . '/config.php';

session_start();

error_reporting(0);
ini_set('display_errors', 0);

date_default_timezone_set('Asia/Ho_Chi_Minh');

$db_file = __DIR__ . '/brain.db';
$db = new SQLite3($db_file);

$db->exec('CREATE TABLE IF NOT EXISTS products (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, price REAL NOT NULL, description TEXT, remaining_quantity INTEGER)');
$db->exec('CREATE TABLE IF NOT EXISTS customers (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, phone TEXT UNIQUE, email TEXT, zalo TEXT, registration_date TEXT, created_at INTEGER DEFAULT 0, email_2_sent INTEGER DEFAULT 0, email_3_sent INTEGER DEFAULT 0)');
$db->exec('CREATE TABLE IF NOT EXISTS orders (id INTEGER PRIMARY KEY AUTOINCREMENT, customer_id INTEGER, product_id INTEGER, amount REAL, status TEXT, purchase_date TEXT)');

@$db->exec('ALTER TABLE customers ADD COLUMN created_at INTEGER DEFAULT 0');
@$db->exec('ALTER TABLE customers ADD COLUMN email_2_sent INTEGER DEFAULT 0');
@$db->exec('ALTER TABLE customers ADD COLUMN email_3_sent INTEGER DEFAULT 0');

function send_resend_email($to_email, $subject, $html_body) {
    $api_key    = RESEND_API_KEY;
    $from_email = FROM_EMAIL;

    $ch = curl_init('https://api.resend.com/emails');
    $payload = json_encode([
        'from'    => $from_email,
        'to'      => [$to_email],
        'subject' => $subject,
        'html'    => $html_body,
    ]);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $api_key,
        'Content-Type: application/json',
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

// ============================================================
//  PUBLIC ENDPOINT 1: Kiểm tra trạng thái thanh toán (frontend poll)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'check_payment') {
    $phone = strtolower($_GET['phone'] ?? '');
    $stmt  = $db->prepare('SELECT o.status FROM orders o JOIN customers c ON o.customer_id = c.id WHERE c.phone = :phone COLLATE NOCASE ORDER BY o.id DESC LIMIT 1');
    $stmt->bindValue(':phone', $phone);
    $res    = $stmt->execute();
    $status = 'pending';
    if ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        if ($row['status'] === 'Đã thanh toán') {
            $status = 'paid';
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['status' => $status]);
    exit;
}

// ============================================================
//  PUBLIC ENDPOINT 2: Webhook SePay (JSON POST)
// ============================================================
$rawPayload  = file_get_contents('php://input');
$webhookData = json_decode($rawPayload, true);

if (isset($webhookData['transferAmount']) && isset($webhookData['content'])) {
    $content = strtoupper($webhookData['content']);
    preg_match('/BAT\s*([A-Z0-9]+)/', $content, $matches);
    if (!empty($matches[1])) {
        $phone = strtolower($matches[1]);
        $stmt  = $db->prepare('SELECT id, name, email FROM customers WHERE phone = :phone COLLATE NOCASE LIMIT 1');
        $stmt->bindValue(':phone', $phone);
        $res = $stmt->execute();
        if ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $customer_id = $row['id'];
            $c_name      = $row['name'];
            $c_email     = $row['email'];

            $stmt_p = $db->prepare('SELECT id, name FROM products WHERE price = :amount LIMIT 1');
            $stmt_p->bindValue(':amount', $webhookData['transferAmount']);
            $res_p = $stmt_p->execute();
            if ($row_p = $res_p->fetchArray(SQLITE3_ASSOC)) {
                $product_id   = $row_p['id'];
                $product_name = $row_p['name'];
            } else {
                $res_def      = $db->querySingle('SELECT id, name FROM products ORDER BY id ASC LIMIT 1', true);
                $product_id   = $res_def ? $res_def['id'] : 1;
                $product_name = $res_def ? $res_def['name'] : 'Khóa học Becoming A Trainer';
            }

            $check_pending = $db->querySingle("SELECT id FROM orders WHERE customer_id = $customer_id AND status = 'Pending'");

            if ($check_pending) {
                $order_id = $check_pending;
                $stmt2    = $db->prepare("UPDATE orders SET product_id = :pid, amount = :amount, status = 'Đã thanh toán', purchase_date = :date WHERE id = :id");
                $stmt2->bindValue(':id', $order_id);
            } else {
                $stmt2 = $db->prepare('INSERT INTO orders (customer_id, product_id, amount, status, purchase_date) VALUES (:cid, :pid, :amount, :status, :date)');
                $stmt2->bindValue(':cid', $customer_id);
            }

            $stmt2->bindValue(':pid', $product_id);
            $stmt2->bindValue(':amount', $webhookData['transferAmount']);
            $stmt2->bindValue(':status', 'Đã thanh toán');
            $stmt2->bindValue(':date', date('d/m/Y H:i'));
            $stmt2->execute();

            if (!$check_pending) {
                $order_id = $db->lastInsertRowID();
            }

            $stmt3 = $db->prepare('UPDATE products SET remaining_quantity = remaining_quantity - 1 WHERE id = :id AND remaining_quantity > 0');
            $stmt3->bindValue(':id', $product_id, SQLITE3_INTEGER);
            $stmt3->execute();

            if (!empty($c_email)) {
                $subject = "Xác nhận thanh toán thành công - " . $product_name;
                $body    = "<h2>Xin chào " . htmlspecialchars($c_name) . ",</h2>
                            <p>Hệ thống đã nhận được khoản thanh toán <strong>" . number_format($webhookData['transferAmount'], 0, ',', '.') . " VNĐ</strong> của bạn cho sản phẩm: <strong>" . htmlspecialchars($product_name) . "</strong>.</p>
                            <p>Chúc mừng bạn đã chính thức tham gia khóa học. Đây là mã đơn hàng của bạn: #" . $order_id . "</p>
                            <p>Ban tổ chức sẽ liên hệ hỗ trợ bạn tham gia vào lớp học trong thời gian sớm nhất.</p>
                            <br><p>Trân trọng,<br>Lumina Global Team</p>";
                send_resend_email($c_email, $subject, $body);
            }
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

// ============================================================
//  PUBLIC ENDPOINT 3: add_customer AJAX (từ landing page)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_customer' && !empty($_POST['ajax'])) {
    $stmt = $db->prepare('INSERT OR IGNORE INTO customers (name, phone, email, zalo, registration_date, created_at) VALUES (:name, :phone, :email, :zalo, :date, :created_at)');
    $stmt->bindValue(':name',       $_POST['name']);
    $stmt->bindValue(':phone',      $_POST['phone']);
    $stmt->bindValue(':email',      $_POST['email'] ?? '');
    $stmt->bindValue(':zalo',       !empty($_POST['zalo']) ? $_POST['zalo'] : $_POST['phone']);
    $stmt->bindValue(':date',       date('d/m/Y'));
    $stmt->bindValue(':created_at', time());
    $stmt->execute();

    $res_cid = $db->querySingle("SELECT id FROM customers WHERE phone = '" . SQLite3::escapeString($_POST['phone']) . "'");

    if ($res_cid) {
        $check_pending = $db->querySingle("SELECT id FROM orders WHERE customer_id = $res_cid AND status = 'Pending'");
        if (!$check_pending) {
            $res_def    = $db->querySingle('SELECT id FROM products WHERE price = 99999 LIMIT 1', true);
            if (empty($res_def)) {
                $res_def = $db->querySingle('SELECT id FROM products ORDER BY id ASC LIMIT 1', true);
            }
            $product_id = (is_array($res_def) && isset($res_def['id'])) ? $res_def['id'] : 1;

            $stmt_ord = $db->prepare('INSERT INTO orders (customer_id, product_id, amount, status, purchase_date) VALUES (:cid, :pid, :amount, :status, :date)');
            $stmt_ord->bindValue(':cid',    $res_cid);
            $stmt_ord->bindValue(':pid',    $product_id);
            $stmt_ord->bindValue(':amount', 0);
            $stmt_ord->bindValue(':status', 'Pending');
            $stmt_ord->bindValue(':date',   date('d/m/Y H:i'));
            $stmt_ord->execute();
        }
    }

    $email = $_POST['email'] ?? '';
    $name  = $_POST['name']  ?? '';
    if (!empty($email)) {
        $subject1 = "Chào mừng anh/chị bắt đầu hành trình \"Becoming A Trainer\" 🚀";
        $body1    = "<p>Xin chào " . htmlspecialchars($name) . ",</p>
                    <p>Cảm ơn anh/chị đã tin tưởng và để lại thông tin đăng ký tư vấn cho chương trình <strong>Becoming A Trainer</strong>. Tôi là Linh từ đội ngũ Lumina Global.</p>
                    <p>Tôi hiểu rằng ở vị thế hiện tại, anh/chị đã tích lũy được một bề dày kinh nghiệm đáng tự hào. Nhưng việc giữ những \"bí quyết\" đó cho riêng mình đôi khi lại là một sự lãng phí. Hành trình biến chiều sâu chuyên môn của anh/chị thành sức ảnh hưởng xứng tầm chính thức bắt đầu từ ngày hôm nay.</p>
                    <p>Hiện tại, chuyên gia của chúng tôi đang xem xét kỹ lưỡng hồ sơ và những rào cản anh/chị vừa chia sẻ trong form khảo sát. Chúng tôi sẽ sớm liên hệ qua Zalo/SĐT để xếp lịch tư vấn 1:1, nhằm đảm bảo chương trình này thực sự giải quyết đúng bài toán của anh/chị.</p>
                    <p>Trong vài ngày tới, tôi sẽ gửi cho anh/chị một vài góc nhìn thực chiến nhất về nghề Huấn luyện chuyên nghiệp. Hãy nhớ kiểm tra hộp thư nhé!</p>
                    <p>Hẹn sớm gặp lại anh/chị,<br><strong>Lumina Global Team</strong></p>";
        send_resend_email($email, $subject1, $body1);
    }

    header('Content-Type: application/json');
    if ($db->lastErrorCode()) {
        echo json_encode(['success' => false, 'error' => $db->lastErrorMsg()]);
    } else {
        echo json_encode(['success' => true]);
    }
    exit;
}

// ============================================================
//  AUTH GATE — tất cả request từ đây đều cần đăng nhập
// ============================================================
$login_error = '';

if (isset($_POST['bat_logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['bat_login_username'])) {
    $input_user = $_POST['bat_login_username'] ?? '';
    $input_pass = $_POST['bat_login_password'] ?? '';
    if ($input_user === ADMIN_USERNAME && hash_equals(ADMIN_PASSWORD_HASH, hash('sha256', $input_pass))) {
        $_SESSION['bat_admin_logged_in'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    $login_error = 'Sai tên đăng nhập hoặc mật khẩu.';
}

if (!isset($_SESSION['bat_admin_logged_in'])) {
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <title>BAT Admin — Đăng nhập</title>
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
            .login-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 360px; }
            h2 { color: #0a2540; margin-bottom: 24px; text-align: center; font-size: 1.3rem; }
            label { display: block; margin-bottom: 6px; font-size: 0.85rem; color: #555; font-weight: 600; }
            input[type=text], input[type=password] { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem; margin-bottom: 16px; }
            button { width: 100%; padding: 12px; background: #0a2540; color: white; border: none; border-radius: 6px; font-size: 1rem; font-weight: bold; cursor: pointer; }
            button:hover { background: #d4af37; }
            .error { color: #e74c3c; font-size: 0.85rem; margin-bottom: 14px; text-align: center; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>BAT Admin Panel</h2>
            <?php if ($login_error): ?>
                <p class="error"><?= htmlspecialchars($login_error) ?></p>
            <?php endif; ?>
            <form method="POST">
                <label>Tên đăng nhập</label>
                <input type="text" name="bat_login_username" required autofocus>
                <label>Mật khẩu</label>
                <input type="password" name="bat_login_password" required>
                <button type="submit">Đăng nhập</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ============================================================
//  ADMIN ACTIONS (chỉ chạy khi đã đăng nhập)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_product') {
        $stmt = $db->prepare('INSERT INTO products (name, price, description, remaining_quantity) VALUES (:name, :price, :description, :qty)');
        $stmt->bindValue(':name',        $_POST['name']);
        $stmt->bindValue(':price',       $_POST['price']);
        $stmt->bindValue(':description', $_POST['description']);
        $stmt->bindValue(':qty',         $_POST['remaining_quantity']);
        $stmt->execute();

    } elseif ($action === 'delete_product') {
        $stmt = $db->prepare('DELETE FROM products WHERE id=:id');
        $stmt->bindValue(':id', $_POST['id']);
        $stmt->execute();

    } elseif ($action === 'delete_customer') {
        $stmt = $db->prepare('DELETE FROM customers WHERE id=:id');
        $stmt->bindValue(':id', $_POST['id']);
        $stmt->execute();

    } elseif ($action === 'add_order') {
        $stmt = $db->prepare('INSERT INTO orders (customer_id, product_id, amount, status, purchase_date) VALUES (:cid, :pid, :amount, :status, :date)');
        $stmt->bindValue(':cid',    $_POST['customer_id']);
        $stmt->bindValue(':pid',    $_POST['product_id']);
        $stmt->bindValue(':amount', $_POST['amount']);
        $stmt->bindValue(':status', $_POST['status']);
        $stmt->bindValue(':date',   date('d/m/Y H:i'));
        $stmt->execute();

        $stmt2 = $db->prepare('UPDATE products SET remaining_quantity = remaining_quantity - 1 WHERE id = :id AND remaining_quantity > 0');
        $stmt2->bindValue(':id', (int)$_POST['product_id'], SQLITE3_INTEGER);
        $stmt2->execute();

    } elseif ($action === 'delete_order') {
        $stmt = $db->prepare('DELETE FROM orders WHERE id=:id');
        $stmt->bindValue(':id', $_POST['id']);
        $stmt->execute();

    } elseif ($action === 'complete_order') {
        $order_id = (int)$_POST['id'];

        $stmt = $db->prepare("UPDATE orders SET status = 'Đã thanh toán', purchase_date = :date WHERE id = :id AND status = 'Pending'");
        $stmt->bindValue(':id',   $order_id);
        $stmt->bindValue(':date', date('d/m/Y H:i'));
        $stmt->execute();

        if ($db->changes() > 0) {
            $stmt_info = $db->prepare("SELECT o.amount, c.name as c_name, c.email as c_email, p.name as p_name, p.id as p_id FROM orders o JOIN customers c ON o.customer_id = c.id JOIN products p ON o.product_id = p.id WHERE o.id = :id LIMIT 1");
            $stmt_info->bindValue(':id', $order_id);
            $res_info = $stmt_info->execute();
            if ($row_info = $res_info->fetchArray(SQLITE3_ASSOC)) {
                $c_email  = $row_info['c_email'];
                $c_name   = $row_info['c_name'];
                $p_name   = $row_info['p_name'];
                $amount   = $row_info['amount'];
                $p_id     = $row_info['p_id'];

                $stmt3 = $db->prepare('UPDATE products SET remaining_quantity = remaining_quantity - 1 WHERE id = :id AND remaining_quantity > 0');
                $stmt3->bindValue(':id', $p_id, SQLITE3_INTEGER);
                $stmt3->execute();

                if (!empty($c_email)) {
                    $subject = "Xác nhận thanh toán thành công - " . $p_name;
                    $body    = "<h2>Xin chào " . htmlspecialchars($c_name) . ",</h2>
                                <p>Hệ thống đã xác nhận đơn hàng <strong>" . ($amount > 0 ? number_format($amount, 0, ',', '.') . " VNĐ" : "của bạn") . "</strong> cho sản phẩm: <strong>" . htmlspecialchars($p_name) . "</strong>.</p>
                                <p>Chúc mừng bạn đã chính thức tham gia khóa học. Đây là mã đơn hàng của bạn: #" . $order_id . "</p>
                                <p>Ban tổ chức sẽ liên hệ hỗ trợ bạn tham gia vào lớp học trong thời gian sớm nhất.</p>
                                <br><p>Trân trọng,<br>Lumina Global Team</p>";
                    send_resend_email($c_email, $subject, $body);
                }
            }
        }
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// ============================================================
//  Lấy dữ liệu cho HTML
// ============================================================
$products  = [];
$res = $db->query('SELECT * FROM products');
while ($row = $res->fetchArray(SQLITE3_ASSOC)) { $products[] = $row; }

$customers = [];
$res = $db->query('SELECT * FROM customers ORDER BY id DESC');
while ($row = $res->fetchArray(SQLITE3_ASSOC)) { $customers[] = $row; }

$orders = [];
$res    = $db->query("SELECT o.*, c.name as customer_name, p.name as product_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id LEFT JOIN products p ON o.product_id = p.id ORDER BY o.id DESC");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) { $orders[] = $row; }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>BAT Admin Panel</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7f6; margin: 0; padding: 0; }
        .header { background: #0a2540; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .header h2 { margin: 0; font-size: 1.1rem; }
        .btn-logout { background: transparent; border: 1px solid rgba(255,255,255,0.4); color: white; padding: 6px 14px; border-radius: 4px; cursor: pointer; font-size: 0.85rem; }
        .btn-logout:hover { background: rgba(255,255,255,0.1); }
        .container { max-width: 1000px; margin: 20px auto; background: white; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; }
        .tabs { display: flex; background: #eef2f5; border-bottom: 1px solid #ddd; }
        .tab { padding: 15px 25px; cursor: pointer; font-weight: bold; color: #555; transition: 0.3s; }
        .tab:hover { background: #e0e6ed; }
        .tab.active { background: white; color: #d4af37; border-bottom: 3px solid #d4af37; }
        .tab-content { padding: 20px; display: none; }
        .tab-content.active { display: block; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; color: #333; }
        .btn { padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; transition: 0.2s; text-decoration: none; display: inline-block; }
        .btn-primary { background: #d4af37; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .card { background: #fdfdfd; border: 1px solid #eee; padding: 20px; border-radius: 6px; margin-bottom: 20px; }
        h3 { margin-top: 0; color: #0a2540; }
    </style>
    <script>
        function showTab(id) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.getElementById('tab-' + id).classList.add('active');
            document.getElementById('content-' + id).classList.add('active');
        }
    </script>
</head>
<body>
    <div class="header">
        <h2>Becoming A Trainer — Admin Panel</h2>
        <form method="POST" style="margin:0;">
            <button type="submit" name="bat_logout" value="1" class="btn-logout">Đăng xuất</button>
        </form>
    </div>

    <div class="container">
        <div class="tabs">
            <div class="tab active" id="tab-products"  onclick="showTab('products')">Sản Phẩm</div>
            <div class="tab"        id="tab-customers" onclick="showTab('customers')">Khách Hàng</div>
            <div class="tab"        id="tab-orders"    onclick="showTab('orders')">Đơn Hàng</div>
        </div>

        <!-- SẢN PHẨM -->
        <div class="tab-content active" id="content-products">
            <div class="card">
                <h3>Thêm Sản Phẩm Mới</h3>
                <form action="" method="POST">
                    <input type="hidden" name="action" value="add_product">
                    <div class="form-group"><label>Tên Sản Phẩm</label><input type="text" name="name" required></div>
                    <div class="form-group"><label>Giá (VNĐ)</label><input type="number" name="price" required></div>
                    <div class="form-group"><label>Mô tả</label><input type="text" name="description"></div>
                    <div class="form-group"><label>Số lượng</label><input type="number" name="remaining_quantity" required></div>
                    <button type="submit" class="btn btn-primary">Thêm Sản Phẩm</button>
                </form>
            </div>
            <h3>Danh sách Sản Phẩm</h3>
            <table>
                <tr><th>ID</th><th>Tên</th><th>Giá</th><th>Mô tả</th><th>Còn lại</th><th>Hành động</th></tr>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= number_format($p['price'], 0, ',', '.') ?> đ</td>
                    <td><?= htmlspecialchars($p['description']) ?></td>
                    <td><b><?= $p['remaining_quantity'] ?></b></td>
                    <td>
                        <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Xóa sản phẩm này?');">
                            <input type="hidden" name="action" value="delete_product">
                            <input type="hidden" name="id"     value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-danger">Xóa</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- KHÁCH HÀNG -->
        <div class="tab-content" id="content-customers">
            <div class="card">
                <h3>Thêm Khách Hàng</h3>
                <form action="" method="POST">
                    <input type="hidden" name="action" value="add_customer">
                    <div class="form-group"><label>Họ Tên</label><input type="text" name="name" required></div>
                    <div class="form-group"><label>Số điện thoại</label><input type="text" name="phone" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email"></div>
                    <div class="form-group"><label>Zalo</label><input type="text" name="zalo"></div>
                    <button type="submit" class="btn btn-primary">Thêm Khách Hàng</button>
                </form>
            </div>
            <h3>Danh sách Khách Hàng</h3>
            <table>
                <tr><th>ID</th><th>Tên</th><th>SĐT</th><th>Email</th><th>Zalo</th><th>Ngày ĐK</th><th>Hành động</th></tr>
                <?php foreach ($customers as $c): ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= htmlspecialchars($c['phone']) ?></td>
                    <td><?= htmlspecialchars($c['email'] ?? '') ?></td>
                    <td><?= htmlspecialchars($c['zalo']) ?></td>
                    <td><?= $c['registration_date'] ?></td>
                    <td>
                        <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Xóa khách hàng này?');">
                            <input type="hidden" name="action" value="delete_customer">
                            <input type="hidden" name="id"     value="<?= $c['id'] ?>">
                            <button type="submit" class="btn btn-danger">Xóa</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- ĐƠN HÀNG -->
        <div class="tab-content" id="content-orders">
            <div class="card">
                <h3>Tạo Đơn Hàng Mới</h3>
                <form action="" method="POST">
                    <input type="hidden" name="action" value="add_order">
                    <div class="form-group">
                        <label>Khách Hàng</label>
                        <select name="customer_id" required>
                            <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['phone']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sản Phẩm</label>
                        <select name="product_id" required>
                            <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> — Còn: <?= $p['remaining_quantity'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Số tiền thanh toán</label><input type="number" name="amount" required></div>
                    <div class="form-group">
                        <label>Trạng thái</label>
                        <select name="status">
                            <option value="Đã thanh toán">Đã thanh toán</option>
                            <option value="Chưa thanh toán">Chưa thanh toán</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Tạo Đơn Hàng</button>
                </form>
            </div>
            <h3>Danh sách Đơn Hàng</h3>
            <table>
                <tr><th>ID</th><th>Khách Hàng</th><th>Sản Phẩm</th><th>Số tiền</th><th>Trạng thái</th><th>Ngày mua</th><th>Hành động</th></tr>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?= $o['id'] ?></td>
                    <td><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td><?= htmlspecialchars($o['product_name']) ?></td>
                    <td><?= number_format($o['amount'], 0, ',', '.') ?> đ</td>
                    <td><span style="color: <?= $o['status'] === 'Đã thanh toán' ? 'green' : 'orange' ?>;"><?= $o['status'] ?></span></td>
                    <td><?= $o['purchase_date'] ?></td>
                    <td>
                        <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Xóa đơn hàng này?');">
                            <input type="hidden" name="action" value="delete_order">
                            <input type="hidden" name="id"     value="<?= $o['id'] ?>">
                            <button type="submit" class="btn btn-danger">Xóa</button>
                        </form>
                        <?php if ($o['status'] === 'Pending'): ?>
                        <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận đơn hàng này đã thanh toán và gửi email xác nhận?');">
                            <input type="hidden" name="action" value="complete_order">
                            <input type="hidden" name="id"     value="<?= $o['id'] ?>">
                            <button type="submit" class="btn" style="background:#28a745; color:white;">Xác nhận</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>
