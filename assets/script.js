// popupmanager
// v1.0.0

$(function(){    

	initPopupManager();
	
	function initPopupManager()
	{		
		//Color-Abgleich	
		$('.field-colorinput-group input[type=color]').on("input change", function(){
			$(this).parent().prevAll('input[type=text]').val(this.value);
		});
		$('.field-colorinput-group input[type=text]').on("input change", function(){
			$(this).next().children('input[type=color]').val(this.value);
		});
	}
    
});