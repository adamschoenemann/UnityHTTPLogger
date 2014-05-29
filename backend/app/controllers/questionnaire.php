<?php

namespace Controllers;

class Questionnaire extends Controller
{
	
	static function parse_answers($answers)
	{
		$answers = 
			\explode("|", substr($answers, 0, strlen($answers) - 1));

		$categories = array(
			O, C, E, A, N,
			O, C, E, A, N,
			O, C, E, A, N,
			O, C, E, A, N,
			O, C, E, A, N,
			O, C, E, A, N
		);

		$flipped = array(
			true,
			false,
			false,
			true,
			false,
			true,
			true,
			false,
			true,
			false,
			true,
			true,
			true,
			false,
			false,
			true,
			false,
			true,
			false,
			true,
			false,
			true,
			true,
			true,
			true,
			true,
			true,
			true,
			true,
			true
		);

		$questions = array(
			array("I love to read challenging material.", " I avoid difficult reading material."), // flip
			array("I leave a mess in my room.", " I love to tidy up."), //noflip
			array("I often feel uncomfortable about others.", " I feel comfortable around people."), //noflip
			array("I am interested in others' problems.", " I am not interested in other people's problems."), //flip
			array("I am relaxed all the time.", " I get stressed out easily."), // noflip
			array("I have a vivid imagination.", " I do not have a good imagination."), // flip
			array("I follow a schedule.", " I waste my time."), // flip
			array("I don't like to draw attention to myself.", " I don't mind being in the center of attention."), //noflip
			array("I have a good word for everyone.", " I have a sharp tongue."), // flip
			array("I seldom feel blue.", " I often feel blue."), // noflip
			array("I carry the conversation to a higher level.", " I will not probe deeply into a subject."), // flip
			array("I continue until everything is perfect.", " I do things in a half-way manner."), // flip
			array("I start conversations.", " I don't talk a lot."), // flip
			array("I feel little concern of others.", " I inquire about others' well being."), // noflip
			array("I rarely get irritated.", " I get irritated easily."), // noflip
			array("I catch on to things easily.", " I am not quick to understand things."), // flip
			array("I find it difficult to get down to work.", " I get chores done right away."), // noflip
			array("I make friends easily.", "I am a very private person."), // flip
			array("I am indifferent to the feeling of others.", " I sympathize with others' feelings."), // noflip
			array("I worry about things.", " I am not easily bothered by things."), // flip
			array("I have a difficulty imagining things.", " I spend my time reflecting on things."), // noflip
			array("I complete my duties.", " I shirk my duties."), // flip
			array("I take control of things.", " I wait for others to lead the way."), // flip
			array("I make people feel at ease.", " I do not know how to comfort others."), // flip
			array("I get angry easily.", " I seldom get mad."), // flip
			array("CONTROL I have a rich vocabulary.", " I do not use any difficult words."), // flip
			array("CONTROL I am always prepared.", " I make a mess of things."), // flip
			array("(CONTROL) I talk to a lot of people at parties.", " I find it difficult to approach others."), // flip
			array("CONTROL I can't stand confrontations.", " I love a good fight."), // flip
			array("CONTROL I get overwhelmed by emotions.", " I seldom get emotional.") // flip
		);

		$avgs = array();
		$grouped_questions = array();
		foreach($answers as $i => &$a)
		{
			$a = intval($a);
			if($flipped[$i])
				$a = 5 - $a;
	
			if(array_key_exists($categories[$i], $grouped_questions) == false)
				$grouped_questions[$categories[$i]] = array();

			$answer_index;
			if($a < 3)
				$answer_index = ($flipped[$i]) ? 1 : 0;
			else 
				$answer_index = ($flipped[$i]) ? 0 : 1;
			$grouped_questions[$categories[$i]][] = $questions[$i][$answer_index] . " => " . $answers[$i];
			$avgs[$categories[$i]] += ($a / 6) / 6;
		}
		return array("answers" => $answers,
					 "questions" => $grouped_questions,
					 "avgs" => $avgs);
	}

	function view_answer($f3, $params)	
	{
		$id = $params["id"];
		$respondents = $this->db->exec("
			SELECT * FROM answers WHERE session_id=?
		", $id);
		$respondent = $respondents[0];
		$answers = $respondent["answers"];
		
		$parsed = $this->parse_answers($answers);
		unset($respondent["answers"]);
		$respondent = $respondent + $parsed;
		// $respondent["answers"] = $answers;
		// $respondent["questions"] = $grouped_questions;
		// $respondent["avgs"] = $avgs;


		echo \Utils::json_encode($respondent, true);
	}

	function view_avgs($f3, $params)
	{
		$f3->reroute("/view/stats");
		echo "Go to /view/stats instead!";

	}

	static function sort_avgs($answers)
	{

		asort($answers);
		$order = array(
			"O" => 0,
			"C" => 1,
			"E" => 2,
			"A" => 3,
			"N" => 4
		);

		// \Utils::print_r($answers);
		$keys = array_keys($answers);
		$vals = array_values($answers);
		$result = $answers;

		$swapped = true;
		$max = 20;
		while($swapped == true && $max--)
		{
			$swapped = false;
			for($i = 0, $len = count($vals); $i < $len; $i++)
			{
				$val = $vals[$i];
				$nextval = $vals[$i + 1];

				$key = $keys[$i];
				$nextkey = $keys[$i + 1];
				// echo "$key: $val, $nextkey: " . floatval($nextval) . "<br>";
				// $is_equal = abs(floatval($val) - floatval($nextval) < 0.01);
				$is_equal = number_format($val, 4) === number_format($nextval, 4);
				if($is_equal && $order[$key] > $order[$nextkey])
				{
					// echo "$key == $nextkey<br>";
					$swapped = true;
					$result = self::assoc_swap($result, $key, $nextkey);
				}
			}
			$keys = array_keys($result);
			$vals = array_values($result);
			// \Utils::print_r($result);
			// echo "<br>";
		}
		
		return $result;

	}

	static function assoc_swap($array, $key1, $key2)
	{
		$array = self::assoc_replace_key($array, $key1, "tmp");
		$array = self::assoc_replace_key($array, $key2, $key1);
		$array = self::assoc_replace_key($array, "tmp", $key2);
		return $array;
	}

	static function assoc_replace_key($array, $key1, $key2)
	{
	    $keys = array_keys($array);
	    $index = array_search($key1, $keys);

	    if ($index !== false) {
	        $keys[$index] = $key2;
	        $array = array_combine($keys, $array);
	    }

	    return $array;
	}

}