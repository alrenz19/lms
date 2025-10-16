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
          Course: <?php echo htmlspecialchars($course['title']); ?>
        </h1>
        <p class="text-gray-500 text-sm">User Progress Overview</p>
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
              <th class="px-4 py-3 text-left border-b">Last Activity</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <?php 
            foreach ($users as $i => $u): 
              // --- Get user's watched modules
              $watched_stmt = $conn->prepare("
                  SELECT video_id, updated_at 
                  FROM user_video_progress 
                  WHERE user_id = ? 
                  AND video_id IN (SELECT id FROM course_videos WHERE course_id = ?)
              ");
              $watched_stmt->bind_param("ii", $u['id'], $course_id);
              $watched_stmt->execute();
              $watched_videos = $watched_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
              $watched_stmt->close();

              $watched_ids = array_column($watched_videos, 'video_id');
              $module_timestamps = array_column($watched_videos, 'updated_at');

              // --- Calculate module progress
              $total_modules = count($course_videos);
              $watched_count = count($watched_ids);
              $progress = $total_modules > 0 ? round(($watched_count / $total_modules) * 100) : 0;

              // --- Check quiz completion
              $quiz_completed = false;
              $quiz_timestamp = null;
              if ($has_quiz) {
                  $quiz_check = $conn->prepare("
                      SELECT completed, updated_at 
                      FROM user_progress 
                      WHERE user_id = ? AND course_id = ? 
                      LIMIT 1
                  ");
                  $quiz_check->bind_param("ii", $u['id'], $course_id);
                  $quiz_check->execute();
                  $quiz_result = $quiz_check->get_result()->fetch_assoc();
                  if (!empty($quiz_result)) {
                      $quiz_completed = $quiz_result['completed'] == 1;
                      $quiz_timestamp = $quiz_result['updated_at'] ?? null;
                  }
                  $quiz_check->close();
              }

              // --- Total + completion
              $total_items = $total_modules + ($has_quiz ? 1 : 0);
              $completed_items = $watched_count + ($quiz_completed ? 1 : 0);
              $progress = $total_items > 0 ? round(($completed_items / $total_items) * 100) : 0;
              $completed_all = $completed_items === $total_items;

              // --- Status
              if ($watched_count === 0) {
                  $status = '<span class="text-gray-700 bg-gray-50 px-2 py-1 rounded border border-gray-300 text-xs font-semibold">Not Started</span>';
              } elseif ($completed_all) {
                  $status = '<span class="text-green-700 bg-green-50 px-2 py-1 rounded border border-green-300 text-xs font-semibold">Completed</span>';
              } else {
                  $module_status_html = "<div><b>Modules</b><ul class='ml-4 mt-1 mb-2'>";
                  foreach ($course_videos as $v) {
                      $isWatched = in_array($v['id'], $watched_ids);
                      $symbol = $isWatched ? '✅' : '❌';
                      $module_status_html .= "<li class='mb-1'><span>{$v['module_name']}</span><span class='ml-2'>{$symbol}</span></li>";
                  }
                  $module_status_html .= "</ul>";
                  if ($has_quiz) {
                      $quiz_symbol = $quiz_completed ? '✅' : '❌';
                      $module_status_html .= "<b>Exam</b><ul class='ml-4 mt-1'><li><span>Take Quiz</span><span>{$quiz_symbol}</span></li></ul>";
                  }
                  $module_status_html .= "</div>";
                  $status = $module_status_html;
              }

              // --- 🕒 Last Activity: find latest timestamp
              $timestamps = array_filter([$quiz_timestamp, ...$module_timestamps]);
              if (!empty($timestamps)) {
                  rsort($timestamps); // latest first
                  $last_activity = date("M d, Y h:i A", strtotime($timestamps[0]));
              } else {
                  $last_activity = "—";
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
                <td class="px-4 py-2 border-b text-gray-600"><?php echo $last_activity; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>