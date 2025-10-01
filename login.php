<?php
$message = '';
$messageClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = isset($_POST['username']) ? trim((string)$_POST['username']) : '';
	$password = isset($_POST['password']) ? trim((string)$_POST['password']) : '';

	if ($username === '' || $password === '') {
		$message = 'All fields are required!';
		$messageClass = 'error';
	} elseif ($username === 'admin' && $password === '1234') {
		$message = 'Login Successful';
		$messageClass = 'success';
	} else {
		$message = 'Invalid Username or Password';
		$messageClass = 'error';
	}
}

function h(string $value): string {
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Building a Simple Login Form with PHP</title>
	<style>
		:root { 
			--fg:#1f2937; 
			--muted:#6b7280; 
			--accent:#2563eb; 
		}
		* { box-sizing: border-box; }
		body { 
			margin:0; 
			ont-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; 
			background:#ffffff; 
			color:var(--fg); 
		}
		.container { 
			max-width: 560px; 
			margin: 48px auto; 
			padding: 0 16px; 
		}
		h1 { 
			margin: 0 0 16px; 
			font-size: 1.5rem; 
		}
		p.sub { 
			margin: 0 0 20px; 
			color: var(--muted); 
		}
		.card { 
			border:1px solid #e5e7eb; 
			border-radius: 12px; 
			padding: 18px; 
		}
		.grid { 
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
			font-size: 1rem; 
		}
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
			background: #dcfce7; border: 1px solid #86efac; 
		}
		.message.error { 
			color: #b91c1c; 
			background: #fee2e2; 
			border: 1px solid #fecaca; 
		}
	</style>
</head>
<body>
	<div class="container">
		<h1>Login</h1>
		<p class="sub">Enter your credentials to continue.</p>
		<div class="card">
			<form method="post" action="">
				<div class="grid">
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
			<?php if ($message !== ''): ?>
				<div class="message <?php echo h($messageClass); ?>"><?php echo h($message); ?></div>
			<?php endif; ?>
		</div>
	</div>
</body>
</html>

