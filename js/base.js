var IS_LOG = true;
var ROOT = "/phpIM/index.php/";
var log = function (message) {
	if(window.console && IS_LOG) {
		console.log("DEBUG : " + message);
	}
}