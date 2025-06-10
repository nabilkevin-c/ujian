<?php
// Database connection parameters
$host = "localhost";
$dbname = "supermarket_db";
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS products (
    kode_produk VARCHAR(50) PRIMARY KEY,
    nama_produk VARCHAR(255) NOT NULL,
    harga_produk DECIMAL(12,2) NOT NULL,
    satuan_produk VARCHAR(50) NOT NULL,
    nama_supplier VARCHAR(255) NOT NULL,
    persediaan INT NOT NULL,
    deskripsi TEXT
)");

// Handle Create or Update Product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_produk = $conn->real_escape_string($_POST['kode_produk']);
    $nama_produk = $conn->real_escape_string($_POST['nama_produk']);
    $harga_produk = floatval($_POST['harga_produk']);
    $satuan_produk = $conn->real_escape_string($_POST['satuan_produk']);
    $nama_supplier = $conn->real_escape_string($_POST['nama_supplier']);
    $persediaan = intval($_POST['persediaan']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);

    if (isset($_POST['action'])) {
        // Create new
        if ($_POST['action'] === 'create') {
            // Check if kode_produk exists
            $check = $conn->query("SELECT * FROM products WHERE kode_produk='$kode_produk'");
            if ($check->num_rows > 0) {
                $error = "Kode produk sudah ada, gunakan kode lain.";
            } else {
                $sql = "INSERT INTO products (kode_produk, nama_produk, harga_produk, satuan_produk, nama_supplier, persediaan, deskripsi)
                VALUES ('$kode_produk', '$nama_produk', $harga_produk, '$satuan_produk', '$nama_supplier', $persediaan, '$deskripsi')";
                if (!$conn->query($sql)) {
                    $error = "Gagal menambah produk: " . $conn->error;
                }
            }
        }
        // Update existing
        else if ($_POST['action'] === 'update') {
            $sql = "UPDATE products SET 
                nama_produk='$nama_produk',
                harga_produk=$harga_produk,
                satuan_produk='$satuan_produk',
                nama_supplier='$nama_supplier',
                persediaan=$persediaan,
                deskripsi='$deskripsi'
                WHERE kode_produk='$kode_produk'";
            if (!$conn->query($sql)) {
                $error = "Gagal memperbarui produk: " . $conn->error;
            }
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $kode_produk = $conn->real_escape_string($_GET['delete']);
    $conn->query("DELETE FROM products WHERE kode_produk='$kode_produk'");
    header("Location: " . strtok($_SERVER["REQUEST_URI"],'?'));
    exit;
}

// Fetch products
$result = $conn->query("SELECT * FROM products ORDER BY kode_produk ASC");

// For Edit
$edit_product = null;
if (isset($_GET['edit'])) {
    $kode_produk = $conn->real_escape_string($_GET['edit']);
    $res = $conn->query("SELECT * FROM products WHERE kode_produk='$kode_produk'");
    if ($res && $res->num_rows === 1) {
        $edit_product = $res->fetch_assoc();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>CRUD Produk Supermarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container my-5">
    <h1 class="mb-4 text-center">Manajemen Produk Supermarket</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Form for Create or Update -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <?= $edit_product ? "Edit Produk" : "Tambah Produk Baru" ?>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="action" value="<?= $edit_product ? 'update' : 'create' ?>" />
                <div class="mb-3">
                    <label for="kode_produk" class="form-label">Kode Produk</label>
                    <input type="text" class="form-control" id="kode_produk" name="kode_produk" required
                        value="<?= $edit_product ? htmlspecialchars($edit_product['kode_produk']) : '' ?>"
                        <?= $edit_product ? 'readonly' : '' ?>>
                </div>
                <div class="mb-3">
                    <label for="nama_produk" class="form-label">Nama Produk</label>
                    <input type="text" class="form-control" id="nama_produk" name="nama_produk" required
                    value="<?= $edit_product ? htmlspecialchars($edit_product['nama_produk']) : '' ?>">
                </div>
                <div class="mb-3">
                    <label for="harga_produk" class="form-label">Harga Produk (Rp)</label>
                    <input type="number" step="0.01" class="form-control" id="harga_produk" name="harga_produk" required min="0"
                    value="<?= $edit_product ? htmlspecialchars($edit_product['harga_produk']) : '' ?>">
                </div>
                <div class="mb-3">
                    <label for="satuan_produk" class="form-label">Satuan Produk</label>
                    <select class="form-select" id="satuan_produk" name="satuan_produk" required>
                        <?php
                        $satuan_options = ["box", "pcs", "kg", "liter", "botol"];
                        foreach ($satuan_options as $option) {
                            $selected = ($edit_product && $edit_product['satuan_produk'] === $option) ? "selected" : "";
                            echo "<option value=\"$option\" $selected>$option</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="nama_supplier" class="form-label">Nama Supplier</label>
                    <input type="text" class="form-control" id="nama_supplier" name="nama_supplier" required
                    value="<?= $edit_product ? htmlspecialchars($edit_product['nama_supplier']) : '' ?>">
                </div>
                <div class="mb-3">
                    <label for="persediaan" class="form-label">Persediaan</label>
                    <input type="number" class="form-control" id="persediaan" name="persediaan" required min="0"
                    value="<?= $edit_product ? htmlspecialchars($edit_product['persediaan']) : '' ?>">
                </div>
                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi Singkat</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= $edit_product ? htmlspecialchars($edit_product['deskripsi']) : '' ?></textarea>
                </div>
                <button type="submit" class="btn btn-success"><?= $edit_product ? "Update Produk" : "Tambah Produk" ?></button>
                <?php if ($edit_product): ?>
                    <a href="<?= strtok($_SERVER["REQUEST_URI"],'?') ?>" class="btn btn-secondary ms-2">Batal</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Table of products -->
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">Daftar Produk</div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th>Harga (Rp)</th>
                        <th>Satuan</th>
                        <th>Supplier</th>
                        <th>Persediaan</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['kode_produk']) ?></td>
                        <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                        <td><?= number_format($row['harga_produk'], 2, ',', '.') ?></td>
                        <td><?= htmlspecialchars($row['satuan_produk']) ?></td>
                        <td><?= htmlspecialchars($row['nama_supplier']) ?></td>
                        <td><?= intval($row['persediaan']) ?></td>
                        <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                        <td>
                            <a href="?edit=<?= urlencode($row['kode_produk']) ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="?delete=<?= urlencode($row['kode_produk']) ?>" onclick="return confirm('Yakin ingin menghapus produk ini?');" class="btn btn-sm btn-danger">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada produk.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

