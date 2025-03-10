<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }
        .container {
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            width: 300px;
            text-align: center;
        }
        input {
            width: 90%;
            padding: 8px;
            margin: 10px 0;
        }
        button {
            width: 90%;
            padding: 10px;
            background: blue;
            color: white;
            border: none;
            cursor: pointer;
        }
        .toggle-link {
            color: blue;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 id="form-title">Login</h2>
        <form id="auth-form" method="POST" action="auth.php">
            <input type="email" name="email" id="email" placeholder="Email" required>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <input type="text" name="username" id="username" placeholder="Username" style="display: none;">
            <input type="hidden" name="action" id="form-action" value="login">
            <button type="submit">Submit</button>
        </form>

        <p>
            <span id="toggle-text">Don't have an account?</span> 
            <span class="toggle-link" id="toggle-form">Sign up</span>
        </p>
    </div>
    
    <script>
        const formTitle = document.getElementById('form-title');
        const authForm = document.getElementById('auth-form');
        const toggleText = document.getElementById('toggle-text');
        const toggleForm = document.getElementById('toggle-form');
        const usernameInput = document.getElementById('username');
        let isSignUp = false;

        toggleForm.addEventListener('click', () => {
            isSignUp = !isSignUp;
            formTitle.innerText = isSignUp ? 'Sign Up' : 'Login';
            toggleText.innerText = isSignUp ? 'Already have an account?' : "Don't have an account?";
            toggleForm.innerText = isSignUp ? 'Login' : 'Sign up';
            usernameInput.style.display = isSignUp ? 'block' : 'none';
            document.getElementById('form-action').value = isSignUp ? 'signup' : 'login';
        });
    </script>
</body>
</html>
