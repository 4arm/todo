<?php
// Define database connection constants
// *** YOU MUST CHANGE THESE VALUES TO MATCH YOUR SETUP ***
define('DB_HOST', 'localhost');
define('DB_NAME', 'todo_app');
define('DB_USER', 'todo_user');
define('DB_PASS', 'your_strong_password'); // <-- Change this!

$pdo = null;

try {
    // Establish PDO connection
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    
    // Set PDO error mode to exception for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Display a friendlier error message instead of dying with sensitive info
    die("Database connection failed. Please check the credentials and ensure MariaDB is running on localhost. Error: " . $e->getMessage());
}

// --- CONTROLLER LOGIC ---

// 1. Add New Task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $task = trim($_POST['task']);
    if (!empty($task)) {
        $stmt = $pdo->prepare("INSERT INTO todos (task) VALUES (:task)");
        $stmt->execute([':task' => $task]);
    }
    // Redirect to prevent form resubmission on refresh
    header('Location: index.php');
    exit;
}

// 2. Delete Task
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM todos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header('Location: index.php');
    exit;
}

// 3. Toggle Task Completion Status
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Get current status
    $stmt = $pdo->prepare("SELECT is_completed FROM todos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $current_status = $stmt->fetchColumn();
    
    // Toggle the status
    $new_status = $current_status ? 0 : 1;

    $stmt = $pdo->prepare("UPDATE todos SET is_completed = :status WHERE id = :id");
    $stmt->execute([':status' => $new_status, ':id' => $id]);
    header('Location: index.php');
    exit;
}

// 4. Fetch All Tasks (READ operation)
$stmt = $pdo->prepare("SELECT id, task, is_completed FROM todos ORDER BY is_completed ASC, created_at DESC");
$stmt->execute();
$todos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MariaDB PHP To-Do App</title>
    <script src="[https://cdn.tailwindcss.com](https://cdn.tailwindcss.com)"></script>
    <style>
        /* Custom styles for better aesthetics */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7fafc;
        }
        .container {
            max-width: 600px;
        }
        .todo-item {
            transition: all 0.3s ease;
        }
        .completed-task {
            opacity: 0.6;
            text-decoration: line-through;
        }
    </style>
</head>
<body class="min-h-screen flex justify-center py-10 bg-gray-100">

    <div class="container bg-white shadow-xl rounded-xl p-6 md:p-10">
        <h1 class="text-3xl font-extrabold text-gray-800 mb-6 border-b pb-3">
            PHP/MariaDB To-Do List
        </h1>

        <form method="POST" action="index.php" class="flex space-x-3 mb-8">
            <input
                type="text"
                name="task"
                placeholder="Add a new task..."
                required
                class="flex-grow p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <button
                type="submit"
                name="add_task"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-300"
            >
                Add
            </button>
        </form>

        <?php if (empty($todos)): ?>
            <div class="text-center p-8 bg-blue-50 rounded-lg border-dashed border-2 border-blue-300 text-gray-600">
                <p class="font-medium">No tasks yet! Start adding something to do.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($todos as $todo): ?>
                    <?php
                        $is_completed = $todo['is_completed'];
                        $item_classes = $is_completed ? 'bg-green-100 border-green-300 completed-task' : 'bg-white border-gray-200';
                        $text_classes = $is_completed ? 'text-gray-500 line-through' : 'text-gray-800';
                    ?>
                    <div class="todo-item flex items-center justify-between p-4 border rounded-lg shadow-sm <?= $item_classes ?>">
                        <span class="flex-grow text-lg <?= $text_classes ?>">
                            <?= htmlspecialchars($todo['task']) ?>
                        </span>

                        <div class="flex items-center space-x-2 ml-4">
                            
                            <a href="?action=toggle&id=<?= $todo['id'] ?>"
                                class="p-2 rounded-full transition duration-150 ease-in-out
                                <?= $is_completed ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' ?> text-white"
                                title="<?= $is_completed ? 'Mark as Pending' : 'Mark as Complete' ?>"
                            >
                                <?php if ($is_completed): ?>
                                    <svg xmlns="[http://www.w3.org/2000/svg](http://www.w3.org/2000/svg)" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                <?php else: ?>
                                    <svg xmlns="[http://www.w3.org/2000/svg](http://www.w3.org/2000/svg)" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                <?php endif; ?>
                            </a>

                            <a href="?action=delete&id=<?= $todo['id'] ?>"
                                onclick="return confirm('Are you sure you want to delete this task?');"
                                class="p-2 rounded-full bg-gray-300 hover:bg-gray-400 text-gray-800 transition duration-150 ease-in-out"
                                title="Delete Task"
                            >
                                <svg xmlns="[http://www.w3.org/2000/svg](http://www.w3.org/2000/svg)" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
