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
 * Settings for the Course Fisher.
 *
 * @package   local_coursefisher
 * @copyright 2014 and above Roberto Pinna, Diego Fantoma, Angelo Calò
 * @copyright 2016 and above Francesco Carbone
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    $ADMIN->add('localplugins', new admin_category('coursefisher', new lang_string('pluginname', 'local_coursefisher')));

    $page = new admin_settingpage('local_coursefisher_general', new lang_string('generalsettings', 'local_coursefisher'));

    $page->add(new admin_setting_configtext('local_coursefisher/title',
                new lang_string('title', 'local_coursefisher'),
                new lang_string('configtitle', 'local_coursefisher'),
                'Course Fisher'));

    $page->add(new admin_setting_configtext('local_coursefisher/course_helplink',
                new lang_string('coursehelplink', 'local_coursefisher'),
                new lang_string('configcoursehelplink', 'local_coursefisher'),
                ''));

    $choices = array();
    $choices['view'] = get_string('view', 'local_coursefisher');
    $choices['edit'] = get_string('edit', 'local_coursefisher');
    $choices['import'] = get_string('import', 'local_coursefisher');
    $defaultchoices = array('view', 'edit', 'import');
    $page->add(new admin_setting_configmultiselect('local_coursefisher/actions',
                new lang_string('actions', 'local_coursefisher'),
                new lang_string('configactions', 'local_coursefisher'),
                 $defaultchoices, $choices));

    $ADMIN->add('coursefisher', $page);

    $page = new admin_settingpage('backend_config', new lang_string('configurationbackend', 'local_coursefisher'));

    $choices = array();
    foreach (core_plugin_manager::instance()->get_plugins_of_type('coursefisherbackend') as $backend) {
        $choices[$backend->name] = $backend->displayname;
    }
    $page->add(new admin_setting_configselect('local_coursefisher/backend',
                new lang_string('backend', 'local_coursefisher'),
                new lang_string('configbackend', 'local_coursefisher'),
                '', $choices));

    $page->add(new admin_setting_configtextarea('local_coursefisher/locator',
                new lang_string('locator', 'local_coursefisher'),
                new lang_string('configlocator', 'local_coursefisher'),
                ''));

    $page->add(new admin_setting_configtextarea('local_coursefisher/parameters',
                new lang_string('parameters', 'local_coursefisher'),
                new lang_string('configparameters', 'local_coursefisher'),
                ''));

    $page->add(new admin_setting_configtext('local_coursefisher/separator',
                new lang_string('separator', 'local_coursefisher'),
                new lang_string('configseparator', 'local_coursefisher'),
                ''));

    $page->add(new admin_setting_configcheckbox('local_coursefisher/firstrow',
                new lang_string('firstrow', 'local_coursefisher'),
                new lang_string('configfirstrow', 'local_coursefisher'),
                0));

    $page->add(new admin_setting_configtextarea('local_coursefisher/fieldlist',
                new lang_string('fieldlist', 'local_coursefisher'),
                new lang_string('configfieldlist', 'local_coursefisher'),
                ''));

    $page->add(new admin_setting_heading('local_coursefisher/configurablefields', '',
                new lang_string('configcoursefields', 'local_coursefisher')));

    $page->add(new admin_setting_configtextarea('local_coursefisher/fieldlevel',
                new lang_string('fieldlevel', 'local_coursefisher'),
                new lang_string('configfieldlevel', 'local_coursefisher'),
                ''));

    $page->add(new admin_setting_configtext('local_coursefisher/course_code',
                new lang_string('courseidnumber', 'local_coursefisher'),
                '',
                ''));

    $page->add(new admin_setting_configtext('local_coursefisher/course_fullname',
                new lang_string('coursefullname', 'local_coursefisher'),
                '',
                ''));

    $page->add(new admin_setting_configtext('local_coursefisher/course_shortname',
                new lang_string('courseshortname', 'local_coursefisher'),
                '',
                ''));

    $page->add(new admin_setting_configtextarea('local_coursefisher/fieldtest',
                new lang_string('fieldtest', 'local_coursefisher'),
                new lang_string('configfieldtest', 'local_coursefisher'),
                ''));

    $backendtestlink = html_writer::tag('a', new lang_string('configurationtest', 'local_coursefisher'),
                array('href' => new moodle_url('/local/coursefisher/backend/test.php')));
    $page->add(new admin_setting_heading('local_coursefisher/backendtestlink', '', $backendtestlink));

    $ADMIN->add('coursefisher', $page);

    $page = new admin_settingpage('groups', 'Generazione di gruppi di corsi');

    $page->add(new admin_setting_configtext('local_coursefisher/course_group',
                'Raggruppamento corsi',
                'Campo_Codice_del_corso_Padre=Combinazione di codici o singolo codice che identifica univocamente il corso (in genere stesso valore che si mette nel campo codice corso)<br>es:[%mut_padre_cod%]=[%aa_offerta%]-[%cds_cod%]-[%pds_cod%]-[%aa_regdid%]-[%af_cod%]-[%partizione_codice%]',
                ''));

    $page->add(new admin_setting_configcheckbox('local_coursefisher/forceonlygroups',
                'Creazione solo gruppi di corsi',
                'Forza la creazione solo dei gruppi di corsi, i docenti non potranno creare corsi figli singolarmente. I corsi singoli potranno essere creati comunque.',
                0));

    $choices = array();
    $choices['meta'] = get_string('meta', 'local_coursefisher');
    $choices['guest'] = get_string('guest', 'local_coursefisher');
    $page->add(new admin_setting_configselect('local_coursefisher/linktype',
                'Collegamento ai corsi figli',
                'L\'accesso dai corsi figli al corso padre deve avvenire tramite',
                'meta', $choices));

    $page->add(new admin_setting_configtext('local_coursefisher/linked_course_category',
                'Isola corsi figli in una categoria a parte',
                'es. query o filtri get. Usare [%campo%] per sostituire i campi utente, p.es. [%uidnumber%]',
                ''));

    $ADMIN->add('coursefisher', $page);

    $page = new admin_settingpage('templating', 'Impostazioni di base del corso');

    $page->add(new admin_setting_configtextarea('local_coursefisher/course_summary',
                'Introduzione al corso',
                'Testo da usare come descrizione dei nuovi corsi',
                ''));

    $page->add(new admin_setting_configtext('local_coursefisher/sectionzero_name',
                'Nome della prima sezione',
                'Nome della prima sezione',
                ''));

    $page->add(new admin_setting_configtext('local_coursefisher/educationaloffer_link',
                'Formato del link alla scheda dell\'insegnamento',
                'Formato del link alla scheda dell\'insegnamento. Se vuoto il link non verr&agrave; creato',
                ''));

    $page->add(new admin_setting_configtext('local_coursefisher/course_template',
                'Nome breve template',
                'Se indicato, il contenuto del corso corrispondente verr&agrave; importato nel nuovo spazio',
                ''));

    $ADMIN->add('coursefisher', $page);

    $page->add(new admin_setting_configcheckbox('local_coursefisher/autocreation',
                'Creazione automatica corsi',
                'Se il backend lo prevede, è pssibile abilitare la creazione automatica dei corsirecuperati dal backend ad ogni esecuzione del cron ',
                0));

    $page = new admin_settingpage('local_coursefisher_notifications', new lang_string('notifysettings', 'local_coursefisher'));

    $page->add(new admin_setting_configtextarea('local_coursefisher/email_condition',
                'Condizione per invio mail ad account di supporto',
                'es. query o filtri get. Usare [%campo%] per sostituire i campi utente, p.es. [%uidnumber%]',
                ''));
    $page->add(new admin_setting_users_with_capability('local_coursefisher/notifycoursecreation',
                new lang_string('notifycoursecreation', 'local_coursefisher'),
                new lang_string('confignotifycoursecreation', 'local_coursefisher'),
                array(), 'local/coursefisher:addallcourses'));

    $ADMIN->add('coursefisher', $page);

    $page = new admin_settingpage('filter_config', new lang_string('filter', 'local_coursefisher'));

    $choices = array();
    $choices['shown'] = new lang_string('shown', 'local_coursefisher');
    $choices['hidden'] = new lang_string('hidden', 'local_coursefisher');
    $page->add(new admin_setting_configselect('local_coursefisher/display',
                new lang_string('coursefisherwill', 'local_coursefisher'),
                '',
                'shown', $choices));

    $fieldnames = array('lastname', 'firstname', 'username', 'email', 'city', 'idnumber', 'institution', 'department', 'address');
    $fields = array('' => get_string('choose'));
    foreach ($fieldnames as $fieldname) {
        $fields[$fieldname] = get_string($fieldname);
    }

    $customfields = $DB->get_records('user_info_field');
    if (!empty($customfields)) {
        foreach ($customfields as $customfield) {
            if (in_array($customfield->datatype, array('text', 'menu', 'checkbox'))) {
                $fields[$customfield->shortname] = $customfield->name;
            }
        }
    }

    $page->add(new admin_setting_configselect('local_coursefisher/userfield',
                new lang_string('ifuserprofilefield', 'local_coursefisher'),
                '',
                '', $fields));

    $operators = array('contains' => get_string('contains', 'filters'),
                       'doesnotcontain' => get_string('doesnotcontain', 'filters'),
                       'isequalto' => get_string('isequalto', 'filters'),
                       'isnotequalto' => get_string('isnotequalto', 'filters'),
                       'startswith' => get_string('startswith', 'filters'),
                       'endswith' => get_string('endswith', 'filters'));

    $page->add(new admin_setting_configselect('local_coursefisher/operator',
                '',
                '',
                'contains', $operators));

    $page->add(new admin_setting_configtext('local_coursefisher/matchvalue',
                '',
                '',
                ''));

    $ADMIN->add('coursefisher', $page);
}

