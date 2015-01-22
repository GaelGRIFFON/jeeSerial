<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

global $deviceConfiguration;
$deviceConfiguration = array(
    'volet' => array(
        'name' => 'Volet Roulant',
        'isVisible' => 1,
        'isEnable' => 1,
        'category' => array('automatism' => '1'),
        'commands' => array(
            array('name' => 'on_UP', 'type' => 'action', 'subtype' => 'other', 'isVisible' => 1, 'isHistorized' => 0, 'unite' => '', 'eventOnly' => 0,
                'logicalId' => 'M',
                'value' => 'Etat_UP',
                'display' => '{"icon":"<i class=\"fa fa-arrow-up\"><\/i>"}',
				'configuration' => array(
                    'deltaAdresse' => '20'
                )
            ),
            array('name' => 'off_UP', 'type' => 'action', 'subtype' => 'other', 'isVisible' => 1, 'isHistorized' => 0, 'unite' => '', 'eventOnly' => 0,
                'logicalId' => 'A',
                'value' => 'Etat_UP',
                'display' => '{"icon":"<i class=\"fa fa-stop\"><\/i>"}',
				'configuration' => array(
                    'deltaAdresse' => '30'
                )
            ),
            array('name' => 'Etat_UP', 'type' => 'info', 'subtype' => 'binary', 'isVisible' => 0, 'isHistorized' => 0, 'unite' => '', 'eventOnly' => 1,
				'logicalId' => 'E',
				'configuration' => array(
                    'deltaAdresse' => '00'
                )
            ),
            array('name' => 'on_DOWN', 'type' => 'action', 'subtype' => 'other', 'isVisible' => 1, 'isHistorized' => 0, 'unite' => '', 'eventOnly' => 0,
                'logicalId' => 'M',
                'value' => 'Etat_DOWN',
                'display' => '{"icon":"<i class=\"fa fa-arrow-down\"><\/i>"}',
				'configuration' => array(
                    'deltaAdresse' => '21'
                )
            ),
            array('name' => 'off_DOWN', 'type' => 'action', 'subtype' => 'other', 'isVisible' => 1, 'isHistorized' => 0, 'unite' => '', 'eventOnly' => 0,
                'logicalId' => 'A',
                'value' => 'Etat_DOWN',
                'display' => '{"icon":"<i class=\"fa fa-stop\"><\/i>"}',
				'configuration' => array(
                    'deltaAdresse' => '31'
                )
            ),
            array('name' => 'Etat_DOWN', 'type' => 'info', 'subtype' => 'binary', 'isVisible' => 0, 'isHistorized' => 0, 'unite' => '', 'eventOnly' => 1,
				'logicalId' => 'E',
				'configuration' => array(
                    'deltaAdresse' => '01'
                )
            ),
			array('name' => 'lock_UP', 'type' => 'action', 'subtype' => 'other', 'isVisible' => 1, 'isHistorized' => 0, 'unite' => '', 'eventOnly' => 0,
                'logicalId' => 'I',
                'value' => 'Inhibition_UP',
                'template' => '{"dashboard":"smallLock","mobile":"lock"}',
				'configuration' => array(
                    'deltaAdresse' => '40'
                )
            ),
			array('name' => 'unlock_UP', 'type' => 'action', 'subtype' => 'other', 'isVisible' => 1, 'isHistorized' => 0, 'unite' => '', 'eventOnly' => 0,
                'logicalId' => 'D',
                'value' => 'Inhibition_UP',
                'template' => '{"dashboard":"smallLock","mobile":"lock"}',
				'configuration' => array(
                    'deltaAdresse' => '50'
                )
            ),
			array('name' => 'lock_DOWN', 'type' => 'action', 'subtype' => 'other', 'isVisible' => 1, 'isHistorized' => 0, 'unite' => '', 'eventOnly' => 0,
                'logicalId' => 'I',
                'value' => 'Inhibition_DOWN',
                'template' => '{"dashboard":"smallLock","mobile":"lock"}',
				'configuration' => array(
                    'deltaAdresse' => '41'
                )
            ),
			array('name' => 'unlock_DOWN', 'type' => 'action', 'subtype' => 'other', 'isVisible' => 1, 'isHistorized' => 0, 'unite' => '', 'eventOnly' => 0,
                'logicalId' => 'D',
                'value' => 'Inhibition_DOWN',
                'template' => '{"dashboard":"smallLock","mobile":"lock"}',
				'configuration' => array(
                    'deltaAdresse' => '51'
                )
            ),            
			array('name' => 'Inhibition_UP', 'type' => 'info', 'subtype' => 'binary', 'isVisible' => 0, 'isHistorized' => 0, 'unite' => '', 'eventOnly' => 1,
				'logicalId' => 'F',
				'configuration' => array(
                    'deltaAdresse' => '10'
                )
            ),
			array('name' => 'Inhibition_DOWN', 'type' => 'info', 'subtype' => 'binary', 'isVisible' => 0, 'isHistorized' => 0, 'unite' => '', 'eventOnly' => 1,
				'logicalId' => 'F',
				'configuration' => array(
                    'deltaAdresse' => '11'
                )
            )
        )
    )
);
?>
