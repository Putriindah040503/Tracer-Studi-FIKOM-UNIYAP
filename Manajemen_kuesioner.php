<?php
session_start();
include 'koneksi.php';

// Check authentication
if (!isset($_SESSION['id']) || !in_array($_SESSION['role'], ['admin', 'fakultas'])) {
    header("Location: login.php");
    exit;
}

// Get categories
$kategoris = mysqli_query($conn, "SELECT id, nama_kategori FROM kuesioner_kategori ORDER BY nama_kategori");

// Get questionnaire data with proper JOIN
$query = "SELECT kp.*, kk.nama_kategori AS kategori, kp.kategori_id
          FROM kuesioner_pertanyaan kp 
          JOIN kuesioner_kategori kk ON kp.kategori_id = kk.id 
          ORDER BY kp.kategori_id ASC, kp.urutan ASC";
$result = mysqli_query($conn, $query);

$kuesionerData = [];
while ($row = mysqli_fetch_assoc($result)) {
    $opsi = [];
    if (in_array($row['tipe_input'], ['radio', 'checkbox', 'select'])) {
        $opsiQuery = "SELECT opsi_text, opsi_value FROM kuesioner_opsi_jawaban WHERE pertanyaan_id = ? ORDER BY id";
        $opsiStmt = mysqli_prepare($conn, $opsiQuery);
        mysqli_stmt_bind_param($opsiStmt, "i", $row['id']);
        mysqli_stmt_execute($opsiStmt);
        $opsiResult = mysqli_stmt_get_result($opsiStmt);
        
        while ($o = mysqli_fetch_assoc($opsiResult)) {
            $opsi[] = ['text' => $o['opsi_text'], 'value' => $o['opsi_value']];
        }
        mysqli_stmt_close($opsiStmt);
    }
    $kuesionerData[] = array_merge($row, ['opsi' => $opsi]);
}

// Get next available order number
$nextOrderQuery = "SELECT COALESCE(MAX(urutan), 0) + 1 as next_order FROM kuesioner_pertanyaan";
$nextOrderResult = mysqli_query($conn, $nextOrderQuery);
$nextOrder = mysqli_fetch_assoc($nextOrderResult)['next_order'];

// Get alumni data for dropdown (if needed for questionnaire)
$alumniQuery = "SELECT id, nama_lengkap, npm, tahun_lulus, program_studi FROM alumni ORDER BY nama_lengkap";
$alumniResult = mysqli_query($conn, $alumniQuery);
$alumniData = [];
while ($alumni = mysqli_fetch_assoc($alumniResult)) {
    $alumniData[] = $alumni;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kuesioner</title>
    <link href="styless.css?v=1.0" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <style>
        .sortable-item {
            cursor: move;
            transition: all 0.3s ease;
        }
        .sortable-item:hover {
            background-color: #f8f9fa;
        }
        .sortable-ghost {
            opacity: 0.4;
            background-color: #e3f2fd;
        }
        .sortable-chosen {
            background-color: #e8f5e8;
        }
        .drag-handle {
            cursor: grab;
            color: #6c757d;
        }
        .drag-handle:hover {
            color: #495057;
        }
        .drag-handle:active {
            cursor: grabbing;
        }
        .table-container {
            position: relative;
        }
        .reorder-mode {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .order-number {
            font-weight: bold;
            color: #0d6efd;
            min-width: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <nav class="sidebar"><?php include 'sidebar.php'; ?></nav>

    <main>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-clipboard-list"></i> Manajemen Kuesioner</h2>
            <div>
                <button class="btn btn-info me-2" onclick="toggleReorderMode()" id="reorderBtn">
                    <i class="fas fa-sort"></i> Atur Urutan
                </button>
                <button class="btn btn-success me-2" onclick="previewKuesioner()">
                    <i class="fas fa-eye"></i> Preview Kuesioner
                </button>
                <button class="btn btn-primary" onclick="openModal()">
                    <i class="fas fa-plus"></i> Tambah Pertanyaan
                </button>
            </div>
        </div>

        <!-- Reorder Mode Notification -->
        <div id="reorderNotification" class="reorder-mode" style="display: none;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Mode Pengaturan Urutan Aktif</strong> - Drag dan drop baris untuk mengubah urutan pertanyaan
                </div>
                <div>
                    <button class="btn btn-success btn-sm me-2" onclick="saveNewOrder()">
                        <i class="fas fa-save"></i> Simpan Urutan
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="cancelReorder()">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select class="form-select" id="filterKategori" onchange="filterData()">
                            <option value="">Semua Kategori</option>
                            <?php 
                            mysqli_data_seek($kategoris, 0);
                            while ($k = mysqli_fetch_assoc($kategoris)): 
                            ?>
                                <option value="<?= htmlspecialchars($k['nama_kategori']) ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterTipe" onchange="filterData()">
                            <option value="">Semua Tipe</option>
                            <option value="text">Text</option>
                            <option value="textarea">Textarea</option>
                            <option value="radio">Radio</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="select">Select</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterStatus" onchange="filterData()">
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Non-aktif</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari pertanyaan..." oninput="filterData()">
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5>Total Pertanyaan</h5>
                        <h3 id="totalPertanyaan"><?= count($kuesionerData) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5>Pertanyaan Aktif</h5>
                        <h3 id="pertanyaanAktif"><?= count(array_filter($kuesionerData, fn($item) => $item['aktif'] == 1)) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5>Pertanyaan Wajib</h5>
                        <h3 id="pertanyaanWajib"><?= count(array_filter($kuesionerData, fn($item) => $item['wajib'] == 1)) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5>Total Kategori</h5>
                        <h3><?= mysqli_num_rows($kategoris) ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> Daftar Pertanyaan Kuesioner</h5>
            </div>
            <div class="table-responsive table-container">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="30px">
                                <span id="dragColumn" style="display: none;">
                                    <i class="fas fa-arrows-alt"></i>
                                </span>
                                <span id="noColumn">No</span>
                            </th>
                            <th>Pertanyaan</th>
                            <th>Kategori</th>
                            <th>Tipe</th>
                            <th>Opsi</th>
                            <th>Wajib</th>
                            <th>Status</th>
                            <th>Urutan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Tambah/Edit Pertanyaan -->
    <div class="modal fade" id="kuesionerModal">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 id="modalTitle">Tambah Pertanyaan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="kuesionerForm" onsubmit="return saveData(event)">
                    <div class="modal-body">
                        <input type="hidden" id="itemId">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="pertanyaan" rows="3" required placeholder="Masukkan pertanyaan kuesioner..."></textarea>
                                    <div class="form-text">Tuliskan pertanyaan dengan jelas dan mudah dipahami</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Urutan</label>
                                    <input type="number" class="form-control" id="urutan" min="1" value="<?= $nextOrder ?>" readonly>
                                    <div class="form-text">Urutan akan diatur otomatis. Gunakan fitur 'Atur Urutan' untuk mengubah posisi.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                    <select class="form-select" id="kategori" required>
                                        <option value="">Pilih Kategori</option>
                                        <?php 
                                        mysqli_data_seek($kategoris, 0);
                                        while ($k = mysqli_fetch_assoc($kategoris)): 
                                        ?>
                                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tipe Input <span class="text-danger">*</span></label>
                                    <select class="form-select" id="tipe" onchange="toggleOptions()" required>
                                        <option value="">Pilih Tipe</option>
                                        <option value="text">Text (Input singkat)</option>
                                        <option value="textarea">Textarea (Input panjang)</option>
                                        <option value="radio">Radio (Pilih satu)</option>
                                        <option value="checkbox">Checkbox (Pilih banyak)</option>
                                        <option value="select">Select (Dropdown)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Container untuk opsi jawaban -->
                        <div id="opsiContainer" class="mb-3" style="display:none">
                            <label class="form-label">Opsi Jawaban <span class="text-danger">*</span></label>
                            <div class="card">
                                <div class="card-body">
                                    <div id="opsiList"></div>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addOption()">
                                        <i class="fas fa-plus"></i> Tambah Opsi
                                    </button>
                                    <div class="form-text mt-2">Minimal 2 opsi untuk tipe radio, checkbox, dan select</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="wajib">
                                    <label class="form-check-label">Pertanyaan Wajib</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="aktif" checked>
                                    <label class="form-check-label">Status Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Pertanyaan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Preview Kuesioner -->
    <div class="modal fade" id="previewModal">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5>Preview Kuesioner Alumni</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="previewContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        const data = <?= json_encode($kuesionerData) ?>;
        let filteredData = [...data];
        let originalData = [...data];
        let isReorderMode = false;
        let sortable = null;
        const modal = new bootstrap.Modal(document.getElementById('kuesionerModal'));
        const nextOrder = <?= $nextOrder ?>;

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            renderTable();
            document.getElementById('kuesionerModal').addEventListener('hidden.bs.modal', resetForm);
        });

        function renderTable() {
            const tbody = document.getElementById('tableBody');
            if (!filteredData.length) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4">Tidak ada data</td></tr>';
                return;
            }

            tbody.innerHTML = filteredData.map((item, i) => `
                <tr class="sortable-item" data-id="${item.id}">
                    <td>
                        <span class="drag-handle" style="display: ${isReorderMode ? 'inline' : 'none'};">
                            <i class="fas fa-grip-vertical"></i>
                        </span>
                        <span class="order-number" style="display: ${isReorderMode ? 'none' : 'inline'};">
                            ${i + 1}
                        </span>
                    </td>
                    <td>
                        <div class="fw-medium">${item.pertanyaan}</div>
                        ${item.opsi && item.opsi.length ? 
                            `<small class="text-muted">Opsi: ${item.opsi.map(o => o.text).join(', ')}</small>` 
                            : ''
                        }
                    </td>
                    <td><span class="badge bg-info">${item.kategori}</span></td>
                    <td><span class="badge bg-secondary">${item.tipe_input.toUpperCase()}</span></td>
                    <td>
                        ${item.opsi && item.opsi.length ? 
                            `<span class="badge bg-primary">${item.opsi.length} opsi</span>` 
                            : '<span class="text-muted">-</span>'
                        }
                    </td>
                    <td>${item.wajib == 1 ? '<span class="badge bg-danger">Ya</span>' : '<span class="badge bg-light text-dark">Tidak</span>'}</td>
                    <td>${item.aktif == 1 ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Non-aktif</span>'}</td>
                    <td><span class="badge bg-primary">${item.urutan}</span></td>
                    <td>
                        <div class="btn-group" style="display: ${isReorderMode ? 'none' : 'inline-flex'};">
                            <button class="btn btn-sm btn-warning" onclick="editItem(${item.id})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteItem(${item.id})" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function filterData() {
            if (isReorderMode) {
                Swal.fire('Perhatian!', 'Matikan mode pengaturan urutan terlebih dahulu', 'warning');
                return;
            }

            const kategori = document.getElementById('filterKategori').value;
            const tipe = document.getElementById('filterTipe').value;
            const status = document.getElementById('filterStatus').value;
            const search = document.getElementById('searchInput').value.toLowerCase();

            filteredData = data.filter(item => {
                return (!kategori || item.kategori === kategori) &&
                       (!tipe || item.tipe_input === tipe) &&
                       (status === '' || item.aktif == status) &&
                       (!search || item.pertanyaan.toLowerCase().includes(search));
            });
            renderTable();
        }

        function toggleReorderMode() {
            if (isReorderMode) {
                cancelReorder();
            } else {
                startReorderMode();
            }
        }

        function startReorderMode() {
            isReorderMode = true;
            
            // Reset filters to show all data
            document.getElementById('filterKategori').value = '';
            document.getElementById('filterTipe').value = '';
            document.getElementById('filterStatus').value = '';
            document.getElementById('searchInput').value = '';
            filteredData = [...data];
            
            // Update UI
            document.getElementById('reorderBtn').innerHTML = '<i class="fas fa-times"></i> Batal Urutan';
            document.getElementById('reorderBtn').className = 'btn btn-secondary me-2';
            document.getElementById('reorderNotification').style.display = 'block';
            document.getElementById('dragColumn').style.display = 'inline';
            document.getElementById('noColumn').style.display = 'none';
            
            // Disable filters
            document.getElementById('filterKategori').disabled = true;
            document.getElementById('filterTipe').disabled = true;
            document.getElementById('filterStatus').disabled = true;
            document.getElementById('searchInput').disabled = true;
            
            renderTable();
            
            // Initialize sortable
            const tbody = document.getElementById('tableBody');
            sortable = Sortable.create(tbody, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                handle: '.drag-handle',
                onEnd: function(evt) {
                    updateOrderNumbers();
                }
            });
        }

        function cancelReorder() {
            isReorderMode = false;
            
            // Restore original data order
            filteredData = [...originalData];
            
            // Update UI
            document.getElementById('reorderBtn').innerHTML = '<i class="fas fa-sort"></i> Atur Urutan';
            document.getElementById('reorderBtn').className = 'btn btn-info me-2';
            document.getElementById('reorderNotification').style.display = 'none';
            document.getElementById('dragColumn').style.display = 'none';
            document.getElementById('noColumn').style.display = 'inline';
            
            // Enable filters
            document.getElementById('filterKategori').disabled = false;
            document.getElementById('filterTipe').disabled = false;
            document.getElementById('filterStatus').disabled = false;
            document.getElementById('searchInput').disabled = false;
            
            // Destroy sortable
            if (sortable) {
                sortable.destroy();
                sortable = null;
            }
            
            renderTable();
        }

        function updateOrderNumbers() {
            const tbody = document.getElementById('tableBody');
            const rows = Array.from(tbody.querySelectorAll('.sortable-item'));
            
            // Update filteredData order based on current row positions
            const newOrderedData = [];
            rows.forEach((row, index) => {
                const id = parseInt(row.dataset.id);
                const item = filteredData.find(d => d.id === id);
                if (item) {
                    newOrderedData.push(item);
                }
            });
            
            filteredData = newOrderedData;
        }

        async function saveNewOrder() {
            const newOrder = filteredData.map((item, index) => ({
                id: item.id,
                urutan: index + 1
            }));

            try {
                const response = await fetch('update-urutan-kuesioner.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ orders: newOrder })
                });

                const result = await response.json();

                if (result.status === 'success') {
                    // Update original data with new order
                    originalData = [...filteredData];
                    data.length = 0;
                    data.push(...filteredData);
                    
                    cancelReorder();
                    Swal.fire('Berhasil!', 'Urutan pertanyaan berhasil disimpan', 'success');
                } else {
                    Swal.fire('Error!', result.message || 'Gagal menyimpan urutan', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error!', 'Terjadi kesalahan saat menyimpan urutan', 'error');
            }
        }

        function openModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Pertanyaan';
            document.getElementById('urutan').value = nextOrder + filteredData.length;
            modal.show();
        }

        function editItem(id) {
            const item = data.find(d => d.id == id);
            if (!item) return;

            document.getElementById('modalTitle').textContent = 'Edit Pertanyaan';
            document.getElementById('itemId').value = item.id;
            document.getElementById('pertanyaan').value = item.pertanyaan;
            document.getElementById('kategori').value = item.kategori_id;
            document.getElementById('tipe').value = item.tipe_input;
            document.getElementById('wajib').checked = item.wajib == 1;
            document.getElementById('aktif').checked = item.aktif == 1;
            document.getElementById('urutan').value = item.urutan;

            toggleOptions();
            if (item.opsi?.length) {
                const opsiList = document.getElementById('opsiList');
                opsiList.innerHTML = '';
                item.opsi.forEach(opsi => {
                    addOption(opsi.text);
                });
            }

            modal.show();
        }

        function toggleOptions() {
            const tipe = document.getElementById('tipe').value;
            const container = document.getElementById('opsiContainer');
            const list = document.getElementById('opsiList');
            
            if (['radio', 'checkbox', 'select'].includes(tipe)) {
                container.style.display = 'block';
                if (!list.children.length) {
                    addOption();
                    addOption();
                }
            } else {
                container.style.display = 'none';
                list.innerHTML = '';
            }
        }

        function addOption(value = '') {
            const list = document.getElementById('opsiList');
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <input type="text" class="form-control" placeholder="Opsi jawaban" value="${value}" required>
                <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            list.appendChild(div);
        }

        function resetForm() {
            document.getElementById('kuesionerForm').reset();
            document.getElementById('itemId').value = '';
            document.getElementById('opsiContainer').style.display = 'none';
            document.getElementById('opsiList').innerHTML = '';
            document.getElementById('aktif').checked = true;
            document.getElementById('urutan').value = nextOrder + filteredData.length;
        }

        async function saveData(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('id', document.getElementById('itemId').value);
            formData.append('pertanyaan', document.getElementById('pertanyaan').value);
            formData.append('kategori', document.getElementById('kategori').value);
            formData.append('tipe', document.getElementById('tipe').value);
            formData.append('wajib', document.getElementById('wajib').checked ? 1 : 0);
            formData.append('aktif', document.getElementById('aktif').checked ? 1 : 0);
            formData.append('urutan', document.getElementById('urutan').value);

            // Add options
            document.querySelectorAll('#opsiList input').forEach(input => {
                if (input.value.trim()) formData.append('opsi[]', input.value.trim());
            });

            try {
                const response = await fetch('simpan_kuesioner.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.status === 'success') {
                    modal.hide();
                    Swal.fire('Berhasil!', result.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error!', result.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error!', 'Gagal menyimpan data', 'error');
            }
        }

        function deleteItem(id) {
            Swal.fire({
                title: 'Hapus Pertanyaan?',
                text: 'Data yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch('hapus_kuesioner.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `id=${id}`
                        });
                        const data = await response.json();

                        if (data.status === 'success') {
                            Swal.fire('Terhapus!', data.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    } catch (error) {
                        Swal.fire('Error!', 'Gagal menghapus data', 'error');
                    }
                }
            });
        }

        function previewKuesioner() {
            // Generate preview content
            const activeQuestions = data.filter(item => item.aktif == 1);
            const sortedQuestions = activeQuestions.sort((a, b) => a.urutan - b.urutan);
            
            let previewHTML = `
                <div class="container-fluid">
                    <div class="text-center mb-4">
                        <h3 class="text-primary">Kuesioner Alumni</h3>
                        <p class="text-muted">Preview tampilan kuesioner untuk alumni</p>
                    </div>
                    <form class="needs-validation" novalidate>
            `;

            let currentCategory = '';
            sortedQuestions.forEach((item, index) => {
                if (item.kategori !== currentCategory) {
                    if (currentCategory !== '') {
                        previewHTML += '</div></div>';
                    }
                    currentCategory = item.kategori;
                    previewHTML += `
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-folder"></i> ${item.kategori}</h5>
                            </div>
                            <div class="card-body">
                    `;
                }

                previewHTML += `
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            ${index + 1}. ${item.pertanyaan}
                            ${item.wajib == 1 ? '<span class="text-danger">*</span>' : ''}
                        </label>
                `;

                switch (item.tipe_input) {
                    case 'text':
                        previewHTML += `<input type="text" class="form-control" placeholder="Masukkan jawaban..." ${item.wajib == 1 ? 'required' : ''}>`;
                        break;
                    case 'textarea':
                        previewHTML += `<textarea class="form-control" rows="3" placeholder="Masukkan jawaban..." ${item.wajib == 1 ? 'required' : ''}></textarea>`;
                        break;
                    case 'radio':
                        if (item.opsi && item.opsi.length) {
                            item.opsi.forEach((opsi, opsiIndex) => {
                                previewHTML += `
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="question_${item.id}" id="radio_${item.id}_${opsiIndex}" value="${opsi.value}" ${item.wajib == 1 ? 'required' : ''}>
                                        <label class="form-check-label" for="radio_${item.id}_${opsiIndex}">
                                            ${opsi.text}
                                        </label>
                                    </div>
                                `;
                            });
                        }
                        break;
                    case 'checkbox':
                        if (item.opsi && item.opsi.length) {
                            item.opsi.forEach((opsi, opsiIndex) => {
                                previewHTML += `
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="question_${item.id}[]" id="checkbox_${item.id}_${opsiIndex}" value="${opsi.value}">
                                        <label class="form-check-label" for="checkbox_${item.id}_${opsiIndex}">
                                            ${opsi.text}
                                        </label>
                                    </div>
                                `;
                            });
                        }
                        break;
                    case 'select':
                        previewHTML += `<select class="form-select" ${item.wajib == 1 ? 'required' : ''}>
                            <option value="">Pilih jawaban...</option>`;
                        if (item.opsi && item.opsi.length) {
                            item.opsi.forEach(opsi => {
                                previewHTML += `<option value="${opsi.value}">${opsi.text}</option>`;
                            });
                        }
                        previewHTML += `</select>`;
                        break;
                }

                previewHTML += `</div>`;
            });

            if (currentCategory !== '') {
                previewHTML += '</div></div>';
            }

            previewHTML += `
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-primary btn-lg" disabled>
                            <i class="fas fa-paper-plane"></i> Kirim Kuesioner
                        </button>
                        <div class="text-muted mt-2">
                            <small>* Ini adalah preview saja. Tombol tidak berfungsi.</small>
                        </div>
                    </div>
                </form>
                </div>
            `;

            document.getElementById('previewContent').innerHTML = previewHTML;
            const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
            previewModal.show();
        }

    function renderTable() {
        const tbody = document.getElementById('tableBody');
        if (!filteredData.length) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4">Tidak ada data</td></tr>';
            return;
        }

        // Mengelompokkan data berdasarkan kategori
        const groupedData = filteredData.reduce((acc, item) => {
            acc[item.kategori] = acc[item.kategori] || [];
            acc[item.kategori].push(item);
            return acc;
        }, {});

        // Merender data per kategori
        tbody.innerHTML = Object.keys(groupedData).map(kategori => {
            const items = groupedData[kategori];
            return `
                <tr>
                    <td colspan="9" class="table-primary text-center">${kategori}</td>
                </tr>
                ${items.map((item, i) => `
                    <tr class="sortable-item" data-id="${item.id}">
                        <td>
                            <span class="drag-handle" style="display: ${isReorderMode ? 'inline' : 'none'};">
                                <i class="fas fa-grip-vertical"></i>
                            </span>
                            <span class="order-number" style="display: ${isReorderMode ? 'none' : 'inline'};">
                                ${i + 1}
                            </span>
                        </td>
                        <td>
                            <div class="fw-medium">${item.pertanyaan}</div>
                            ${item.opsi && item.opsi.length ? 
                                `<small class="text-muted">Opsi: ${item.opsi.map(o => o.text).join(', ')}</small>` 
                                : ''
                            }
                        </td>
                        <td><span class="badge bg-info">${item.kategori}</span></td>
                        <td><span class="badge bg-secondary">${item.tipe_input.toUpperCase()}</span></td>
                        <td>
                            ${item.opsi && item.opsi.length ? 
                                `<span class="badge bg-primary">${item.opsi.length} opsi</span>` 
                                : '<span class="text-muted">-</span>'
                            }
                        </td>
                        <td>${item.wajib == 1 ? '<span class="badge bg-danger">Ya</span>' : '<span class="badge bg-light text-dark">Tidak</span>'}</td>
                        <td>${item.aktif == 1 ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Non-aktif</span>'}</td>
                        <td><span class="badge bg-primary">${item.urutan}</span></td>
                        <td>
                            <div class="btn-group" style="display: ${isReorderMode ? 'none' : 'inline-flex'};">
                                <button class="btn btn-sm btn-warning" onclick="editItem(${item.id})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteItem(${item.id})" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('')}
            `;
        }).join('');
    }

    </script>
</body>
</html>