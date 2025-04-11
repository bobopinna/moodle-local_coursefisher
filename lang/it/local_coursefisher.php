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

$string['actions'] = 'Azioni';
$string['addcourse'] = 'Aggiungi corso';
$string['addcoursegroup'] = 'Aggiungi gruppo di corsi';
$string['addcourses'] = 'Aggiungi corsi';
$string['addsinglecourse'] = 'Aggiungi corso singolo';
$string['autocreation'] = 'Creazione automatica corsi';
$string['availablecourses'] = 'Corsi creabili';
$string['backend'] = 'Backend';
$string['backendconfigerror'] = 'Errore di configurazione Backend';
$string['backendemptydata'] = 'Il backend non ha restituito dati, si prega di controllare la configurazione';
$string['backendfailure'] = 'Non &egrave; possibile collegarsi al backend per il recupero dei corsi';
$string['backendnotinstalled'] = 'Il backend selezionato non &egrave; installato';
$string['backendnotset'] = 'Nessun backend configurato, si prega di selezionarne uno e salvare la configurazione prima di effetuare il test';
$string['backendready'] = 'Backend funzionante';
$string['backendsettings'] = 'Impostazioni backend';
$string['backendtestpage'] = 'Test backend';
$string['chooseexistsaction'] = 'Alcuni corsi del gruppo di corsi risultano già esistenti. Cosa vuoi fare con questi corsi?';
$string['choosenextaction'] = 'Cosa vuoi fare dopo aver creato il corso:';
$string['choosewhatadd'] = 'Scegli cosa aggiungere:';
$string['configactions'] = 'Seleziona cosa pu&ograve; scegliere di fare il docente dopo la creazione del corso';
$string['configautocreation'] = 'Se il backend selezionato lo supporta, questo abilita la creazione automatica dei corsi.';
$string['configbackend'] = 'Tipo di Backend';
$string['configcoursefields'] = '<strong>Nota:</strong><br />I campi nella "Lista dei campi ricevuti" possono essere combinati utilizando la notazione [%campo%].<br />I campi numerici possono utilizzare le notazioni [%campo+numero%] e [%campo-numero%] per modificare il valore. I campi alfanumerici possono essere troncati all\'ennesimo carattere utilizzando [%campo#n%].<br />Esempio: "iCorsi [%dipartimento#10%] - [%anno%]/[%anno+1%]"';
$string['configcoursehelplink'] = 'Se definito un link o un pulsante viene mostrato  vicino agli strumenti del Course Fisher';
$string['configcoursesummary'] = 'Questo testo verr&agrave; inserito come descrizione del corso. I campi recuperati dal backend possono essere usati per personalizzarlo.';
$string['configcoursetemplate'] = 'Il nome breve del corso che sar&grave; utilizzato come modello.';
$string['configeducationalofferlink'] = 'Una risorsa URL sar&agrave; aggiunta alla sezione generale di ogni corso creato. Pu&ograve; essere utile per collegare alla pagina pubblica di presentazione del corso.';
$string['configemailcondition'] = 'Una notifica verr&agrave; inviata agli utenti che soddisfano questa regola.';
$string['configerrors'] = 'Sono stati rilevati alcuni errori di configurazione';
$string['configfieldlevel'] = 'Un livello per riga. Il primo livello di categoria nella prima riga. Puoi usare la notazione "ID categoria=>Nome Categoria" per assegnare un ID alla categoria';
$string['configfieldlist'] = 'La lista dei campi ricevuti, uno per riga nell\'ordine in cui vengono ricevuti';
$string['configfieldtest'] = 'Valori per i test del backend, uno per riga nel formato [%campo%]:valore';
$string['configfirstrow'] = 'se contiene la lista dei campi CSV';
$string['configforceonlygroups'] = 'Se abilitata i docenti possono creare solo i corsi singoli e i gruppi di corsi. Se un corso èparte di un gruppo non pu&ograve; essere creato come cors a se stante.';
$string['configgrouprule'] = 'Questo definisce la regola di confronto per riuniore il corso padre con i figli. Esempio: [%codice_padre%]=[%anno%]-[%codice_cdl%]-[%codice_insegnamento%]';
$string['configlinkedcoursecategory'] = 'Definisci questo per creare un\'unica categoria che contenga tutti i corsi di figlio, in assegna i corsi figlio verranno creati nelle categorie definite dal backend. Esempio: "Corsi figlio [%dipartimento#10%] - [%anno%]/[%anno+1%]"';
$string['configlinktype'] = 'I collegamenti ai corsi figlio devono essere creati con il metodo selezionato';
$string['configlocator'] = 'Es. file://path or mysql:username:password@host:port/database/table usare più righe per usare più sorgenti in ordine';
$string['confignotifycoursecreation'] = 'Invia la notifica di creazione corso agli utenti selezionati.';
$string['configparameters'] = 'Es. query o filtri get. Usare [%campo%] per sostituire i campi utente, p.es. [%uidnumber%]';
$string['configsectionzero'] = 'Se definito, i titoli della sezione generale dei nuovi corsi sar&agrave; impostato. I campi recuperati dal backend possono essere usati per personalizzarlo';
$string['configseparator'] = 'Separatore dei campi, usato solo dove serve (es. csv)';
$string['configsortcoursesby'] = 'Come devono essere ordinati i corsi nelle categorie';
$string['configtitle'] = 'Il titolo della sezione del blocco Amministrazione dove gli utenti trovano gli strumenti Course Fisher';
$string['configurationtest'] = 'Test della configurazione';
$string['coursecontentsettings'] = 'Impostazioni contenuto corso';
$string['coursefisher:addallcourses'] = 'Aggiungere tutti i corsi attivabili con il Course Fisher';
$string['coursefisher:addcourses'] = 'Aggiungere i propri corsi attivabili con il Course Fisher';
$string['coursefisherwill'] = 'Course Fisher verr&agrave;';
$string['coursefullname'] = 'Nome corso';
$string['coursegroup'] = 'Gruppo di corsi';
$string['coursehelplink'] = 'URL della pagina di aiuto';
$string['courseidnumber'] = 'ID corso';
$string['courselink'] = 'Corso collegato';
$string['courselinkmessage'] = 'Questo corso &egrave; collegato con il corso di {$a}. Cliccare sul link qui sotto.';
$string['coursenotfound'] = 'Corso non disponibile';
$string['coursenotifyhtml'] = 'Gentile Amministratore,<br />
è necessario verificare il nuovo corso<br />
<b>{$a->coursefullname}</b><br /><br />
link al Corso: <a href="{$a->courseurl}">{$a->courseurl}</a>';
$string['coursenotifyhtmlcomplete'] = 'Gentile Amministratore,<br />
è necessario verificare il nuovo corso<br />
<b>{$a->coursefullname}</b><br /><br />
link al Corso: <a href="{$a->courseurl}">{$a->courseurl}</a><br />
link alla pagina dell\'offerta formativa: <a href="{$a->educationalofferurl}">{$a->educationalofferurl}</a>';
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
$string['coursesautocreationtask'] = 'Task per la creazione automatica corsi';
$string['coursesettings'] = 'Impostazioni corso';
$string['coursesgroupsettings'] = 'Impostazioni gruppi di corsi';
$string['courseshortname'] = 'Nome breve corso';
$string['coursesummary'] = 'Descrizione corso';
$string['coursetemplate'] = 'Nome breve corso modello';
$string['disabled'] = 'Disabilitato';
$string['edit'] = 'Modifica le impostazioni corso';
$string['educationaloffer'] = 'Pagina dell\'offerta Formativa';
$string['educationalofferlink'] = 'Link Informazioni del corso';
$string['educationaloffermessage'] = 'Qui puoi trovare tutte le informazioni sull\'offerta formativa di questo corso';
$string['emailcondition'] = 'Notifica se';
$string['enroltocourse'] = 'Accedi al corso come docente';
$string['entercourse'] = 'Accedi al corso';
$string['execute'] = 'Esegui';
$string['existentcourse'] = 'Questo corso è già stato creato';
$string['existentcourses'] = 'Corsi esistenti';
$string['fieldlevel'] = 'Lista livelli categorie';
$string['fieldlist'] = 'Lista dei campi ricevuti';
$string['fieldtest'] = 'Valori per i test';
$string['filter'] = 'Visibilità';
$string['firstrow'] = 'Salta la prima riga';
$string['forceonlygroups'] = 'Forza la creazione del gruppo';
$string['generalsettings'] = 'Impostazioni generali';
$string['grouprule'] = 'Regola gruppo';
$string['guest'] = 'Connessi con l\'accesso agli ospiti attivato per i corsi figli';
$string['help'] = 'Aiuto';
$string['hidden'] = 'Nascosto';
$string['ifuserprofilefield'] = 'se il campo del profilo utente';
$string['import'] = 'Importa dati da un altro corso';
$string['join'] = 'Uniscili al gruppo di corsi';
$string['linkedcoursecategory'] = 'Categoria corsi figlio';
$string['linktype'] = 'Collegamento corsi figlio';
$string['locator'] = 'Sorgente (URL)';
$string['meta'] = 'Connessi con il metodo di iscrizione Meta Link nel corso padre';
$string['nocourseavailable'] = 'Spiacente non ci sono corsi attivabili';
$string['noguestormeta'] = 'I plugin di iscrizione Accesso ospiti o Meta corso per utilizzare la creazione dei gruppi di corsi';
$string['notenabled'] = 'Spiacente, non sei abilitato ad aggiungere corsi';
$string['notifycoursecreation'] = 'Invia la mail di avviso creazione corso a';
$string['notifysettings'] = 'Impostazioni notifiche';
$string['nouserfilterset'] = 'Nessun filtro definito';
$string['parameters'] = 'Parametri da passare';
$string['pluginname'] = 'Course Fisher';
$string['privacy:metadata'] = 'Il plugin Course Fisher recupera i dati dal backend, crea i corsi e assegna il ruolo docente. Non salva alcun dato.';
$string['sectionzero'] = 'Titolo sezione generale del corso';
$string['separated'] = 'Mantienili separati dal gruppo di corsi';
$string['separator'] = 'separatore';
$string['shown'] = 'Mostrato';
$string['sortcoursesby'] = 'Ordina corsi';
$string['subplugintype_coursefisherbackend'] = 'Backend';
$string['subplugintype_coursefisherbackend_plural'] = 'Backend';
$string['title'] = 'Titolo';
$string['view'] = 'Visualizza il corso';
