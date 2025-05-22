<?php

require 'vendor/autoload.php'; // AWS SDK dan predis

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Predis\Client as PredisClient;

// Config PostgreSQL
$pg_host = 'localhost';
$pg_port = 5432;
$pg_dbname = 'fullstack';
$pg_user = 'postgres';
$pg_pass = '12345678';

// Config Redis
$redis_host = '127.0.0.1';
$redis_port = 6379;

// Config AWS S3
$s3_bucket = 'my-client-bucket';
$s3_region = 'ap-southeast-1';  //contoh: us-east-1
$s3_key = 'your-aws-access-key';
$s3_secret = 'your-aws-secret-key';

// Buat koneksi PDO PostgreSQL
function getPgConnection() {
    global $pg_host, $pg_port, $pg_dbname, $pg_user, $pg_pass;
    $dsn = "pgsql:host=$pg_host;port=$pg_port;dbname=$pg_dbname;";
    try {
        $pdo = new PDO($dsn, $pg_user, $pg_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        return $pdo;
    } catch (PDOException $e) {
        die("Koneksi PostgreSQL gagal: " . $e->getMessage());
    }
}

// Buat koneksi Redis Predis
function getRedisConnection() {
    global $redis_host, $redis_port;
    return new PredisClient([
        'scheme' => 'tcp',
        'host'   => $redis_host,
        'port'   => $redis_port,
    ]);
}

// Buat koneksi AWS S3
function getS3Client() {
    global $s3_region, $s3_key, $s3_secret;
    return new S3Client([
        'region' => $s3_region,
        'version' => 'latest',
        'credentials' => [
            'key'    => $s3_key,
            'secret' => $s3_secret,
        ],
    ]);
}

// Fungsi upload gambar ke S3, mengembalikan URL image
function uploadImageToS3($file_tmp_path, $file_name) {
    global $s3_bucket;
    $s3 = getS3Client();
    try {
        $result = $s3->putObject([
            'Bucket' => $s3_bucket,
            'Key'    => 'client_logos/' . basename($file_name),
            'SourceFile' => $file_tmp_path,
            'ACL'    => 'public-read',
            'ContentType' => mime_content_type($file_tmp_path),
        ]);
        return $result['ObjectURL'];
    } catch (AwsException $e) {
        echo "Gagal upload gambar ke S3: " . $e->getMessage();
        return null;
    }
}

// Fungsi simpan data client baru
function createClient($data, $file) {
    $pdo = getPgConnection();
    $redis = getRedisConnection();

    // upload gambar ke S3
    $img_url = 'no-image.jpg';
    if ($file && isset($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
        $upload_url = uploadImageToS3($file['tmp_name'], $file['name']);
        if ($upload_url !== null) {
            $img_url = $upload_url;
        }
    }

    $sql = "INSERT INTO my_client 
            (name, slug, is_project, self_capture, client_prefix, client_logo, address, phone_number, city, created_at, updated_at) 
            VALUES (:name, :slug, :is_project, :self_capture, :client_prefix, :client_logo, :address, :phone_number, :city, NOW(), NOW())
            RETURNING *";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => $data['name'],
        ':slug' => $data['slug'],
        ':is_project' => $data['is_project'] ?? '0',
        ':self_capture' => $data['self_capture'] ?? '1',
        ':client_prefix' => $data['client_prefix'],
        ':client_logo' => $img_url,
        ':address' => $data['address'] ?? null,
        ':phone_number' => $data['phone_number'] ?? null,
        ':city' => $data['city'] ?? null,
    ]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    // Simpan ke Redis dengan key = slug
    $redisKey = $client['slug'];
    $redis->set($redisKey, json_encode($client));

    return $client;
}

// Fungsi membaca data client berdasarkan slug
function readClient($slug) {
    $pdo = getPgConnection();
    $redis = getRedisConnection();

    $redisKey = $slug;
    $cached = $redis->get($redisKey);
    if ($cached) {
        return json_decode($cached, true);
    }

    $sql = "SELECT * FROM my_client WHERE slug = :slug AND deleted_at IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':slug' => $slug]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($client) {
        $redis->set($redisKey, json_encode($client));
    }
    return $client;
}

// Fungsi update client
function updateClient($slug, $data, $file = null) {
    $pdo = getPgConnection();
    $redis = getRedisConnection();

    // Ambil data lama dulu
    $oldClient = readClient($slug);
    if (!$oldClient) {
        return null; // Tidak ditemukan
    }

    $img_url = $oldClient['client_logo'];
    // Jika ada file baru, upload ke S3
    if ($file && isset($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
        $upload_url = uploadImageToS3($file['tmp_name'], $file['name']);
        if ($upload_url !== null) {
            $img_url = $upload_url;
        }
    }

    $sql = "UPDATE my_client SET
                name = :name,
                is_project = :is_project,
                self_capture = :self_capture,
                client_prefix = :client_prefix,
                client_logo = :client_logo,
                address = :address,
                phone_number = :phone_number,
                city = :city,
                updated_at = NOW()
            WHERE slug = :slug AND deleted_at IS NULL
            RETURNING *";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => $data['name'] ?? $oldClient['name'],
        ':is_project' => $data['is_project'] ?? $oldClient['is_project'],
        ':self_capture' => $data['self_capture'] ?? $oldClient['self_capture'],
        ':client_prefix' => $data['client_prefix'] ?? $oldClient['client_prefix'],
        ':client_logo' => $img_url,
        ':address' => $data['address'] ?? $oldClient['address'],
        ':phone_number' => $data['phone_number'] ?? $oldClient['phone_number'],
        ':city' => $data['city'] ?? $oldClient['city'],
        ':slug' => $slug,
    ]);
    $updatedClient = $stmt->fetch(PDO::FETCH_ASSOC);

    // Hapus cache lama di Redis dan set cache baru
    $redisKey = $slug;
    $redis->del($redisKey);
    $redis->set($redisKey, json_encode($updatedClient));

    return $updatedClient;
}

// Fungsi soft delete client
function deleteClient($slug) {
    $pdo = getPgConnection();
    $redis = getRedisConnection();

    $sql = "UPDATE my_client SET deleted_at = NOW() WHERE slug = :slug AND deleted_at IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':slug' => $slug]);

    // Hapus cache Redis
    $redis->del($slug);

    return $stmt->rowCount() > 0;
}

// Contoh implementasi penggunaan fungsi-fungsi di atas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'create') {
        $data = $_POST;
        $file = $_FILES['client_logo'] ?? null;
        $result = createClient($data, $file);
        echo json_encode(['status'=>'created', 'data'=>$result]);
    } else if ($action === 'read') {
        $slug = $_POST['slug'] ?? '';
        $result = readClient($slug);
        echo json_encode(['status'=>'read', 'data'=>$result]);
    } else if ($action === 'update') {
        $slug = $_POST['slug'] ?? '';
        $data = $_POST;
        $file = $_FILES['client_logo'] ?? null;
        $result = updateClient($slug, $data, $file);
        echo json_encode(['status'=>'updated', 'data'=>$result]);
    } else if ($action === 'delete') {
        $slug = $_POST['slug'] ?? '';
        $success = deleteClient($slug);
        echo json_encode(['status' => $success ? 'deleted' : 'not found']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Client</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Form Data Client</h2>
    <form method="POST" enctype="multipart/form-data" class="mt-4">
        <input type="hidden" name="action" value="create">
        <div class="mb-3">
            <label for="name" class="form-label">Nama Client</label>
            <input type="text" class="form-control" name="name" id="name" placeholder="Nama Client" required>
        </div>
        <div class="mb-3">
            <label for="slug" class="form-label">Slug Client</label>
            <input type="text" class="form-control" name="slug" id="slug" placeholder="Slug Client" required>
        </div>
        <div class="mb-3">
            <label for="is_project" class="form-label">Is Project</label>
            <select class="form-select" name="is_project" id="is_project">
                <option value="0">0</option>
                <option value="1">1</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="self_capture" class="form-label">Self Capture (char 1)</label>
            <input type="text" class="form-control" name="self_capture" id="self_capture" value="1" required>
        </div>
        <div class="mb-3">
            <label for="client_prefix" class="form-label">Client Prefix (4 chars)</label>
            <input type="text" class="form-control" name="client_prefix" id="client_prefix" placeholder="Client Prefix" required>
        </div>
        <div class="mb-3">
            <label for="client_logo" class="form-label">Logo Client</label>
            <input type="file" class="form-control" name="client_logo" id="client_logo" accept="image/*">
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Alamat</label>
            <textarea class="form-control" name="address" id="address" placeholder="Alamat"></textarea>
        </div>
        <div class="mb-3">
            <label for="phone_number" class="form-label">Nomor Telepon</label>
            <input type="text" class="form-control" name="phone_number" id="phone_number" placeholder="Nomor Telepon">
        </div>
        <div class="mb-3">
            <label for="city" class="form-label">Kota</label>
            <input type="text" class="form-control" name="city" id="city" placeholder="Kota">
        </div>
        <button type="submit" class="btn btn-primary">Create Client</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
