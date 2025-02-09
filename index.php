
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>YouTube Clipper</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body>
    <div id="app" class="min-h-screen transition-colors duration-200">
      <!-- Responsive Navbar -->
      <nav class="fixed w-full z-10 transition-colors duration-200" id="navbar">
        <div class="container mx-auto px-4">
          <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-2">
              <i data-lucide="youtube" class="w-8 h-8 text-red-600"></i>
              <span class="text-xl font-bold">YouTube Clipper</span>
            </div>
            <div class="flex items-center gap-4">
              <button
                id="themeToggle"
                class="p-2 rounded-full transition-colors duration-200"
              ></button>
              <a href="https://github.com" target="_blank" class="p-2 rounded-full hover:bg-gray-200 transition-colors duration-200">
                <i data-lucide="github" class="w-5 h-5"></i>
              </a>
            </div>
          </div>
        </div>
      </nav>

      <div class="container mx-auto px-4 py-24">
        <div class="max-w-2xl mx-auto">
          <div class="text-center mb-12">
            <div class="flex justify-center items-center gap-3 mb-6">
              <i data-lucide="youtube" class="w-12 h-12"></i>
              <i data-lucide="scissors" class="w-8 h-8"></i>
            </div>
            <h1 class="text-4xl font-bold mb-4">YouTube Clipper</h1>
            <p class="text-lg transition-colors duration-200">
              Create the perfect clip from any YouTube video
            </p>
          </div>

          <form id="clipForm" class="space-y-6" action="process.php" method="POST">
            <div class="p-6 rounded-xl shadow-lg transition-colors duration-200">
              <div class="space-y-6">
                <div>
                  <label class="block text-sm font-medium mb-2">Video URL</label>
                  <div class="relative">
                    <i data-lucide="youtube" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5"></i>
                    <input
                      type="text"
                      id="url"
                      name="url"
                      placeholder="Paste YouTube URL here"
                      required
                      class="w-full pl-12 pr-4 py-3 rounded-lg border transition-colors duration-200 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                    />
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium mb-2">Start Time (seconds)</label>
                    <div class="relative">
                      <i data-lucide="clock" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5"></i>
                      <input
                        type="number"
                        id="startTime"
                        name="start_time"
                        placeholder="0"
                        required
                        min="0"
                        class="w-full pl-12 pr-4 py-3 rounded-lg border transition-colors duration-200 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                      />
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-2">End Time (seconds)</label>
                    <div class="relative">
                      <i data-lucide="clock" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5"></i>
                      <input
                        type="number"
                        id="endTime"
                        name="end_time"
                        placeholder="60"
                        required
                        min="1"
                        class="w-full pl-12 pr-4 py-3 rounded-lg border transition-colors duration-200 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <button
              type="submit"
              id="submitBtn"
              class="w-full py-4 rounded-lg font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
            >
              Create Clip
            </button>
          </form>

          <!-- Loading Spinner -->
          <div id="loading" class="hidden mt-8">
            <div class="flex flex-col items-center justify-center">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
              <p class="mt-4 text-lg">Creating your clip...</p>
              <p class="text-sm text-gray-500 mt-2">This may take a few minutes depending on the video length</p>
            </div>
          </div>

          <div id="status" class="mt-4 text-center"></div>
        </div>
      </div>
    </div>

    <script>
      // Initialize Lucide icons
      lucide.createIcons();

      // Theme handling
      let isDarkMode = false;
      const app = document.getElementById('app');
      const navbar = document.getElementById('navbar');
      const themeToggle = document.getElementById('themeToggle');
      const form = document.getElementById('clipForm');
      const status = document.getElementById('status');
      const loading = document.getElementById('loading');
      const submitBtn = document.getElementById('submitBtn');

      function updateTheme() {
        if (isDarkMode) {
          app.classList.add('bg-gray-900', 'text-white');
          app.classList.remove('bg-gray-50', 'text-gray-900');
          navbar.classList.add('bg-gray-800');
          navbar.classList.remove('bg-white');
          themeToggle.classList.add('bg-gray-800', 'hover:bg-gray-700');
          themeToggle.classList.remove('bg-gray-200', 'hover:bg-gray-300');
          themeToggle.innerHTML = '<i data-lucide="sun" class="w-5 h-5"></i>';
          
          // Update form styles
          document.querySelector('form > div').classList.add('bg-gray-800');
          document.querySelector('form > div').classList.remove('bg-white');
          
          // Update inputs
          document.querySelectorAll('input').forEach(input => {
            input.classList.add('bg-gray-700', 'border-gray-600');
            input.classList.remove('bg-gray-50', 'border-gray-300');
          });
          
          // Update icons
          document.querySelectorAll('[data-lucide="youtube"]')[0].classList.add('text-red-500');
          document.querySelectorAll('[data-lucide="youtube"]')[0].classList.remove('text-red-600');
          document.querySelectorAll('[data-lucide="scissors"]')[0].classList.add('text-blue-400');
          document.querySelectorAll('[data-lucide="scissors"]')[0].classList.remove('text-blue-600');
          document.querySelectorAll('[data-lucide="youtube"]')[1].classList.add('text-gray-400');
          document.querySelectorAll('[data-lucide="youtube"]')[1].classList.remove('text-gray-500');
          document.querySelectorAll('[data-lucide="clock"]').forEach(icon => {
            icon.classList.add('text-gray-400');
            icon.classList.remove('text-gray-500');
          });
        } else {
          app.classList.add('bg-gray-50', 'text-gray-900');
          app.classList.remove('bg-gray-900', 'text-white');
          navbar.classList.add('bg-white');
          navbar.classList.remove('bg-gray-800');
          themeToggle.classList.add('bg-gray-200', 'hover:bg-gray-300');
          themeToggle.classList.remove('bg-gray-800', 'hover:bg-gray-700');
          themeToggle.innerHTML = '<i data-lucide="moon" class="w-5 h-5"></i>';
          
          // Update form styles
          document.querySelector('form > div').classList.add('bg-white');
          document.querySelector('form > div').classList.remove('bg-gray-800');
          
          // Update inputs
          document.querySelectorAll('input').forEach(input => {
            input.classList.add('bg-gray-50', 'border-gray-300');
            input.classList.remove('bg-gray-700', 'border-gray-600');
          });
          
          // Update icons
          document.querySelectorAll('[data-lucide="youtube"]')[0].classList.add('text-red-600');
          document.querySelectorAll('[data-lucide="youtube"]')[0].classList.remove('text-red-500');
          document.querySelectorAll('[data-lucide="scissors"]')[0].classList.add('text-blue-600');
          document.querySelectorAll('[data-lucide="scissors"]')[0].classList.remove('text-blue-400');
          document.querySelectorAll('[data-lucide="youtube"]')[1].classList.add('text-gray-500');
          document.querySelectorAll('[data-lucide="youtube"]')[1].classList.remove('text-gray-400');
          document.querySelectorAll('[data-lucide="clock"]').forEach(icon => {
            icon.classList.add('text-gray-500');
            icon.classList.remove('text-gray-400');
          });
        }
        lucide.createIcons();
      }

      // Initialize theme
      updateTheme();

      // Theme toggle handler
      themeToggle.addEventListener('click', () => {
        isDarkMode = !isDarkMode;
        updateTheme();
      });

      // Function to validate YouTube URL
      function isValidYouTubeUrl(url) {
        const pattern = /^(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})$/;
        return pattern.test(url);
      }

      // Function to format time in HH:MM:SS
      function formatTime(seconds) {
        return new Date(seconds * 1000).toISOString().substr(11, 8);
      }

      // Form validation and submission handler
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const url = document.getElementById('url').value;
        const startTime = parseInt(document.getElementById('startTime').value);
        const endTime = parseInt(document.getElementById('endTime').value);
        
        // Enhanced URL validation
        if (!isValidYouTubeUrl(url)) {
          status.innerHTML = '<p class="text-red-500 bg-red-100 p-4 rounded-lg">Please enter a valid YouTube URL (e.g., https://youtube.com/watch?v=xxxxx)</p>';
          return;
        }
        
        // Enhanced time validations
        if (isNaN(startTime) || startTime < 0) {
          status.innerHTML = '<p class="text-red-500 bg-red-100 p-4 rounded-lg">Start time must be a positive number</p>';
          return;
        }
        
        if (isNaN(endTime) || endTime <= startTime) {
          status.innerHTML = '<p class="text-red-500 bg-red-100 p-4 rounded-lg">End time must be greater than start time</p>';
          return;
        }

        if (endTime - startTime > 600) { // 10 minutes max
          status.innerHTML = '<p class="text-red-500 bg-red-100 p-4 rounded-lg">Clip duration cannot exceed 10 minutes</p>';
          return;
        }
        
        // Show loading state
        loading.classList.remove('hidden');
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Processing...';
        status.innerHTML = '';
        
        // If validation passes, submit the form
        const formData = new FormData(form);
        fetch('process.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.text())
        .then(data => {
          loading.classList.add('hidden');
          submitBtn.disabled = false;
          submitBtn.innerHTML = 'Create Clip';
          status.innerHTML = data;
        })
        .catch(error => {
          loading.classList.add('hidden');
          submitBtn.disabled = false;
          submitBtn.innerHTML = 'Create Clip';
          status.innerHTML = '<p class="text-red-500 bg-red-100 p-4 rounded-lg">An error occurred. Please try again.</p>';
        });
      });

      // Handle scroll for navbar shadow
      window.addEventListener('scroll', () => {
        if (window.scrollY > 0) {
          navbar.classList.add('shadow-md');
        } else {
          navbar.classList.remove('shadow-md');
        }
      });
    </script>
  </body>
</html>

































<!-- <!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>YouTube Clipper</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body>
    <div id="app" class="min-h-screen transition-colors duration-200">
      <div class="absolute top-4 right-4">
        <button
          id="themeToggle"
          class="p-2 rounded-full transition-colors duration-200"
        >
        </button>
      </div>

      <div class="container mx-auto px-4 py-16">
        <div class="max-w-2xl mx-auto">
          <div class="text-center mb-12">
            <div class="flex justify-center items-center gap-3 mb-6">
              <i data-lucide="youtube" class="w-12 h-12"></i>
              <i data-lucide="scissors" class="w-8 h-8"></i>
            </div>
            <h1 class="text-4xl font-bold mb-4">YouTube Clipper</h1>
            <p class="text-lg transition-colors duration-200">
              Create the perfect clip from any YouTube video
            </p>
          </div>

          <form id="clipForm" class="space-y-6" action="process.php" method="POST">
            <div class="p-6 rounded-xl shadow-lg transition-colors duration-200">
              <div class="space-y-6">
                <div>
                  <label class="block text-sm font-medium mb-2">Video URL</label>
                  <div class="relative">
                    <i data-lucide="youtube" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5"></i>
                    <input
                      type="text"
                      id="url"
                      name="url"
                      placeholder="Paste YouTube URL here"
                      required
                      class="w-full pl-12 pr-4 py-3 rounded-lg border transition-colors duration-200 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                    />
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium mb-2">Start Time</label>
                    <div class="relative">
                      <i data-lucide="clock" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5"></i>
                      <input
                        type="number"
                        id="startTime"
                        name="start_time"
                        placeholder="0"
                        required
                        min="0"
                        class="w-full pl-12 pr-4 py-3 rounded-lg border transition-colors duration-200 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                      />
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-2">End Time</label>
                    <div class="relative">
                      <i data-lucide="clock" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5"></i>
                      <input
                        type="number"
                        id="endTime"
                        name="end_time"
                        placeholder="60"
                        required
                        min="1"
                        class="w-full pl-12 pr-4 py-3 rounded-lg border transition-colors duration-200 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <button
              type="submit"
              class="w-full py-4 rounded-lg font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
            >
              Create Clip
            </button>
          </form>

          <div id="status" class="mt-4 text-center"></div>
        </div>
      </div>
    </div>

    <script>
      // Initialize Lucide icons
      lucide.createIcons();

      // Theme handling
      let isDarkMode = false;
      const app = document.getElementById('app');
      const themeToggle = document.getElementById('themeToggle');
      const form = document.getElementById('clipForm');
      const status = document.getElementById('status');

      function updateTheme() {
        if (isDarkMode) {
          app.classList.add('bg-gray-900', 'text-white');
          app.classList.remove('bg-gray-50', 'text-gray-900');
          themeToggle.classList.add('bg-gray-800', 'hover:bg-gray-700');
          themeToggle.classList.remove('bg-gray-200', 'hover:bg-gray-300');
          themeToggle.innerHTML = '<i data-lucide="sun" class="w-5 h-5"></i>';
          
          // Update form styles
          document.querySelector('form > div').classList.add('bg-gray-800');
          document.querySelector('form > div').classList.remove('bg-white');
          
          // Update inputs
          document.querySelectorAll('input').forEach(input => {
            input.classList.add('bg-gray-700', 'border-gray-600');
            input.classList.remove('bg-gray-50', 'border-gray-300');
          });
          
          // Update icons
          document.querySelectorAll('[data-lucide="youtube"]')[0].classList.add('text-red-500');
          document.querySelectorAll('[data-lucide="youtube"]')[0].classList.remove('text-red-600');
          document.querySelectorAll('[data-lucide="scissors"]')[0].classList.add('text-blue-400');
          document.querySelectorAll('[data-lucide="scissors"]')[0].classList.remove('text-blue-600');
          document.querySelectorAll('[data-lucide="youtube"]')[1].classList.add('text-gray-400');
          document.querySelectorAll('[data-lucide="youtube"]')[1].classList.remove('text-gray-500');
          document.querySelectorAll('[data-lucide="clock"]').forEach(icon => {
            icon.classList.add('text-gray-400');
            icon.classList.remove('text-gray-500');
          });
        } else {
          app.classList.add('bg-gray-50', 'text-gray-900');
          app.classList.remove('bg-gray-900', 'text-white');
          themeToggle.classList.add('bg-gray-200', 'hover:bg-gray-300');
          themeToggle.classList.remove('bg-gray-800', 'hover:bg-gray-700');
          themeToggle.innerHTML = '<i data-lucide="moon" class="w-5 h-5"></i>';
          
          // Update form styles
          document.querySelector('form > div').classList.add('bg-white');
          document.querySelector('form > div').classList.remove('bg-gray-800');
          
          // Update inputs
          document.querySelectorAll('input').forEach(input => {
            input.classList.add('bg-gray-50', 'border-gray-300');
            input.classList.remove('bg-gray-700', 'border-gray-600');
          });
          
          // Update icons
          document.querySelectorAll('[data-lucide="youtube"]')[0].classList.add('text-red-600');
          document.querySelectorAll('[data-lucide="youtube"]')[0].classList.remove('text-red-500');
          document.querySelectorAll('[data-lucide="scissors"]')[0].classList.add('text-blue-600');
          document.querySelectorAll('[data-lucide="scissors"]')[0].classList.remove('text-blue-400');
          document.querySelectorAll('[data-lucide="youtube"]')[1].classList.add('text-gray-500');
          document.querySelectorAll('[data-lucide="youtube"]')[1].classList.remove('text-gray-400');
          document.querySelectorAll('[data-lucide="clock"]').forEach(icon => {
            icon.classList.add('text-gray-500');
            icon.classList.remove('text-gray-400');
          });
        }
        lucide.createIcons();
      }

      // Initialize theme
      updateTheme();

      // Theme toggle handler
      themeToggle.addEventListener('click', () => {
        isDarkMode = !isDarkMode;
        updateTheme();
      });

      // Form validation and submission handler
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const url = document.getElementById('url').value;
        const startTime = parseInt(document.getElementById('startTime').value);
        const endTime = parseInt(document.getElementById('endTime').value);
        
        // Validate YouTube URL
        if (!url.match(/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/)) {
          status.innerHTML = '<p class="text-red-500">Please enter a valid YouTube URL</p>';
          return;
        }
        
        // Validate times
        if (isNaN(startTime) || startTime < 0) {
          status.innerHTML = '<p class="text-red-500">Start time must be a positive number</p>';
          return;
        }
        
        if (isNaN(endTime) || endTime <= startTime) {
          status.innerHTML = '<p class="text-red-500">End time must be greater than start time</p>';
          return;
        }
        
        // Show loading state
        status.innerHTML = '<p class="text-blue-500">Processing your clip...</p>';
        
        // If validation passes, submit the form
        const formData = new FormData(form);
        fetch('process.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.text())
        .then(data => {
          status.innerHTML = data;
        })
        .catch(error => {
          status.innerHTML = '<p class="text-red-500">An error occurred. Please try again.</p>';
        });
      });
    </script>
  </body>
</html> -->