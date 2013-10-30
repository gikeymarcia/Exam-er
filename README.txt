Isn't creating multiple versions of a midterm annoying? Not anymore. This program can randomize placement of questions, randomize options within each question, and will create an answer key for this randomized exam. Want to make another version? Refresh your browser and version B is ready.

Welcome to Exam-er

Exam-er is written in PHP and runs in a web browser.  Using this program is simple.
1.  Enter your exam questions and options into the file 'Exam.txt'.
2.  Place the Exam-er folder in your webserver (local servers like WAMP, MAMP, or LAMP are probably better options since you don't want your exam bouncing around the web).
3.  Naviagate to the folder (e.g., "[your server]/Exam-er/")
4.  Copy/Paste the randomized exam into your favorite word processor and you're done.
5.  Want to make another version?  Hit refresh on your browser and repeat step 4.

Pro-tip:  Make sure to remove the answer key from the final exam unless you want to really go easy on the kids.


Exam.txt format
	This file is a tab-delimited text file and should be opened in a spreadsheet program like excel, Calc, Numbers, etc.
	The first row in the spreadsheet tells you what information is expected in each cell.  Each subsequent row in the sheet corresponds to a single question on your exam.
	For each row you'll need:
		a 'Q' which is the question you're asking.  This question is HTML formatted so if you'd like to include things like <b>bold</b> or <i>italics</i> then you need to use the appropriate html tags.
		c1, c2, c3, c4: These are the options for your multiple choice question.  HTML formatting also applies for each of the options.  To mark a given option as the correct response place an asterisk, *, as the first character of the cell.  The program will remove the asterisk from the formatted version of your exam.
		'shuffle' is the column that tells the program how to randomize your options.  If you'd like the options for this question to be randomized set to 'TRUE', if you wouldn't like the options shuffled set to 'FALSE', if you'd like to keep the final position constant but shuffle the other options you can use either 'ALL OF THE ABOVE' or 'NONE OF THE ABOVE' as the shuffle value.  'ALL OF THE ABOVE' and 'NONE OF THE ABOVE' function identically but I included both due to popular request.
	That's it.  Go forth and populate Exam.txt and start making midterms more efficently.
	
Enjoy,

Mikey