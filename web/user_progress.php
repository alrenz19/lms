<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get detailed progress information
$stmt = $conn->prepare("
    SELECT 
        c.id as course_id,
        c.title as course_title,
        c.description as course_description,
        COUNT(DISTINCT q.id) as total_quizzes,
        COUNT(DISTINCT up.quiz_id) as completed_quizzes,
        COALESCE((COUNT(DISTINCT up.quiz_id) * 100 / COUNT(DISTINCT q.id)), 0) as course_progress,
        (
            SELECT COUNT(*) 
            FROM questions qs 
            JOIN quizzes qz ON qs.quiz_id = qz.id
            WHERE qz.course_id = c.id
        ) as total_questions,
        (
            SELECT COUNT(*)
            FROM user_progress up2
            JOIN quizzes q2 ON up2.quiz_id = q2.id
            WHERE q2.course_id = c.id 
            AND up2.user_id = ?
            AND up2.score > 0
        ) as correct_answers,
        COALESCE(MAX(up.updated_at), c.created_at) as last_activity
    FROM courses c
    LEFT JOIN quizzes q ON c.id = q.course_id
    LEFT JOIN user_progress up ON q.id = up.quiz_id AND up.user_id = ?
    GROUP BY c.id
    ORDER BY last_activity DESC, c.title ASC
");

$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate overall statistics
$total_courses = count($courses);
$completed_courses = 0;
$total_correct_answers = 0;
$total_questions = 0;

foreach ($courses as $course) {
    if ($course['course_progress'] == 100) {
        $completed_courses++;
    }
    $total_correct_answers += $course['correct_answers'];
    $total_questions += $course['total_questions'];
}

$overall_progress = $total_courses > 0 ? ($completed_courses / $total_courses) * 100 : 0;
$overall_score = $total_questions > 0 ? ($total_correct_answers / $total_questions) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Progress - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="content">
            <div class="container mt-4">
                <div class="progress-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="page-title text-white">
                                <i class="bi bi-mortarboard-fill text-white"></i>
                                My Learning Progress
                            </h1>
                            <p class="text-white opacity-90 mb-0">Track your course completion and achievements</p>
                        </div>
                    </div>
                </div>

                <!-- Overall Progress Stats -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-icon purple">
                                <i class="bi bi-book-half"></i>
                            </div>
                            <div class="stats-info">
                                <h3>Course Progress</h3>
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-purple" role="progressbar" 
                                         style="width: <?php echo round($overall_progress); ?>%">
                                        <?php echo round($overall_progress); ?>%
                                    </div>
                                </div>
                                <p class="stats-detail">
                                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                                    <?php echo $completed_courses; ?> of <?php echo $total_courses; ?> courses completed
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-icon green">
                                <i class="bi bi-trophy-fill"></i>
                            </div>
                            <div class="stats-info">
                                <h3>Overall Score</h3>
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?php echo round($overall_score); ?>%">
                                        <?php echo round($overall_score); ?>%
                                    </div>
                                </div>
                                <p class="stats-detail">
                                    <i class="bi bi-star-fill text-warning me-1"></i>
                                    <?php echo $total_correct_answers; ?> of <?php echo $total_questions; ?> questions correct
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-icon orange">
                                <i class="bi bi-lightning-charge-fill"></i>
                            </div>
                            <div class="stats-info">
                                <h3>Active Learning</h3>
                                <h2><?php echo count($courses); ?></h2>
                                <p class="stats-detail">
                                    <i class="bi bi-clock-fill text-primary me-1"></i>
                                    Courses in progress
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Course Progress Cards -->
                <div class="row">
                    <div class="col-12">
                        <div class="section-header mb-4">
                            <h2>
                                <i class="bi bi-collection-play me-2 text-purple"></i>
                                Your Courses
                            </h2>
                            <div class="search-wrapper">
                                <i class="bi bi-search search-icon"></i>
                                <input type="search" 
                                       class="search-box" 
                                       id="searchCourses" 
                                       placeholder="Search your courses..." 
                                       required>
                                <button type="button" class="clear-search" onclick="clearSearch()">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <?php foreach ($courses as $course): ?>
                    <div class="col-md-6 col-lg-4 mb-4" data-course-card>
                        <div class="course-progress-card">
                            <div class="course-header">
                                <div class="course-icon">
                                    <i class="bi bi-journal-text"></i>
                                </div>
                                <div class="course-info">
                                    <h4><?php echo htmlspecialchars($course['course_title']); ?></h4>
                                    <span class="badge bg-<?php echo $course['course_progress'] >= 100 ? 'success' : 'purple'; ?>">
                                        <?php echo $course['course_progress'] >= 100 ? 'Completed' : 'In Progress'; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="course-description">
                                <?php echo htmlspecialchars($course['course_description']); ?>
                            </div>
                            
                            <div class="progress-section">
                                <div class="progress">
                                    <div class="progress-bar bg-purple" role="progressbar" 
                                         style="width: <?php echo round($course['course_progress']); ?>%">
                                        <?php echo round($course['course_progress']); ?>%
                                    </div>
                                </div>
                            </div>

                            <div class="course-stats">
                                <div class="stat-item">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    <div>
                                        <small>Completed Quizzes</small>
                                        <strong><?php echo $course['completed_quizzes']; ?> / <?php echo $course['total_quizzes']; ?></strong>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <div>
                                        <small>Correct Answers</small>
                                        <strong><?php echo $course['correct_answers']; ?> / <?php echo $course['total_questions']; ?></strong>
                                    </div>
                                </div>
                            </div>

                            <div class="course-footer">
                                <div class="last-activity">
                                    <i class="bi bi-clock-history"></i>
                                    Last activity: <?php echo date('F j, Y g:i A', strtotime($course['last_activity'])); ?>
                                </div>
                                <a href="view_course.php?id=<?php echo $course['course_id']; ?>" 
                                   class="btn btn-purple">
                                    <i class="bi bi-play-circle-fill"></i> Continue
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchCourses');
            const courseCards = document.querySelectorAll('[data-course-card]');
            
            function filterCourses(searchTerm) {
                searchTerm = searchTerm.toLowerCase();
                courseCards.forEach(card => {
                    const title = card.querySelector('h4').textContent.toLowerCase();
                    const description = card.querySelector('.course-description').textContent.toLowerCase();
                    card.style.display = (title.includes(searchTerm) || description.includes(searchTerm)) ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', (e) => {
                filterCourses(e.target.value);
            });

            window.clearSearch = function() {
                searchInput.value = '';
                filterCourses('');
                searchInput.focus();
            };
        });
    </script>
    <style>
    .progress-header {
        background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px rgba(109, 40, 217, 0.1);
    }

    .page-title {
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .page-title i {
        font-size: 1.75rem;
    }

    .text-white {
        color: #fff !important;
    }

    .opacity-90 {
        opacity: 0.9;
    }

    .stats-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        height: 100%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
    }

    .stats-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
    }

    .stats-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.25rem;
    }

    .stats-icon.purple {
        background: rgba(99, 102, 241, 0.1);
        color: #6366f1;
    }

    .stats-icon.green {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .stats-icon.orange {
        background: rgba(249, 115, 22, 0.1);
        color: #f97316;
    }

    .stats-icon i {
        font-size: 24px;
    }

    .stats-info h3 {
        font-size: 0.9rem;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 0.75rem;
    }

    .stats-info h2 {
        font-size: 2.25rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.5rem;
    }

    .stats-detail {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .progress {
        height: 0.6rem;
        border-radius: 1rem;
        background-color: #f3f4f6;
        overflow: hidden;
    }

    .progress-bar {
        transition: width 0.5s ease;
    }

    .bg-purple {
        background-color: #8b5cf6 !important;
    }

    .text-purple {
        color: #8b5cf6 !important;
    }

    .course-progress-card {
        background: white;
        border-radius: 15px;
        padding: 1.75rem;
        height: 100%;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
        border: 1px solid #f3f4f6;
    }

    .course-progress-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
        border-color: #e5e7eb;
    }

    .course-header {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .course-icon {
        width: 48px;
        height: 48px;
        background: rgba(139, 92, 246, 0.1);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .course-icon i {
        font-size: 24px;
        color: #6366f1;
    }

    .course-info h4 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 0.5rem 0;
        line-height: 1.4;
    }

    .badge {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.35rem 0.75rem;
        border-radius: 9999px;
    }

    .badge.bg-purple {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
    }

    .course-description {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 1.25rem;
        line-height: 1.6;
    }

    .course-stats {
        background: #f9fafb;
        border-radius: 12px;
        padding: 1.25rem;
        margin: 1.25rem 0;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.875rem;
    }

    .stat-item i {
        font-size: 1.25rem;
        color: #6366f1;
    }

    .stat-item small {
        font-size: 0.75rem;
        color: #6b7280;
        display: block;
        margin-bottom: 0.25rem;
    }

    .stat-item strong {
        font-size: 0.9rem;
        color: #1f2937;
        font-weight: 600;
    }

    .course-footer {
        margin-top: auto;
        padding-top: 1.25rem;
        border-top: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .last-activity {
        font-size: 0.75rem;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-purple {
        background-color: #8b5cf6;
        border-color: #8b5cf6;
        color: white;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        font-weight: 500;
        font-size: 0.875rem;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .btn-purple:hover {
        background-color: #7c3aed;
        border-color: #7c3aed;
        color: white;
        transform: translateY(-1px);
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .section-header h2 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .search-wrapper {
        position: relative;
        width: 320px;
    }

    .search-box {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        font-size: 0.875rem;
        color: #1f2937;
        transition: all 0.2s ease;
        background-color: white;
    }

    .search-box:focus {
        outline: none;
        border-color: #8b5cf6;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
    }

    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        font-size: 1rem;
    }

    .clear-search {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6b7280;
        padding: 0;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s ease;
    }

    .clear-search:hover {
        color: #1f2937;
    }

    @media (max-width: 768px) {
        .search-wrapper {
            width: 100%;
            margin-top: 1rem;
        }

        .section-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
    </style>
</body>
</html>
