<?php
include './get_progress.php';
include 'includes/header.php';
?>
<style>
.tab-btn:hover{
    background-color: #f6f9ff;
}
.active-tab{
    background-color: #eff4ff;
}
</style>
<div class="p-8 sm:ml-72">
  <div class="container mx-auto max-w-7xl">
    <!-- Course Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl p-10 shadow-lg mb-8 text-white relative overflow-hidden">
      <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mr-20 -mt-20"></div>
      <div class="absolute bottom-0 left-0 w-48 h-48 bg-white opacity-5 rounded-full -ml-10 -mb-10"></div>
      <div class="absolute bottom-20 right-20 w-32 h-32 bg-white opacity-5 rounded-full"></div>
      
      <div class="flex flex-col lg:flex-row justify-between items-start gap-6 relative z-10">
        <div class="flex-1">
          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-4">
            <h1 class="text-3xl font-bold mb-0"><?php echo htmlspecialchars($questions['title']); ?></h1>
          </div>
          <p class="text-blue-100 text-lg mb-8 max-w-3xl leading-relaxed"><?php echo htmlspecialchars($questions['description']); ?></p>
        </div>
        <a href="dashboard.php" class="px-5 py-2.5 bg-transparent border border-white rounded-full text-white hover:bg-white/10 transition-colors flex items-center gap-2 font-medium shadow-sm">
          <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
      </div>
    </div>

    <!-- Course Content Section -->
    <div class="mt-12">
      <div class="flex items-center mb-6">
        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center mr-3 text-blue-600">
          <i class="bi bi-journal-check text-xl"></i>
        </div>
        <h5 class="text-2xl font-bold text-gray-800">Course Content</h5>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="col-span-1">
          <div class="quiz-card bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
            <div class="p-6">
              <h3 class="text-xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($questions['title']); ?></h3>
              <div class="col-span-3 place-content-center ">
                <div class="flex justify-between items-center mt-2 mb-2">
                  <span id="progress-text" class="text-xs font-medium text-gray-500 ">
                    <?php echo round($overall_progress); ?>% complete
                  </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4 mb-7">
                  <div id="progress-bar" class="bg-blue-600 h-4 rounded-full" style="width: <?php echo round($overall_progress); ?>%"></div>
                </div>
              </div>

              <?php $first = true; foreach ($videos as $module): ?>
                <button type="button"
                        class="tab-btn <?= ($first) ? 'active-tab' : '' ?> w-full py-2.5 rounded-sm font-medium flex items-center gap-2 p-4 video-progress"
                        data-tab="module<?= $module['id'] ?>"
                        data-video-id="<?= $module['id'] ?>">
                  <?= htmlspecialchars($module['module_name']) ?>
                  <span class="check-icon ml-auto">
                    <?= !empty($video_progress[$module['id']]) ? '<i class="bi bi-check-lg text-green-500"></i>' : '' ?>
                  </span>
                </button>
              <?php $first = false; endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Course Content Videos/PDF -->
        <div class="col-span-2">
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 p-6">
            <?php 
            $previous_completed = true;
            foreach ($videos as $index => $video): 
              $video_id = $video['id'];
              $tabId = 'module' . $video_id;
              $file_extension = pathinfo($video['video_url'], PATHINFO_EXTENSION);

              $isFirst = ($index === 0);
              $current_progress = !empty($video_progress[$video_id]);
              $canAccess = $isFirst || $previous_completed;
            ?>
              <div id="<?= $tabId ?>" class="tab-content <?= $isFirst ? '' : 'hidden' ?>">
                <div class="p-0">
                  <h4 class="text-xl font-medium text-gray-900 mb-4"><?= $video['module_description']; ?></h4>

                  <div class="relative">
                    <?php if ($file_extension === 'mp4'): ?>
                      <div class="relative w-full pt-[56.25%]">
                        <video class="module-video absolute top-0 left-0 w-full h-full object-contain"
                               controls <?= $canAccess ? '' : 'data-locked="1"' ?>>
                          <source src="get_video.php?course_id=<?= $course_id ?>&id=<?= $video_id ?>" type="video/mp4">
                        </video>
                      </div>
                    <?php elseif ($file_extension === 'pdf'): ?>
                      <div class="relative w-full">
                        <!-- Scrollable wrapper -->
                        <div class="pdf-scroll-wrapper overflow-y-scroll h-[500px] border rounded" data-video-id="<?= $video_id ?>">
                          <div class="h-[1200px]"> <!-- Force tall container for demo -->
                            <embed src="get_pdf.php?course_id=<?= $course_id ?>&id=<?= $video_id ?>" 
                                  type="application/pdf" 
                                  width="100%" 
                                  height="100%">
                          </div>
                        </div>

                        <div class="completion-status" data-video-id="<?= $video_id ?>">
                          <?php if (!isset($video_progress[$video_id])): ?>
                            <p class="text-sm text-gray-500 mt-2 text-center">
                              
                            </p>
                          <?php else: ?>
                            <p class="text-right text-green-600 font-medium mt-4">✔ Completed</p>
                          <?php endif; ?>
                        </div>
                      </div>

                    <?php endif; ?>

                    <?php if (!$canAccess): ?>
                      <div class="lock-overlay absolute inset-0 bg-white/85 backdrop-blur-sm flex flex-col items-center justify-center text-center p-6">
                        <i class="bi bi-lock text-2xl mb-2 text-gray-700"></i>
                        <p class="text-sm text-gray-700 font-semibold">
                          You must complete the previous module before proceeding.
                        </p>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php $previous_completed = $current_progress; endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Quiz Section -->
    <div class="mt-12">
        <div class="flex items-center mb-6">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center mr-3 text-blue-600">
                <i class="bi bi-journal-check text-xl"></i>
            </div>
            <h5 class="text-2xl font-bold text-gray-800">Course Quiz</h5>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <?php if ($questions['has_questions']): ?>
                <?php 
                    $question = $questions;
                ?>
                <div class="quiz-card bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                    <div class="p-6">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mb-4 text-blue-600">
                            <i class="bi bi-question-circle text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Quiz</h3>
                        
                        <?php if ($question['is_completed']): ?>
                            <a href="quiz_review.php?id=<?= $question['course_id']; ?>"
                              class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 border-2 border-blue-600 text-blue-600 rounded-xl hover:bg-blue-50 transition-colors font-medium">
                                <i class="bi bi-eye"></i> Review Quiz
                            </a>
                        <?php elseif ($question['attempts'] >= 1 && $question['score_percentage'] < 70): ?>
                            <a href="take_quiz.php?id=<?= $question['course_id']; ?>"
                              class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition-colors font-medium shadow-sm">
                                <i class="bi bi-arrow-repeat"></i> Retake Quiz (<?= 3 - $question['attempts']; ?> left)
                            </a>
                        <?php elseif ($overall_progress === 0): ?>
                            <button disabled
                                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-gray-300 text-gray-500 rounded-xl cursor-not-allowed">
                                <i class="bi bi-lock"></i> Finish the module first
                            </button>
                        <?php else: ?>
                            <a href="take_quiz.php?id=<?= $question['course_id']; ?>"
                              class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium shadow-sm">
                                <i class="bi bi-play-fill"></i> Start Quiz
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- No quiz available fallback (already present) -->
                <div class="col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 p-8 text-center">
                    <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-4 text-blue-600">
                        <i class="bi bi-clipboard-x text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">No Exam Available</h3>
                    <p class="text-gray-500 mb-0">This course doesn't have any exam.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const buttons = document.querySelectorAll('.tab-btn');
  const tabs = document.querySelectorAll('.tab-content');

  buttons.forEach(button => {
    button.addEventListener('click', () => {
      const target = button.getAttribute('data-tab');
      buttons.forEach(btn => btn.classList.remove('active-tab'));
      tabs.forEach(tab => tab.classList.add('hidden'));
      document.getElementById(target).classList.remove('hidden');
      button.classList.add('active-tab');
    });
  });

  // Bind video ended listeners
  function bindVideoEndedListeners(scope = document) {
    scope.querySelectorAll('video').forEach(v => {
      if (!v.dataset.boundEnded) {
        v.addEventListener('ended', function () {
          const videoId = v.closest('.tab-content').id.replace('module', '');
          markModuleAsWatched(videoId);
        });
        v.dataset.boundEnded = '1';
      }
    });
  }
  bindVideoEndedListeners();

  function unlockModulesAccordingToProgress(data) {
    const btns = Array.from(document.querySelectorAll('.tab-btn'));
    for (let i = 0; i < btns.length; i++) {
      const currentId = btns[i].getAttribute('data-video-id');
      const prevCompleted = (i === 0) ? true : (data.video_progress[btns[i - 1].getAttribute('data-video-id')] === 'completed');
      const tab = document.getElementById('module' + currentId);
      if (!tab) continue;
      const overlay = tab.querySelector('.lock-overlay');
      if (prevCompleted && overlay) {
        overlay.remove();
        tab.querySelectorAll('[data-locked="1"]').forEach(el => el.removeAttribute('data-locked'));
      }
    }
    bindVideoEndedListeners();
  }

  function switchToNextTab(completedVideoId, data) {
    const buttons = Array.from(document.querySelectorAll('.tab-btn'));
    const idx = buttons.findIndex(b => b.getAttribute('data-video-id') === String(completedVideoId));
    if (idx === -1 || idx === buttons.length - 1) return;
    const nextBtn = buttons[idx + 1];
    const prevId = buttons[idx].getAttribute('data-video-id');
    const prevCompleted = data.video_progress[prevId] === 'completed';
    if (prevCompleted) nextBtn.click();
  }

  document.querySelectorAll('.pdf-scroll-wrapper').forEach(wrapper => {
    let marked = false;
    const videoId = wrapper.getAttribute('data-video-id');

    wrapper.addEventListener('scroll', function() {
      if (marked) return;
      const atBottom = wrapper.scrollTop + wrapper.clientHeight >= wrapper.scrollHeight - 5;
      if (atBottom) {
        marked = true;
        markModuleAsWatched(videoId); // <-- your existing function
      }
    });
  });



  function markModuleAsWatched(videoId) {
    fetch('update_video_progress.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        video_id: videoId,
        user_id: "<?php echo $_SESSION['user_id']; ?>",
        course_id: "<?php echo $course_id; ?>"
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showToast(data.message || 'Successfully done this module', 'success');
        updateProgressUI(data);
        unlockModulesAccordingToProgress(data);
        switchToNextTab(videoId, data);
      } else {
        showToast(data.message || 'Something went wrong', 'error');
      }
    })
    .catch(err => console.error('Error:', err));
  }

  function updateProgressUI(data) {
    const progressText = document.getElementById("progress-text");
    const progressBar = document.getElementById("progress-bar");
    if (progressText && progressBar) {
      progressText.textContent = `${data.overall_progress}% complete`;
      progressBar.style.width = `${data.overall_progress}%`;
    }

    const progressElements = document.querySelectorAll('.video-progress');
    progressElements.forEach(element => {
      const videoId = element.getAttribute('data-video-id');
      const isCompleted = data.video_progress[videoId] === 'completed';
      const checkIcon = element.querySelector('.check-icon');
      checkIcon.innerHTML = isCompleted ? '<i class="bi bi-check-lg text-green-500"></i>' : '';
    });

    const completionContainers = document.querySelectorAll('.completion-status');
    completionContainers.forEach(container => {
      const videoId = container.getAttribute('data-video-id');
      const isCompleted = data.video_progress[videoId] === 'completed';
      if (isCompleted) {
        container.innerHTML = `<p class="text-right text-green-600 font-medium mt-4">✔ Completed</p>`;
      }
    });

    const quizActionBtn = document.getElementById('quiz-action-btn');
    if (quizActionBtn) {
      if (data.can_access_quiz) {
        quizActionBtn.innerHTML = `
          <a href="take_quiz.php?id=${data.course_id}"
             class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium shadow-sm">
            <i class="bi bi-play-fill"></i> Start Quiz
          </a>
        `;
      } else {
        quizActionBtn.innerHTML = `
          <button disabled
                  class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-gray-300 text-gray-500 rounded-xl cursor-not-allowed">
            <i class="bi bi-lock"></i> Finish the module first
          </button>
        `;
      }
    }

    unlockModulesAccordingToProgress(data);
  }
});
</script>

<script>
function showToast(message, type = 'info') {
  let toastContainer = document.getElementById('toast-container');
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.id = 'toast-container';
    toastContainer.className = 'fixed bottom-4 right-4 z-50 flex flex-col gap-2';
    document.body.appendChild(toastContainer);
  }
  const toast = document.createElement('div');
  toast.className = 'transform transition-all duration-300 ease-in-out translate-x-full';
  const config = {
    success: ['bg-green-500', 'text-white', 'check-circle'],
    error: ['bg-red-500', 'text-white', 'alert-circle'],
    warning: ['bg-amber-500', 'text-white', 'alert-triangle'],
    info: ['bg-blue-500', 'text-white', 'info']
  };
  const [bgColor, textColor, icon] = config[type] || config.info;
  toast.className += ` ${bgColor} ${textColor} rounded-lg shadow-lg p-4 mb-2 flex items-center`;
  toast.innerHTML = `<i data-lucide="${icon}" class="w-5 h-5 mr-2"></i><span>${message}</span>`;
  toastContainer.appendChild(toast);
  if (typeof lucide !== 'undefined') {
    lucide.createIcons({ attrs: { class: ["stroke-current"] } });
  }
  setTimeout(() => toast.classList.replace('translate-x-full', 'translate-x-0'), 10);
  setTimeout(() => {
    toast.classList.replace('translate-x-0', 'translate-x-full');
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}
</script>
