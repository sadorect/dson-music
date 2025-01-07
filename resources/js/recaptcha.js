function submitForm(formId) {
    grecaptcha.ready(function() {
        grecaptcha.execute(config.services.recaptcha_site_key, {action: 'submit'}).then(function(token) {
            const form = document.getElementById(formId);
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'recaptcha_token';
            input.value = token;
            form.appendChild(input);
            form.submit();
        });    
      
      });
    }
