<?php
#Application name: PhpCollab
#Status page: 2
#Path by root: ../languages/help_da.php

//translator(s): mahuni (Pandaen)
$help["setup_mkdirMethod"] = "Hvis safe-mode er sat til, bliver du nødt til at konfigurere en ftp-adgang, for at kunne oprette mapper med fil-redigeringen.";
$help["setup_notifications"] = "E-mail påmindelser (Opgaver, nye beskeder, opgaveændringer og så videre.)<br />Godkendt smtp/sendmail er nødvendig.";
$help["setup_forcedlogin"] = "Ved fejl, nægt eksternt link med login og password i adresse(url)";
$help["setup_langdefault"] = "Vælg sprog som skal bruges ved login, eller lad den forblive blank, og sprog vil blive valgt ud fra browserindstillinger.";
$help["setup_myprefix"] = "Ret denne værdi, hvis du har tabeller med samme navn i din database.<br><br>assignments<br>bookmarks<br>bookmarks_categories<br>calendar<br>files<br>logs<br>members<br>notes<br>notifications<br>organizations<br>phases<br>posts<br>projects<br>reports<br>sorting<br>subtasks<br>support_posts<br>support_requests<br>tasks<br>teams<br>topics<br>updates<br><br>Efterlad blank, hvis du ikke ønsker at bruge et præfix.";
$help["setup_loginmethod"] = "Metode til at gemme kodeord i database.<br>&quot;Kryptér&quot; for at CVS autentifikation og htaccess autentifikation skal virke (Hvis CVS support og/eller htaccess autentifikation er tilladt).";
$help["admin_update"] = "Følg præcist den rigtige rækkefølge, for at opdatere din udgave<br>1. Ændre opsætning (Supplement til nye parametre)<br>2. Ændre database (Opdatér som følge af din nye udgave)";
$help["task_scope_creep"] = "Forskel i dage mellem planlagt dato og færdig dato (Fed, hvis positiv værdi)";
$help["max_file_size"] = "Maksimum størrelse på fil-upload";
$help["project_disk_space"] = "Total størrelse af filer i dette projekt";
$help["project_scope_creep"] = "Forskel i dage mellem planlagt dato og færdig dato (Fed, hvis positiv værdi). Totalt for alle opgaver.";
$help["mycompany_logo"] = "Upload dit firmas logo, dette vil blive vist i stedet for navn, i header";
$help["calendar_shortname"] = "Værdi ved månedlig visning i kalender. Tvungen";
$help["user_autologout"] = "Tid i sekunder, før man bliver logget af på grund af manglende aktivitet. 0 for at deaktivere.";
$help["user_timezone"] = "Vælg din GMT tidszone";
//2.4
$help["setup_clientsfilter"] = "Filtrér for kun at se logførte brugere";
$help["setup_projectsfilter"] = "Filtrér for kun at se projekt, hvis brugere er en del af en gruppe.";
//2.5
$help["setup_notificationMethod"] = "Sæt metode for at sende email notifikationer: med intern php mail funktion (kræver en smtp server eller sendmail konfigureret i phps parametre) eller med en personlig smtp server";
