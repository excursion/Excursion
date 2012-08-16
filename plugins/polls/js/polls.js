$(document).ready(function() {
	$('form#polls').submit(function() {
			var formInput = $(this).serialize();
			$.post($(this).attr('action'),formInput, function(data){
				$('form#polls').slideUp("fast", function() {				   
					$('#poll-results').show();
					$.ajax({
						success: function(html){
							location.reload();
						}
					});
				});
			});
		return false;	
	});
});