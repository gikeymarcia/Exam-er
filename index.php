<!DOCTYPE html>
<?php
    ini_set('auto_detect_line_endings', TRUE);                              // fixes problems reading files saved on mac
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href="css/global.css" rel="stylesheet" type="text/css" />
        <title>Exam-er</title>
    </head>
    <body>
        <?php
            $Raw                = getFromFile('Exam.txt', FALSE);           // ## SET ##    tells the program which file to use for exam creation
            $examName           = 'Sample Exam';                            // ## SET ##    Goes at the top of the exam
            $QuestionShuffling  = TRUE;                                     // ## SET ##    if TRUE then questions will be shuffled
            $optionShuffling    = TRUE;                                     // ## SET ##    if TRUE then questions options will be shuffled
            $boldedAnswer       = TRUE;                                     // ## SET ##    if TRUE then the correct answer will be bolded
            $colorCodeShuffle   = TRUE;                                     // ## SET ##    if TRUE then shuffle type will change the color of the question (TRUE = Black, FALSE = Red, All/None of the above = Blue)
            
            
            ### shuffles questions
            if($QuestionShuffling == TRUE) {
                shuffle($Raw);
            }
            
            
            // The following code formats the raw line input as the new, complex, variable $Exam[#]
            $Exam = array();                        // create exam array
            foreach ($Raw as $line) {
                $Exam[] = array(
                                'Question' => $line['Q'],
                                'choices' => figureChoices($line),
                                'shuffle' => $line['shuffle'],
                                );
            }
            
            // Readable($Exam,"this is what has been read");
            
            // the following code chunk shuffles answer choices for each question
            $examLength = count($Exam);
            for ($num = 0; $num < $examLength; $num++) {                    // go through each exam question
                
                if (($optionShuffling == FALSE)
                     OR ($Exam[$num]['shuffle'] == 'FALSE')
                 ) {    // turns off option shuffling when set above (all trials) or if set to FALSE for this question
                    continue;
                }
                elseif (($Exam[$num]['shuffle'] == 'ALL OF THE ABOVE')
                         OR ($Exam[$num]['shuffle'] == 'NONE OF THE ABOVE')
                 ){        // special all of the above shuffle that keeps last choice in last position while other options
                    $last = array_pop($Exam[$num]['choices']);              // hold last choice apart from other choices
                    shuffle($Exam[$num]['choices']);                        // shuffle choices without last choice
                    $Exam[$num]['choices'][] = $last;                       // place last choice back into the choices (in final position)
                    $last = null;                                           // empty the temporary last choice holder
                    continue;                                               // jump back to the top of the current loop without finishing code in loop
                }
                shuffle($Exam[$num]['choices']);                            // shuffle all choices for this question
            }
            
            
            // Output all of the test questions and choices in the format we want
            echo '<h1>' . $examName . '</h1>';
            $color = 'black';
            echo '<ol>';
            foreach ($Exam as $item) {                                        // Loop that repeats for each question
                if ($item['Question'] == '') {                                // skip $Exam[0] (it's empty because question array position corresponds with question #)
                    continue;
                }
                
                // color coding the question to denote what type of shuffling happened to it
                if ($colorCodeShuffle == TRUE) {
                    switch ($item['shuffle']) {
                        case 'TRUE':
                            $color = 'black';
                            break;
                        case 'FALSE':
                            $color = 'red';
                            break;
                        case 'ALL OF THE ABOVE';
                            $color = 'blue';
                            break;
                        case 'NONE OF THE ABOVE';
                            $color = 'blue';
                            break;
                    }    
                }
                                
                echo '<li class="' . $color . '">'
                    .    '<p>' . removeJunk($item['Question']) . '</p>'
                    .    '<ol type = "A">';
                    foreach ($item['choices'] as $lure) {                                   // outputs each choice into sublist
                        if(($boldedAnswer == TRUE)                                              // bolds the correct answer if value is set above
                            AND ($lure['correct'] == 'TRUE')
                        ) {
                            echo '<li><b>' . removeJunk($lure['text']) . '</b></li>';
                            continue;
                        }
                        echo '<li>' . removeJunk($lure['text']) . '</li>';                      // Output answer choice
                    }
                    echo '</ol>';                                                            // ends answer choice sublist
                    echo '<br>';
                    echo '</li>';
                    
            }
            echo '</ol>';                                                                // ends ordered list
            
            
            // The following Code creates the answer key
            echo '<ol>';
            echo '<h1>Answer Key</h1>';
            $truePos = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M');
            $qCount = 1;
            foreach ($Exam as $Q) {
                $foundCorrect = 0;
                for ($i = 0; $i < count($Q['choices']); $i++) {            // check all options for each question
                    if ($Q['choices'][$i]['correct'] == 'TRUE') {
                        echo '<li>' . $truePos[$i] . '</li>';        // output letter of a correct answer
                        $foundCorrect++;
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
            echo '</ol>';
            
            
            /*    Exam-er
                A program for randmoizing midterms and making answer keys
                Copyright 2012-2013 Mikey Garcia
                
                
                This program is free software: you can redistribute it and/or modify
                it under the terms of the GNU General Public License version 3 as published by
                the Free Software Foundation.
            
                This program is distributed in the hope that it will be useful,
                but WITHOUT ANY WARRANTY; without even the implied warranty of
                MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
                GNU General Public License for more details.
            
                You should have received a copy of the GNU General Public License
                along with this program.  If not, see <http://www.gnu.org/licenses/>
             */
            ?>
    </body>
</html>

<?php
    ######## CUSTOM FUNCTIONS
    //function that reads from tab delimited data file and populates each position in array with header(key)-cell(values)
    function GetFromFile($fileLoc, $padding = TRUE) {
        $file    = fopen($fileLoc, 'r');                    // open the file passed through the function arguement
        $keys    = fgetcsv($file, 0, "\t");                // pulling header data from top row of file
        if ($padding == TRUE):
            $out    = array(0 => 0, 1 => 0);            // leave positions 0 and 1 blank (so when I call $array[#] it will corespond to the row in excel)
        endif;
        while ($line = fgetcsv($file, 0, "\t")) {        // capture each remaining line from the file
            $tOut    = array_combine($keys, $line);        // combine the line of data with the header
            if(isBlankLine($tOut)) {                    // do not include blank lines in output
                continue;
            }
            $out[]    = $tOut;                            // add this combined header<->line array to the ouput array
        }
        return $out;
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
    
    function isBlankLine($array) {                                // if an array is empty, all positions == "", return TRUE
        foreach ($array as $item) {
            if($item <> "") {
                return FALSE;
            }
        }
        return TRUE;
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
    
    function figureChoices ($dataLine) {
        $outputChoices = array ();
        
        $opChar = 'c';
        $count  = 1;
        $opCheck = $opChar.$count;                    // i.e., 'c1'
        while ( isset($dataLine[$opCheck]) ) {
            if( $dataLine[$opCheck] == '') {
                $count++;
                $opCheck = $opChar.$count;
                continue;
            }
            $outputChoices[] = array ( 'text' => hideTrue($dataLine[$opCheck]), 'correct' => isCorrect($dataLine[$opCheck]) );
            $count++;
            $opCheck = $opChar.$count;
        }
        return $outputChoices;
        
    }
    
    #### Debug function I use to display arrays in an easy to read fashion
    function Readable($displayArray, $NameOfDisplayed = 'unspecified'){
        echo '<br />';    
        echo "Below is the array for <b>{$NameOfDisplayed}</b>";
        echo '<pre>';
        print_r($displayArray);
        echo '</pre>';
    }
?>