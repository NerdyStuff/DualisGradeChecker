# DualisGradeChecker
This is a quick 'n' dirty implementation to check if there are new grades in the dualis dashboard written in PHP

You have to create a cron script or use a similar tool, to execute the script. It uses curL to emulate a browser request. Use your credential for dualis to login and let the tool surf the site. You also have to change the id's in the options array. Therefore you can use the inspect tool of your web browser and take the id's from the dropdownmenu used for the semesters. The correct id's  are in the value tag of the elements. 
