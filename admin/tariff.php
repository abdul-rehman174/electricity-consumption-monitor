<?php
/**
 * Admin -> Tariff: view and edit the WAPDA unit prices (slab rates) from the app.
 * Saving replaces the whole tariff_slabs table with the submitted rows
 * (a small table, so a wipe-and-reinsert inside a transaction is simplest).
 * Admin-only (NFR-02): server-side role check on every request.
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $froms = $_POST['slab_from'] ?? [];
    $tos   = $_POST['slab_to']   ?? [];
    $rates = $_POST['rate']      ?? [];

    // Build the list of valid, non-empty rows.
    $rows = [];
    $count = count($froms);
    for ($i = 0; $i < $count; $i++) {
        $from = trim($froms[$i] ?? '');
        $to   = trim($tos[$i] ?? '');
        $rate = trim($rates[$i] ?? '');

        // Skip fully blank rows.
        if ($from === '' && $to === '' && $rate === '') {
            continue;
        }
        if (!is_numeric($from) || (int) $from < 1) {
            $errors[] = "Row " . ($i + 1) . ": 'From' must be a whole number of 1 or more.";
            continue;
        }
        if ($rate === '' || !is_numeric($rate) || $rate < 0) {
            $errors[] = "Row " . ($i + 1) . ": 'Rate' must be a non-negative number.";
            continue;
        }
        // Empty 'To' means unbounded (and above).
        $to_val = ($to === '') ? null : (int) $to;
        if ($to_val !== null && $to_val < (int) $from) {
            $errors[] = "Row " . ($i + 1) . ": 'To' cannot be less than 'From'.";
            continue;
        }
        $rows[] = ['from' => (int) $from, 'to' => $to_val, 'rate' => (float) $rate];
    }

    if (!$errors && !$rows) {
        $errors[] = 'Please define at least one slab.';
    }

    if (!$errors) {
        // Keep slabs in ascending order.
        usort($rows, fn($a, $b) => $a['from'] <=> $b['from']);

        mysqli_begin_transaction($conn);
        try {
            mysqli_query($conn, 'DELETE FROM tariff_slabs');
            $stmt = mysqli_prepare($conn, 'INSERT INTO tariff_slabs (slab_from, slab_to, rate) VALUES (?, ?, ?)');
            foreach ($rows as $r) {
                mysqli_stmt_bind_param($stmt, 'iid', $r['from'], $r['to'], $r['rate']);
                mysqli_stmt_execute($stmt);
            }
            mysqli_stmt_close($stmt);
            mysqli_commit($conn);
            set_flash('success', 'Tariff rates updated. New bills use these prices immediately.');
            redirect('admin/tariff.php');
        } catch (Exception $ex) {
            mysqli_rollback($conn);
            $errors[] = 'Could not save tariff: ' . $ex->getMessage();
        }
    }
}

// Load current slabs to display.
$res = mysqli_query($conn, 'SELECT slab_from, slab_to, rate FROM tariff_slabs ORDER BY slab_from ASC');
$slabs = [];
while ($row = mysqli_fetch_assoc($res)) {
    $slabs[] = $row;
}

$page_title = 'Tariff Rates';
require __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-9">
        <h3 class="mb-1"><i class="bi bi-cash-coin text-success"></i> Tariff Rates (Price per Unit)</h3>
        <p class="text-muted">Each slab sets the price (PKR) of one unit (kWh) within its range.
           Leave <strong>To</strong> empty on the last slab to mean &ldquo;and above&rdquo;.
           Bills are calculated progressively (each unit charged at its slab's rate).</p>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="card"><div class="card-body">
            <form method="post" id="tariffForm">
                <table class="table align-middle" id="slabTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:30%">From (unit)</th>
                            <th style="width:30%">To (unit)</th>
                            <th style="width:30%">Rate (PKR / unit)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($slabs as $s): ?>
                        <tr>
                            <td><input type="number" min="1" step="1" name="slab_from[]" class="form-control" value="<?= e($s['slab_from']) ?>"></td>
                            <td><input type="number" min="1" step="1" name="slab_to[]" class="form-control" value="<?= e($s['slab_to']) ?>" placeholder="(and above)"></td>
                            <td><input type="number" min="0" step="0.01" name="rate[]" class="form-control" value="<?= e($s['rate']) ?>"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="bi bi-x-lg"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addRow()"><i class="bi bi-plus-lg"></i> Add slab</button>
                <hr>
                <button type="submit" class="btn btn-ecms"><i class="bi bi-check-lg"></i> Save Rates</button>
                <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-link">Back to Admin</a>
            </form>
        </div></div>

        <p class="text-muted small mt-2">
            Tip: for a single flat price, keep just one row — From <code>1</code>, To <em>(empty)</em>, and your price per unit.
        </p>
    </div>
</div>

<script>
function addRow() {
    const tbody = document.querySelector('#slabTable tbody');
    const tr = document.createElement('tr');
    tr.innerHTML =
        '<td><input type="number" min="1" step="1" name="slab_from[]" class="form-control"></td>' +
        '<td><input type="number" min="1" step="1" name="slab_to[]" class="form-control" placeholder="(and above)"></td>' +
        '<td><input type="number" min="0" step="0.01" name="rate[]" class="form-control"></td>' +
        '<td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'tr\').remove()"><i class="bi bi-x-lg"></i></button></td>';
    tbody.appendChild(tr);
}
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
