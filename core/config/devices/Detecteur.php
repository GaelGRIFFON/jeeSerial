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
    'detecteur' => array(
        'name' => 'Detecteur Securité',
        'isVisible' => 1,
        'isEnable' => 1,
        'category' => array('security' => '1'),
        'commands' => array(
            array('name' => 'Etat', 'type' => 'info', 'subtype' => 'binary', 'isVisible' => 1, 'isHistorized' => 0, 'unite' => '', 'eventOnly' => 1,
				'logicalId' => 'E',
                'template' => '{"dashboard":"alert","mobile":"alert"}',
				'configuration' => array(
                    'deltaAdresse' => '00'
                )
            ),
            array('name' => 'lock', 'type' => 'action', 'subtype' => 'other', 'isVisible' => 1, 'isHistorized' => 0, 'unite' => '', 'eventOnly' => 0,
                'logicalId' => 'I',
                'value' => 'Inhibition',
                'template' => '{"dashboard":"smallLock","mobile":"lock"}',
				'configuration' => array(
                    'deltaAdresse' => '40'
                )
            ),
			array('name' => 'unlock', 'type' => 'action', 'subtype' => 'other', 'isVisible' => 1, 'isHistorized' => 0, 'unite' => '', 'eventOnly' => 0,
                'logicalId' => 'D',
                'value' => 'Inhibition',
                'template' => '{"dashboard":"smallLock","mobile":"lock"}',
				'configuration' => array(
                    'deltaAdresse' => '50'
                )
            ),
			array('name' => 'Inhibition', 'type' => 'info', 'subtype' => 'binary', 'isVisible' => 0, 'isHistorized' => 0, 'unite' => '', 'eventOnly' => 1,
				'logicalId' => 'F',
				'configuration' => array(
                    'deltaAdresse' => '10'
                )
            )
        )
    )
);
?>
