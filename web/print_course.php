<?php
session_start();
require_once '../config.php';

// Admin-only access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$course_id = $_GET['id'] ?? 0;
if ($course_id <= 0) {
    header("Location: manage_courses.php");
    exit;
}

// Get course title
$course_stmt = $conn->prepare("SELECT title FROM courses WHERE id = ?");
$course_stmt->bind_param("i", $course_id);
$course_stmt->execute();
$course = $course_stmt->get_result()->fetch_assoc();
$course_stmt->close();

// Check if course has quiz
$quiz_stmt = $conn->prepare("SELECT COUNT(*) AS quiz_count FROM questions WHERE course_id = ?");
$quiz_stmt->bind_param("i", $course_id);
$quiz_stmt->execute();
$has_quiz = $quiz_stmt->get_result()->fetch_assoc()['quiz_count'] > 0;
$quiz_stmt->close();

// Fetch assigned users
$query = "
    SELECT 
        u.id,
        u.full_name,
        s.name AS section_name,
        d.name AS division_name
    FROM user_courses uc
    JOIN users u ON uc.user_id = u.id
    LEFT JOIN section s ON u.section = s.id
    LEFT JOIN division d ON u.division = d.id
    WHERE uc.course_id = ? AND uc.removed = 0
    ORDER BY u.full_name
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch all course modules
$videos_stmt = $conn->prepare("SELECT id, module_name FROM course_videos WHERE course_id = ?");
$videos_stmt->bind_param("i", $course_id);
$videos_stmt->execute();
$course_videos = $videos_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$videos_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Course Participants - <?php echo htmlspecialchars($course['title']); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
  <div class="max-w-6xl mx-auto my-8 bg-white p-6 rounded-lg shadow-sm">
    <div class="flex justify-between items-center mb-6 border-b pb-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">
          <?php echo htmlspecialchars($course['title']); ?>
        </h1>
        <p class="text-gray-500 text-sm">User Progress Overview (Module-Level)</p>
      </div>
      <div>
        <button onclick="window.print()" 
          class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition">
          🖨️ Print
        </button>
        <a href="manage_courses.php" 
          class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition">
          ← Back
        </a>
      </div>
    </div>

    <?php if (empty($users)): ?>
      <div class="p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700 rounded">
        No users are currently assigned to this course.
      </div>
    <?php elseif (empty($course_videos)): ?>
      <div class="p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700 rounded">
        This course has no modules/videos.
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full border border-gray-200 text-sm">
          <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
            <tr>
              <th class="px-4 py-3 text-left border-b">#</th>
              <th class="px-4 py-3 text-left border-b">Full Name</th>
              <th class="px-4 py-3 text-left border-b">Division</th>
              <th class="px-4 py-3 text-left border-b">Section</th>
              <th class="px-4 py-3 text-left border-b">Progress</th>
              <th class="px-4 py-3 text-left border-b">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <?php 
            foreach ($users as $i => $u): 
              // Get user’s watched videos
              $watched_stmt = $conn->prepare("
                  SELECT video_id FROM user_video_progress 
                  WHERE user_id = ? AND video_id IN (
                      SELECT id FROM course_videos WHERE course_id = ?
                  )
              ");
              $watched_stmt->bind_param("ii", $u['id'], $course_id);
              $watched_stmt->execute();
              $watched_videos = $watched_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
              $watched_ids = array_column($watched_videos, 'video_id');
              $watched_stmt->close();

              // Progress calculation
            $total_modules = count($course_videos);
            $watched_count = count($watched_ids);
            $progress = $total_modules > 0 ? round(($watched_count / $total_modules) * 100) : 0;

            // Check if quiz completed (if course has quiz)
            $quiz_completed = false;
            if ($has_quiz) {
                $quiz_check = $conn->prepare("
                    SELECT completed 
                    FROM user_progress 
                    WHERE user_id = ? AND course_id = ? 
                    LIMIT 1
                ");
                $quiz_check->bind_param("ii", $u['id'], $course_id);
                $quiz_check->execute();
                $quiz_result = $quiz_check->get_result()->fetch_assoc();
                $quiz_completed = !empty($quiz_result) && $quiz_result['completed'] == 1;
                $quiz_check->close();
            }

              // Determine final completion
                $completed_all = $watched_count === $total_modules && (!$has_quiz || $quiz_completed);

                // Determine Status
                if ($watched_count === 0) {
                    $status = '<span class="text-gray-700 bg-gray-50 px-2 py-1 rounded border border-gray-300 text-xs font-semibold">Not Started</span>';
                } elseif ($completed_all) {
                    $status = '<span class="text-green-700 bg-green-50 px-2 py-1 rounded border border-green-300 text-xs font-semibold">Completed</span>';
                } else {
                    // In Progress — show check/X per module
                    $module_status_html = "<ul class='space-y-1'>";
                    foreach ($course_videos as $v) {
                        $isWatched = in_array($v['id'], $watched_ids);
                        $symbol = $isWatched ? '✅' : '❌';
                        $module_status_html .= "<li>{$v['module_name']} <span class='ml-1'>{$symbol}</span></li>";
                    }
                    if ($has_quiz) {
                        $quiz_symbol = $quiz_completed ? '✅' : '❌';
                        $module_status_html .= "<li>Take Quiz <span class='ml-1'>{$quiz_symbol}</span></li>";
                    }
                    $module_status_html .= "</ul>";
                    $status = $module_status_html;
                }
            ?>
              <tr class="align-top hover:bg-gray-50">
                <td class="px-4 py-2 border-b text-gray-600"><?php echo $i + 1; ?></td>
                <td class="px-4 py-2 border-b font-medium text-gray-800"><?php echo htmlspecialchars($u['full_name']); ?></td>
                <td class="px-4 py-2 border-b text-gray-600"><?php echo htmlspecialchars($u['division_name'] ?? '—'); ?></td>
                <td class="px-4 py-2 border-b text-gray-600"><?php echo htmlspecialchars($u['section_name'] ?? '—'); ?></td>
                <td class="px-4 py-2 border-b">
                  <div class="flex items-center gap-2">
                    <div class="w-32 bg-gray-200 rounded-full h-2.5">
                      <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?php echo $progress; ?>%"></div>
                    </div>
                    <span class="text-xs font-medium text-gray-700"><?php echo $progress; ?>%</span>
                  </div>
                </td>
                <td class="px-4 py-2 border-b text-gray-700"><?php echo $status; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>