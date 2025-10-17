<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'user') {
    header("Location: index.php");
    exit;
}

// Updated query without referencing the quizzes table
$query = "
    SELECT 
        u.id AS user_id,
        u.username,
        u.full_name,
        u.email,

        -- ✅ Count total enrolled courses from user_courses
        (
            SELECT COUNT(DISTINCT uc.course_id)
            FROM user_courses uc
            WHERE uc.user_id = u.id
              AND (uc.removed = 0 OR uc.removed IS NULL)
        ) AS total_enrolled_courses,

        -- ✅ Get enrolled course titles
        (
            SELECT GROUP_CONCAT(DISTINCT c3.title SEPARATOR ', ')
            FROM courses c3
            INNER JOIN user_courses uc3 ON c3.id = uc3.course_id
            WHERE uc3.user_id = u.id
              AND (uc3.removed = 0 OR uc3.removed IS NULL)
              AND c3.removed = 0
        ) AS enrolled_courses,

        -- ✅ Get completed course titles
        (
            SELECT GROUP_CONCAT(DISTINCT c2.title SEPARATOR ', ')
            FROM courses c2
            INNER JOIN user_progress up2 ON c2.id = up2.course_id
            WHERE up2.user_id = u.id
              AND up2.completed = 1
              AND c2.removed = 0
        ) AS completed_courses,

        -- ✅ Total correct answers
        (
            SELECT COUNT(*)
            FROM user_progress up3
            WHERE up3.user_id = u.id
              AND up3.score > 0
        ) AS total_correct_answers,

        MAX(up.updated_at) AS last_activity

    FROM users u
    LEFT JOIN user_progress up ON u.id = up.user_id
    WHERE u.role IN ('user', 'admin')
    GROUP BY u.id
    ORDER BY last_activity DESC
";



$result = $conn->query($query);

// Get total courses and active users
$total_courses = $conn->query("SELECT COUNT(*) as total FROM courses WHERE removed = 0")->fetch_assoc()['total'];
$total_users = $result->num_rows;

// Calculate overall platform statistics
$total_completed_courses = 0;
$total_correct_answers = 0;
$active_users_last_week = 0;
$current_time = time();

$rows = []; // make sure this is defined before the loop

while ($row = $result->fetch_assoc()) {
    // ✅ Completed course count
    $completed_count = 0;
    if (!empty($row['completed_courses'])) {
        $completed_count = count(array_filter(array_map('trim', explode(',', $row['completed_courses']))));
    }
    $row['completed_courses_count'] = $completed_count;

    // ✅ Enrolled course count
    $enrolled_count = 0;
    if (!empty($row['enrolled_courses'])) {
        $enrolled_count = count(array_filter(array_map('trim', explode(',', $row['enrolled_courses']))));
    }
    $row['enrolled_courses_count'] = $enrolled_count;

    // ✅ Track totals
    $total_completed_courses += $completed_count;
    $total_correct_answers += $row['total_correct_answers'];

    if ($row['last_activity'] && strtotime($row['last_activity']) > ($current_time - 7 * 24 * 60 * 60)) {
        $active_users_last_week++;
    }

    // ✅ push after all processing
    $rows[] = $row;
}



$result->data_seek(0); // Reset result pointer

// Calculate average completion rate
$avg_completion = $total_users > 0 ? round($total_completed_courses / $total_users, 1) : 0;

// Include the header
include 'includes/header.php';

// Include the dashboard card component
include_once 'components/dashboard_card.php';
?>
    
<div class="p-8 sm:ml-72">
    <div class="container mx-auto">
        <!-- Page header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-6 mb-6 flex justify-between items-center shadow-sm">
            <div class="flex items-center">
                <div class="bg-white/10 p-3 rounded-lg mr-4">
                    <i data-lucide="bar-chart-2" class="h-8 w-8 text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">User Progress Overview</h1>
                    <p class="text-blue-100">Monitor learning progress and performance across all users</p>
                </div>
            </div>
        </div>

        <!-- Platform Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-700 font-medium">Active Users</h3>
                    <div class="bg-blue-100 text-blue-800 p-2 rounded-lg">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $total_users; ?></div>
                <div class="text-sm text-gray-500 flex items-center">
                    <i data-lucide="activity" class="w-4 h-4 mr-1 text-blue-500"></i>
                    <span><?php echo $active_users_last_week; ?> active this week</span>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-700 font-medium">Total Courses</h3>
                    <div class="bg-blue-100 text-blue-800 p-2 rounded-lg">
                        <i data-lucide="book-open" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $total_courses; ?></div>
                <div class="text-sm text-gray-500 flex items-center">
                    <i data-lucide="check-circle" class="w-4 h-4 mr-1 text-green-500"></i>
                    <span><?php echo $total_completed_courses; ?> completions</span>
                </div>
            </div>
            
            <!-- <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-700 font-medium">Correct Answers</h3>
                    <div class="bg-green-100 text-green-800 p-2 rounded-lg">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $total_correct_answers; ?></div>
                <div class="text-sm text-gray-500 flex items-center">
                    <i data-lucide="globe" class="w-4 h-4 mr-1 text-blue-500"></i>
                    <span>Platform-wide</span>
                </div>
            </div> -->
            
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-700 font-medium">Average Completion</h3>
                    <div class="bg-amber-100 text-amber-800 p-2 rounded-lg">
                        <i data-lucide="zap" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $avg_completion; ?></div>
                <div class="text-sm text-gray-500 flex items-center">
                    <i data-lucide="book" class="w-4 h-4 mr-1 text-amber-500"></i>
                    <span>Courses per user</span>
                </div>
            </div>
        </div>

        <!-- User Progress Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i data-lucide="users" class="w-5 h-5 text-blue-600 mr-2"></i>
                    User Progress Details
                </h2>
                <div class="flex items-center gap-2">
                    <!-- Download CSV Button -->
                    <button onclick="downloadCSV()" 
                            class="flex items-center gap-2 px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors text-sm font-medium">
                        <i data-lucide="download" class="w-4 h-4"></i>
                    </button>
                    
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-64">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </span>
                        <input type="search" 
                            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            id="searchProgress" 
                            placeholder="Search users..." 
                            autocomplete="off">
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrolled Courses</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed Courses</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Activity</th>
                        </tr>
                    </thead>
                    <tbody id="progressTableBody" class="divide-y divide-gray-200">
                        <?php if ($result->num_rows === 0): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-500">
                                        <i data-lucide="bar-chart-off" class="h-12 w-12 text-gray-300 mb-4"></i>
                                        <h3 class="text-lg font-medium text-gray-900 mb-1">No progress data available</h3>
                                        <p class="text-gray-500">User progress information will appear here when users start taking courses</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $result->free_result(); // free memory
                                foreach ($rows as $row):
                                $completion_rate = $row['total_enrolled_courses'] > 0 ? (($row['completed_courses_count'] ?? 0) / $row['total_enrolled_courses']) * 100 : 0;
                            ?>
                            <tr class="hover:bg-blue-50/50 transition-colors" data-name="<?php echo strtolower($row['full_name']); ?>" data-email="<?php echo strtolower($row['email']); ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 flex-shrink-0 bg-blue-50 rounded-full flex items-center justify-center mr-4">
                                            <i data-lucide="user" class="w-5 h-5 text-blue-600"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($row['full_name']); ?>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                @<?php echo htmlspecialchars($row['username']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($row['email']); ?>
                                </td>

                                <td class="px-10 py-4 whitespace-nowrap align-top">
                                    <div class="text-sm font-medium text-blue-600 flex items-center mb-1">
                                        <i data-lucide="book-open" class="w-4 h-4 mr-1 <?php echo !empty($row['enrolled_courses']) ? 'text-blue-500' : 'text-gray-300'; ?>"></i>
                                        <?php echo $row['enrolled_courses_count'] ?? 0; ?>
                                    </div>

                                    <?php if (!empty($row['enrolled_courses'])): ?>
                                        <?php 
                                            $enrolled_list = array_map('trim', explode(',', $row['enrolled_courses']));
                                        ?>
                                        <ul class="[list-style-type:'▹'] list-inside text-xs text-gray-600 space-y-0.5">
                                            <?php foreach ($enrolled_list as $course): ?>
                                                <li><?php echo htmlspecialchars($course); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </td>

                                <td class="px-10 py-4 whitespace-nowrap align-top">
                                    <div class="text-sm font-medium text-emerald-600 flex items-center mb-1">
                                        <i data-lucide="check-circle" class="w-4 h-4 mr-1 <?php echo !empty($row['completed_courses']) ? 'text-emerald-500' : 'text-gray-300'; ?>"></i>
                                        <?php echo $row['completed_courses_count'] ?? 0; ?>
                                    </div>

                                    <?php if (!empty($row['completed_courses'])): ?>
                                        <?php 
                                            // Split the string into course names
                                            $courses_list = array_map('trim', explode(',', $row['completed_courses']));
                                        ?>
                                        <ul class="[list-style-type:'▹'] list-inside text-xs text-gray-600 space-y-0.5">
                                            <?php foreach ($courses_list as $course): ?>
                                                <li><?php echo htmlspecialchars($course); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                                        <div class="bg-blue-600 h-2.5 rounded-full" 
                                             style="width: <?php echo round($completion_rate); ?>%" 
                                             title="<?php echo round($completion_rate); ?>% completed"></div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div class="text-xs text-gray-500 myProgress">
                                            <?php echo round($completion_rate); ?>%
                                        </div>
                                        <?php if ($completion_rate === 100): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                <i data-lucide="check" class="w-3 h-3 mr-1"></i> Complete
                                            </span>
                                        <?php elseif ($completion_rate > 0): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                <i data-lucide="clock" class="w-3 h-3 mr-1"></i> In Progress
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                <i data-lucide="clock" class="w-3 h-3 mr-1"></i> Not Started
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($row['last_activity']): ?>
                                        <span class="text-sm text-gray-500 flex items-center">
                                            <i data-lucide="clock" class="w-4 h-4 mr-1 text-blue-500"></i>
                                            <?php 
                                                $time_ago = time() - strtotime($row['last_activity']);
                                                if ($time_ago < 60*60*24) { // less than 24 hours
                                                    echo date('g:i A', strtotime($row['last_activity']));
                                                } else {
                                                    echo date('M j, Y', strtotime($row['last_activity']));
                                                }
                                            ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-400 italic">No activity</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Empty Search Results Message -->
            <div id="noSearchResults" class="hidden p-12 text-center">
                <i data-lucide="search-x" class="h-12 w-12 text-gray-300 mx-auto mb-4"></i>
                <h3 class="text-lg font-medium text-gray-700 mb-2">No users found</h3>
                <p class="text-gray-500 mb-4">Try adjusting your search term</p>
                <button class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm font-medium" 
                        onclick="clearSearch()">
                    Clear Search
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function downloadCSV() {
        const rows = document.querySelectorAll('#progressTableBody tr:not(.hidden)');
        let csvContent = "data:text/csv;charset=utf-8,";

        const headers = ["Full Name", "Username", "Email", "Enrolled Courses Count", "Enrolled Courses", "Completed Courses Count", "Completed Courses", "Last Activity"];
csvContent += headers.join(",") + "\r\n";

rows.forEach(row => {
    if (row.querySelector('td[colspan]')) return;

    const name = row.querySelector('.text-gray-900')?.textContent.trim() || "";
    const username = row.querySelector('.text-xs.text-gray-500')?.textContent.trim().replace('@', '') || "";
    const email = row.querySelectorAll('td')[1]?.textContent.trim() || "";

    // Enrolled
    const enrolledCount = row.querySelector('.text-sm.font-medium.text-blue-600')?.textContent.trim() || "0";
    const enrolledList = Array.from(row.querySelectorAll('td:nth-child(3) ul li'))
        .map(li => li.textContent.trim().replace(/,/g, ''))
        .join('; ');

    // Completed
    const completedCount = row.querySelector('.text-sm.font-medium.text-emerald-600')?.textContent.trim() || "0";
    const completedList = Array.from(row.querySelectorAll('td:nth-child(4) ul li'))
        .map(li => li.textContent.trim().replace(/,/g, ''))
        .join('; ');

    const lastActivity = row.querySelectorAll('td:last-child')?.[0]?.textContent.trim() || "No activity";

    const rowData = [
        `"${name}"`,
        `"${username}"`,
        `"${email}"`,
        `"${enrolledCount}"`,
        `"${enrolledList}"`,
        `"${completedCount}"`,
        `"${completedList}"`,
        `"${lastActivity}"`
    ];
    csvContent += rowData.join(",") + "\r\n";
});


        // ✅ Create CSV file and trigger download
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "user_progress_report.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Search functionality
        const searchInput = document.getElementById('searchProgress');
        const tableRows = document.querySelectorAll('#progressTableBody tr:not([colspan])');
        const noResultsMessage = document.getElementById('noSearchResults');
        const tableBody = document.getElementById('progressTableBody');
        
        function filterTable(searchTerm) {
            searchTerm = searchTerm.toLowerCase();
            let hasResults = false;
            
            tableRows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const email = row.getAttribute('data-email') || '';
                const username = row.querySelector('.text-gray-500')?.textContent.toLowerCase() || '';
                
                if (name.includes(searchTerm) || email.includes(searchTerm) || username.includes(searchTerm)) {
                    row.classList.remove('hidden');
                    hasResults = true;
                } else {
                    row.classList.add('hidden');
                }
            });
            
            // Show/hide no results message
            if (!hasResults && searchTerm.length > 0) {
                noResultsMessage.classList.remove('hidden');
                // If there's a "no data available" row, hide it during search
                const noDataRow = document.querySelector('#progressTableBody tr[colspan]');
                if (noDataRow) noDataRow.classList.add('hidden');
            } else {
                noResultsMessage.classList.add('hidden');
                // Restore "no data available" row if it exists and search is cleared
                if (searchTerm === '') {
                    const noDataRow = document.querySelector('#progressTableBody tr[colspan]');
                    if (noDataRow) noDataRow.classList.remove('hidden');
                }
            }
        }
        
        searchInput.addEventListener('input', function() {
            filterTable(this.value);
        });
        
        // Clear search function
        window.clearSearch = function() {
            searchInput.value = '';
            filterTable('');
            searchInput.focus();
        };
    });
</script>