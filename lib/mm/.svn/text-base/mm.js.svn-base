/* Micro mailer javascript 

Copyright 2010-2011 Bruce Alderson
*/
var mm = { 
	hasPosted 	: false, 
	error 		: '#mm-error', 
	thanks 		: '#mm-thanks',
	
	// hide popups
	hide		: function() {
		$(mm.error+':visible,'+mm.thanks+':visible').
			fadeOut(250, window.location.reload());
	},
	// simple form validation
	validated	: function(form) {
		var ok = true;
		
		form
			.find('label.error').removeClass('error').end()
			.find('input:enabled.required, textarea:enabled.required')
			.removeClass('error')
			.each(function(i) {
				var c = $(this), v = c.val(), fail = false;

				if (v.length == 0) fail = true;
				else if (c.hasClass('currency') && Number(v) == NaN) fail = true;
				else if (c.hasClass('number') && Number(v) == NaN) fail = true;
				else if (c.hasClass('email') && ! v.match(/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/))
					fail = true;
					
				if (fail) {
					c.addClass('error');
					c.parent().addClass('error');
					ok = false;
				}		
			}
		);
	
		return ok;
	},
	// simple background form post
	init		: function(o) {
	
		// hook form behaviours
		$('form#mm')
			.submit(function(e) {
				var f = $(this);
				
				mm.hasPosted = true;		
				
				if (!mm.validated(f))
					return false;
					
				$.ajax({
					type: f.attr('method'),
					url: f.attr('action'),
					data: f.serialize(), 
					dataType: 'json',
					success: function(d) {

						if (d === undefined || d.ok === undefined || !d.ok)
							$(mm.error).fadeIn(250);
						else
							$(mm.thanks).fadeIn(250);
					}
				});
				return false;
			}
		)
		.find('input:enabled.required, textarea:enabled.required').blur( 
			function(e) {
				if (!mm.hasPosted) return false;
				
				// revalidate form
				if (!mm.validated($(this).parent().parent()))
					return false;					
			}
		)
		.end().find('input,textarea').first().focus();
	}
};