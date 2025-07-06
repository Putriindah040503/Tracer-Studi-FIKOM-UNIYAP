<?php
session_start();
include 'koneksi.php';

// Check alumni authentication
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'alumni') {
    header("Location: login.php");
    exit;
}

$alumni_id = $_SESSION['id'];

// Cek apakah alumni sudah mengisi kuesioner
$cekJawaban = mysqli_query($conn, "SELECT COUNT(*) as total FROM kuesioner_jawaban WHERE alumni_id = $alumni_id");
$sudahIsi = mysqli_fetch_assoc($cekJawaban)['total'] > 0;

// Ambil data kuesioner yang aktif
$query = "
    SELECT kp.id, kp.pertanyaan, kk.nama_kategori AS kategori, kp.tipe_input, kp.wajib, kp.urutan
    FROM kuesioner_pertanyaan kp
    JOIN kuesioner_kategori kk ON kp.kategori_id = kk.id
    WHERE kp.aktif = 1
    ORDER BY kp.urutan ASC
";

$result = mysqli_query($conn, $query);
$kuesionerData = [];

while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['id'];
    $opsi = [];
    
    if (in_array($row['tipe_input'], ['radio', 'checkbox', 'select'])) {
        $resOpsi = mysqli_query($conn, "SELECT id, opsi_text, opsi_value FROM kuesioner_opsi_jawaban WHERE pertanyaan_id = $id");
        while ($r = mysqli_fetch_assoc($resOpsi)) {
            $opsi[] = [
                'id' => $r['id'],
                'text' => $r['opsi_text'],
                'value' => $r['opsi_value']
            ];
        }
    }

    $kuesionerData[] = [
        'id' => $id,
        'pertanyaan' => $row['pertanyaan'],
        'kategori' => $row['kategori'],
        'tipe_input' => $row['tipe_input'],
        'wajib' => (bool)$row['wajib'],
        'urutan' => (int)$row['urutan'],
        'opsi' => $opsi
    ];
}

// Jika sudah mengisi, ambil jawaban sebelumnya
$jawabanSebelumnya = [];
if ($sudahIsi) {
    $queryJawaban = "SELECT pertanyaan_id, jawaban, opsi_id FROM kuesioner_jawaban WHERE alumni_id = $alumni_id";
    $resultJawaban = mysqli_query($conn, $queryJawaban);
    while ($row = mysqli_fetch_assoc($resultJawaban)) {
        $jawabanSebelumnya[$row['pertanyaan_id']] = [
            'text' => $row['jawaban'],
            'opsi_id' => $row['opsi_id']
        ];
    }
}

$nama_alumni = $_SESSION['alumni_nama'] ?? 'Alumni';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuesioner Tracer Study - Alumni</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .kuesioner-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .kuesioner-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .kuesioner-header {
            background: linear-gradient(135deg, #00509D, #003B70);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .kategori-section {
            border-left: 4px solid #00509D;
            background: #f8f9fa;
            padding: 1rem;
            margin: 1.5rem 0;
        }
        .question-item {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        .question-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .question-number {
            background: #00509D;
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .required-label {
            color: #dc3545;
            font-weight: bold;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 0.75rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #00509D;
            box-shadow: 0 0 0 0.2rem rgba(0, 80, 157, 0.25);
        }
        .btn-submit {
            background: linear-gradient(135deg, #00509D, #003B70);
            border: none;
            color: white;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #003B70, #002952);
            transform: translateY(-2px);
            color: white;
        }
        .progress-bar {
            background: linear-gradient(90deg, #00509D, #003B70);
        }
        .rating-stars {
            font-size: 1.5rem;
            color: #ddd;
            cursor: pointer;
        }
        .rating-stars.active {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="kuesioner-container">
        <div class="kuesioner-card">
            <div class="kuesioner-header">
                <h2><i class="fas fa-clipboard-list me-2"></i>Kuesioner Tracer Study</h2>
                <p class="mb-0">Selamat datang, <?= htmlspecialchars($nama_alumni) ?></p>
                <?php if ($sudahIsi): ?>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>Anda sudah mengisi kuesioner sebelumnya. Anda dapat memperbarui jawaban Anda.
                </div>
                <?php endif; ?>
            </div>

            <div class="p-4">
                <form id="kuesionerForm" onsubmit="submitKuesioner(event)">
                    <div class="progress mb-4">
                        <div class="progress-bar" role="progressbar" style="width: 0%" id="progressBar"></div>
                    </div>

                    <?php 
                    $currentKategori = '';
                    $questionNumber = 1;
                    foreach ($kuesionerData as $item): 
                        if ($currentKategori !== $item['kategori']):
                            if ($currentKategori !== '') echo '</div>';
                            $currentKategori = $item['kategori'];
                    ?>
                    <div class="kategori-section">
                        <h5 class="mb-3"><i class="fas fa-folder me-2"></i><?= ucfirst($item['kategori']) ?></h5>
                    <?php endif; ?>

                        <div class="question-item" data-required="<?= $item['wajib'] ? 'true' : 'false' ?>">
                            <div class="d-flex align-items-start mb-3">
                                <div class="question-number me-3"><?= $questionNumber ?></div>
                                <div class="flex-grow-1">
                                    <label class="form-label fw-bold">
                                        <?= htmlspecialchars($item['pertanyaan']) ?>
                                        <?php if ($item['wajib']): ?>
                                            <span class="required-label">*</span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <?php 
                            $jawabanLama = $jawabanSebelumnya[$item['id']] ?? null;
                            switch ($item['tipe_input']):
                                case 'text': ?>
                                    <input type="text" class="form-control" name="jawaban[<?= $item['id'] ?>]" 
                                           value="<?= $jawabanLama ? htmlspecialchars($jawabanLama['text']) : '' ?>"
                                           <?= $item['wajib'] ? 'required' : '' ?>>
                                <?php break;
                                
                                case 'textarea': ?>
                                    <textarea class="form-control" name="jawaban[<?= $item['id'] ?>]" rows="4" 
                                              <?= $item['wajib'] ? 'required' : '' ?>><?= $jawabanLama ? htmlspecialchars($jawabanLama['text']) : '' ?></textarea>
                                <?php break;
                                
                                case 'radio':
                                    foreach ($item['opsi'] as $opsi): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="jawaban[<?= $item['id'] ?>]" 
                                                   value="<?= $opsi['id'] ?>" id="radio_<?= $item['id'] ?>_<?= $opsi['id'] ?>"
                                                   <?= $jawabanLama && $jawabanLama['opsi_id'] == $opsi['id'] ? 'checked' : '' ?>
                                                   <?= $item['wajib'] ? 'required' : '' ?>>
                                            <label class="form-check-label" for="radio_<?= $item['id'] ?>_<?= $opsi['id'] ?>">
                                                <?= htmlspecialchars($opsi['text']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach;
                                break;
                                
                                case 'checkbox':
                                    foreach ($item['opsi'] as $opsi): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="jawaban[<?= $item['id'] ?>][]" 
                                                   value="<?= $opsi['id'] ?>" id="check_<?= $item['id'] ?>_<?= $opsi['id'] ?>"
                                                   <?= $jawabanLama && strpos($jawabanLama['text'], $opsi['id']) !== false ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="check_<?= $item['id'] ?>_<?= $opsi['id'] ?>">
                                                <?= htmlspecialchars($opsi['text']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach;
                                break;
                                
                                case 'select': ?>
                                    <select class="form-select" name="jawaban[<?= $item['id'] ?>]" <?= $item['wajib'] ? 'required' : '' ?>>
                                        <option value="">Pilih jawaban...</option>
                                        <?php foreach ($item['opsi'] as $opsi): ?>
                                            <option value="<?= $opsi['id'] ?>" 
                                                    <?= $jawabanLama && $jawabanLama['opsi_id'] == $opsi['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($opsi['text']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php break;
                                
                                case 'rating': ?>
                                    <div class="rating-container">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="rating-stars <?= $jawabanLama && $jawabanLama['text'] >= $i ? 'active' : '' ?>" 
                                                  data-rating="<?= $i ?>" data-question="<?= $item['id'] ?>">★</span>
                                        <?php endfor; ?>
                                        <input type="hidden" name="jawaban[<?= $item['id'] ?>]" 
                                               value="<?= $jawabanLama ? $jawabanLama['text'] : '' ?>" 
                                               <?= $item['wajib'] ? 'required' : '' ?>>
                                    </div>
                                <?php break;
                            endswitch; ?>
                        </div>

                    <?php 
                    $questionNumber++;
                    endforeach; ?>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-submit btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>
                            <?= $sudahIsi ? 'Perbarui Jawaban' : 'Kirim Kuesioner' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        // Rating stars functionality
        document.querySelectorAll('.rating-stars').forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                const questionId = this.dataset.question;
                const hiddenInput = document.querySelector(`input[name="jawaban[${questionId}]"]`);
                
                hiddenInput.value = rating;
                
                // Update visual stars
                const allStars = document.querySelectorAll(`[data-question="${questionId}"]`);
                allStars.forEach((s, index) => {
                    s.classList.toggle('active', index < rating);
                });
            });
        });

        // Progress bar
        function updateProgress() {
            const totalQuestions = document.querySelectorAll('.question-item').length;
            const answeredQuestions = document.querySelectorAll('.question-item').length;
            let answered = 0;

            document.querySelectorAll('.question-item').forEach(item => {
                const inputs = item.querySelectorAll('input, textarea, select');
                const hasAnswer = [...inputs].some(input => {
                    if (input.type === 'radio' || input.type === 'checkbox') {
                        return input.checked;
                    }
                    return input.value.trim() !== '';
                });
                if (hasAnswer) answered++;
            });

            const percentage = (answered / totalQuestions) * 100;
            document.getElementById('progressBar').style.width = percentage + '%';
        }

        // Add event listeners for progress tracking
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('change', updateProgress);
                input.addEventListener('input', updateProgress);
            });
            updateProgress();
        });

        function submitKuesioner(event) {
            event.preventDefault();
            
            const formData = new FormData(document.getElementById('kuesionerForm'));
            
            // Validasi form
            const requiredQuestions = document.querySelectorAll('[data-required="true"]');
            let allValid = true;
            
            requiredQuestions.forEach(question => {
                const inputs = question.querySelectorAll('input, textarea, select');
                const hasAnswer = [...inputs].some(input => {
                    if (input.type === 'radio' || input.type === 'checkbox') {
                        return input.checked;
                    }
                    return input.value.trim() !== '';
                });
                
                if (!hasAnswer) {
                    allValid = false;
                    question.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    question.style.border = '2px solid #dc3545';
                    setTimeout(() => {
                        question.style.border = '1px solid #e9ecef';
                    }, 3000);
                }
            });

            if (!allValid) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: 'Mohon lengkapi semua pertanyaan yang wajib diisi.'
                });
                return;
            }

            // Submit form
            fetch('simpan_jawaban.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = 'dashboard-alumni.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat menyimpan data.'
                });
            });
        }
    </script>
</body>
</html>