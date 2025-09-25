<?php
/**
 * Dashboard Card Component
 * 
 * @param string $title The card title
 * @param string $value The main value to display
 * @param string $icon The Lucide icon name
 * @param string $color The accent color (default: 'blue')
 */

function dashboardCard($title, $value, $icon, $color = 'blue') {
    $bgColor = "bg-{$color}-100";
    $textColor = "text-{$color}-800";
    $iconBg = "bg-{$color}-500";
    
    echo <<<HTML
    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-gray-700 font-medium">{$title}</h3>
            <div class="{$bgColor} {$textColor} p-2 rounded-lg">
                <i data-lucide="{$icon}" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="flex items-center">
            <span class="text-3xl font-bold text-gray-900">{$value}</span>
        </div>
    </div>
HTML;
}

/**
 * Usage Example:
 * 
 * <?php 
 * include_once('components/dashboard_card.php');
 * dashboardCard('Total Users', $user_count, 'users', 'blue');
 * dashboardCard('Active Courses', $course_count, 'book-open', 'green');
 * dashboardCard('Quiz Submissions', $quiz_count, 'file-text', 'amber');
 * ?>
 */ 