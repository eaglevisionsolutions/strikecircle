
$(function() {
	$('#loginForm').on('submit', function(e) {
		e.preventDefault();
		var $btn = $(this).find('button[type=submit]');
		$btn.prop('disabled', true);
		$('#loginError').text('');
		var email = $(this).find('input[name=email]').val();
		var password = $(this).find('input[name=password]').val();
		API.request('auth/login', {
			method: 'POST',
			body: JSON.stringify({ email, password })
		})
		.then(function(res) {
			API.setToken(res.data.access_token);
			window.location.href = '/dashboard';
		})
		.catch(function(err) {
			$('#loginError').text(err.message || 'Login failed.');
		})
		.always(function() {
			$btn.prop('disabled', false);
		});
	});
});
