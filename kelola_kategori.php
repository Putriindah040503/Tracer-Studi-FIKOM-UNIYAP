<?php
include 'koneksi.php';
$notif = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $nama = $_POST['nama_kategori'];
    $deskripsi = $_POST['deskripsi'];

    if ($id) {
        $stmt = $conn->prepare("UPDATE kuesioner_kategori SET nama_kategori=?, deskripsi=? WHERE id=?");
        $stmt->bind_param("ssi", $nama, $deskripsi, $id);
        $stmt->execute();
        $notif = "Kategori diperbarui.";
    } else {
        $stmt = $conn->prepare("INSERT INTO kuesioner_kategori (nama_kategori, deskripsi) VALUES (?, ?)");
        $stmt->bind_param("ss", $nama, $deskripsi);
        $stmt->execute();
        $notif = "Kategori ditambahkan.";
    }
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM kuesioner_kategori WHERE id = $id");
    $notif = "Kategori dihapus.";
}

$data = $conn->query("SELECT * FROM kuesioner_kategori");
?>

<h3>Manajemen Kategori</h3>
<?php if ($notif) echo "<div style='background:#d4edda;padding:10px'>$notif</div>"; ?>

<form method="POST">
    <input type="hidden" name="id" id="id">
    Nama: <input type="text" name="nama_kategori" required><br>
    Deskripsi: <input type="text" name="deskripsi"><br>
    <button type="submit">Simpan</button>
</form>

<table border="1" cellpadding="5">
    <tr><th>#</th><th>Nama</th><th>Deskripsi</th><th>Aksi</th></tr>
    <?php $no=1; while($row = $data->fetch_assoc()): ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><?= $row['nama_kategori'] ?></td>
        <td><?= $row['deskripsi'] ?></td>
        <td>
            <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Hapus?')">Hapus</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
