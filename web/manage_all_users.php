<?php
session_start();
require_once '../config.php';

// Admin guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$course_id = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;
if ($course_id <= 0) {
    header("Location: manage_courses.php");
    exit;
}

// Fetch course info for header & pre-fills
$course_stmt = $conn->prepare("SELECT id, title, department, division, section FROM courses WHERE id = ? LIMIT 1");
$course_stmt->bind_param("i", $course_id);
$course_stmt->execute();
$course = $course_stmt->get_result()->fetch_assoc();
$course_stmt->close();
if (!$course) {
    header("Location: manage_courses.php");
    exit;
}

// Fetch lists for dropdowns
$current_user_id = (int)$_SESSION['user_id'];

$users_result = $conn->query("
    SELECT u.id, u.full_name, u.username, u.email
    FROM users u
    WHERE (u.role = 'user' OR u.role = 'admin')
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

$all_users = $users_result ? $users_result->fetch_all(MYSQLI_ASSOC) : [];

$divisions_result = $conn->query("SELECT Id AS id, name FROM division ORDER BY name");
$divisions = $divisions_result ? $divisions_result->fetch_all(MYSQLI_ASSOC) : [];

$sections_result = $conn->query("SELECT Id AS id, name FROM section ORDER BY name");
$sections = $sections_result ? $sections_result->fetch_all(MYSQLI_ASSOC) : [];

$departments_result = $conn->query("SELECT id, Name AS name FROM department ORDER BY Name");
$departments = $departments_result ? $departments_result->fetch_all(MYSQLI_ASSOC) : [];

// Get currently assigned users for this course (from user_courses)
$assigned_users_stmt = $conn->prepare("
    SELECT uc.user_id, u.full_name, u.username, u.email
    FROM user_courses uc
    JOIN users u ON uc.user_id = u.id
    WHERE uc.course_id = ? AND uc.removed = 0
    ORDER BY u.full_name
");
$assigned_users_stmt->bind_param("i", $course_id);
$assigned_users_stmt->execute();
$assigned_users = $assigned_users_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$assigned_users_stmt->close();
$assigned_user_ids = array_column($assigned_users, 'user_id');

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Update course scope (department/division/section)
    if (isset($_POST['update_course_scope'])) {
        $new_division = $_POST['division'] !== '' ? (int)$_POST['division'] : null;
        $new_section = $_POST['section'] !== '' ? (int)$_POST['section'] : null;
        $new_department = $_POST['department'] !== '' ? (int)$_POST['department'] : null;

        $stmt = $conn->prepare("UPDATE courses SET division = ?, section = ?, department = ? WHERE id = ?");
        $stmt->bind_param("iiii", $new_division, $new_section, $new_department, $course_id);
        $ok = $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = $ok ? "Course scope updated." : "Failed to update course.";
        header("Location: manage_all_users.php?course_id={$course_id}");
        exit;
    }

    // 2) Assign selected users to course
    if (isset($_POST['assign_selected_users']) && !empty($_POST['selected_user'])) {
        $selected_users = $_POST['selected_user']; // array of user ids
        $check_stmt = $conn->prepare("SELECT id, removed FROM user_courses WHERE user_id = ? AND course_id = ? LIMIT 1");
        $insert_stmt = $conn->prepare("INSERT INTO user_courses (user_id, course_id, removed) VALUES (?, ?, 0)");
        $update_stmt = $conn->prepare("UPDATE user_courses SET removed = 0 WHERE id = ?");

        foreach ($selected_users as $uid) {
            $uid = (int)$uid;
            if ($uid <= 0) continue;

            // Check existing record
            $check_stmt->bind_param("ii", $uid, $course_id);
            $check_stmt->execute();
            $existing = $check_stmt->get_result()->fetch_assoc();

            if ($existing) {
                // If user-course exists but was removed, reactivate it
                if ($existing['removed'] == 1) {
                    $update_stmt->bind_param("i", $existing['id']);
                    $update_stmt->execute();
                }
                // If already active, skip
                continue;
            }

            // If doesn't exist, insert new
            $insert_stmt->bind_param("ii", $uid, $course_id);
            $insert_stmt->execute();
        }
        
        $check_stmt->close();
        $insert_stmt->close();
        $update_stmt->close();

        $_SESSION['success'] = "Selected users assigned to course.";
        header("Location: manage_all_users.php?course_id={$course_id}");
        exit;
    }

    // 3) Assign users by filter
    if (isset($_POST['assign_by_filter'])) {
        $filter_division = $_POST['filter_division'] !== '' ? (int)$_POST['filter_division'] : null;
        $filter_section = $_POST['filter_section'] !== '' ? (int)$_POST['filter_section'] : null;
        $filter_department = $_POST['filter_department'] !== '' ? (int)$_POST['filter_department'] : null;

        $sql = "SELECT id FROM users WHERE (role = 'user' OR role = 'admin') AND id != ?";
        $params = [$current_user_id];
        $types = 'i';

        if ($filter_division !== null) { $sql .= " AND division = ?"; $params[] = $filter_division; $types .= 'i'; }
        if ($filter_section !== null)  { $sql .= " AND section = ?";  $params[] = $filter_section;  $types .= 'i'; }
        if ($filter_department !== null) { $sql .= " AND department = ?"; $params[] = $filter_department; $types .= 'i'; }

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $user_ids = array_map(fn($r) => (int)$r['id'], $res);
        if (!empty($user_ids)) {
            $check_stmt = $conn->prepare("SELECT id, removed FROM user_courses WHERE user_id = ? AND course_id = ? LIMIT 1");
            $insert_stmt = $conn->prepare("INSERT INTO user_courses (user_id, course_id, removed) VALUES (?, ?, 0)");
            $update_stmt = $conn->prepare("UPDATE user_courses SET removed = 0 WHERE id = ?");


            foreach ($user_ids as $uid) {
                $uid = (int)$uid;
                if ($uid <= 0) continue;

                // Check existing record
                $check_stmt->bind_param("ii", $uid, $course_id);
                $check_stmt->execute();
                $existing = $check_stmt->get_result()->fetch_assoc();

                if ($existing) {
                    // If user-course exists but was removed, reactivate it
                    if ($existing['removed'] == 1) { // <-- should be 1, not 0
                        $update_stmt->bind_param("i", $existing['id']);
                        $update_stmt->execute();
                    }
                    // If already active, skip
                    continue;
                }

                // If doesn't exist, insert new
                $insert_stmt->bind_param("ii", $uid, $course_id);
                $insert_stmt->execute();
            }

            $insert_stmt->close();
            $check_stmt->close();
            $update_stmt->close();

            $_SESSION['success'] = "Users matching filter assigned to course.";
        } else {
            $_SESSION['error'] = "No users match that filter.";
        }

        header("Location: manage_all_users.php?course_id={$course_id}");
        exit;
    }

    // 4) Remove / unassign selected users
    if (isset($_POST['remove_selected_users']) && !empty($_POST['selected_user_remove'])) {
        $to_remove = $_POST['selected_user_remove'];
        $remove_stmt = $conn->prepare("UPDATE user_courses SET removed = 1 WHERE user_id = ? AND course_id = ?");
        foreach ($to_remove as $uid) {
            $uid = (int)$uid;
            if ($uid <= 0) continue;
            $remove_stmt->bind_param("ii", $uid, $course_id);
            $remove_stmt->execute();
        }
        $remove_stmt->close();

        $_SESSION['success'] = "Selected users removed from course.";
        header("Location: manage_all_users.php?course_id={$course_id}");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Manage Users/Sections - <?php echo htmlspecialchars($course['title']); ?></title>
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
                    <i data-lucide="book-open" class="w-6 h-6"></i>
                    Manage Users/Sections — <?php echo htmlspecialchars($course['title']); ?>
                </h1>
                <p class="text-blue-100">Assign users or scope (division / section / department) to this course</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="edit_course.php?id=<?php echo $course_id; ?>" class="px-4 py-2 bg-white text-blue-600 rounded-lg shadow-sm flex items-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Course
                </a>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
                <div><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>
                <div><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Course scope (division/section/department) -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                    <h2 class="text-lg font-semibold mb-3 flex items-center gap-2"><i data-lucide="sliders" class="w-5 h-5 text-blue-600"></i> Course Scope</h2>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Division</label>
                            <select name="division" class="w-full border rounded px-3 py-2">
                                <option value="">-- None --</option>
                                <?php foreach ($divisions as $d): ?>
                                    <option value="<?php echo (int)$d['id']; ?>" <?php echo ($course['division'] == $d['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                            <select name="section" class="w-full border rounded px-3 py-2">
                                <option value="">-- None --</option>
                                <?php foreach ($sections as $s): ?>
                                    <option value="<?php echo (int)$s['id']; ?>" <?php echo ($course['section'] == $s['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                            <select name="department" class="w-full border rounded px-3 py-2">
                                <option value="">-- None --</option>
                                <?php foreach ($departments as $dep): ?>
                                    <option value="<?php echo (int)$dep['id']; ?>" <?php echo ($course['department'] == $dep['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dep['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" name="update_course_scope" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <i data-lucide="save" class="w-4 h-4 mr-1 inline"></i> Save Scope
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Middle + Right: User assignment controls and lists -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Assign by Filter -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                    <h3 class="text-md font-semibold mb-3 flex items-center gap-2"><i data-lucide="filter" class="w-5 h-5 text-blue-600"></i> Assign by Filter</h3>
                    <p class="text-xs text-gray-700 mt-2 mb-5">This will enroll all users that match their selected division / section / department into the course.</p>
                    <form method="POST" action="" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                        <div>
                            <label class="text-xs text-gray-600">Division</label>
                            <select name="filter_division" class="w-full border rounded px-3 py-2">
                                <option value="">Any</option>
                                <?php foreach ($divisions as $d): ?>
                                    <option value="<?php echo (int)$d['id']; ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-600">Section</label>
                            <select name="filter_section" class="w-full border rounded px-3 py-2">
                                <option value="">Any</option>
                                <?php foreach ($sections as $s): ?>
                                    <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-600">Department</label>
                            <select name="filter_department" class="w-full border rounded px-3 py-2">
                                <option value="">Any</option>
                                <?php foreach ($departments as $dep): ?>
                                    <option value="<?php echo (int)$dep['id']; ?>"><?php echo htmlspecialchars($dep['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="text-right">
                            <button type="submit" name="assign_by_filter" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                                <i data-lucide="users" class="w-4 h-4 mr-1 inline"></i> Assign By Filter
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Assign selected users -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-md font-semibold flex items-center gap-2"><i data-lucide="user-plus" class="w-5 h-5 text-blue-600"></i> Assign Selected Users</h3>
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
                                    <?php if (empty($all_users)): ?>
                                        <tr><td colspan="4" class="p-4 text-center text-gray-500">No users found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($all_users as $u): 
                                            $checked = in_array((int)$u['id'], $assigned_user_ids) ? 'checked' : '';
                                        ?>
                                            <tr class="border-t hover:bg-blue-50">
                                                <td class="px-4 py-2 text-center">
                                                    <input type="checkbox" name="selected_user[]" value="<?php echo (int)$u['id']; ?>" class="row-checkbox">
                                                </td>
                                                <td class="px-4 py-2"><?php echo htmlspecialchars($u['full_name']); ?></td>
                                                <td class="px-4 py-2">@<?php echo htmlspecialchars($u['username']); ?></td>
                                                <td class="px-4 py-2"><?php echo htmlspecialchars($u['email']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 flex gap-2">
                            <button type="submit" name="assign_selected_users" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                                <i data-lucide="user-plus" class="w-4 h-4 mr-1 inline"></i> Assign Selected
                            </button>
                            <button type="button" id="checkAssignedBtn" class="px-4 py-2 bg-amber-100 text-amber-800 rounded hover:bg-amber-200 transition">Check All Assigned Users</button>
                        </div>
                    </form>
                </div>

                <!-- Currently assigned users (with remove) -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                    <h3 class="text-md font-semibold mb-3 flex items-center gap-2"><i data-lucide="users" class="w-5 h-5 text-blue-600"></i> Currently Assigned Users</h3>
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
                                    <?php if (empty($assigned_users)): ?>
                                        <tr><td colspan="4" class="p-4 text-center text-gray-500">No users assigned to this course.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($assigned_users as $au): ?>
                                            <tr class="border-t hover:bg-blue-50">
                                                <td class="px-4 py-2 text-center">
                                                    <input type="checkbox" name="selected_user_remove[]" value="<?php echo (int)$au['user_id']; ?>" class="row-remove-checkbox">
                                                </td>
                                                <td class="px-4 py-2"><?php echo htmlspecialchars($au['full_name']); ?></td>
                                                <td class="px-4 py-2">@<?php echo htmlspecialchars($au['username']); ?></td>
                                                <td class="px-4 py-2"><?php echo htmlspecialchars($au['email']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 text-right">
                            <button type="submit" name="remove_selected_users" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                                <i data-lucide="trash-2" class="w-4 h-4 mr-1 inline"></i> Remove Selected
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
lucide.createIcons();

// select/deselect helpers
document.addEventListener('DOMContentLoaded', function () {
    const master = document.getElementById('masterCheckbox');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    if (master) {
        master.addEventListener('change', () => {
            rowCheckboxes.forEach(cb => cb.checked = master.checked);
        });
    }
    document.getElementById('selectAllBtn').addEventListener('click', (e) => {
        e.preventDefault();
        rowCheckboxes.forEach(cb => cb.checked = true);
    });
    document.getElementById('deselectAllBtn').addEventListener('click', (e) => {
        e.preventDefault();
        rowCheckboxes.forEach(cb => cb.checked = false);
    });

    // remove master for assigned users
    const masterRemove = document.getElementById('masterRemoveCheckbox');
    const rowRemoveCheckboxes = document.querySelectorAll('.row-remove-checkbox');
    if (masterRemove) {
        masterRemove.addEventListener('change', () => {
            rowRemoveCheckboxes.forEach(cb => cb.checked = masterRemove.checked);
        });
    }

    // Check assigned button: auto-check checkboxes in "Assign selected" for users who are already assigned
    document.getElementById('checkAssignedBtn')?.addEventListener('click', function(e){
        e.preventDefault();
        const assigned = <?php echo json_encode($assigned_user_ids); ?>;
        rowCheckboxes.forEach(cb => {
            const uid = parseInt(cb.value, 10);
            cb.checked = assigned.includes(uid);
        });
    });
});
</script>
</body>
</html>
