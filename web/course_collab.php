<?php
session_start();
require_once '../config.php';

// Admin guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
if ($course_id <= 0) {
    header("Location: manage_courses.php");
    exit;
}

// Fetch course info
$stmt = $conn->prepare("SELECT id, title, department, division, section FROM courses WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$course) {
    header("Location: manage_courses.php");
    exit;
}

// Fetch all admins (excluding current user)
$current_user_id = (int)$_SESSION['user_id'];
$admins_result = $conn->query("
    SELECT u.id, u.full_name, u.username, u.email
    FROM users u
    WHERE u.role = 'admin'
      AND u.id != $current_user_id
      AND u.id NOT IN (
          SELECT cc.admin_id
          FROM course_collab cc
          WHERE cc.course_id = $course_id
            AND cc.removed = 0
      )
      AND u.id NOT IN (
          SELECT uc.user_id
          FROM user_courses uc
          WHERE uc.course_id = $course_id
            AND uc.removed = 0
      )
    ORDER BY u.full_name
");
$admins = $admins_result ? $admins_result->fetch_all(MYSQLI_ASSOC) : [];

// Get currently assigned collaborators
$assigned_stmt = $conn->prepare("
    SELECT cc.admin_id, u.full_name, u.username, u.email
    FROM course_collab cc
    JOIN users u ON cc.admin_id = u.id
    WHERE cc.course_id = ?
    ORDER BY u.full_name
");
$assigned_stmt->bind_param("i", $course_id);
$assigned_stmt->execute();
$assigned = $assigned_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$assigned_stmt->close();
$assigned_ids = array_column($assigned, 'user_id');

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assign collaborators
    if (isset($_POST['assign_selected']) && !empty($_POST['selected_admin'])) {
        $selected = $_POST['selected_admin'];

        $check_stmt = $conn->prepare("SELECT id FROM course_collab WHERE course_id = ? AND admin_id = ? LIMIT 1");
        $insert_stmt = $conn->prepare("INSERT INTO course_collab (course_id, admin_id) VALUES (?, ?)");

        foreach ($selected as $uid) {
            $uid = (int)$uid;
            if ($uid <= 0) continue;

            $check_stmt->bind_param("ii", $course_id, $uid);
            $check_stmt->execute();
            $exists = $check_stmt->get_result()->fetch_assoc();

            if (!$exists) {
                $insert_stmt->bind_param("ii", $course_id, $uid);
                $insert_stmt->execute();
            }
        }
        $check_stmt->close();
        $insert_stmt->close();

        $_SESSION['success'] = "Admin/s assigned successfully.";
        header("Location: course_collab.php?course_id=$course_id");
        exit;
    }

    // Remove collaborators
    if (isset($_POST['remove_selected']) && !empty($_POST['selected_remove'])) {
        $to_remove = $_POST['selected_remove'];
        $stmt = $conn->prepare("DELETE FROM course_collab WHERE course_id = ? AND admin_id = ?");
        foreach ($to_remove as $uid) {
            $uid = (int)$uid;
            $stmt->bind_param("ii", $course_id, $uid);
            $stmt->execute();
        }
        $stmt->close();

        $_SESSION['success'] = "Selected collaborators removed.";
        header("Location: course_collab.php?course_id=$course_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Course Collaborators - <?php echo htmlspecialchars($course['title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-blue-50">
<?php include 'includes/sidebar.php'; ?>

<div class="p-8 sm:ml-72">
    <div class="container mx-auto">

        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-6 mb-6 shadow-sm flex justify-between items-center">
            <div>
                <h1 class="text-2xl text-white font-bold flex items-center gap-2">
                    <i data-lucide="users" class="w-6 h-6"></i>
                    Manage Collaborators — <?php echo htmlspecialchars($course['title']); ?>
                </h1>
                <p class="text-blue-100">Assign admins who can manage this course’s modules and quizzes.</p>
            </div>
            <a href="edit_course.php?id=<?php echo $course_id; ?>" class="px-4 py-2 bg-white text-blue-600 rounded-lg shadow-sm flex items-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Course
            </a>
        </div>

        <!-- Alerts -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
                <div><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            </div>
        <?php endif; ?>

        <!-- Assign Collaborators -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-md font-semibold flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-5 h-5 text-blue-600"></i> Assign Admins as Collaborators
                </h3>
                <div>
                    <button id="selectAllBtn" class="px-3 py-1 text-xs bg-gray-100 rounded mr-2">Select All</button>
                    <button id="deselectAllBtn" class="px-3 py-1 text-xs bg-gray-100 rounded">Deselect All</button>
                </div>
            </div>

            <form method="POST" action="">
                <div class="overflow-x-auto max-h-96 overflow-y-auto border rounded">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-4 py-2"><input type="checkbox" id="masterCheckbox"></th>
                                <th class="px-4 py-2 text-left">Name</th>
                                <th class="px-4 py-2 text-left">Username</th>
                                <th class="px-4 py-2 text-left">Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($admins)): ?>
                                <tr><td colspan="4" class="p-4 text-center text-gray-500">No admins found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($admins as $a): ?>
                                    <tr class="border-t hover:bg-blue-50">
                                        <td class="px-4 py-2 text-center">
                                            <input type="checkbox" name="selected_admin[]" value="<?php echo (int)$a['id']; ?>" class="row-checkbox">
                                        </td>
                                        <td class="px-4 py-2"><?php echo htmlspecialchars($a['full_name']); ?></td>
                                        <td class="px-4 py-2">@<?php echo htmlspecialchars($a['username']); ?></td>
                                        <td class="px-4 py-2"><?php echo htmlspecialchars($a['email']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="submit" name="assign_selected" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                        <i data-lucide="user-plus" class="w-4 h-4 mr-1 inline"></i> Assign Selected
                    </button>
                </div>
            </form>
        </div>

        <!-- Assigned Collaborators -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
            <h3 class="text-md font-semibold mb-3 flex items-center gap-2">
                <i data-lucide="users" class="w-5 h-5 text-blue-600"></i> Currently Assigned Collaborators
            </h3>
            <form method="POST" action="">
                <div class="overflow-auto max-h-64 border rounded">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2"><input type="checkbox" id="masterRemoveCheckbox"></th>
                                <th class="px-4 py-2 text-left">Name</th>
                                <th class="px-4 py-2 text-left">Username</th>
                                <th class="px-4 py-2 text-left">Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($assigned)): ?>
                                <tr><td colspan="4" class="p-4 text-center text-gray-500">No collaborators assigned.</td></tr>
                            <?php else: ?>
                                <?php foreach ($assigned as $a): ?>
                                    <tr class="border-t hover:bg-blue-50">
                                        <td class="px-4 py-2 text-center">
                                            <input type="checkbox" name="selected_remove[]" value="<?php echo (int)$a['admin_id']; ?>" class="row-remove-checkbox">
                                        </td>
                                        <td class="px-4 py-2"><?php echo htmlspecialchars($a['full_name']); ?></td>
                                        <td class="px-4 py-2">@<?php echo htmlspecialchars($a['username']); ?></td>
                                        <td class="px-4 py-2"><?php echo htmlspecialchars($a['email']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-right">
                    <button type="submit" name="remove_selected" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                        <i data-lucide="trash-2" class="w-4 h-4 mr-1 inline"></i> Remove Selected
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
lucide.createIcons();

document.addEventListener('DOMContentLoaded', function () {
    const master = document.getElementById('masterCheckbox');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    master?.addEventListener('change', () => {
        rowCheckboxes.forEach(cb => cb.checked = master.checked);
    });
    document.getElementById('selectAllBtn').addEventListener('click', e => {
        e.preventDefault();
        rowCheckboxes.forEach(cb => cb.checked = true);
    });
    document.getElementById('deselectAllBtn').addEventListener('click', e => {
        e.preventDefault();
        rowCheckboxes.forEach(cb => cb.checked = false);
    });

    const masterRemove = document.getElementById('masterRemoveCheckbox');
    const removeBoxes = document.querySelectorAll('.row-remove-checkbox');
    masterRemove?.addEventListener('change', () => {
        removeBoxes.forEach(cb => cb.checked = masterRemove.checked);
    });
});
</script>
</body>
</html>
