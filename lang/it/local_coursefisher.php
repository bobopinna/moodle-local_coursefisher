<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'local_coursefisher', language 'it'
 *
 * @package   local_coursefisher
 * @copyright 2014 and above Roberto Pinna, Diego Fantoma and Angelo Calò
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['coursefisher:addallcourses'] = 'Aggiungere tutti i corsi attivabili con il Course Fisher';
$string['coursefisher:addcourses'] = 'Aggiungere i propri corsi attivabili con il Course Fisher';
$string['pluginname'] = 'Course Fisher';
$string['configtitle'] = 'Titolo';
$string['courseguides'] = 'Guide corsi';
$string['courseregisters'] = 'Registri corsi';
$string['addcourses'] = 'Aggiungi corsi';
$string['addcourse'] = 'Aggiungi corso';
$string['nocourseavailable'] = 'Non ci sono corsi disponibili';
$string['coursegroup'] = 'Gruppo di corsi';
$string['addcoursegroup'] = 'Aggiungi gruppo di corsi';
$string['addsinglecourse'] = 'Aggiungi corso singolo';
$string['entercourse'] = 'Accedi al corso';
$string['enroltocourse'] = 'Accedi al corso come docente';
$string['availablecourses'] = 'Corsi creabili';
$string['existentcourses'] = 'Corsi esistenti';
$string['backendfailure'] = 'Non &egrave; possibile collegarsi al backend per il recupero dei corsi';
$string['edit'] = 'Modifica le impostazioni corso';
$string['view'] = 'Visualizza il corso';
$string['import'] = 'Importa dati da un altro corso';
$string['coursenotfound'] = 'Corso non disponibile';
$string['filter'] = 'Filtro utenti';
$string['shown'] = 'Mostrato';
$string['hidden'] = 'Nascosto';
$string['nouserfilterset'] = 'Nessun filtro definito';
$string['ifuserprofilefield'] = 'se il campo del profilo utente';
$string['nocourseavailable'] = 'Spicente non ci sono corsi attivabili';
$string['courselink'] = 'Corso collegato';
$string['courselinkmessage'] = 'Questo corso &egrave; collegato con il corso di {$a}. Cliccare sul link qui sotto.';
$string['choosewhatadd'] = 'Scegli cosa aggiungere:';
$string['choosenextaction'] = 'Cosa vuoi fare dopo aver creato il corso:';
$string['execute'] = 'Esegui';
$string['linkhelppage'] = 'URL della pagina di help';
$string['insertlink'] = 'Inserisci il link';
$string['configurationtest'] = 'Test della configurazione';
$string['configurationbackend'] = 'Configurazione del backend';
$string['backend'] = 'Backend';
$string['backendtype'] = 'Tipo di Backend';
$string['locatorurl'] = 'Sorgente (URL)';
$string['sourceformat'] = 'eg. file://path or mysql:username:password@host:port/database/table usare più righe per usare più sorgenti in ordine';
$string['parameters'] = 'Parametri da passare';
$string['parametersformat'] = 'es. query o filtri get. Usare [%campo%] per sostituire i campi utente, p.es. [%uidnumber%]';
$string['testvalue'] = 'Valori per i test';
$string['testvalueformat'] = 'uno per riga in forma [CAMPO]:valore';
$string['separator'] = 'separatore';
$string['separatoruse'] = 'separatore dei campi, usato solo dove serve (es. csv)';
$string['firstrow'] = 'Salta la prima riga';
$string['firstrowcontent'] = 'se contiene la lista dei campi CSV';
$string['fieldlist'] = 'Lista dei campi ricevuti';
$string['fieldlistformat'] = 'uno per riga nell\'ordine in cui vengono ricevuti';
$string['chooseexistsaction'] = 'Alcuni corsi del gruppo di corsi risultano già esistenti. Cosa vuoi fare con questi corsi?';
$string['join'] = 'Uniscili al gruppo di corsi';
$string['separated'] = 'Mantienili separati dal gruppo di corsi';
$string['educationaloffer'] = 'Pagina dell\'offerta Formativa';
$string['educationaloffermessage'] = 'Qui puoi trovare tutte le informazioni sull\'offerta formativa di questo corso';
$string['coursenotifysubject'] = 'Course Fisher - Un nuovo corso creato richiede la tua attenzione!';
$string['coursenotifytext'] = 'Gentile Amministratore,
è necessario verificare il nuovo corso
{$a->coursefullname}

link al Corso: {$a->courseurl}';
$string['coursenotifytextcomplete'] = 'Gentile Amministratore,
è necessario verificare il nuovo corso
{$a->coursefullname}

link al Corso: {$a->courseurl}

link alla pagina dell\'offerta formativa: {$a->educationalofferurl}';
$string['coursenotifyhtml'] = 'Gentile Amministratore,<br />
è necessario verificare il nuovo corso<br />
<b>{$a->coursefullname}</b><br /><br />
link al Corso: <a href="{$a->courseurl}">{$a->courseurl}</a>';
$string['coursenotifyhtmlcomplete'] = 'Gentile Amministratore,<br />
è necessario verificare il nuovo corso<br />
<b>{$a->coursefullname}</b><br /><br />
link al Corso: <a href="{$a->courseurl}">{$a->courseurl}</a><br />
link alla pagina dell\'offerta formativa: <a href="{$a->educationalofferurl}">{$a->educationalofferurl}</a>';
$string['meta'] = 'Connessi con il metodo di iscrizione Meta Link nel corso padre';
$string['guest'] = 'Connessi con l\'accesso agli ospiti attivato per i corsi figli';
$string['existentcourse'] = 'Questo corso è già stato creato';
$string['notifycoursecreation'] = 'Invia la mail di avviso creazione corso a';
$string['confignotifycoursecreation'] = 'Invia la notifica di creazione corso agli utenti selezionati. La notifica verrà inviata solo agli utenti che hanno il ruolo selezionato.';
$string['subplugintype_coursefisherbackend'] = 'Backend';
$string['subplugintype_coursefisherbackend_plural'] = 'Backend';
