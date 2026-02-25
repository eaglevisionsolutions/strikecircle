
$(function() {
	$('#loginForm').on('submit', function(e) {
		e.preventDefault();
		var $btn = $(this).find('button[type=submit]');
		$btn.prop('disabled', true);
		$('#loginError').text('');
		var identifier = $(this).find('input[name=identifier]').val();
		var password = $(this).find('input[name=password]').val();
		var remember_me = $(this).find('input[name=remember_me]').is(':checked');
		API.request('auth/login', {
			method: 'POST',
			body: JSON.stringify({ identifier, password, remember_me })
		})
		.then(function(res) {
			API.setToken(res.data.access_token);
			window.location.href = '/dashboard';
		})
		.catch(function(err) {
			$('#loginError').text((err && err.message) || 'Login failed.');
		})
		.always(function() {
			$btn.prop('disabled', false);
		});
	});

	$('#btnGoogle').on('click', function() {
		window.location.href = '/api/v1/auth/oauth/google/start';
	});
	$('#btnFacebook').on('click', function() {
		window.location.href = '/api/v1/auth/oauth/facebook/start';
	});
});
