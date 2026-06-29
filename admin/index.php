<?php
/**
 * Admin Panel (FR-19): list every registered user.
 * Protected by require_admin() — a server-side role check on every request (NFR-02).
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Pull all users with their appliance counts.
$sql = 'SELECT u.id, u.name, u.email, u.role, u.created_at,
               (SELECT COUNT(*) FROM appliances a WHERE a.user_id = u.id) AS appliance_count
        FROM users u
        ORDER BY u.created_at DESC';
$res = mysqli_query($conn, $sql);
$users = [];
while ($row = mysqli_fetch_assoc($res)) {
    $users[] = $row;
}

$page_title = 'Admin Panel';
require __DIR__ . '/../includes/header.php';
?>
<h3 class="mb-3"><i class="bi bi-shield-lock text-success"></i> Admin Panel — Users</h3>

<div class="card"><div class="card-body p-0">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>#</th><th>Name</th><th>Email</th><th>Role</th>
                <th class="text-end">Appliances</th><th>Registered</th><th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $i => $u): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= e($u['name']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td>
                    <?php if ($u['role'] === 'admin'): ?>
                        <span class="badge bg-warning text-dark">admin</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">user</span>
                    <?php endif; ?>
                </td>
                <td class="text-end"><?= (int) $u['appliance_count'] ?></td>
                <td><?= e(date('d M Y', strtotime($u['created_at']))) ?></td>
                <td class="text-end">
                    <?php if ((int) $u['id'] === (int) current_user_id()): ?>
                        <span class="text-muted small">You</span>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/admin/delete_user.php?id=<?= (int) $u['id'] ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Delete this user and all their data? This cannot be undone.');">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div></div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
