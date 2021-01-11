confirmationBox = function (options){
	var modal = options.modal || $('#sooper-modal');
	if(typeof(options) == "string" && options=="hide"){
		modal.modal('hide');
		return;
	}

	var buttonType = "success";
	if(typeof(options) == "object" && "buttonType" in options){
		buttonType = options.buttonType || buttonType;
	}

	modal.find('.modal-title').text(options.title);
	if(typeof(options.empty) == "boolean" && options.empty){
		modal.find('.modal-body').html("");
	}

	if(typeof(options.body) == "object") {
		if(modal.find('.modal-body').text().length == 0){
			options.body.appendTo(modal.find('.modal-body'));
		}
	}
	else {
		modal.find('.modal-body').append(options.body);
	}

	if(typeof(options.buttonName) == "string"){
		modal.find('.continue').html(options.buttonName);
	}

	var buttonClose = "Close";
	if(typeof(options.closeName) == "string"){
		buttonClose = options.closeName;
	}
	modal.find('.btn-inverse').html(buttonClose);
	modal.find('.continue').attr('class', "btn continue").show();
	modal.find('.continue').addClass('btn-'+buttonType);
	modal.find('.continue').unbind('click');
	modal.find('.continue').click(options.action);
	modal.modal({show:true})
};

$(document).ready(function () {
	var emailInput = $("#winner-email");
	var zipCodeInput = $("#zip-code");
	var phoneInput = $("#telephone");
	var addressInput = $("#address");
	var cityInput = $("#city");
	var imgCrop, originalheight, originalwidth;
	var imageSource = $('#imagePreview');

	$('#confirmButton').on('click', function(e){
		$('.js-vldtr-alert.js-vldtr-alert-required.js-vldtr-text2.fieldMessage').remove();
		$('.js-vldtr-alert.js-vldtr-alert-required.js-vldtr-text2.phoneMessage').remove();
		$('.js-vldtr-alert.js-vldtr-alert-required.js-vldtr-text2.zipCode').remove();
		$('.js-vldtr-alert.js-vldtr-alert-required.js-vldtr-text2.cityMessage').remove();
		e.preventDefault();
		if(validateStreetAddress() &&validateCity() &&isValidWinnerZipCode()&&getTelephoneNumber()){
			validateWinnerData();
		}
	});

	function isValidWinnerZipCode(){
		var zipRegex = /(^\d{3,5}$)|(^\d{5}-\d{4}$)/;
		if(zipRegex.test(zipCodeInput.val())){

			zipCodeInput.removeClass('js-vldtr-error');
			$('#preview-message').remove();
			return true;
		}

		zipCodeInput.addClass('js-vldtr-error');
		if ($('.js-vldtr-alert.js-vldtr-alert-required.js-vldtr-text2.zipCode').length<1) {
			zipCodeInput.after('<span class="js-vldtr-alert js-vldtr-alert-required js-vldtr-text2 zipCode"> Please enter a valid Zip Code </span>')
		}
		return false;
	}


	function validateWinnerData(){
		$.ajax({
            "dataType": "json",
            "type": "POST",
            "url": '../winnerData',
            "data": { winner: getWinnerData() },
            "success": function(data){
                console.log(data);
                $("#WinnerForm").submit();
	        }
        });
	}

	function validateCity(){
		if ($('#city').val().length>1) {
			$('#city').removeClass('js-vldtr-error');
			return true;
		}
		$('#city').addClass('js-vldtr-error');
		$('#city').after('<span class="js-vldtr-alert js-vldtr-alert-required js-vldtr-text2 cityMessage"> City is required. </span>')
		return false;
	}

	function validateStreetAddress(){
		return validatefieldByLength(addressInput,1,'Address is required');
	}

	function validatefieldByLength(field,lengthValue,message){
		if(field.val() != "" && field.val().length > lengthValue){
			field.removeClass('js-vldtr-error');
			return true;
		}

		field.after('<span class="js-vldtr-alert js-vldtr-alert-required js-vldtr-text2 fieldMessage"> '+message+' </span>')

		field.addClass('js-vldtr-error');
		return false
	}


	function getTelephoneNumber(){
		var value = phoneInput.val();

		value = value.replace(/[-()+ ]/g, "");
		if(value[0] == 1){
			value = value.substring(1, value.length);
		}

		if(isNaN(value) || value.length != 10){
			phoneInput.addClass('js-vldtr-error');
			phoneInput.after('<span class="phoneMessage js-vldtr-alert js-vldtr-alert-required js-vldtr-text2"> Phone number invalid </span>')
			return false;
		}

		phoneInput.removeClass('js-vldtr-error');

		return value;
	}

	function getWinnerData(){
		var winnerData = {
			winnerId: $("#winnerID").val(),
			address: addressInput.val(),
			city: cityInput.val(),
			state: $("#state").val(),
			phone: getTelephoneNumber(),
			zip: zipCodeInput.val()
		};
		return winnerData;
	}

	$('#fileUpload').change(
		function () {
			readURL(this);
			cropperInit();
		}
	);

	function cropperInit() {
		$('.selfie_img').css('height',$('.selfie_img').width());//responsive square image area
		$('#imagePreview').cropper('destroy');//refreshing cropper instance
		$('#imagePreview').cropper({
			aspectRatio: 1 / 1,
			crop: updateCoords,
			autoCropArea:1
			// autoCrop:false,
		});

		$('.rotate-btn').removeAttr('hidden');
		$('.flip-btn').removeAttr('hidden');
		$('.crop-btn').removeAttr('hidden');

		$('.rotate-btn').removeAttr('disabled');
		$('.flip-btn').removeAttr('disabled');
		$('.crop-btn').removeAttr('disabled');
	}

	$('.crop-btn').click(function () {
		if (!($('#fileUpload').val()==""))
		{
			var uploadUrl=window.location.href;
			uploadUrl=uploadUrl.replace("winnerphotoconfirmationupload","photouploading");
			uploadCrop(uploadUrl);
		}
	});

	$('.rotate-btn').click(function () {
		if (!($('#fileUpload').val()==""))
			$('#imagePreview').cropper('rotate', 90);
	});

	$('.flip-vertical').click(function () {
		if (!($('#fileUpload').val()==""))
			cropperFlip($(this));
	});

	$('.flip-horizontal').click(function () {
		if (!($('#fileUpload').val()==""))
			cropperFlip($(this));
	});
	function cropperFlip(flipBtn) {
		$('#imagePreview').cropper('enable');
		var flipH=$('#flipHorizontalValue').val();
		var flipV=$('#flipVerticalValue').val();
		if (flipBtn.attr('class').includes('flip-horizontal'))
		{
			flipH=flipH*(-1);
		}
		else
		{
			flipV=flipV*(-1);
		}
		$('#imagePreview').cropper('scale', flipH, flipV);
	}

	function updateCoords(coord) {
		$('#x').val(coord.x);
	    $('#y').val(coord.y);
	    $('#w').val(coord.width);
	    $('#h').val(coord.height);
	    $('#rotationValue').val(coord.rotate);
		$('#flipHorizontalValue').val(coord.scaleX);
		$('#flipVerticalValue').val(coord.scaleY);
	}

	function readURL(input) {

	    if (input.files && input.files[0])
		{
	        imageUrl = window.URL.createObjectURL(input.files[0])//cropper wont work otherwise
			imageSource.attr("src", imageUrl);
			imageSource.attr('style','max-width:100%');
	    }
	}

	$('#imageUploadSubmit').click(
		function (e) {
			e.preventDefault();
			$(this).attr('disabled','true');
			var uploadUrl=window.location.href;
			var siteCode = $("#site-code").val();
			uploadUrl=uploadUrl.replace("winnerphotoconfirmationupload","photouploading");
			if($('#fileUpload').val().length>0){
				uploadCrop(uploadUrl, 1);
			} else {
				window.location.href=window.location.href.replace("winnerphotoconfirmationupload","sharegoodnews");
			}
		}
	);

	function getFileName(url) {
		var fileName=url;
		if (fileName!="") {
			var tmp =fileName.split('/winnerphoto/');
			var removedCache=tmp[1].split('?');
			fileName=removedCache[0];
		}
		return fileName
	}

	function uploadCrop(uploadUrl,submit) {
		submit=submit||'';
		var uploadTime=Date.now();
		var quality = 1;
		var mainImage=$('#imagePreview').cropper('getCroppedCanvas');
		var thunbImage=$('#imagePreview').cropper('getCroppedCanvas',{width:128,height:128});
		mainImage.toBlob(
			function (blob) {
				if (blob.size>7168000) {
					quality = 0.5;
				}
				var formData = new FormData();
				formData.append('croppedImage', blob);
				formData.append('uploadTime', uploadTime);
				$.ajax(uploadUrl, {
					method: "POST",
					data: formData,
					processData: false,
					contentType: false,
					success: function (imgUrl) {
						$('#croppedImageUrl').val(imgUrl);
						$('#imagePreview').attr('src', imgUrl+"?"+new Date().getTime());
						cropperInit();
						if (submit==1) {
							$.post($('#imageCropping').attr('action'),$('#imageCropping').serializeArray())
							.success(function () {
								window.location.href=window.location.href.replace("winnerphotoconfirmationupload","sharegoodnews");//redirect to echosign link
							});
						}
					},
					error: function () {
					  console.log('Upload error');
					}
				});
			}
		,'image/jpeg',quality);

		if (submit==1) {
			thunbImage.toBlob(
				function (blob){
					var formData=new FormData();
					formData.append('thumbImage',blob);
					formData.append('uploadTime', uploadTime);
					var uploadUrl=(""+window.location.href).split('winnerphotoconfirmationupload')[0]+"photouploading/"+$("#imageCodeName").val()+"/thumb";

					$.ajax(uploadUrl,{
						method: "POST",
						data: formData,
						processData: false,
						contentType: false,
					});

				}
			,'image/jpeg',quality);
		}
		return;
	}
});
