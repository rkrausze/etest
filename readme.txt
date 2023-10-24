======================================
The E-Test module for Moodle 2.7 - 4.1
======================================

    The E-Test-Module enables the teacher to create sequences of exercises/tasks/test (made by EF-Editor) that can be
    attempted under controled conditions.
    The teacher makes a set of exercises/tasks via EF-Editor. These can be assembled in E-Test-module in small pools
    with additional conditions.
    If a student attempts the E-Test, he/she gets a randomly sellected collection of exercises from these pools.
    An attempt results in a grade (german: "Einstufung" - thats why "E"-Test, derived from german "Einstufungstest").
    All results of the students can be reviewed and detailed investigated via the comfortable "protocol" functionality.
    If an exercise contained an error it is even possiple to correct this exercise afterward and recalulate the whole
    results of the students.
    To reduce the amount of attemps in protocol you can archive them using special archive tags.
    To perform E-Tests with students that have no own moodle accounts you can use "subusers" and do all the E-Test via
    only one moodle login. 

    Further information about E-Test can be found under http://studierplatz2000.tu-dresden.de.

    This plugin is distributed under the terms of the General Public License
    (see http://www.gnu.org/licenses/gpl.txt for details)

    This software is provided "AS IS" without a warranty of any kind.

    This version (2016012200) is stable. If there are errors or problems pleese contact me via mail mail@krausze.de or
    use https://sourceforge.net/p/studierplatz/tickets/new with sub project "Moodle-Module-E-Test".

    The version for Moodle 1.7 - 1.9 can be found under

        TODO correct link http://moodle.org/mod/data/view.php?d=13&rid=4193.

    Working examples (but still Moodle 1.9 and in german language) can be visited under

        http://poolux.psychopool.tu-dresden.de/moodle/course/view.php?id=24

    (login as guest)

    The software to generate the exercises ("EF-Editor") can be obtained under
    http://sourceforge.net/projects/studierplatz/files/EF-Editor/efb_install.exe .


======================================
To INSTALL or UPDATE the E-Test module
======================================

    1. download zip file for this plugin from either of the following locations:

        (i) the Moodle.org plugins repository
        (ii) http://sourceforge.net/projects/studierplatz/

    2. unzip the zip file, to create a folder called "etest"

    3. upload this "etest" folder to "mod/etest" on your Moodle 2.x site

    4. log in to Moodle as administrator to initiate install/upgrade

        if install/upgrade does not begin automatically, you can initiate it manually by navigating to the following link:
        Settings -> Site administration -> Notifications


======================================
To add an E-Test activity to your Moodle course
======================================

    0. Create and compile some exercises by EF-Editor using an E-Test shape

    1. Login to Moodle, and navigate to a course page

    2. Enable "Edit mode" on course page

    3. Locate course topic/week where you wish to add the Studierplatz

    4. Select "E-Test" from "Add an activity" menu

    5. Fill out the form. Add the exercises.

    6. click "Save changes" at bottom of page
