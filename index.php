<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="css/global.css" rel="stylesheet" type="text/css" />
		<link href='http://fonts.googleapis.com/css?family=Kreon' rel='stylesheet' type='text/css' />
		<title>Test Questions</title>
	</head>
	<body>
			<?php
			$Raw				=	getFromFile("Exam.txt");					// ## SET ##	tells the program which file to use for exam creation
			$QuestionShuffling	=	FALSE;										// ## SET ##	if TRUE then questions will be shuffled
			$optionShuffling	=	TRUE;										// ## SET ##	if TRUE then questions options will be shuffled
			$boldedAnswer		=	TRUE;										// ## SET ##	if TRUE then the correct answer will be bolded
			$colorCodeShuffle	=	TRUE;										// ## SET ##	if TRUE then shuffle type will change the color of the question (TRUE = Black, FALSE = Red, All/None of the above = Blue)
			
			
			### shuffles questions
			if($QuestionShuffling == TRUE) {
				shuffle($Raw);
			}
			
			
			// The following code formats the raw line input as the new, complex, variable $Exam[#]
			$Exam = array();						// create exam array
			foreach ($Raw as $line) {
				$Exam[] = array(
								"Question" => $line['Q'],
								"choices" => array( 0 => array("text" => hideTrue($line['c1']), "correct" => isCorrect($line['c1']) ),
													1 => array("text" => hideTrue($line['c2']), "correct" => isCorrect($line['c2']) ),
													2 => array("text" => hideTrue($line['c3']), "correct" => isCorrect($line['c3']) ),
													3 => array("text" => hideTrue($line['c4']), "correct" => isCorrect($line['c4']) )	),
								"shuffle" => $line['shuffle']);
			}
			
			
			// the following code chunk shuffles answer choices for each question
			$examLength = count($Exam);
			for ($tmp = 0; $tmp < $examLength; $tmp++) {
				// turns off option shuffling for all trials (value is set above)
				if($optionShuffling == FALSE) {
					continue;
				}
				// skip shuffling for header line or if shuffle for this question is == FALSE
				elseif($Exam[$tmp]['shuffle'] == "FALSE") {
					continue;
				}
				elseif ($Exam[$tmp]['shuffle'] == "ALL OF THE ABOVE" OR $Exam[$tmp]['shuffle'] == "NONE OF THE ABOVE"){		// special all of the above shuffle that keeps last choice in last position while other options
					$last = array_pop($Exam[$tmp]['choices']);				// hold last choice apart from other choices
					shuffle($Exam[$tmp]['choices']);						// shuffle choices without last choice
					// line I'm most worried about
					$Exam[$tmp]['choices'][] = $last;						// place last choice back into the choices (in final position)
					$last = null;											// empty the temporary last choice holder
					continue;												// jump back to the top of the current loop without finishing code in loop
				}
				shuffle($Exam[$tmp]['choices']);							// shuffle all choices for this question
			}
			
			
			// Output all of the test questions and choices in the format we want
			echo "<ol>";
			$color = "black";
			foreach ($Exam as $item) {										// Loop that repeats for each question
				
				if ($item['Question'] == "") {								// skip $Exam[0] (it's empty because question array position corresponds with question #)
					continue;
				}
				
				// color coding the question to denote what type of shuffling happened to it
				if ($colorCodeShuffle == TRUE) {
					switch ($item['shuffle']) {
						case "TRUE":
							$color = "black";
							break;
						case "FALSE":
							$color = 'red';
							break;
						case "ALL OF THE ABOVE";
							$color = "blue";
							break;
						case "NONE OF THE ABOVE";
							$color = "blue";
							break;
					}	
				}
				
				
				
				
				echo '<li class="'.$color.'">';								// declares that the question is the next item on list
				echo "<p>" . removeJunk($item['Question']) . "</p>";		// Outputs the question as it's own paragraph
				echo "<ol type = \"A\">";									// makes sublist (where choices go)
				foreach ($item['choices'] as $lure) {						// outputs each choice into sublist
					// bolds the correct answer if value is set above
					if($boldedAnswer == TRUE	&&	$lure['correct'] == "TRUE") {
						echo "<li><b>" . removeJunk($lure['text']) . "</b></li>";
						continue;
					}
					echo "<li>" . removeJunk($lure['text']) . "</li>";		// Output answer choice
				}
				echo "</ol>";												// ends answer choice sublist
				echo "</li>";
				echo "<br />";
			}
			echo "</ol>";													// ends ordered list
			
			
			// The following Code creates the answer key
			echo "<br /> <br /> <h1>Answer Key</h1>";
			echo "<ol>";
			$truePos = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J");
			$qCount = 1;
			// setting value to 3 so the header column can be skipped easily (skips first iteration of foreach loop)
			foreach ($Exam as $Q) {
				$foundCorrect = 0;
				for ($i = 0; $i < count($Q['choices']); $i++) {
					if ($Q['choices'][$i]['correct'] == "TRUE") {
						echo "<li>" . "\t".$truePos[$i] . "</li>";
						$foundCorrect = $foundCorrect + 1;
						//echo "GOT ONE!<br />";
					}
				}
				// checks to make sure we found 1 and only 1 answer for each question of the exam
				if ($foundCorrect == 0) {
					echo "<li>NO CORRECT ANSWER FOUND FOR QUESTION {$qCount}!  STOP, FIX INPUT SHEET!</li>";
				} elseif ($foundCorrect > 1) {
					echo "<li>MORE THAN ONE CORRECT ANSWER FOUND FOR QUESTION {$qCount}!  STOP, FIX INPUT SHEET!</li>";
				}
				$qCount++;
			}
			echo "</ol>";
			?>
	</body>
</html>

<?php		######## CUSTOM FUNCTIONS
	//function that reads from tab delimited data file and populates each position in array with header(key)-cell(values)
	function getFromFile($fileLoc) {
		$file = fopen($fileLoc, 'r');					//open the file passed through the function arguement
		$keys = fgetcsv($file, 0, "\t");				//pulling header data from file
		$out = array();									//makes array that will be returned
		while ($line = fgetcsv($file, 0, "\t")) {		//get data from each line in the .txt file
			$tOut = array_combine($keys, $line);		//combine header file keys with current line of data
			$out[] = $tOut;								//append header-key line to next row of output array	
		}
		return $out;									//return the output array
	}
	
	function removeJunk ($string) {
		// Function from http://shiflett.org/blog/2005/oct/convert-smart-quotes-with-php
		// added chr(252) 'lowercase u with umlat'
		$search = array(chr(145),
						chr(146),
						chr(147),
						chr(148),
						chr(151),
						chr(252),
						chr(176)
						);
						
		$replace = array("'",
						 "'",
						 '"',
						 '"',
						 '-',
						 '&uuml;',
						 '&deg;');
		
		return str_replace($search, $replace, $string);
	}
	
	function isCorrect($choice) {
		$choice = trim($choice);
		if (substr($choice, 0, 1) == '*') {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	function hideTrue ($option) {
		$option = trim($option);
		if (substr($option, 0, 1) == '*') {
			return substr($option, 1);
		}
		else {
			return $option;
		}
	}
	
	#### Debug function I use to display arrays in an easy to read fashion
	function Readable($displayArray, $NameOfDisplayed = "unspecified"){
		echo "<br />";	
		echo "Below is the array for <b>{$NameOfDisplayed}</b>";
		echo '<pre>';
		print_r($displayArray);
		echo '</pre>';
	}
?>