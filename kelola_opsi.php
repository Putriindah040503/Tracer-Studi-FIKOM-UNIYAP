<?php
include 'koneksi.php';
$pertanyaan_id = $_GET['id'];
$pertanyaan = $conn->query("SELECT * FROM kuesioner_pertanyaan WHERE id = $pertanyaan_id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $opsi_text = $_POST['opsi_text'];
    $opsi_value = $_POST['opsi_value'];
    $urutan = $_POST['urutan'];
    $stmt = $conn->prepare("INSERT INTO kuesioner_opsi_jawaban (pertanyaan_id, opsi_text, opsi_value, urutan) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $pertanyaan_id, $opsi_text, $opsi_value, $urutan);
    $stmt->execute();
}

// Hapus opsi
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM kuesioner_opsi_jawaban WHERE id = $id");
}

$opsi = $conn->query("SELECT * FROM kuesioner_opsi_jawaban WHERE pertanyaan_id = $pertanyaan_id ORDER BY urutan");
?>

<h3>Kelola Opsi: <?= $pertanyaan['pertanyaan'] ?></h3>

<form method="POST">
    Teks Opsi: <input type="text" name="opsi_text" required>
    Nilai: <input type="text" name="opsi_value" required>
    Urutan: <input type="number" name="urutan" value="1">
    <button type="submit">Tambah</button>
</form>

<table>
    <tr><th>#</th><th>Opsi</th><th>Value</th><th>Urutan</th><th>Aksi</th></tr>
    <?php $no = 1; while($r = $opsi->fetch_assoc()): ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><?= $r['opsi_text'] ?></td>
        <td><?= $r['opsi_value'] ?></td>
        <td><?= $r['urutan'] ?></td>
        <td><a href="?id=<?= $pertanyaan_id ?>&hapus=<?= $r['id'] ?>" onclick="return confirm('Hapus opsi ini?')">Hapus</a></td>
    </tr>
    <?php endwhile; ?>
</table>
