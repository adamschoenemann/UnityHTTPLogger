<?php
namespace API;
class PilotTest extends API
{

	public function post_answers($f3, $params)
	{
		$post = $f3->get("POST");
		$model = new \DB\SQL\Mapper($this->db, "pilot_test");
		$model->copyFrom("POST");
		$model->save();
	}
}