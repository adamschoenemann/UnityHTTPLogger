<?php
namespace API;
class Questionnaire extends API
{

	public function post_answers($f3, $params)
	{
		$post = $f3->get("POST");
		$model = new \DB\SQL\Mapper($this->db, "answers");
		$model->copyFrom("POST");
		$model->save();
	}
}