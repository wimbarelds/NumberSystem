<?php
	
	function str($input)	// To be used in array_map
	{
		$input = ((string) $input);
		return $input;
	}
	
	class NSC
	{
		private $sys = array();	// Array to store number systems
		
		function NSC()
		{	// Constructor
			// Define default number systems
			
			$this->sys["binary"] = array(0, 1);
			$this->sys["quaternary"] = array(0, 1, 2, 3);
			$this->sys["quinary"] = array(0, 1, 2, 3, 4);
			$this->sys["sexal"] = array(0, 1, 2, 3, 4, 5);
			$this->sys["octal"] = array(0, 1, 2, 3, 4, 5, 6, 7);
			$this->sys["decimal"] = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
			$this->sys["hexadecimal"] = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "A", "B", "C", "D", "E", "F");
			
			$this->sys["base64"] = array(
				"A", "B", "C", "D", "E", "F", "G", "H", 
				"I", "J", "K", "L", "M", "N", "O", "P", 
				"Q", "R", "S", "T", "U", "V", "W", "X", 
				"Y", "Z", "a", "b", "c", "d", "e", "f", 
				"g", "h", "i", "j", "k", "l", "m", "n", 
				"o", "p", "q", "r", "s", "t", "u", "v", 
				"w", "x", "y", "z", "0", "1", "2", "3", 
				"4", "5", "6", "7", "8", "9", "+", "/"
			);  // NOTE: Base 64 will not convert strings to/from in the same way normal base 64 encoding does, this merely uses the character set used in base64 and uses a different algorithm for the conversion from and to than the base64_encode/decode algorithm.
			
			$this->sys["urlsafe"] = array(
				"A", "B", "C", "D", "E", "F", "G", "H", 
				"I", "J", "K", "L", "M", "N", "O", "P", 
				"Q", "R", "S", "T", "U", "V", "W", "X", 
				"Y", "Z", "a", "b", "c", "d", "e", "f", 
				"g", "h", "i", "j", "k", "l", "m", "n", 
				"o", "p", "q", "r", "s", "t", "u", "v", 
				"w", "x", "y", "z", "0", "1", "2", "3", 
				"4", "5", "6", "7", "8", "9", ".", "-",
				"~", "_"
			);	// These characters are all safe for in-url use. 98 characters in total
			
			$this->sys["base96"] = array(
				"A", "B", "C", "D", "E", "F", "G", "H",  // 8 
				"I", "J", "K", "L", "M", "N", "O", "P",  // 16
				"Q", "R", "S", "T", "U", "V", "W", "X",  // 24
				"Y", "Z", "a", "b", "c", "d", "e", "f",  // 32
				"g", "h", "i", "j", "k", "l", "m", "n",  // 40
				"o", "p", "q", "r", "s", "t", "u", "v",  // 48
				"w", "x", "y", "z", "`", "1", "2", "3",  // 56
				"4", "5", "6", "7", "8", "9", "0", "-",  // 64
				"=", "~", "!", "@", "#", '$', "%", "^",  // 72
				"&", "*", "(", ")", "_", "+", "[", "]",  // 80
				"\\", ";", "'", ",", ".", "/", "{", "}", // 88
				"|", ":", '"', "<", ">", '?', " ", "\n"  // 96
			);	// These characters aren't always safe, but they're all human readable and recognizable
			
			$this->sys["base222"] = array();
			for($i = 32; $i < 127; $i++)
			{	$this->sys["base222"][] = chr($i); }
			for($i = 128; $i < 255; $i++)
			{	$this->sys["base222"][] = chr($i); }
				// These characters aren't always safe, but they're all actual basic text characters
			
			$this->sys["ascii"] = array();
			for($i = 0; $i < 256; $i++)
			{	$this->sys["ascii"][] = chr($i); }
			unset($i);
				// Full Ascii table
		}
		
		public function addNS($array, $name)
		{
			$this->sys[$name] = $array;
		}
		
		private function getNS($get)
		{
			// Are we using a custom number system?
			if(is_numeric($get))
			{
				$ns = array();
				$max = ((int) $get);
				
				for($i = 0; $i < $max; $i++)
				{	$ns[] = $i; }
			}
			// Are we using a default number system?
			elseif(isset($this->sys[$get]))
			{	$ns = $this->sys[$get]; }
			// We do not recognize / are unable to interpret this numbersystem
			else
			{	$ns = false; }
			
			return $ns;
		}
		
		private function parseFrom($i, $type = "decimal")
		{	// Interpret input
			
			// We do not need to interpret integers, we understand those
			if($type == "decimal" || $type == 10)
			{	return $i; }
			
			// Fetch the numbersystem
			if(!($ns = $this->getNS($type)))
			{	return false; }
			
			// Separate input into array in chunks as large as the largest chunk in the number system
			$input = str_split(((string) $i), strlen(end($ns)));
			// Cast numbersystem values to string
			$ns = array_map("str", $ns);
			
			$return = 0;			// We keep adding to this until we're done
			$count = count($input);
			$ns_base = count($ns);
			
			for($i = 0; $i < $count; $i++)
			{
				$num = array_search($input[$i], $ns, false);	// What "number" does this character represent in our number system
				$exponent = $count - $i - 1;					// The first (left-most) character of a "number string", is the largest value
				$plus = pow($ns_base, $exponent) * $num;		// The addition we need to make to our return value
				$return += $plus;
			}
			return $return;
		}
		
		private function parseTo($i, $type)
		{	// Convert interpretation to output
			
			// We do not need to interpret integers, we understand those
			if($type == "decimal" || $type == 10)
			{	return $i; }
			
			// Fetch the numbersystem
			if(!($ns = $this->getNS($type)))
			{	return false; }
			
			// Separate input into array in chunks (Our input here will be an integer, so all chunks can be 1 character in size)
			$input = str_split((string) $i);
			// Cast numbersystem values to string
			$ns = array_map("str", $ns);
			// In a base [x] numbersystem, the value represented of [y]000 is [y] times [x] to the power of the number of characters [x] is removed from the far right
			$ns_base = count($ns);
			
			// Determine wether we'll return our value in a string or array form
			if(strlen(end($ns)) > 1)
			{	$return = array(); }
			else
			{	$return = ""; }
			
			// $x represents the the number of characters we're removed from the far right
			// $y represents the actual individual number irrespective of it's location within the value
			
			// We will start by finding the smallest [n]000 type number that we can not fit inside out number, the number of 0's in this number will be the size (in characters) of the number we're looking for
			for($x = 0; pow($ns_base, $x) <= $i; $x++){}
			
			// We then go through each of those characters
			for(; $x > 0; $x--)
			{
				// We count back from ns_base to find the number that will fit inside our input
				for($y = $ns_base; $y * pow($ns_base, ($x - 1)) > $i; $y--){}
				// Substract the fitting value from our input number
				$i -= $y * pow($ns_base, ($x - 1));
				
				// Append the appropriate character to match our number to our output
				if(is_array($return))
				{	$return[] = $ns[$y]; }
				else
				{	$return .= $ns[$y]; }
			}
			return $return;
		}
		
		public function convert($num, $to = "decimal", $from = "decimal")
		{
			return $this->parseTo($this->parseFrom($num, $from), $to);
		}
	}
	
	$ns = new NSC;
	
	$a = array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "a", "b", "c", "d", "e", "f");
	$r = array();
	
	for($i = 0; $i < 16; $i++) {
		for($j = 0; $j < 16; $j++) {
			$r[] = $a[$i].$a[$j];
		}
	}
	
	$ns->addNS($r, "biheximal");
	if(isset($_GET["i"]))
	{
		if(!isset($_GET["from"])) $_GET["from"] = "decimal";
		if(!isset($_GET["to"])) $_GET["to"] = "decimal";
		echo "<pre>";
		var_dump($ns->convert($_GET["i"], $_GET["to"], $_GET["from"]));
		echo "</pre>";
	}
	//echo "<pre>";
	//var_dump($ns->convert("eb04afc2bfa381ec", "ascii", "biheximal"));
	//echo "</pre>";
	
?>