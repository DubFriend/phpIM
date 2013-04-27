<?php
class New_Conversations_Model extends Model {

}

class New_Conversations_View {

}

class New_Conversations_Controller extends Controller {
	const INITIAL_SLEEP_TIME = 5000000; //5 seconds
	const UPDATE_SLEEP_TIME = 1000000; //1 seconds
	const MAX_UPDATE_CHECKS = 30;

	 
	protected function post() {
		usleep(self::INITIAL_SLEEP_TIME);
		while(!$this->is_updated()) {
			usleep(self::UPDATE_SLEEP_TIME);
		}
		return "conversations response";
	}

	private function is_updated() {
		return rand(0, 1);
	}
}
?>