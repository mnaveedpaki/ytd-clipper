<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get user inputs
$url = $_POST['url'] ?? '';
$start_time = intval($_POST['start_time'] ?? 0);
$end_time = intval($_POST['end_time'] ?? 0);

// Enhanced input validation
if (empty($url)) {
    die('<p class="text-red-500 bg-red-100 p-4 rounded-lg">Please provide a valid YouTube URL.</p>');
}

if (!preg_match('/^(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})$/', $url)) {
    die('<p class="text-red-500 bg-red-100 p-4 rounded-lg">Invalid YouTube URL format. Please provide a valid YouTube URL.</p>');
}

if ($start_time < 0) {
    die('<p class="text-red-500 bg-red-100 p-4 rounded-lg">Start time cannot be negative.</p>');
}

if ($end_time <= $start_time) {
    die('<p class="text-red-500 bg-red-100 p-4 rounded-lg">End time must be greater than start time.</p>');
}

// Maximum clip duration validation (10 minutes)
if ($end_time - $start_time > 600) {
    die('<p class="text-red-500 bg-red-100 p-4 rounded-lg">Clip duration cannot exceed 10 minutes.</p>');
}

// Define absolute paths
$temp_file = __DIR__ . "/temp_video.mp4"; // Temporary video file
$output_file = __DIR__ . "/downloads/output_" . time() . ".mp4"; // Output file

// Create downloads directory if it doesn't exist
if (!file_exists(__DIR__ . '/downloads')) {
    mkdir(__DIR__ . '/downloads', 0777, true);
}

try {
    $download_command = "/opt/homebrew/bin/yt-dlp"
        . " -f 'bestvideo[ext=mp4]+bestaudio[ext=m4a]/mp4'"  // Optimized format selection
        . " --merge-output-format mp4"
        . " --ffmpeg-location /opt/homebrew/bin/ffmpeg"
        . " --no-warnings"
        . " --force-overwrites"
        . " --no-playlist"
        . " --output " . escapeshellarg($temp_file)
        . " " . escapeshellarg($url);

    // Execute command and capture real-time output
    $descriptorspec = array(
        1 => array("pipe", "w"), // stdout
        2 => array("pipe", "w")  // stderr
    );
    
    $process = proc_open($download_command, $descriptorspec, $pipes);
    
    if (is_resource($process)) {
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $return_var = proc_close($process);
        
        if ($return_var !== 0) {
            throw new Exception("Failed to download video. Please check the URL and try again.");
        }
    } else {
        throw new Exception("Failed to start download process");
    }

    // Verify downloaded file
    if (!file_exists($temp_file) || filesize($temp_file) < 1024) {
        throw new Exception("Download failed: Invalid or empty video file");
    }

    // Get video duration using ffprobe
    $duration_command = "/opt/homebrew/bin/ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($temp_file);
    $duration = floatval(trim(shell_exec($duration_command)));

    // Validate time stamps against video duration
    if ($end_time > $duration) {
        throw new Exception("End time exceeds video duration (" . round($duration) . " seconds)");
    }

    // Clip the video with enhanced quality settings
    $clip_command = "/opt/homebrew/bin/ffmpeg -i " . escapeshellarg($temp_file) 
        . " -ss " . escapeshellarg($start_time)
        . " -to " . escapeshellarg($end_time)
        . " -c:v libx264 -crf 18 -preset fast"
        . " -c:a aac -b:a 192k"
        . " -movflags +faststart"  // Enable streaming
        . " " . escapeshellarg($output_file);

    exec($clip_command, $output, $return_var);

    if ($return_var !== 0 || !file_exists($output_file)) {
        throw new Exception("Failed to create clip. Please try again.");
    }

    // Clean up the temporary file
    if (file_exists($temp_file)) {
        unlink($temp_file);
    }

    // Get relative path for the output file
    $relative_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $output_file);
    
    // Return success response with enhanced UI
    echo "<div class='p-6 rounded-xl shadow-lg bg-white dark:bg-gray-800 text-center space-y-4'>
            <div class='flex items-center justify-center mb-4'>
                <i data-lucide='check-circle' class='w-12 h-12 text-green-500'></i>
            </div>
            <p class='text-green-500 font-medium'>Your clip is ready!</p>
            <div class='flex justify-center gap-4 mt-4'>
                <a href='{$relative_path}' 
                   class='inline-flex items-center px-6 py-3 rounded-lg font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200'
                   download>
                    <i data-lucide='download' class='w-5 h-5 mr-2'></i>
                    Download
                </a>
                <button onclick='shareClip(\"{$relative_path}\")' 
                        class='inline-flex items-center px-6 py-3 rounded-lg font-medium text-white bg-green-600 hover:bg-green-700 transition-colors duration-200'>
                    <i data-lucide='share-2' class='w-5 h-5 mr-2'></i>
                    Share
                </button>
            </div>
          </div>
          <script>
            lucide.createIcons();
            
            function shareClip(url) {
                const fullUrl = window.location.origin + url;
                if (navigator.share) {
                    navigator.share({
                        title: 'YouTube Clip',
                        text: 'Check out this clip I created!',
                        url: fullUrl
                    }).catch(console.error);
                } else {
                    // Fallback: copy to clipboard
                    navigator.clipboard.writeText(fullUrl).then(() => {
                        alert('Link copied to clipboard!');
                    }).catch(() => {
                        alert('Failed to copy link. URL: ' + fullUrl);
                    });
                }
            }
          </script>";

} catch (Exception $e) {
    // Clean up any partial downloads
    if (file_exists($temp_file)) {
        unlink($temp_file);
    }
    
    // Return error message with enhanced UI
    echo "<div class='p-6 rounded-xl shadow-lg bg-red-50 dark:bg-red-900 text-center'>
            <div class='flex items-center justify-center mb-4'>
                <i data-lucide='alert-circle' class='w-12 h-12 text-red-500'></i>
            </div>
            <p class='text-red-500 dark:text-red-400'>" . htmlspecialchars($e->getMessage()) . "</p>
          </div>
          <script>lucide.createIcons();</script>";
}
?>




























<?php
// Enable error reporting
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// // Get user inputs
// $url = $_POST['url'] ?? '';
// $start_time = intval($_POST['start_time'] ?? 0);
// $end_time = intval($_POST['end_time'] ?? 0);

// if (empty($url) || $start_time >= $end_time) {
//     die("Invalid input. Please provide a valid YouTube URL, start time, and end time.");
// }

// // Define absolute paths
// $temp_file = __DIR__ . "/temp_video.mp4"; // Temporary video file
// $output_file = __DIR__ . "/downloads/output_" . time() . ".mp4"; // Output file

// // Create downloads directory if it doesn't exist
// if (!file_exists(__DIR__ . '/downloads')) {
//     mkdir(__DIR__ . '/downloads', 0777, true);
// }

// try {
//     $download_command = "/opt/homebrew/bin/yt-dlp"
//         . " -f 'bestvideo+bestaudio'"
//         . " --merge-output-format mp4"
//         . " --ffmpeg-location /opt/homebrew/bin/ffmpeg"  // Ensure ffmpeg path is correct
//         . " --no-warnings"
//         . " --force-overwrites"
//         . " --no-playlist"                     
//         . " --output " . escapeshellarg($temp_file)
//         . " " . escapeshellarg($url);

//     // Execute command and capture real-time output
//     $descriptorspec = array(
//         1 => array("pipe", "w"), // stdout
//         2 => array("pipe", "w")  // stderr
//     );
    
//     $process = proc_open($download_command, $descriptorspec, $pipes);
    
//     if (is_resource($process)) {
//         $stdout = stream_get_contents($pipes[1]);
//         $stderr = stream_get_contents($pipes[2]);
//         fclose($pipes[1]);
//         fclose($pipes[2]);
//         $return_var = proc_close($process);
        
//         // Log the complete output
//         $full_output = "STDOUT:\n$stdout\nSTDERR:\n$stderr";
//         file_put_contents("debug.log", "Download Command: {$download_command}\nOutput: {$full_output}\nReturn Var: {$return_var}\n", FILE_APPEND);
        
//         if ($return_var !== 0) {
//             throw new Exception("Download process failed. Error output: " . $stderr);
//         }
//     } else {
//         throw new Exception("Failed to start download process");
//     }

//     // Verify file exists and has content
//     if (!file_exists($temp_file)) {
//         throw new Exception("Download failed: Output file was not created");
//     }
    
//     if (filesize($temp_file) < 1024) { // Less than 1KB is probably an error
//         throw new Exception("Download failed: Output file is too small");
//     }

//     // Step 2: Clip the video using FFmpeg - only changed the quality settings here
//     $clip_command = "/opt/homebrew/bin/ffmpeg -i {$temp_file} -ss {$start_time} -to {$end_time} -c:v libx264 -crf 18 -c:a aac -b:a 192k -preset fast -avoid_negative_ts make_zero {$output_file}";
//     exec($clip_command, $output, $return_var);

//     // Log the output and return status
//     file_put_contents("debug.log", "Clip Command: {$clip_command}\nOutput: " . print_r($output, true) . "\nReturn Var: {$return_var}\n", FILE_APPEND);

//     if ($return_var !== 0 || !file_exists($output_file)) {
//         die("Failed to clip the video. Please try again.");
//     }

//     // Step 3: Clean up the temporary file
//     if (file_exists($temp_file)) {
//         unlink($temp_file);
//     }

//     // Provide download and share buttons
//     echo "<div class='p-6 rounded-xl shadow-lg bg-white text-center'>
//             <p class='text-green-500 mb-4'>Your clip is ready!</p>
//             <a href='{$output_file}' class='inline-block px-6 py-3 rounded-lg font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200' download>Download</a>
//             <button onclick='shareClip(\"{$output_file}\")' class='inline-block px-6 py-3 ml-4 rounded-lg font-medium text-white bg-green-600 hover:bg-green-700 transition-colors duration-200'>Share</button>
//           </div>
//           <script>
//             function shareClip(url) {
//               if (navigator.share) {
//                 navigator.share({
//                   title: 'YouTube Clip',
//                   text: 'Check out this clip I created!',
//                   url: url
//                 }).then(() => {
//                   console.log('Thanks for sharing!');
//                 }).catch(console.error);
//               } else {
//                 alert('Sharing is not supported in this browser.');
//               }
//             }
//           </script>";

// } catch (Exception $e) {
//     // Clean up any partial downloads
//     if (file_exists($temp_file)) {
//         unlink($temp_file);
//     }
//     die("An error occurred: " . htmlspecialchars($e->getMessage()));
// }
?>
