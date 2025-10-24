<?php
session_start();
require_once "config.php";

function h($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

// Login functionality
$message = '';
$messageClass = '';
$isAuthenticated = isset($_SESSION['user_id']);

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: public_resume.php");
    exit;
}

// Handle login
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :u");
        $stmt->execute(['u' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // ✅ Use password_verify() for hashed passwords
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $isAuthenticated = true;
            header("Location: public_resume.php");
            exit;
        } else {
            $message = "Invalid username or password.";
            $messageClass = "error";
        }
    } else {
        $message = "All fields are required.";
        $messageClass = "error";
    }
}

// Handle resume update
if ($isAuthenticated && isset($_POST['action']) && $_POST['action'] === 'update') {
    $uid = $_SESSION['user_id'];
    
    // Update personal info
    $update = $pdo->prepare("UPDATE personal_info SET full_name=?, title=?, email=?, phone=?, location=?, github_url=? WHERE user_id=?");
    $update->execute([
        $_POST['name'], $_POST['title'], $_POST['email'], $_POST['phone'], $_POST['location'], $_POST['github'], $uid
    ]);

    // Replace skills
    $pdo->prepare("DELETE FROM skills WHERE user_id=?")->execute([$uid]);
    if (!empty($_POST['skill_categories'])) {
        foreach ($_POST['skill_categories'] as $i => $cat) {
            $skills = array_map('trim', explode(',', $_POST['skill_items'][$i]));
            foreach ($skills as $sk) {
                if ($sk !== '') {
                    $ins = $pdo->prepare("INSERT INTO skills (user_id, category, skill_name) VALUES (?,?,?)");
                    $ins->execute([$uid, $cat, $sk]);
                }
            }
        }
    }

    // Replace education
    $pdo->prepare("DELETE FROM education WHERE user_id=?")->execute([$uid]);
    if (!empty($_POST['edu_level'])) {
        foreach ($_POST['edu_level'] as $i => $lvl) {
            $ins = $pdo->prepare("INSERT INTO education (user_id, level, school, years, display_order) VALUES (?,?,?,?,?)");
            $ins->execute([$uid, $_POST['edu_level'][$i], $_POST['edu_school'][$i], $_POST['edu_years'][$i], $i]);
        }
    }

    // Replace projects
    $pdo->prepare("DELETE FROM projects WHERE user_id=?")->execute([$uid]);
    if (!empty($_POST['proj_name'])) {
        foreach ($_POST['proj_name'] as $i => $nm) {
            $ins = $pdo->prepare("INSERT INTO projects (user_id, name, description, technologies, link, display_order) VALUES (?,?,?,?,?,?)");
            $ins->execute([
                $uid,
                $_POST['proj_name'][$i],
                $_POST['proj_description'][$i],
                $_POST['proj_tech'][$i],
                $_POST['proj_link'][$i],
                $i
            ]);
        }
    }

    $message = "Resume updated successfully!";
    $messageClass = "success";
    header("Refresh:0");
    exit;
}

// Get resume data
$id = $_GET['id'] ?? 1; // Default to user ID 1 if not specified
$stmt = $pdo->prepare("SELECT * FROM personal_info WHERE user_id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$p) { 
    die("Resume not found."); 
}

// Get skills
$skillsQ = $pdo->prepare("SELECT category, skill_name FROM skills WHERE user_id=?");
$skillsQ->execute([$id]);
$skills = [];
while ($r = $skillsQ->fetch(PDO::FETCH_ASSOC)) {
    $skills[$r['category']][] = $r['skill_name'];
}

// Get education
$edu = $pdo->prepare("SELECT * FROM education WHERE user_id=? ORDER BY display_order");
$edu->execute([$id]);
$eduList = $edu->fetchAll(PDO::FETCH_ASSOC);

// Get projects
$proj = $pdo->prepare("SELECT * FROM projects WHERE user_id=? ORDER BY display_order");
$proj->execute([$id]);
$projects = $proj->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo h($p['full_name']); ?> — Resume</title>
    <style>
        :root { 
            --fg:#1c1c1c; 
            --muted:#5b5b5b; 
            --accent:#2563eb; 
            --bg:#ffffff; 
            --chip:#f1f5f9; 
        }
        * { box-sizing: border-box; }
        body { 
            margin:0; 
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; 
            background: var(--bg); 
            color: var(--fg); 
        }
        .container { 
            max-width: 960px; 
            margin: 40px auto; 
            padding: 0 24px 48px; 
        }
        header { 
            display:flex; 
            align-items:center; 
            justify-content: space-between; 
            gap: 12px; 
            border-bottom: 2px solid #eee; 
            padding-bottom: 16px; 
            margin-bottom: 24px; 
        }
        h1 { 
            margin: 0; 
            font-size: 2rem; 
        }
        .subtitle { 
            color: var(--muted); 
            margin-top: 4px; 
        }
        .section { 
            margin-top: 28px; 
        }
        .section h2 { 
            font-size: 1.1rem; 
            letter-spacing: .04em; 
            text-transform: uppercase; 
            color: var(--muted); 
            margin: 0 0 12px; 
        }
        .grid { 
            display: grid; 
            grid-template-columns: 1fr; 
            gap: 12px; 
        }
        @media (min-width: 720px) { 
            .grid-2 { grid-template-columns: 1fr 1fr; } 
        }
        .kv { 
            display:flex; 
            gap:8px; 
            align-items: baseline; 
        }
        .kv .k { 
            min-width: 120px; 
            color: var(--muted); 
        }
        .chips { 
            display:flex; 
            flex-wrap: wrap; 
            gap: 8px; 
        }
        .chip { 
            background: var(--chip); 
            padding: 6px 10px; 
            border-radius: 999px; 
            font-size: 0.9rem; 
        }
        .card { 
            border:1px solid #eee; 
            border-radius: 10px; 
            padding: 14px; 
        }
        .card h3 { 
            margin: 0 0 6px; 
            font-size: 1.05rem; 
        }
        .card p { 
            margin: 6px 0 12px; 
            color: var(--muted); 
        }
        a { color: var(--accent); text-decoration: none; }
        a:hover { text-decoration: underline; }
        
        .button { 
            padding: 10px 16px; 
            background: var(--accent); 
            color:#fff; 
            border:0; 
            border-radius:8px; 
            font-weight:600; 
            cursor:pointer; 
            text-decoration: none;
            display: inline-block;
            font-size: 0.95rem;
        }
        .button:hover { filter: brightness(0.95); }
        .button-secondary {
            background: #6b7280;
        }
        .button-small {
            padding: 6px 12px;
            font-size: 0.85rem;
        }
        .button-danger {
            background: #dc2626;
        }
        .message { 
            margin: 12px 0; 
            padding: 10px 12px; 
            border-radius: 8px; 
            font-weight: 600; 
        }
        .message.success { 
            color: #166534; 
            background: #dcfce7; 
            border: 1px solid #86efac; 
        }
        .message.error { 
            color: #b91c1c; 
            background: #fee2e2; 
            border: 1px solid #fecaca; 
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-content {
            background-color: white;
            padding: 24px;
            border-radius: 12px;
            max-width: 700px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .modal-title {
            font-size: 1.5rem;
            margin: 0;
        }
        .close {
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
            border: none;
            background: none;
        }
        .close:hover {
            color: #000;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .form-group small {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 0.85rem;
        }
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .section-divider {
            border-top: 2px solid #e5e7eb;
            margin: 24px 0;
            padding-top: 20px;
        }
        .section-divider h3 {
            font-size: 1.2rem;
            margin: 0 0 16px;
            color: var(--accent);
        }
        .repeatable-item {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
            position: relative;
        }
        .repeatable-item .remove-btn {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .add-more {
            margin-top: 12px;
        }
    </style>
</head>
<body>
<div class="container">
        <header>
            <div>
<h1><?php echo h($p['full_name']); ?></h1>
                <div class="subtitle"><?php echo h($p['title']); ?></div>
            </div>
            <div class="header-actions">
                <?php if (!$isAuthenticated): ?>
                    <button class="button" onclick="showLoginModal()">Login</button>
                <?php else: ?>
                    <button class="button" onclick="showEditModal()">Edit Resume</button>
                    <a class="button button-secondary" href="?logout=1">Logout</a>
                <?php endif; ?>
            </div>
        </header>

        <?php if ($message !== ''): ?>
            <div class="message <?php echo h($messageClass); ?>"><?php echo h($message); ?></div>
        <?php endif; ?>

        <section class="section">
            <h2>Personal Info</h2>
            <div class="grid">
                <div class="kv">
                    <div class="k">Email</div>
                    <div class="v">
                        <a href="mailto:<?php echo h($p['email']); ?>"><?php echo h($p['email']); ?></a>
                    </div>
                </div>
                <div class="kv">
                    <div class="k">Phone</div>
                    <div class="v"><?php echo h($p['phone']); ?></div>
                </div>
                <div class="kv">
                    <div class="k">Location</div>
                    <div class="v"><?php echo h($p['location']); ?></div>
                </div>
                <div class="kv">
                    <div class="k">GitHub</div>
                    <div class="v">
                        <a href="<?php echo h($p['github_url']); ?>" target="_blank" rel="noopener"><?php echo h($p['github_url']); ?></a>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
<h2>Skills</h2>
            <div class="grid grid-2">
                <?php foreach ($skills as $category => $items): ?>
                    <div class="card">
                        <h3><?php echo h($category); ?></h3>
                        <div class="chips">
                            <?php foreach ($items as $skill): ?>
                                <span class="chip"><?php echo h($skill); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
<?php endforeach; ?>
            </div>
        </section>

        <section class="section">
<h2>Education</h2>
            <div class="grid">
                <?php foreach ($eduList as $edu): ?>
                    <div class="card">
                        <h3><?php echo h($edu['level']); ?> <br> <?php echo h($edu['school']); ?></h3>
                        <div class="subtitle"><?php echo h($edu['years']); ?></div>
                    </div>
<?php endforeach; ?>
            </div>
        </section>

        <section class="section">
<h2>Projects</h2>
            <div class="grid">
                <?php foreach ($projects as $proj): ?>
                    <div class="card">
                        <h3><?php echo h($proj['name']); ?></h3>
                        <p><?php echo h($proj['description']); ?></p>
                        <div class="chips">
                            <?php 
                            $techs = explode(',', $proj['technologies']);
                            foreach ($techs as $tech): 
                                $tech = trim($tech);
                                if ($tech): ?>
                                    <span class="chip"><?php echo h($tech); ?></span>
                                <?php endif;
                            endforeach; ?>
                        </div>
                        <?php if (!empty($proj['link'])): ?>
                            <p><a href="<?php echo h($proj['link']); ?>" target="_blank" rel="noopener">View Project</a></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Login</h2>
                <button class="close" onclick="closeLoginModal()">&times;</button>
            </div>
            <form method="post" action="">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="button-group">
                    <button class="button" type="submit">Login</button>
                    <button class="button button-secondary" type="button" onclick="closeLoginModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Resume</h2>
                <button class="close" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="post" action="">
                <input type="hidden" name="action" value="update">
                
                <!-- Personal Info -->
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?php echo h($p['full_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="title">Title/Position</label>
                    <input type="text" id="title" name="title" value="<?php echo h($p['title']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo h($p['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" value="<?php echo h($p['phone']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" value="<?php echo h($p['location']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="github">GitHub URL</label>
                    <input type="url" id="github" name="github" value="<?php echo h($p['github_url']); ?>" required>
                </div>

                <!-- Skills Section -->
                <div class="section-divider">
                    <h3>Skills</h3>
                    <div id="skillsContainer">
                        <?php foreach ($skills as $category => $items): ?>
                        <div class="repeatable-item">
                            <button type="button" class="button button-small button-danger remove-btn" onclick="removeItem(this)">Remove</button>
                            <div class="form-group">
                                <label>Category</label>
                                <input type="text" name="skill_categories[]" value="<?php echo h($category); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Skills</label>
                                <input type="text" name="skill_items[]" value="<?php echo h(implode(', ', $items)); ?>" required>
                                <small>Separate skills with commas (e.g., PHP, Python, Java)</small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="button button-small add-more" onclick="addSkill()">+ Add Skill Category</button>
                </div>

                <!-- Education Section -->
                <div class="section-divider">
                    <h3>Education</h3>
                    <div id="educationContainer">
                        <?php foreach ($eduList as $edu): ?>
                        <div class="repeatable-item">
                            <button type="button" class="button button-small button-danger remove-btn" onclick="removeItem(this)">Remove</button>
                            <div class="form-group">
                                <label>Level</label>
                                <input type="text" name="edu_level[]" value="<?php echo h($edu['level']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>School</label>
                                <input type="text" name="edu_school[]" value="<?php echo h($edu['school']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Years</label>
                                <input type="text" name="edu_years[]" value="<?php echo h($edu['years']); ?>" required>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="button button-small add-more" onclick="addEducation()">+ Add Education</button>
                </div>

                <!-- Projects Section -->
                <div class="section-divider">
                    <h3>Projects</h3>
                    <div id="projectsContainer">
                        <?php foreach ($projects as $proj): ?>
                        <div class="repeatable-item">
                            <button type="button" class="button button-small button-danger remove-btn" onclick="removeItem(this)">Remove</button>
                            <div class="form-group">
                                <label>Project Name</label>
                                <input type="text" name="proj_name[]" value="<?php echo h($proj['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="proj_description[]" required><?php echo h($proj['description']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Technologies</label>
                                <input type="text" name="proj_tech[]" value="<?php echo h($proj['technologies']); ?>">
                                <small>Separate technologies with commas (e.g., Java, MySQL)</small>
                            </div>
                            <div class="form-group">
                                <label>Project Link</label>
                                <input type="url" name="proj_link[]" value="<?php echo h($proj['link']); ?>">
                            </div>
    </div>
<?php endforeach; ?>
</div>
                    <button type="button" class="button button-small add-more" onclick="addProject()">+ Add Project</button>
                </div>

                <div class="button-group">
                    <button class="button" type="submit">Save All Changes</button>
                    <button class="button button-secondary" type="button" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showLoginModal() {
            document.getElementById('loginModal').classList.add('show');
        }
        
        function closeLoginModal() {
            document.getElementById('loginModal').classList.remove('show');
        }
        
        function showEditModal() {
            document.getElementById('editModal').classList.add('show');
        }
        
        function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
        }
        
        function removeItem(button) {
            button.parentElement.remove();
        }
        
        function addSkill() {
            const container = document.getElementById('skillsContainer');
            const item = document.createElement('div');
            item.className = 'repeatable-item';
            item.innerHTML = `
                <button type="button" class="button button-small button-danger remove-btn" onclick="removeItem(this)">Remove</button>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="skill_categories[]" required>
                </div>
                <div class="form-group">
                    <label>Skills</label>
                    <input type="text" name="skill_items[]" required>
                    <small>Separate skills with commas (e.g., PHP, Python, Java)</small>
                </div>
            `;
            container.appendChild(item);
        }
        
        function addEducation() {
            const container = document.getElementById('educationContainer');
            const item = document.createElement('div');
            item.className = 'repeatable-item';
            item.innerHTML = `
                <button type="button" class="button button-small button-danger remove-btn" onclick="removeItem(this)">Remove</button>
                <div class="form-group">
                    <label>Level</label>
                    <input type="text" name="edu_level[]" required>
                </div>
                <div class="form-group">
                    <label>School</label>
                    <input type="text" name="edu_school[]" required>
                </div>
                <div class="form-group">
                    <label>Years</label>
                    <input type="text" name="edu_years[]" required>
                </div>
            `;
            container.appendChild(item);
        }
        
        function addProject() {
            const container = document.getElementById('projectsContainer');
            const item = document.createElement('div');
            item.className = 'repeatable-item';
            item.innerHTML = `
                <button type="button" class="button button-small button-danger remove-btn" onclick="removeItem(this)">Remove</button>
                <div class="form-group">
                    <label>Project Name</label>
                    <input type="text" name="proj_name[]" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="proj_description[]" required></textarea>
                </div>
                <div class="form-group">
                    <label>Technologies</label>
                    <input type="text" name="proj_tech[]">
                    <small>Separate technologies with commas (e.g., Java, MySQL)</small>
                </div>
                <div class="form-group">
                    <label>Project Link</label>
                    <input type="url" name="proj_link[]">
                </div>
            `;
            container.appendChild(item);
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const loginModal = document.getElementById('loginModal');
            const editModal = document.getElementById('editModal');
            if (event.target === loginModal) {
                closeLoginModal();
            }
            if (event.target === editModal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>
