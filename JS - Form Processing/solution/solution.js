const { Console } = require("console");

/**
 * Example of a local function which is not exported. You may use it internally in processFormData().
 * This function verifies the base URL (i.e., the URL prefix) and returns true if it is valid.
 * @param {*} url 
 */
function verifyBaseUrl(url)
{
	return Boolean(url.match(/^https:\/\/[-a-z0-9._]+([:][0-9]+)?(\/[-a-z0-9._/]*)?$/i));
}

/**
 * Example of a local function which is not exported. You may use it internally in processFormData().
 * This function verifies the relative URL (i.e., the URL suffix) and returns true if it is valid.
 * @param {*} url 
 */
function verifyRelativeUrl(url)
{
	return Boolean(url.match(/^[-a-z0-9_/]*([?]([-a-z0-9_\]\[]+=[^&=]*&)*([-a-z0-9_\]\[]+=[^&=?#]*)?)?$/i));
}

function isJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

/**
 * Main exported function that process the form and yields the sanitized data (or errors).
 * @param {*} formData Input data as FormData instance.
 * @param {*} errors Object which collects errors (if any).
 * @return Serialized JSON containing sanitized form data.
 */
function processFormData(formData, errors)
{
	let date_error, time_error, repeat_error, url_error, method_error, body_error = false;
	let there_is_error = false;
	let return_json, json;
	let url_base,date,time,repeat,url,method,body;
	url_base = formData.getAll('url_base');
	if (!verifyBaseUrl(url_base[0])) {
		there_is_error = true;
		errors.url_base = "Invalid URL format.";
	}

	date = formData.getAll('date');
	let date_errors_arr = {};
	for (let i = 0; i < date.length; i++) {
		if (date[i].match(/^[0-9]?[0-9]\.[0-9]?[0-9]\.[0-9][0-9][0-9][0-9]$/)) {
			let my_date = date[i].split(".");
			date[i] = Date.UTC(my_date[2], my_date[1] - 1, my_date[0]) / 1000;
		}
		else if (date[i].match(/^[0-9]?[0-9]\/[0-9]?[0-9]\/[0-9][0-9][0-9][0-9]$/)) {
			let my_date = date[i].split("/");
			date[i] = Date.UTC(my_date[2], my_date[0] - 1, my_date[1]) / 1000;
		}
		else if (date[i].match(/^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]$/)) {
			let my_date = date[i].split("-");
			date[i] = Date.UTC(my_date[0], my_date[1] - 1, my_date[2]) / 1000;
		}
		else {
			date_error = true;
			there_is_error = true;
			date_errors_arr[i] = "invalid";
		}
	}
	if (date_error) {
		errors.date = date_errors_arr;
	}

	body = formData.getAll('body');
	let body_errors_arr = {};
	for (let i = 0; i < body.length; i++) {
		if (!body[i] == "" && !isJsonString(body[i]) ) {
			there_is_error = true;
			body_error = true;
			body_errors_arr[i] = "invalid"
		}
	}
	if (body_error) {
		errors.body = body_errors_arr;
	}

	time = formData.getAll('time');
	let there_is_from_to = new Array(time.length).fill(false);
	let from_array = new Array(time.length).fill(0);
	let to_array = new Array(time.length).fill(0);
	let time_errors_arr = {};
	for (let i = 0; i < time.length; i++) {
		if (time[i].match(/^[12]?[0-9]:[0-9][0-9]$/)) {
			let my_arr = time[i].split(":");
			if (my_arr[0] > 24 || my_arr[1] > 59) {
				time_error = true;
				there_is_error = true;
				time_errors_arr[i] = "invalid";
			}
			time[i] = parseInt(my_arr[0]) * 3600 + parseInt(my_arr[1]) * 60;
		}
		else if (time[i].match(/^[12]?[0-9]:[0-9][0-9]:[0-9][0-9]$/)) {
			let my_arr = time[i].split(":");
			if (my_arr[0] > 24 || my_arr[1] > 59 || my_arr[2] > 59) {
				time_error = true;
				there_is_error = true;
				time_errors_arr[i] = "invalid";
			}
			time[i] = (parseInt(my_arr[0]) * 3600) + (parseInt(my_arr[1]) * 60) + parseInt(my_arr[2]);
		}
		else if (time[i].match(/^[12]?[0-9]:[0-9][0-9]\s+-\s+[12]?[0-9]:[0-9][0-9]$/)) {
			let my_arr = time[i].split(/[\s:-]+/);
			from_array[i] = parseInt(my_arr[0]) * 3600 + parseInt(my_arr[1]) * 60;
			to_array[i] = parseInt(my_arr[2]) * 3600 + parseInt(my_arr[3]) * 60;
			there_is_from_to[i] = true;
		}
		else if (time[i].match(/^[12]?[0-9]:[0-9][0-9]:[0-9][0-9]\s+-\s+[12]?[0-9]:[0-9][0-9]:[0-9][0-9]$/)) {
			let my_arr = time[i].split(/[\s:-]+/);
			from_array[i] = parseInt(my_arr[0]) * 3600 + parseInt(my_arr[1]) * 60 + parseInt(my_arr[2]);
			to_array[i] = parseInt(my_arr[3]) * 3600 + parseInt(my_arr[4]) * 60 + parseInt(my_arr[5]);
			there_is_from_to[i] = true;
		}
		else {
			time_error = true;
			there_is_error = true;
			time_errors_arr[i] = "invalid";
		}
		if (to_array[i] < from_array[i]) {
			time_error = true;
			there_is_error = true;
			time_errors_arr[i] = "invalid";
		}
		else if (time[i] > 86400) {
			time_error = true;
			there_is_error = true;
			time_errors_arr[i] = "invalid";
		}
	}
	if (time.length > 1) {
		for (let i = 0; i < there_is_from_to.length; i++) {
			let check_for_from_and_to = false;
			if (there_is_from_to[i] == true) {
				check_for_from_and_to = true;
			}
			if (check_for_from_and_to) {
				time_errors_arr[i] = "invalid";
			}
		}
	}
	if (time_error) {
		errors.time = time_errors_arr;
	}

	repeat = formData.getAll('repeat');
	let repeat_errors_arr = {};
	for (let i = 0; i < repeat.length; i++) {
		if (!(repeat[i] > 0 && repeat[i] < 101)) {
			there_is_error = true;
			repeat_error = true;
			repeat_errors_arr[i] = "invalid";
		}
	}
	if (repeat_error) {
		errors.repeat = repeat_errors_arr;
	}

	url = formData.getAll('url');
	let url_errors_arr = {};
	for (let i = 0; i < url.length; i++) {
		if (verifyRelativeUrl(url[i])) {
			url[i] = url_base[0] + url[i];
		}
		else {
			there_is_error = true;
			url_error = true;
			url_errors_arr[i] = "invalid";
		}
	}
	if (url_error) {
		errors.url = url_errors_arr;
	}

	method = formData.getAll('method');
	let method_errors_arr = {};
	for (let i = 0; i < method.length; i++) {
		if (method[i] != "GET" && method[i] != "POST" && method[i] != "PUT" && method[i] != "DELETE") {
			there_is_error = true;
			method_error = true;
			method_errors_arr[i] = "invalid";
		}
	}
	if (method_error) {
		errors.method = method_errors_arr;
	}

	if (!there_is_error) {
		for (let i = 0; i < repeat.length; i++) {
			if (there_is_from_to[i]) {
				if (body[i].length == 0) {
					json = [{'date' : date[i], 'time' : {'from' : from_array[i], 'to' : to_array[i]}, 'repeat' : parseInt(repeat[i]), 'url' : url[i], 
					'method' : method[i], 'body' : {}}];
				}
				else {
					json = [{'date' : date[i], 'time' : {'from' : from_array[i], 'to' : to_array[i]}, 'repeat' : parseInt(repeat[i]), 'url' : url[i], 
					"method" : method[i], "body" : JSON.parse(body[i])}];
				}
			}
			else {
				if (body[i].length == 0) {
					json = [{'date' : date[i], 'time' : time[i], 'repeat' : parseInt(repeat[i]), 'url' : url[i], 
					'method' : method[i], 'body' : {}}];
				}
				else {
					json = [{'date' : date[i], 'time' : time[i], 'repeat' : parseInt(repeat[i]), 'url' : url[i], 
					"method" : method[i], "body" : JSON.parse(body[i])}];
				}
			}
			if (i == 0) {
				return_json = json;
			}
			else {
				return_json = return_json.concat(json);
			}
		}
		return JSON.stringify(return_json);
	}
	else {
		return null;
	}
}


// In nodejs, this is the way how export is performed.
// In browser, module has to be a global varibale object.
module.exports = { processFormData };