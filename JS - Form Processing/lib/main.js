$(function(){
	$('.btn-danger').hide();
	$('.alert-danger').hide();
	$('#json').hide();

	/*
	 * Adding and removing operations blocks...
	 */
	const operationsContainer = $('#operations-container');
	let operationsCount = 0;
	const operationTemplate = $('#operation-form-template');
	operationTemplate.attr('id', '');
	operationTemplate.remove();

	addOperation();
	$('#add-operation').click(addOperation);
	
	function updateHeadings()
	{
		$('h4.card-title').each(function(idx){
			$(this).text(`Operation #${idx+1}`);
		})
	}

	function deleteOperation()
	{
		if (operationsCount > 1) {
			$(this).closest('.operation-form-group').remove();
			--operationsCount;
			updateHeadings();
		}

		if (operationsCount < 2) {
			$('.btn-danger').hide();
		}
	}

	function addOperation()
	{
		++operationsCount;
		const firstOperation = operationTemplate.clone();
		firstOperation.find('.btn-danger').click(deleteOperation);
		operationsContainer.append(firstOperation);
		updateHeadings();
		if (operationsCount > 1) {
			$('.btn-danger').show();
		}
	}


	/*
	 * Form Submission...
	 */

	function setError(elem, errMsg)
	{
		elem.setCustomValidity(errMsg);
		const divAlert = $(elem).closest('.form-group').find('.alert-danger');
		divAlert.text(errMsg);
		divAlert.show();
	}

	$('#submit-btn').click(function(){
		$('.form-control').each(function(){
			this.setCustomValidity('');
		});
	})

	const form = document.getElementById('form');
	$(form).submit(function(ev) {
		ev.preventDefault();
		$('.alert-danger').hide();
		$('#json').hide();

		const formData = new FormData(form);
		const errors = {};
		const res = module.exports.processFormData(formData, errors);
		if (!res) {
			// Show errors...
			$('#form-submit-failed').show();

			$('#form > .form-group > .form-control').each(function(){
				const name = $(this).attr('name');
				if (errors[name]) setError(this, errors[name]);
			});

			$('.operation-form-group').each(function(idx){
				$(this).find('.form-control').each(function(){
					const name = $(this).attr('name');
					if (errors[name] && errors[name][idx]) setError(this, errors[name][idx]);
				});
			});
		}
		else {
			$('#json').show().find('pre').text(res);
		}
	});

	$('#export').click(function(){
		const formData = new FormData(document.getElementById('form'));
		const res = [];
		for (let [ name, value ] of formData.entries()) {
			res.push({ name, value });
		}
		console.log(JSON.stringify(res, undefined, 4));
	});
});
