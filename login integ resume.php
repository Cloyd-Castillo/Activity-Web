<?php
session_start();
$resume = [
	'name' => 'Cloyd Robin C. Castillo',
	'personal' => [
		'Title' => 'CS-3102',
		'Email' => '23-04780@g.batstate-u.edu.ph/cloydrobin02@gmail.com',
		'Phone' => '+63 966 455 8529',
		'Location' => 'San Antonio, San Pascual, Batangas',
		'GitHub' => 'https://github.com/Cloyd-Castillo',
	],
	'skills' => [
		'Languages' => ['PHP', 'Python', 'C++', 'Java'],
		'Databases' => ['MySQL', 'PostgreSQL',],
	],
	'education' => [
		[
			'level' => 'College',
			'school' => 'Batangas State University',
			'years' => '2023 – Present',
		],
		
		[
			'level' => 'Senior High School',
			'school' => 'San Pascual Senior High School 1',
			'years' => '2021-2023',
		],

		[
			'level' => 'High School',
			'school' => 'San Pascual National High School',
			'years' => '2017-2021',
		],

	],
	'projects' => [
		[
			'name' => 'Votify',
			'description' => 'Votify is a console-based Java application designed to provide a secure, efficient, and user-friendly online voting system. It allows voters to participate in polls, view results, and ensures proper data management and privacy. The system connects to a relational database, which stores user accounts, polls, and vote data, offering scalability and secure data management.',
			'tech' => ['Java', 'MySQL'],
			'link' => 'https://github.com/Cloyd-Castillo/Votify-DBMS-',
		],
		[
			'name' => 'ECO-MAP (Group Project)',
			'description' => 'EcoMap is a web-based platform designed to make waste management smarter and more efficient for communities. Group project. The system provides an intuitive interface for managing waste collection, educational resources, and environmental initiatives.',
			'tech' => [
				'HTML5', 'CSS3', 'JavaScript', 'Bootstrap 5.3.0', 'Font Awesome 6.0.0',
				'PHP', 'MySQL', 'Custom CSS/JS libraries'
			],
			'link' => 'https://github.com/Andaljc1218/ECO-MAP'
		],
	],
];

function h(string $value): string {
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$loginMessage = '';
$loginClass = '';
$isAuthenticated = isset($_SESSION['auth']) && $_SESSION['auth'] === true;

if (isset($_GET['logout'])) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = isset($_POST['username']) ? trim((string)$_POST['username']) : '';
	$password = isset($_POST['password']) ? trim((string)$_POST['password']) : '';

	if ($username === '' || $password === '') {
		$loginMessage = 'All fields are required!';
		$loginClass = 'error';
	} elseif ($username === 'admin' && $password === '1234') {
		$loginMessage = 'Login Successful';
		$loginClass = 'success';
        $_SESSION['auth'] = true;
        $isAuthenticated = true;
	} else {
		$loginMessage = 'Invalid Username or Password';
		$loginClass = 'error';
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo h($resume['name']); ?> — Resume</title>
	<style>
		:root { 
			--fg:#1c1c1c; 
			--muted:#5b5b5b; 
			--accent:#2563eb; --bg:#ffffff; 
			--chip:#f1f5f9; 
		}
		* { box-sizing: border-box; }
		body { 
			margin:0; 
			font-family: 
			system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; 
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
			gap: 12px; border-bottom: 
			2px solid #eee; 
			padding-bottom: 16px; 
			margin-bottom: 24px; 
		}
		h1 { 
			margin: 0; f
			ont-size: 2rem; 
		}
		.subtitle { 
			color: var(--muted); 
			margin-top: 4px; 
		}
		.section { 
			margin-top: 
			28px; 
		}
		.section h2 { 
			font-size: 1.1rem; 
			letter-spacing: .04em; 
			text-transform: uppercase; 
			color: var(--muted); margin: 0 0 12px; 
		}
		.grid { 
			display: grid; 
			grid-template-columns: 1fr; 
			gap: 12px; 
		}

		@media (min-width: 720px) { .grid-2 { grid-template-columns: 1fr 1fr; } }
		.kv { 
			display:flex; 
			gap:8px; align-items: baseline; 
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
		.footer { 
			margin-top: 36px; 
			font-size: .9rem; color: var(--muted); 
		}


		.login-container { 
			max-width: 560px; 
			margin: 48px auto; 
			padding: 0 16px; 
		}
		.login-title { 
			margin: 0 0 16px; 
			font-size: 1.5rem; 
		}
		.sub { 
			margin: 0 0 20px; 
			color: var(--muted); 
		}
		.form-card { 
			border:1px solid #e5e7eb; 
			border-radius: 12px; padding: 18px; 
		}
		.form-grid { 
			display:grid; 
			grid-template-columns: 1fr; 
			gap: 12px; 
		}
		label { font-weight: 600; }
		.input { 
			width:100%; 
			padding:10px; 
			border:1px solid #d1d5db; 
			border-radius: 8px; 
			font-size: 1rem; }
		.button { 
			padding: 10px 16px; 
			background: var(--accent); 
			color:#fff; border:0; 
			border-radius:8px; 
			font-weight:600; 
			cursor:pointer; 
		}
		.button:hover { filter: brightness(0.95); }
		.message { 
			margin: 12px 0 0; 
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
		.logout-link { 
			margin-left: auto; 
			padding: 8px 12px; 
			border:1px solid #e5e7eb; 
			border-radius: 8px; 
			color: var(--fg); 
			text-decoration:none; 
		}
		.logout-link:hover { background:#f8fafc; }
	</style>
</head>
<body>
	<div class="container">
			<?php if ($isAuthenticated): ?>
			<header>
				<div>
					<h1><?php echo h($resume['name']); ?></h1>
					<?php if (!empty($resume['personal']['Title'])): ?>
						<div class="subtitle"><?php echo h($resume['personal']['Title']); ?></div>
					<?php endif; ?>
				</div>
				<a class="logout-link" href="?logout=1">Logout</a>
			</header>
			<?php endif; ?>

			<?php if (!$isAuthenticated): ?>
			<div class="login-container">
				<h1 class="login-title">Login</h1>
				<p class="sub">Enter your credentials to continue.</p>
				<div class="form-card">
					<form method="post" action="">
						<div class="form-grid">
							<div>
								<label for="username">Username</label>
								<input class="input" type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? h((string)$_POST['username']) : ''; ?>">
							</div>
							<div>
								<label for="password">Password</label>
								<input class="input" type="password" id="password" name="password">
							</div>
							<div>
								<button class="button" type="submit">Login</button>
							</div>
						</div>
					</form>
					<?php if ($loginMessage !== ''): ?>
						<div class="message <?php echo h($loginClass); ?>"><?php echo h($loginMessage); ?></div>
					<?php endif; ?>
				</div>
			</div>
			<?php else: ?>
			<!-- Logout -->
			<?php endif; ?>

			<?php if ($isAuthenticated): ?>
			<section class="section">
			<h2>Personal Info</h2>
			<div class="grid">
				<?php foreach ($resume['personal'] as $key => $value): if ($key === 'Title') continue; ?>
					<div class="kv">
						<div class="k"><?php echo h($key); ?></div>
						<div class="v">
							<?php if (filter_var($value, FILTER_VALIDATE_URL)): ?>
								<a href="<?php echo h($value); ?>" target="_blank" rel="noopener"><?php echo h($value); ?></a>
							<?php else: ?>
								<?php echo h($value); ?>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</section>

			<section class="section">
			<h2>Skills</h2>
			<div class="grid grid-2">
				<?php foreach ($resume['skills'] as $category => $items): ?>
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
				<?php foreach ($resume['education'] as $edu): ?>
					<div class="card">
						<h3><?php echo h($edu['level']); ?> <br> <?php echo h($edu['school']); ?></h3>
						<div class="subtitle"><?php echo h($edu['years']); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</section>

			<section class="section">
			<h2>Coding Projects</h2>
			<div class="grid">
				<?php foreach ($resume['projects'] as $proj): ?>
					<div class="card">
						<h3><?php echo h($proj['name']); ?></h3>
						<p><?php echo h($proj['description']); ?></p>
						<div class="chips">
							<?php if (!empty($proj['tech'])): foreach ($proj['tech'] as $t): ?>
								<span class="chip"><?php echo h($t); ?></span>
							<?php endforeach; endif; ?>
						</div>
						<?php if (!empty($proj['link'])): ?>
							<p><a href="<?php echo h($proj['link']); ?>" target="_blank" rel="noopener">View project</a></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
			</section>
			<?php endif; ?>

	</div>
</body>
</html>