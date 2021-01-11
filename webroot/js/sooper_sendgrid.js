$(document).ready(function(){
	console.log('Ready to Work!.');

	var firstName = $("#first-name");
	var lastName = $("#last-name");
	var subscribeEmail = $("#sub-email");
	var sourceInput = $("#source");
	var unsubscribeEmail = $("#unsub-email");
	var campaignCode = $("#campaign-code");

	$('#subscribeButton').click(function(e){
		e.preventDefault();
		
		$(this).attr('disabled', 'disabled');

		var data = {
			first_name: firstName.val().trim(),
			last_name: lastName.val().trim(),
			subemail: subscribeEmail.val().trim(),
			source: sourceInput.val().trim(),
			option: 'subscribe'
		};
		console.log(data);

		sendData(data);

		console.log('Click on Subscribe Button.');

	});

	$('#unsubscribeButton').click(function(e){
		e.preventDefault();

		$(this).attr('disabled', 'disabled');

		var data = {
			unsubemail: unsubscribeEmail.val().trim(),
			option: 'unsubscribe'
		};

		console.log(data);
		
		sendData(data);
		
		console.log('Click on Unsubscribe Button.');

	});

	$('#sendCampaignButton').click(function(e){
		e.preventDefault();
		
		$(this).attr('disabled', 'disabled');

		var data = {
			campaignCode: campaignCode.val().trim(),
			option: 'sendCampaign'
		};

		console.log(data); 
		
		sendData(data);
		
		console.log('Click on Send Campaign Button.');

	});

	$('#updateContactButton').click(function(e){
		e.preventDefault();
		
		$(this).attr('disabled', 'disabled');

		var data = {
			option: 'updateContacts'
		};

		console.log(data); 
		
		sendData(data);
		
		console.log('Click on Update Contacts Button.');

	});

	function sendData(data){
		$.ajax({
            "dataType": "json",
            "type": "POST",
            "url": 'grid/gridOption',
            "data": data,
            "success": function(data){
                console.log(data);
                window.location.reload();
            }
        });

        confirmationBox({
            title: 'Test SendGrid API ',
            body: 'Updating SendGrid, Please Wait ...',
            buttonType: 'primary',
            buttonName: 'Ok',
            closeName: 'Close',
            empty: true,
            action: function(){
            	confirmationBox('hide');
            }
        });
	}


});