<?php

class Utils {

	public static function print_r($arr)
	{
		echo str_replace(array('&lt;?php&nbsp;','?&gt;'), '', highlight_string( '<?php ' .     var_export($arr, true) . ' ?>', true ) ), "<br>";
	}

	public static function array_to_table($arr, $user_style = NULL)
	{
		$o = array();

		// Style
		$style = array(
			"table" => array(
				"border-collapse" => "collapse"
			),
			"tr" => array(

			),
			"tr:nth-child(even)" => array(
				"background" => "#aaa"
			),
			"th" => array(
				"padding-right" => "10px;"
			),
			"td" => array(
				"padding" => "2px 10px"
			)

		);
		if($user_style != NULL)
		{
			$style  += $user_style;
		}
		$o[] = "<style>";
		foreach($style as $selector => $rules)
		{
			$o[] = $selector . "{";
			foreach($rules as $attr => $val)
			{
				$o[] = $attr . ":" . $val . ";";
			}
			$o[] = "}";
		}
		$o[] = "</style>";

		// Convert
		$headers = array_keys($arr[0]);
		$o[] = "<table class='generated_table'><tr>";
		foreach($headers as &$h)
		{
			$o[] = "<th>" . $h . "</th>";
		}
		unset($h);
		$o[] = "</tr>";
		
		foreach($arr as $row)
		{
			$o[] = "<tr>";
			foreach($headers as &$h)
			{
				$cell = $row[$h];
				if($cell == NULL) $cell = "";
				$o[] = "<td>" . $cell . "</td>";
			}
			$o[] = "</tr>";
		}
		$o[] = "</table>";
		return implode("", $o);
	}

	public static function send_csv($csv, $filename)
	{
        // send response headers to the browser
        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment;filename='.$filename);
        $fp = fopen('php://output', 'w');
        fputs($fp, $csv);
        fclose($fp);
	}

	public static function array_to_csv($arr, $del = ",", $linedel = "\n")
	{
		$headers = array_keys($arr[0]);
		$o = array();
		$o[] = $headers;
		foreach($arr as &$entry)
		{
			$line = array();
			foreach($headers as &$h)
			{
				$item = $entry[$h];
				if($item == NULL) $item = "";
				$line[] = $item;
			}
			$o[] = $line;
		}

		$result = "";
		foreach($o as $l)
		{
			$result .= implode($del, $l);
			$result .= $linedel;
		}
		$result = substr($result, 0, strlen($result) - strlen($linedel));
		return $result;
	}

	public static function json_encode($obj, $indent = false){
		$result;
		if(self::is_assoc($obj)){
			$result = json_encode(array($obj));
		} else {
			$result = json_encode($obj);
		}

		return ($indent) ? self::indent($result) : $result;
	}

	public static function is_assoc($array) {
		return (bool)count(array_filter(array_keys($array), 'is_string'));
	}

	/**
	 * Indents a flat JSON string to make it more human-readable.
	 *
	 * @param string $json The original JSON string to process.
	 *
	 * @return string Indented version of the original JSON string.
	 */
	public static function indent($json) {

		$result      = '';
		$pos         = 0;
		$strLen      = strlen($json);
		$indentStr   = '  ';
		$newLine     = "\n";
		$prevChar    = '';
		$outOfQuotes = true;

		for ($i=0; $i<=$strLen; $i++) {

        // Grab the next character in the string.
			$char = substr($json, $i, 1);

        // Are we inside a quoted string?
			if ($char == '"' && $prevChar != '\\') {
				$outOfQuotes = !$outOfQuotes;

        // If this character is the end of an element,
        // output a new line and indent the next line.
			} else if(($char == '}' || $char == ']') && $outOfQuotes) {
				$result .= $newLine;
				$pos --;
				for ($j=0; $j<$pos; $j++) {
					$result .= $indentStr;
				}
			}

        // Add the character to the result string.
			$result .= $char;

        // If the last character was the beginning of an element,
        // output a new line and indent the next line.
			if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
				$result .= $newLine;
				if ($char == '{' || $char == '[') {
					$pos ++;
				}

				for ($j = 0; $j < $pos; $j++) {
					$result .= $indentStr;
				}
			}

			$prevChar = $char;
		}

		return $result;
	}


	public static function display_table($models)
	{
		$output = array();

		$header = array();
		$header[] = "<tr>";
		$keys = array_keys($models[0]);
		foreach($keys as $key)
			$header[] = "<td>$key</td>";
		$header[] = "</tr>";

		$body = array();
		foreach($models as $model)
		{
			$body[] = "<tr>";

			$values = array_values($model);
			foreach($values as $val)
				$body[] = "<td>$val</td>";

			$body[] = "</tr>";
		}

		return implode("", $header) . implode("", $body);
	}

	function moveUp($input,$index)
	{
		$new_array = $input;

		if((count($new_array)>$index) && ($index>0)){
			array_splice($new_array, $index-1, 0, $input[$index]);
			array_splice($new_array, $index+1, 1);
		} 

		return $new_array;
	}

	function moveDown($input,$index)
	{
		$new_array = $input;

		if(count($new_array)>$index) {
			array_splice($new_array, $index+2, 0, $input[$index]);
			array_splice($new_array, $index, 1);
		} 

		return $new_array;
	}

}