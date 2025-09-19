    <!-- Add Question Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-70 z-50 hidden items-center justify-center backdrop-blur-sm transition-all duration-300" id="addQuestionModal">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl mx-4 transform transition-all duration-300 max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 p-5 rounded-t-xl flex items-center justify-between sticky top-0 z-10">
                <h5 class="text-lg font-semibold text-white flex items-center">
                    <i data-lucide="plus-circle" class="h-5 w-5 mr-2"></i>
                    Add Quiz (optional)
                </h5>
                <button type="button" class="text-white/80 hover:text-white focus:outline-none" onclick="closeAddQuestionModal()">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>
            <div class="p-6 flex flex-col h-full">
            <div class="flex-grow overflow-y-auto pr-2" id="formScrollArea">
                <!-- Scrollable area -->
                <div class="max-w-4xl mx-auto px-4">
                <form id="quizForm" action="server_controller/manage_course_controller.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="add_question" value="2">
                    <input type="hidden" name="course_id" id="hiddenCourseId" value="<?php echo $course_id; ?>">
                    <div id="questionContainer"></div>
                </form>
                </div>
            </div>

            <!-- Sticky action buttons -->
            <div class="sticky bottom-0 bg-white pt-4 pb-6 px-6 shadow-[0_-2px_10px_rgba(0,0,0,0.05)] z-10 flex justify-end gap-4">
                <button type="button" id="addQuestionBtn"
                class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
                ➕ Add Question
                </button>

                <button type="submit" form="quizForm"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                ✅ Submit
                </button>
            </div>
            </div>
            <template id="questionTemplate">
                <div class="question-block mb-8 p-6 bg-white rounded-xl shadow border border-gray-200 relative">
                <label class="block text-lg font-semibold mb-2 question-label">Question 1.</label>
                <textarea name="questions[0][text]" class="w-full border rounded p-3 mb-4" placeholder="Enter question" required></textarea>

                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-1">Upload question image (optional):</p>
                    <input type="file" onchange="checkRequired(this)" name="questions[0][image]" accept="image/*"
                    class="w-full file:bg-indigo-50 file:text-indigo-700 file:rounded file:font-semibold file:border-0 file:py-2 file:px-4 text-sm text-gray-600" />
                </div>

                <div class="grid gap-4">
                    <!-- Options A-D -->
                    @@OPTIONS@@
                </div>

                <button type="button"
                    class="remove-question-btn mt-6 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
                    🗑️ Remove Question
                </button>
                </div>
            </template>
        </div>
    </div>



    <!-- Question Preview Modal -->
    <div id="previewQuestionModal" class="fixed inset-0 bg-black bg-opacity-70 z-50 hidden items-center justify-center backdrop-blur-sm transition-all duration-300">
        <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto shadow-lg">
            <div id="preview-question-list" class="space-y-6"></div>

            <div class="flex justify-end mt-4">
                <button type="button" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition text-sm font-medium hover:-translate-y-1 shadow-sm" onclick="closePreviewModal()">
                    Close Preview
                </button>
            </div>
        </div>
    </div>

     <!-- Alert Modal -->
    <div id="alertModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md text-center">
                <div class="bg-yellow-50 text-black-800 p-4 rounded-lg mb-4 flex items-start">
                    <i data-lucide="alert-circle" class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <p class="font-medium">Warning</p>
                        <p class="text-sm">This action cannot be undone. Your changes will be lost. Do you really want to close this form?</p>
                    </div>
                </div>
            <div class="flex justify-center space-x-4">
                <button onclick="confirmCloseAddQuestion()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">Yes, Close</button>
                <button onclick="hideModal('alertModal')" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </div>
    </div>


<script>
  const container = document.getElementById('questionContainer');
  const addBtn = document.getElementById('addQuestionBtn');
  const template = document.getElementById('questionTemplate');

  let questionCount = 0;

  function generateOptionsHTML(index) {
    return ['a', 'b', 'c', 'd'].map(letter => `
      <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
        <div class="flex items-center mb-3">
          <input type="radio" name="questions[${index}][correct_answer]" value="${letter}" class="h-5 w-5 text-indigo-600 mr-2 correct-answer-radio" required>
          <label class="w-8 text-center font-medium text-indigo-600 bg-indigo-50 rounded-md py-1 mr-3 uppercase">${letter}</label>
          <span class="text-sm font-medium text-gray-700">Option ${letter.toUpperCase()}</span>
          <span class="ml-auto px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full hidden correct-indicator">Correct</span>
        </div>
        <div class="mb-3">
          <input type="text" name="questions[${index}][options][${letter}]" placeholder="Option ${letter.toUpperCase()}"
            class="option-text w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition" required>
        </div>
        <div>
          <p class="text-xs text-gray-500 mb-1">Upload image (optional):</p>
          <input type="file" name="questions[${index}][option_images][${letter}]" onchange="checkRequired(this)" accept="image/*"
            class="option-file w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
        </div>
      </div>
    `).join('');
  }

  function createQuestionBlock(index) {
    let rawHTML = template.innerHTML.replace(/questions\[0\]/g, `questions[${index}]`);
    rawHTML = rawHTML.replace('@@OPTIONS@@', generateOptionsHTML(index));

    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = rawHTML;
    const questionBlock = tempDiv.firstElementChild;

    questionBlock.querySelector('.question-label').textContent = `Question ${index + 1}.`;

    questionBlock.querySelectorAll('.correct-answer-radio').forEach(radio => {
      radio.addEventListener('change', () => {
        questionBlock.querySelectorAll('.correct-indicator').forEach(ind => ind.classList.add('hidden'));
        radio.closest('div').querySelector('.correct-indicator').classList.remove('hidden');
      });
    });

    questionBlock.querySelector('.remove-question-btn').addEventListener('click', () => {
      questionBlock.remove();
      reindexQuestions();
    });

    return questionBlock;
  }

  function addQuestion() {
    const block = createQuestionBlock(questionCount);
    container.appendChild(block);
    questionCount++;
    updateRemoveButtons();
  }

  function reindexQuestions() {
    const blocks = container.querySelectorAll('.question-block');
    blocks.forEach((block, index) => {
      block.querySelector('.question-label').textContent = `Question ${index + 1}.`;

      block.innerHTML = block.innerHTML
        .replace(/questions\[\d+\]/g, `questions[${index}]`)
        .replace(/name="questions\[\d+\]\[options\]\[([a-d])\]"/g, `name="questions[${index}][options][$1]"`)
        .replace(/name="questions\[\d+\]\[option_images\]\[([a-d])\]"/g, `name="questions[${index}][option_images][$1]"`);

      block.querySelectorAll('.correct-answer-radio').forEach(radio => {
        radio.addEventListener('change', () => {
          block.querySelectorAll('.correct-indicator').forEach(ind => ind.classList.add('hidden'));
          radio.closest('div').querySelector('.correct-indicator').classList.remove('hidden');
        });
      });

      block.querySelector('.remove-question-btn').addEventListener('click', () => {
        block.remove();
        reindexQuestions();
      });
    });

    questionCount = blocks.length;
    updateRemoveButtons();
  }

  function updateRemoveButtons() {
    const removeBtns = container.querySelectorAll('.remove-question-btn');
    if (removeBtns.length === 1) {
      removeBtns[0].setAttribute('disabled', true);
      removeBtns[0].classList.add('opacity-50', 'cursor-not-allowed');
    } else {
      removeBtns.forEach(btn => {
        btn.removeAttribute('disabled');
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
      });
    }
  }

  // 🛠️ Check and remove required attribute when file is used
  function checkRequired(input) {
    const questionBlock = input.closest('.question-block');

    if (input.name.includes('[image]')) {
      // It's the question image, remove required on the textarea
      const textArea = questionBlock.querySelector('textarea');
      if (input.files.length > 0) {
        textArea.removeAttribute('required');
      } else {
        textArea.setAttribute('required', 'required');
      }
    } else if (input.name.includes('[option_images]')) {
      // It's an option image, find the matching text input and toggle required
      const optionDiv = input.closest('div').previousElementSibling; // The div with text input
      const textInput = optionDiv.querySelector('input[type="text"]');
      if (input.files.length > 0) {
        textInput.removeAttribute('required');
      } else {
        textInput.setAttribute('required', 'required');
      }
    }
  }

  window.checkRequired = checkRequired;

  addBtn.addEventListener('click', addQuestion);
  addQuestion(); // Load the first question on page load
</script>

<script>
  document.getElementById('quizForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        form.reset();
        document.getElementById('questionContainer').innerHTML = '';
        questionCount = 0;
        showToast(data.message || 'Successfully submitted', 'success');
        if (data.ok || data.success) window.location.href = 'edit_course.php?id=' + currentCourseId;
        hideModal('addQuestionModal');
        addQuestion();
    })
    .catch(error => {
      console.error('Error submitting form:', error);
      alert('There was an error submitting the quiz.');
    });

  });


  function closeAddQuestionModal() {
    window.location.href = 'edit_course.php?id=' + currentCourseId;
    const modal = document.getElementById('addQuestionModal');
    modal.classList.add('hidden');
  }

</script>
