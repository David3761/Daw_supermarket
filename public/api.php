<?php
session_start();
require 'db_connect.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: Content-Type");

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

function parsePrice($priceStr) {
    return floatval(preg_replace('/[^0-9.]/', '', $priceStr));
}

if ($method === 'GET') {
    if ($action === 'products') {
        $search = $_GET['search'] ?? '';
        $sql = "SELECT * FROM products WHERE name LIKE ? AND (is_available = 1 OR is_available IS NULL)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["%$search%"]);
        $products = $stmt->fetchAll();
        $formatted = array_map(function($p) {
            return [
                "id" => $p['id'],
                "name" => $p['name'],
                "price" => $p['price'],
                "image_urls" => [$p['image_url'] ?? $p['image'] ?? '']
            ];
        }, $products);
        echo json_encode($formatted);
        exit;
    }

    if ($action === 'cart') {
        echo json_encode($_SESSION['cart'] ?? []);
        exit;
    }

    if ($action === 'users') {
        if (($_SESSION['role'] ?? 'user') !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        $stmt = $pdo->query("SELECT id, email, role, is_banned FROM users");
        echo json_encode($stmt->fetchAll());
        exit;
    }
    
    if ($action === 'check_auth') {
        echo json_encode(['logged_in' => isset($_SESSION['user'])]);
        exit;
    }
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if ($action === 'login') {
        $email = $input['email'];
        $password = $input['password'];
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_banned'] == 1) {
                echo json_encode(['success' => false, 'message' => 'This account has been banned.']);
                exit;
            }
            $_SESSION['user'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            echo json_encode(['success' => true, 'role' => $user['role']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        }
        exit;
    }

    if ($action === 'register') {
        $email = $input['email'];
        $password = $input['password'];

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }

        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        if ($stmt->execute([$email, $hashedPassword])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
        exit;
    }

    if ($action === 'add_cart') {
        $product = $input['product'];
        $id = $product['id'] ?? 0;

        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND (is_available = 1 OR is_available IS NULL)");
        $stmt->execute([$id]);
        $dbProduct = $stmt->fetch();

        if (!$dbProduct) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }
        
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['qty']++;
        } else {
            $_SESSION['cart'][$id] = [
                'id' => $id,
                'name' => $dbProduct['name'],
                'price' => $dbProduct['price'],
                'image' => $dbProduct['image_url'],
                'qty' => 1
            ];
        }
        echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
        exit;
    }

    if ($action === 'update_cart') {
        $id = $input['id'];
        $qty = $input['qty'];
        
        if ($qty <= 0) {
            unset($_SESSION['cart'][$id]);
        } elseif (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['qty'] = $qty;
        }
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'place_order') {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'Please login to place an order']);
            exit;
        }
        
        if (empty($_SESSION['cart'])) {
            echo json_encode(['success' => false, 'message' => 'Cart is empty']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT is_banned FROM users WHERE email = ?");
        $stmt->execute([$_SESSION['user']]);
        $user = $stmt->fetch();

        if ($user && $user['is_banned'] == 1) {
            echo json_encode(['success' => false, 'message' => 'Your account is banned. You cannot place orders.']);
            exit;
        }

        $paymentMethod = $input['payment_method'] ?? 'Card';

        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += parsePrice($item['price']) * $item['qty'];
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO orders (user_email, total_price) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user'], $total]);
            
            $orderId = $pdo->lastInsertId();

            $sqlItems = "INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)";
            $stmtItems = $pdo->prepare($sqlItems);

            foreach ($_SESSION['cart'] as $item) {
                $stmtItems->execute([$orderId, $item['id'], $item['qty']]);
            }

            $sqlPayment = "INSERT INTO payments (order_id, payment_method, amount, status) VALUES (?, ?, ?, ?)";
            $stmtPayment = $pdo->prepare($sqlPayment);
            $stmtPayment->execute([$orderId, $paymentMethod, $total, 'completed']);

            unset($_SESSION['cart']);
            echo json_encode(['success' => true]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Order failed: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'add_product') {
        if (($_SESSION['role'] ?? 'user') !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        $name = $input['name'];
        $price = $input['price'];
        $image = $input['image'];

        $stmt = $pdo->prepare("INSERT INTO products (name, price, image_url) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $price, $image])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add product']);
        }
        exit;
    }

    if ($action === 'delete_product') {
        if (($_SESSION['role'] ?? 'user') !== 'admin') exit;
        $id = $input['id'];
        $stmt = $pdo->prepare("UPDATE products SET is_available = 0 WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'ban_user') {
        if (($_SESSION['role'] ?? 'user') !== 'admin') exit;
        $id = $input['id'];
        $is_banned = $input['is_banned'] ? 1 : 0;
        
        $stmt = $pdo->prepare("UPDATE users SET is_banned = ? WHERE id = ?");
        $stmt->execute([$is_banned, $id]);
        echo json_encode(['success' => true]);
        exit;
    }
}
?>