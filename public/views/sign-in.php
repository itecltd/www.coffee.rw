
<?php
require_once __DIR__ . '../../../_ikawa/config/App.php'; // adjust path if needed
?>
<style>
/* Simple toast styling */
.toast {
  visibility: hidden;
  min-width: 250px;
  margin-left: -125px;
  background-color: #333;
  color: #fff;
  text-align: center;
  border-radius: 4px;
  padding: 16px;
  position: fixed;
  z-index: 9999;
  left: 50%;
  top: 30px;
  font-size: 16px;
  opacity: 0;
  transition: opacity 0.5s, top 0.5s;
}

.toast.show {
  visibility: visible;
  opacity: 1;
  top: 50px;
}
.toast.success { background-color: #4CAF50; }
.toast.error   { background-color: #f44336; }
</style>
<div class = 'login-content'>
    
<!-- Login -->
<div class="nk-block toggled" id="l-login">
  <div class="nk-form">
    <div class="input-group">
      <span class="input-group-addon nk-ic-st-pro"><i class="notika-icon notika-support"></i></span>
      <div class="nk-int-st">
        <input type="text" class="form-control" name="username" placeholder="Username">
      </div>
    </div>

    <div class="input-group mg-t-15">
      <span class="input-group-addon nk-ic-st-pro"><i class="notika-icon notika-edit"></i></span>
      <div class="nk-int-st">
        <input type="password" class="form-control" name="password" placeholder="Password">
      </div>
    </div>

    <button type="button" id="loginBtn" class="btn btn-login btn-success btn-float">
      <i class="notika-icon notika-right-arrow"></i>
    </button>
  </div>
</div>
</div>
<div id="toast" class="toast"></div>
<script>
// Simple toast function
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast show ${type}`;

    setTimeout(() => {
        toast.className = 'toast';
    }, 3000);
}

// Pass PHP base URL to JS
const BASE_URL = '<?php echo App::baseUrl(); ?>';

document.getElementById('loginBtn').addEventListener('click', function () {
    const usernameInput = document.querySelector('input[name="username"]');
    const passwordInput = document.querySelector('input[name="password"]');

    const username = usernameInput.value;
    const password = passwordInput.value;

    fetch(`${BASE_URL}/_ikawa/users/login`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ username, password })
    })
    .then(response => response.json())
    .then(res => {
        if (res.success) {
            showToast('Login successful!', 'success');
            usernameInput.value = '';
            passwordInput.value = '';

            setTimeout(() => {
                window.location.href = `${BASE_URL}/operations/`;
            }, 1000);
        } else {
            showToast(res.message, 'error');
            passwordInput.value = '';
        }
    });
});
</script>