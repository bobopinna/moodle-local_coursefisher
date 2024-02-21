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

// Default strings.
$string['pluginname'] = 'Course Fisher';
$string['subplugintype_coursefisherbackend'] = 'Backend';
$string['subplugintype_coursefisherbackend_plural'] = 'Backend';

// Availability strings.
$string['coursefisher:addallcourses'] = 'Aggiungere tutti i corsi attivabili con il Course Fisher';
$string['coursefisher:addcourses'] = 'Aggiungere i propri corsi attivabili con il Course Fisher';

// Settings strings.
$string['generalsettings'] = 'Impostazioni generali';
$string['title'] = 'Titolo';
$string['configtitle'] = 'Il titolo della sezione del blocco Amministrazione dove gli utenti trovano gli strumenti Course Fisher';
$string['coursehelplink'] = 'URL della pagina di aiuto';
$string['configcoursehelplink'] = 'Se definito un link o un pulsante viene mostrato  vicino agli strumenti del Course Fisher';

$string['backendsettings'] = 'Impostazioni backend';
$string['backend'] = 'Backend';
$string['configbackend'] = 'Tipo di Backend';
$string['locator'] = 'Sorgente (URL)';
$string['configlocator'] = 'Es. file://path or mysql:username:password@host:port/database/table usare più righe per usare più sorgenti in ordine';
$string['parameters'] = 'Parametri da passare';
$string['configparameters'] = 'Es. query o filtri get. Usare [%campo%] per sostituire i campi utente, p.es. [%uidnumber%]';
$string['separator'] = 'separatore';
$string['configseparator'] = 'Separatore dei campi, usato solo dove serve (es. csv)';
$string['firstrow'] = 'Salta la prima riga';
$string['configfirstrow'] = 'se contiene la lista dei campi CSV';
$string['fieldlist'] = 'Lista dei campi ricevuti';
$string['configfieldlist'] = 'La lista dei campi ricevuti, uno per riga nell\'ordine in cui vengono ricevuti';
$string['configcoursefields'] = '<strong>Nota:</strong><br />I campi nella "Lista dei campi ricevuti" possono essere combinati utilizando la notazione [%campo%].<br />I campi numerici possono utilizzare le notazioni [%campo+numero%] e [%campo-numero%] per modificare il valore. I campi alfanumerici possono essere troncati all\'ennesimo carattere utilizzando [%campo#n%].<br />Esempio: "iCorsi [%dipartimento#10%] - [%anno%]/[%anno+1%]"';
$string['fieldtest'] = 'Valori per i test';
$string['configfieldtest'] = 'Valori per i test del backend, uno per riga nel formato [%campo%]:valore';
$string['configurationtest'] = 'Test della configurazione';

$string['coursesettings'] = 'Impostazioni corso';
$string['fieldlevel'] = 'Lista livelli categorie';
$string['configfieldlevel'] = 'Un livello per riga. Il primo livello di categoria nella prima riga. Puoi usare la notazione "ID categoria=>Nome Categoria" per assegnare un ID alla categoria';
$string['courseidnumber'] = 'ID corso';
$string['coursefullname'] = 'Nome corso';
$string['courseshortname'] = 'Nome breve corso';
$string['actions'] = 'Azioni';
$string['configactions'] = 'Seleziona cosa pu&ograve; scegliere di fare il docente dopo la creazione del corso';
$string['sortcoursesby'] = 'Ordina corsi';
$string['configsortcoursesby'] = 'Come devono essere ordinati i corsi nelle categorie';
$string['autocreation'] = 'Creazione automatica corsi';
$string['configautocreation'] = 'Se il backend selezionato lo supporta, questo abilita la creazione automatica dei corsi.';

$string['coursesgroupsettings'] = 'Impostazioni gruppi di corsi';
$string['grouprule'] = 'Regola gruppo';
$string['configgrouprule'] = 'Questo definisce la regola di confronto per riuniore il corso padre con i figli. Esempio: [%codice_padre%]=[%anno%]-[%codice_cdl%]-[%codice_insegnamento%]';
$string['forceonlygroups'] = 'Forza la creazione del gruppo';
$string['configforceonlygroups'] = 'Se abilitata i docenti possono creare solo i corsi singoli e i gruppi di corsi. Se un corso èparte di un gruppo non pu&ograve; essere creato come cors a se stante.';
$string['linktype'] = 'Collegamento corsi figlio';
$string['configlinktype'] = 'I collegamenti ai corsi figlio devono essere creati con il metodo selezionato';
$string['meta'] = 'Connessi con il metodo di iscrizione Meta Link nel corso padre';
$string['guest'] = 'Connessi con l\'accesso agli ospiti attivato per i corsi figli';
$string['noguestormeta'] = 'I plugin di iscrizione Accesso ospiti o Meta corso per utilizzare la creazione dei gruppi di corsi';
$string['linkedcoursecategory'] = 'Categoria corsi figlio';
$string['configlinkedcoursecategory'] = 'Definisci questo per creare un\'unica categoria che contenga tutti i corsi di figlio, in assegna i corsi figlio verranno creati nelle categorie definite dal backend. Esempio: "Corsi figlio [%dipartimento#10%] - [%anno%]/[%anno+1%]"';

$string['coursecontentsettings'] = 'Impostazioni contenuto corso';
$string['coursesummary'] = 'Descrizione corso';
$string['configcoursesummary'] = 'Questo testo verr&agrave; inserito come descrizione del corso. I campi recuperati dal backend possono essere usati per personalizzarlo.';
$string['sectionzero'] = 'Titolo sezione generale del corso';
$string['configsectionzero'] = 'Se definito, i titoli della sezione generale dei nuovi corsi sar&agrave; impostato. I campi recuperati dal backend possono essere usati per personalizzarlo';
$string['educationalofferlink'] = 'Link Informazioni del corso';
$string['configeducationalofferlink'] = 'Una risorsa URL sar&agrave; aggiunta alla sezione generale di ogni corso creato. Pu&ograve; essere utile per collegare alla pagina pubblica di presentazione del corso.';
$string['coursetemplate'] = 'Nome breve corso modello';
$string['configcoursetemplate'] = 'Il nome breve del corso che sar&grave; utilizzato come modello.';

$string['notifysettings'] = 'Impostazioni notifiche';
$string['notifycoursecreation'] = 'Invia la mail di avviso creazione corso a';
$string['confignotifycoursecreation'] = 'Invia la notifica di creazione corso agli utenti selezionati.';
$string['emailcondition'] = 'Notifica se';
$string['configemailcondition'] = 'Una notifica verr&agrave; inviata agli utenti che soddisfano questa regola.';

$string['filter'] = 'Visibilità';
$string['coursefisherwill'] = 'Course Fisher verr&agrave;';
$string['shown'] = 'Mostrato';
$string['hidden'] = 'Nascosto';
$string['disabled'] = 'Disabilitato';
$string['ifuserprofilefield'] = 'se il campo del profilo utente';
$string['nouserfilterset'] = 'Nessun filtro definito';

// Plugin interface strings.
$string['addcourses'] = 'Aggiungi corsi';
$string['addcourse'] = 'Aggiungi corso';
$string['help'] = 'Aiuto';
$string['notenabled'] = 'Spiacente, non sei abilitato ad aggiungere corsi';
$string['nocourseavailable'] = 'Spiacente non ci sono corsi attivabili';
$string['coursegroup'] = 'Gruppo di corsi';
$string['addcoursegroup'] = 'Aggiungi gruppo di corsi';
$string['addsinglecourse'] = 'Aggiungi corso singolo';
$string['entercourse'] = 'Accedi al corso';
$string['enroltocourse'] = 'Accedi al corso come docente';
$string['availablecourses'] = 'Corsi creabili';
$string['existentcourses'] = 'Corsi esistenti';
$string['backendtestpage'] = 'Test backend';
$string['backendready'] = 'Backend funzionante';
$string['backendemptydata'] = 'Il backend non ha restituito dati, si prega di controllare la configurazione';
$string['backendconfigerror'] = 'Errore di configurazione Backend';
$string['backendfailure'] = 'Non &egrave; possibile collegarsi al backend per il recupero dei corsi';
$string['backendnotinstalled'] = 'Il backend selezionato non &egrave; installato';
$string['backendnotset'] = 'Nessun backend configurato, si prega di selezionarne uno e salvare la configurazione prima di effetuare il test';
$string['configerrors'] = 'Sono stati rilevati alcuni errori di configurazione';
$string['coursesautocreationtask'] = 'Task per la creazione automatica corsi';
$string['edit'] = 'Modifica le impostazioni corso';
$string['view'] = 'Visualizza il corso';
$string['import'] = 'Importa dati da un altro corso';
$string['coursenotfound'] = 'Corso non disponibile';
$string['courselink'] = 'Corso collegato';
$string['courselinkmessage'] = 'Questo corso &egrave; collegato con il corso di {$a}. Cliccare sul link qui sotto.';
$string['choosewhatadd'] = 'Scegli cosa aggiungere:';
$string['choosenextaction'] = 'Cosa vuoi fare dopo aver creato il corso:';
$string['execute'] = 'Esegui';

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
$string['existentcourse'] = 'Questo corso è già stato creato';
$string['privacy:metadata'] = 'Il plugin Course Fisher recupera i dati dal backend, crea i corsi e assegna il ruolo docente. Non salva alcun dato.';
